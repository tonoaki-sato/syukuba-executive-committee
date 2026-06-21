<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\MeetingParticipant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MeetingController extends Controller
{
    /**
     * 会議一覧の表示
     */
    public function index()
    {
        $activeYear = session('active_fiscal_year', date('Y'));

        // 年度に紐づく会議を抽出
        $meetings = Meeting::where('fiscal_year', $activeYear)
            ->orderBy('held_at', 'desc')
            ->get();

        return view('meetings.index', compact('meetings', 'activeYear'));
    }

    /**
     * 新規会議登録画面の表示
     */
    public function create()
    {
        // 幹事または管理者のみ作成可能
        if (!Auth::user()->isSystemAdmin() && !Auth::user()->isKanji()) {
            abort(403, '会議を作成する権限がありません。');
        }

        $activeYear = session('active_fiscal_year', date('Y'));
        return view('meetings.create', compact('activeYear'));
    }

    /**
     * 新規会議の登録処理と自動アサイン
     */
    public function store(Request $request)
    {
        if (!Auth::user()->isSystemAdmin() && !Auth::user()->isKanji()) {
            abort(403, '会議を作成する権限がありません。');
        }

        $request->validate([
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'type' => ['required', 'in:board,general,subcommittee'],
            'name' => ['required', 'string', 'max:100'],
            'held_at' => ['required', 'date'],
            'location' => ['required', 'string', 'max:255'],
            'agenda' => ['nullable', 'string'],
        ]);

        $meeting = Meeting::create([
            'fiscal_year' => $request->fiscal_year,
            'type' => $request->type,
            'name' => $request->name,
            'held_at' => $request->held_at,
            'location' => $request->location,
            'agenda' => $request->agenda,
            'whiteboard_images' => [],
        ]);

        // 対象ユーザーの自動アサイン（出欠の初期レコードを作成）
        // 1. 幹事会 (board) -> 幹事ロール (kanji) または管理者 (admin) を持つアクティブユーザー
        // 2. 総会 (general) / 部会 (subcommittee) -> 全アクティブユーザー
        $usersQuery = User::where('status', 'active');

        if ($request->type === 'board') {
            $usersQuery->where(function($q) {
                $q->whereJsonContains('roles', 'kanji')
                  ->orWhereJsonContains('roles', 'admin');
            });
        }

        $targetUsers = $usersQuery->get();

        foreach ($targetUsers as $user) {
            MeetingParticipant::create([
                'meeting_id' => $meeting->id,
                'user_id' => $user->id,
                'status' => 'pending', // 最初は未定
            ]);
        }

        return redirect()->route('meetings.show', $meeting)->with('status', 'meeting-created');
    }

    /**
     * 会議詳細・出欠管理画面
     */
    public function show(Meeting $meeting)
    {
        $user = Auth::user();

        // ログインユーザーの出欠レコードを取得
        $myAttendance = MeetingParticipant::where('meeting_id', $meeting->id)
            ->where('user_id', $user->id)
            ->first();

        // 全体の出欠状況を取得
        $participants = MeetingParticipant::where('meeting_id', $meeting->id)
            ->with('user')
            ->get();

        // LINEの会議案内文テンプレートを生成
        $heldAtFormatted = $meeting->held_at->format('Y年m月d日(N) H:i');
        // 曜日の日本語置換
        $weeks = ['日', '月', '火', '水', '木', '金', '土'];
        $w = $weeks[$meeting->held_at->format('w')];
        $heldAtFormatted = str_replace('('.$meeting->held_at->format('N').')', '（'.$w.'）', $heldAtFormatted);

        $lineTemplate = "【会議開催のご案内】\n\n";
        $lineTemplate .= "会議名：{$meeting->name}\n";
        $lineTemplate .= "日時：{$heldAtFormatted}\n";
        $lineTemplate .= "場所：{$meeting->location}\n\n";
        if ($meeting->agenda) {
            $lineTemplate .= "■議題：\n" . trim($meeting->agenda) . "\n\n";
        }
        $lineTemplate .= "システムから出欠の登録をお願いいたします。\n";
        $lineTemplate .= route('meetings.show', $meeting);

        return view('meetings.show', compact('meeting', 'myAttendance', 'participants', 'lineTemplate'));
    }

    /**
     * ログインユーザーの出欠回答・更新処理
     */
    public function updateAttendance(Request $request, Meeting $meeting)
    {
        $request->validate([
            'status' => ['required', 'in:present,absent,pending'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $user = Auth::user();

        // 出欠レコードの有無を確認し、更新または作成
        $attendance = MeetingParticipant::firstOrNew([
            'meeting_id' => $meeting->id,
            'user_id' => $user->id,
        ]);

        $attendance->status = $request->status;
        $attendance->note = $request->note;
        $attendance->save();

        return back()->with('status', 'attendance-updated');
    }

    /**
     * 議事録・ホワイトボード画像編集画面
     */
    public function showMinutesForm(Meeting $meeting)
    {
        if (!Auth::user()->isSystemAdmin() && !Auth::user()->isKanji()) {
            abort(403, '議事録を編集する権限がありません。');
        }

        return view('meetings.minutes', compact('meeting'));
    }

    /**
     * 議事録とホワイトボード写真の登録・更新処理
     */
    public function updateMinutes(Request $request, Meeting $meeting)
    {
        if (!Auth::user()->isSystemAdmin() && !Auth::user()->isKanji()) {
            abort(403, '議事録を編集する権限がありません。');
        }

        $request->validate([
            'minutes' => ['nullable', 'string'],
            'whiteboard_images' => ['nullable', 'array'],
            'whiteboard_images.*' => ['image', 'mimes:jpeg,png,jpg', 'max:5120'], // 最大5MB
        ]);

        $meeting->minutes = $request->minutes;

        // 画像のアップロード処理
        if ($request->hasFile('whiteboard_images')) {
            $existingImages = $meeting->whiteboard_images ?? [];

            foreach ($request->file('whiteboard_images') as $file) {
                // public/whiteboards ディレクトリに保存
                $path = $file->store('whiteboards', 'public');
                $existingImages[] = '/storage/' . $path;
            }

            $meeting->whiteboard_images = $existingImages;
        }

        $meeting->save();

        // LINE用の議事録報告テキストを生成
        $summary = $request->minutes ? mb_strimwidth(strip_tags($request->minutes), 0, 150, '...') : '議事録が登録されました。';
        $lineReport = "【会議議事録登録のお知らせ】\n\n";
        $lineReport .= "会議名：{$meeting->name}\n\n";
        $lineReport .= "■決定事項・概要：\n{$summary}\n\n";
        $lineReport .= "詳細およびホワイトボード写真は以下より確認できます。\n";
        $lineReport .= route('meetings.show', $meeting);

        // LINE報告用テンプレートを一時的にセッションに入れてリダイレクト
        return redirect()->route('meetings.show', $meeting)
            ->with('status', 'minutes-updated')
            ->with('line_report', $lineReport);
    }

    /**
     * 開催年（年度）の切り替え処理（セッション管理）
     */
    public function changeFiscalYear(Request $request)
    {
        $currentYear = (int)date('Y');
        $request->validate([
            'fiscal_year' => ['required', 'integer', 'min:2026', 'max:' . $currentYear],
        ]);

        session(['active_fiscal_year' => $request->fiscal_year]);

        return back()->with('status', 'fiscal-year-changed');
    }
}
