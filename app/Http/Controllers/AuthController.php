<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * ログイン画面表示
     */
    public function showLogin()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->status === 'temporary') {
                return redirect()->route('register.pending');
            }
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }



    /**
     * 登録申請（仮登録）画面表示
     */
    public function showRegister()
    {
        // 紹介者として選択できる、すでに承認済みの正式会員リスト
        $activeMembers = User::where('status', 'active')->orderBy('name_kana')->get();
        return view('auth.register', compact('activeMembers'));
    }

    /**
     * 登録申請（仮登録）処理
     */
    public function postRegister(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'name_kana' => ['required', 'string', 'max:100', 'regex:/^[ぁ-んー\s]+$/u'], // ひらがなとスペースのみ
            'email' => ['required', 'string', 'email', 'max:255', 'unique:comittee_users'],
            'profession' => ['required', 'string', 'max:100'],
            'affiliation' => ['nullable', 'string', 'max:100'],
            'skills' => ['nullable', 'array'],
            'referrer_id' => ['nullable', 'exists:comittee_users,id'],
            'referrer_text' => ['nullable', 'string', 'max:100'],
            'line_display_name' => ['required', 'string', 'max:100'],
        ], [
            'name_kana.regex' => '氏名（かな）はひらがなで入力してください。',
            'email.unique' => 'このメールアドレスは既に登録申請中、または登録済みです。',
        ]);

        $skills = $request->input('skills', []);

        // 新規ユーザー登録（ステータスはデフォルト 'temporary'）
        User::create([
            'name' => $request->name,
            'name_kana' => $request->name_kana,
            'email' => $request->email,
            'profession' => $request->profession,
            'affiliation' => $request->affiliation,
            'skills' => $skills,
            'roles' => ['general'], // 初期登録段階では一般会員ロール
            'referrer_id' => $request->referrer_id,
            'referrer_text' => $request->referrer_id ? null : $request->referrer_text, // referrer_idがない場合のみテキストを保存
            'line_display_name' => $request->line_display_name,
            'status' => 'temporary',
        ]);

        return redirect()->route('register.pending')->with('registered', true);
    }

    /**
     * 承認待ち（仮会員ロックアウト）画面表示
     */
    public function showPending()
    {
        // 登録完了リダイレクト直後か、またはログイン中だが仮会員の場合のみ表示
        if (!Auth::check() && !session('registered')) {
            return redirect()->route('login');
        }

        if (Auth::check() && Auth::user()->status !== 'temporary') {
            return redirect()->route('dashboard');
        }

        return view('auth.pending');
    }

    /**
     * ログイン後トップ（ダッシュボード）
     */
    public function showDashboard()
    {
        $activeYear = session('active_fiscal_year', date('Y'));
        
        $upcomingMeetings = \App\Models\Meeting::where('fiscal_year', $activeYear)
            ->where('held_at', '>=', now()->startOfDay())
            ->where('held_at', '<=', now()->addMonth())
            ->orderBy('held_at')
            ->get();

        return view('dashboard', compact('activeYear', 'upcomingMeetings'));
    }

    /**
     * ログアウト
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    /**
     * マイページ表示
     */
    public function showMyPage()
    {
        $user = Auth::user();
        return view('auth.mypage', compact('user'));
    }



    /**
     * ユーザー詳細プロフィールの表示
     */
    public function showUserDetail(User $user)
    {
        $currentUser = Auth::user();

        // 閲覧権限のチェック:
        // システム管理者 (admin) もしくは 幹事 (kanji) の場合は全ユーザーを閲覧可能。
        // 一般会員の場合は、自分自身の詳細のみ閲覧可能。
        if (!$currentUser->isSystemAdmin() && !$currentUser->isKanji() && $currentUser->id !== $user->id) {
            abort(403, 'このユーザーの詳細情報を閲覧する権限がありません。');
        }

        // 関連データのロード
        $user->load(['referrer', 'approver', 'webAuthnKeys']);

        // 年度所属履歴の取得
        $userYears = \App\Models\UserYear::where('user_id', $user->id)
            ->orderBy('fiscal_year', 'desc')
            ->get();

        return view('auth.user_detail', compact('user', 'userYears'));
    }

    /**
     * マイページ編集画面表示
     */
    public function editMyPage()
    {
        $user = Auth::user();
        return view('auth.mypage_edit', compact('user'));
    }

    /**
     * マイページ編集処理
     */
    public function updateMyPage(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'name_kana' => ['required', 'string', 'max:100', 'regex:/^[ぁ-んー\s]+$/u'], // ひらがなとスペースのみ
            'email' => ['required', 'string', 'email', 'max:255', 'unique:comittee_users,email,' . $user->id],
            'profession' => ['required', 'string', 'max:100'],
            'affiliation' => ['nullable', 'string', 'max:100'],
            'skills' => ['nullable', 'array'],
            'line_display_name' => ['required', 'string', 'max:100'],
        ], [
            'name_kana.regex' => '氏名（かな）はひらがなで入力してください。',
            'email.unique' => 'このメールアドレスは既に他のユーザーに使用されています。',
        ]);

        $user->update([
            'name' => $request->input('name'),
            'name_kana' => $request->input('name_kana'),
            'email' => $request->input('email'),
            'profession' => $request->input('profession'),
            'affiliation' => $request->input('affiliation'),
            'skills' => $request->input('skills', []),
            'line_display_name' => $request->input('line_display_name'),
        ]);

        return redirect()->route('mypage')->with('status', 'プロフィールを更新しました。');
    }
}
