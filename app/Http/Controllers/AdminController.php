<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WebAuthnKey;
use App\Models\PasskeySession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    /**
     * 承認待ちの仮会員一覧表示
     */
    public function pendingUsers()
    {
        $pendingUsers = User::where('status', 'temporary')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.pending', compact('pendingUsers'));
    }

    /**
     * 仮会員の承認
     */
    public function approveUser(Request $request, User $user)
    {
        if ($user->status !== 'temporary') {
            return back()->with('error', 'このユーザーは既に仮会員ではありません。');
        }

        $request->validate([
            'roles' => ['required', 'array'],
            'roles.*' => ['in:general,kanji,admin,equipment_manager'],
        ]);

        // 承認情報の記録とステータス更新
        $user->status = 'active';
        $user->roles = $request->input('roles');
        $user->approved_by = Auth::id(); // 誰が承認したかを記録
        $user->approved_at = now();      // 承認日時を記録
        $user->save();

        // 承認時の現在アクティブ年度の在籍レコードも自動生成
        $activeYear = session('active_fiscal_year', date('Y'));
        \App\Models\UserYear::updateOrCreate(
            [
                'user_id' => $user->id,
                'fiscal_year' => $activeYear,
            ],
            [
                'roles' => $request->input('roles'),
                'status' => 'active',
            ]
        );

        // パスキー登録用の一時セッション（24時間有効なワンタイムトークン）を発行
        $token = Str::random(64);
        PasskeySession::create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => now()->addHours(24),
        ]);

        $registerUrl = route('passkey.register', ['token' => $token]);

        // 承認完了通知（メール代わりにログに出力）
        Log::info("=== ユーザー承認通知メール ===");
        Log::info("To: {$user->email}");
        Log::info("Subject: 【重要】保土ケ谷宿場まつり実行委員会 登録承認およびログイン設定のご案内");
        Log::info("Body: ");
        Log::info("{$user->name} 様\n");
        Log::info("実行委員会への登録が承認されました。以下のURLより、24時間以内にログイン用パスキー（指紋・顔認証）の設定を行ってください。");
        Log::info("パスキー設定URL: {$registerUrl}");
        Log::info("==================================");

        return redirect()->route('admin.users.pending')->with('status', 'user-approved')
            ->with('register_url', $registerUrl)
            ->with('approved_user_name', $user->name);
    }

    /**
     * 仮会員の却下
     */
    public function rejectUser(Request $request, User $user)
    {
        if ($user->status !== 'temporary') {
            return back()->with('error', 'このユーザーは既に仮会員ではありません。');
        }

        $user->status = 'rejected';
        $user->save();

        // 却下通知（ログ出力）
        Log::info("=== 登録申請却下メール ===");
        Log::info("To: {$user->email}");
        Log::info("Subject: 登録申請に関するご連絡");
        Log::info("Body: {$user->name} 様\n誠に恐れ入りますが、実行委員会への登録申請は見送られました。詳細はお問い合わせください。");
        Log::info("===========================");

        return redirect()->route('admin.users.pending')->with('status', 'user-rejected');
    }

    /**
     * 正式会員一覧・パスキー管理画面
     */
    public function users()
    {
        // 承認済みの正式会員をすべて取得（紹介者、承認者のリレーションもロード）
        $users = User::where('status', 'active')
            ->with(['referrer', 'approver', 'webAuthnKeys'])
            ->orderBy('name_kana')
            ->get();

        return view('admin.users', compact('users'));
    }

    /**
     * 既存ユーザーに対するパスキー登録用セッション（ワンタイムURL）の再発行
     */
    public function createPasskeySession(Request $request, User $user)
    {
        if ($user->status !== 'active') {
            return back()->with('error', 'アクティブなユーザーのみパスキーを追加登録できます。');
        }

        // 古いセッションがあれば削除
        PasskeySession::where('user_id', $user->id)->delete();

        // 新しいトークン発行
        $token = Str::random(64);
        PasskeySession::create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => now()->addHours(24),
        ]);

        $registerUrl = route('passkey.register', ['token' => $token]);

        return redirect()->route('admin.users.index')
            ->with('status', 'パスキー登録用セッションを発行しました。')
            ->with('session_user_name', $user->name)
            ->with('register_url', $registerUrl);
    }

    /**
     * パスキーの削除（無効化）
     */
    public function deletePasskey(User $user, WebAuthnKey $key)
    {
        // 所有権の確認
        if ($key->user_id !== $user->id) {
            return back()->with('error', '不正な操作です。');
        }

        $key->delete();

        return back()->with('status', 'passkey-deleted');
    }

    /**
     * ユーザーアカウントの完全削除
     */
    public function deleteUser(User $user)
    {
        // 自分自身の削除は禁止
        if ($user->id === Auth::id()) {
            return back()->with('error', '自分自身のアカウントは削除できません。');
        }

        \DB::transaction(function() use ($user) {
            $user->delete();
        });

        return redirect()->route('admin.users.index')->with('status', 'user-deleted');
    }

    /**
     * 新年度への移行・引き継ぎフォーム表示
     */
    public function showTransitionForm()
    {
        $activeYear = session('active_fiscal_year', date('Y'));
        $targetYear = $activeYear + 1;

        // 現在の活動年度に所属しているアクティブな会員を取得
        $activeUsers = User::where('status', 'active')
            ->whereHas('userYears', function($q) use ($activeYear) {
                $q->where('fiscal_year', $activeYear)->where('status', 'active');
            })
            ->with(['userYears' => function($q) use ($activeYear) {
                $q->where('fiscal_year', $activeYear);
            }])
            ->orderBy('name_kana')
            ->get();

        return view('admin.transition', compact('activeUsers', 'activeYear', 'targetYear'));
    }

    /**
     * 新年度への移行・引き継ぎ処理実行
     */
    public function executeTransition(Request $request)
    {
        $request->validate([
            'target_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'users' => ['required', 'array'],
            'users.*' => ['exists:comittee_users,id'],
            'roles' => ['required', 'array'],
            'copy_gozaichi' => ['nullable', 'boolean'],
        ]);

        $targetYear = $request->target_year;
        $userIds = $request->users;
        $rolesInput = $request->roles; // user_id => [roles] のマップ
        $copyGozaichi = $request->boolean('copy_gozaichi');
        $activeYear = session('active_fiscal_year', date('Y'));

        \DB::transaction(function() use ($targetYear, $userIds, $rolesInput, $copyGozaichi, $activeYear) {
            foreach ($userIds as $userId) {
                $roles = $rolesInput[$userId] ?? ['general'];

                // 移行先年度の在籍レコードを作成または更新
                \App\Models\UserYear::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'fiscal_year' => $targetYear,
                    ],
                    [
                        'roles' => $roles,
                        'status' => 'active',
                    ]
                );
            }

            // ござ市関連データの引き継ぎ
            if ($copyGozaichi) {
                $targetEvent = \App\Models\GozaichiEvent::updateOrCreate(
                    ['fiscal_year' => $targetYear],
                    [
                        'recruitment_status' => 'closed',
                        'is_active' => true,
                    ]
                );

                $sourceEvent = \App\Models\GozaichiEvent::where('fiscal_year', $activeYear)->first();
                if ($sourceEvent) {
                    foreach ($sourceEvent->feeSettings as $sourceFee) {
                        $targetEvent->feeSettings()->updateOrCreate(
                            ['fee_key' => $sourceFee->fee_key],
                            ['fee_value' => $sourceFee->fee_value]
                        );
                    }
                } else {
                    $defaults = [
                        'member_1st' => 2000,
                        'member_general_2nd' => 3000,
                        'member_A_2nd' => 4000,
                        'member_B_2nd' => 5000,
                        'general_1st' => 6000,
                        'general_A_1st' => 8000,
                        'general_B_1st' => 10000,
                        'general_2nd' => 6000,
                        'general_A_2nd' => 8000,
                        'general_B_2nd' => 10000,
                        'tent' => 4500,
                        'weight' => 500,
                        'desk' => 2500,
                        'chair' => 500,
                        'trash_45' => 500,
                        'trash_70' => 700,
                    ];
                    foreach ($defaults as $key => $val) {
                        $targetEvent->feeSettings()->updateOrCreate(
                            ['fee_key' => $key],
                            ['fee_value' => $val]
                        );
                    }
                }
            }
        });

        // システム共通のアクティブ年度を、移行した新しい年度に切り替える
        session(['active_fiscal_year' => $targetYear]);

        return redirect()->route('admin.users.index')
            ->with('status', '移行処理が正常に完了し、年度を ' . $targetYear . ' 年度に切り替えました。');
    }

    /**
     * ユーザー直接追加フォームの表示
     */
    public function createUser()
    {
        // 紹介者候補としてアクティブなユーザー一覧を取得
        $activeUsers = User::where('status', 'active')
            ->orderBy('name_kana')
            ->get();

        return view('admin.users_create', compact('activeUsers'));
    }

    /**
     * 新規ユーザーの直接追加実行
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'name_kana' => ['required', 'string', 'max:100', 'regex:/^[ぁ-んー ]+$/u'], // ひらがなのみ
            'email' => ['required', 'string', 'email', 'max:255', 'unique:comittee_users,email'],
            'profession' => ['required', 'string', 'max:100'],
            'affiliation' => ['nullable', 'string', 'max:100'],
            'skills' => ['nullable', 'array'],
            'referrer_id' => ['nullable', 'exists:comittee_users,id'],
            'referrer_text' => ['nullable', 'string', 'max:100'],
            'line_display_name' => ['required', 'string', 'max:100'],
            'roles' => ['required', 'array'],
            'roles.*' => ['in:general,kanji,admin,equipment_manager'],
        ], [
            'name_kana.regex' => '氏名（かな）はひらがなで入力してください。',
        ]);

        $result = \DB::transaction(function() use ($request) {
            $user = new User();
            $user->name = $request->input('name');
            $user->name_kana = $request->input('name_kana');
            $user->email = $request->input('email');
            $user->profession = $request->input('profession');
            $user->affiliation = $request->input('affiliation');
            $user->skills = $request->input('skills', []);
            $user->roles = $request->input('roles');
            $user->referrer_id = $request->input('referrer_id');
            $user->referrer_text = $request->input('referrer_text');
            $user->line_display_name = $request->input('line_display_name');
            $user->status = 'active';
            $user->approved_by = Auth::id();
            $user->approved_at = now();
            $user->save();

            // 在籍レコードの作成
            $activeYear = session('active_fiscal_year', date('Y'));
            \App\Models\UserYear::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'fiscal_year' => $activeYear,
                ],
                [
                    'roles' => $request->input('roles'),
                    'status' => 'active',
                ]
            );

            // パスキー設定用の一時セッション（24時間有効なワンタイムトークン）を発行
            $token = Str::random(64);
            PasskeySession::create([
                'user_id' => $user->id,
                'token' => $token,
                'expires_at' => now()->addHours(24),
            ]);

            return ['user' => $user, 'token' => $token];
        });

        $registerUrl = route('passkey.register', ['token' => $result['token']]);

        // 登録完了通知（メール代わりにログに出力）
        Log::info("=== ユーザー直接追加登録通知メール ===");
        Log::info("To: {$result['user']->email}");
        Log::info("Subject: 【重要】保土ケ谷宿場まつり実行委員会 アカウント作成のお知らせ");
        Log::info("Body: ");
        Log::info("{$result['user']->name} 様\n");
        Log::info("管理者によって実行委員会システムのアカウントが作成されました。");
        Log::info("以下のURLより、24時間以内にログイン用パスキー（指紋・顔認証）の設定を行ってください。");
        Log::info("パスキー設定URL: {$registerUrl}");
        Log::info("======================================");

        return redirect()->route('admin.users.index')
            ->with('status', 'ユーザーを追加しました。対象者にパスキー設定用URLを共有してください。')
            ->with('register_url', $registerUrl)
            ->with('session_user_name', $result['user']->name);
    }

    /**
     * 管理者用ユーザー編集画面表示
     */
    public function editUser(User $user)
    {
        // 紹介者候補としてアクティブなユーザー一覧を取得 (自分自身は除く)
        $activeUsers = User::where('status', 'active')
            ->where('id', '!=', $user->id)
            ->orderBy('name_kana')
            ->get();

        return view('admin.users_edit', compact('user', 'activeUsers'));
    }

    /**
     * 管理者用ユーザー編集処理
     */
    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'name_kana' => ['required', 'string', 'max:100', 'regex:/^[ぁ-んー\s]+$/u'], // ひらがなとスペースのみ
            'email' => ['required', 'string', 'email', 'max:255', 'unique:comittee_users,email,' . $user->id],
            'profession' => ['required', 'string', 'max:100'],
            'affiliation' => ['nullable', 'string', 'max:100'],
            'skills' => ['nullable', 'array'],
            'referrer_id' => ['nullable', 'exists:comittee_users,id'],
            'referrer_text' => ['nullable', 'string', 'max:100'],
            'line_display_name' => ['required', 'string', 'max:100'],
            'roles' => ['required', 'array'],
            'roles.*' => ['in:general,kanji,admin,equipment_manager'],
            'status' => ['required', 'in:temporary,active,suspended,expelled,rejected'],
        ], [
            'name_kana.regex' => '氏名（かな）はひらがなで入力してください。',
            'email.unique' => 'このメールアドレスは既に他のユーザーに使用されています。',
        ]);

        \DB::transaction(function() use ($request, $user) {
            $user->name = $request->input('name');
            $user->name_kana = $request->input('name_kana');
            $user->email = $request->input('email');
            $user->profession = $request->input('profession');
            $user->affiliation = $request->input('affiliation');
            $user->skills = $request->input('skills', []);
            $user->roles = $request->input('roles');
            $user->referrer_id = $request->input('referrer_id');
            $user->referrer_text = $request->input('referrer_id') ? null : $request->input('referrer_text');
            $user->line_display_name = $request->input('line_display_name');
            $user->status = $request->input('status');
            $user->save();

            // 当年度の在籍レコード (UserYear) のロールとステータスも連動更新
            $activeYear = session('active_fiscal_year', date('Y'));
            \App\Models\UserYear::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'fiscal_year' => $activeYear,
                ],
                [
                    'roles' => $request->input('roles'),
                    'status' => $request->input('status'),
                ]
            );
        });

        Log::info("管理者 (ID: " . Auth::id() . ") がユーザー " . $user->name . " (ID: " . $user->id . ") の情報を更新しました。");

        return redirect()->route('users.show', $user)->with('status', '会員情報を更新しました。');
    }
}
