@extends('layouts.app')

@section('title', 'ポータル')

@section('content')
<div class="row">
    <!-- メインメッセージエリア -->
    <div class="col-12 mb-4">
        <div class="p-4 bg-white rounded shadow-sm border-0 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold text-secondary-color mb-1">
                    こんにちは、{{ Auth::user()->name }} さん
                </h4>
                <p class="text-muted mb-0 small">
                    本日の日付: {{ now()->format('Y年m月d日') }} | 
                    現在の操作対象年: <span class="fw-bold text-primary-color">{{ $activeYear }}年</span>
                </p>
            </div>
            <div class="d-none d-md-block">
                @if(Auth::user()->isSystemAdmin())
                    <span class="badge bg-danger px-3 py-2">システム管理者権限</span>
                @elseif(Auth::user()->isKanji())
                    <span class="badge bg-warning text-dark px-3 py-2">幹事権限</span>
                @else
                    <span class="badge bg-success px-3 py-2">実行委員</span>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- 左側: 直近の会議スケジュール -->
    <div class="col-lg-6 mb-4">
        <div class="card p-4 shadow-sm border-0 h-100">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                <h5 class="fw-bold text-secondary-color mb-0">📅 直近の会議予定</h5>
                <a href="{{ route('meetings.index') }}" class="btn btn-outline-secondary btn-sm">会議一覧</a>
            </div>

            @if($upcomingMeetings->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($upcomingMeetings as $meeting)
                        @php
                            // 曜日日本語表記
                            $weeks = ['日', '月', '火', '水', '木', '金', '土'];
                            $w = $weeks[$meeting->held_at->format('w')];
                            
                            // ユーザーの出欠状況取得
                            $myAttendance = $meeting->users()->where('user_id', Auth::id())->first();
                        @endphp
                        <a href="{{ route('meetings.show', $meeting) }}" class="list-group-item list-group-item-action px-0 py-3 d-flex justify-content-between align-items-start border-bottom">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold text-dark mb-1">
                                    @if($meeting->type === 'board')
                                        <span class="badge bg-danger-subtle text-danger me-1">幹事会</span>
                                    @elseif($meeting->type === 'general')
                                        <span class="badge bg-success-subtle text-success me-1">総会</span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary me-1">部会</span>
                                    @endif
                                    {{ $meeting->name }}
                                </div>
                                <span class="text-muted small d-block mb-1">
                                    ⏰ {{ $meeting->held_at->format('Y-m-d H:i') }}（{{ $w }}）
                                </span>
                                <span class="text-muted small d-block">
                                    📍 場所: {{ $meeting->location }}
                                </span>
                            </div>
                            <div class="text-end">
                                @if($myAttendance)
                                    @if($myAttendance->pivot->status === 'present')
                                        <span class="badge bg-success">出席予定</span>
                                    @elseif($myAttendance->pivot->status === 'absent')
                                        <span class="badge bg-danger">欠席予定</span>
                                    @else
                                        <span class="badge bg-warning text-dark">未回答</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">対象外</span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5 text-muted bg-light rounded">
                    <p class="mb-0 small">現在、予定されている会議はありません。</p>
                </div>
            @endif
        </div>
    </div>

    <!-- 右側: 業務ショートカット & 管理者アクション -->
    <div class="col-lg-6 mb-4">
        <div class="card p-4 shadow-sm border-0 h-100">
            <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3">🛠️ 実務アクション</h5>
            
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="p-3 border rounded text-center bg-light">
                        <span class="fs-4 d-block mb-2">📋</span>
                        <a href="{{ route('meetings.index') }}" class="btn btn-sm btn-outline-secondary w-100">会議予定・出欠</a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 border rounded text-center bg-light">
                        <span class="fs-4 d-block mb-2">👤</span>
                        <a href="{{ route('mypage') }}" class="btn btn-sm btn-outline-secondary w-100">登録プロファイル</a>
                    </div>
                </div>
            </div>

            @if(Auth::user()->isSystemAdmin())
                <h5 class="fw-bold text-danger border-bottom pb-2 mb-3">🛡️ 管理ツール</h5>
                <div class="list-group">
                    <!-- 承認待ちの仮会員数バッジ表示 -->
                    @php
                        $pendingCount = \App\Models\User::where('status', 'temporary')->count();
                    @endphp
                    <a href="{{ route('admin.users.pending') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span>📝 新規メンバーの承認申請</span>
                        @if($pendingCount > 0)
                            <span class="badge bg-danger rounded-pill">{{ $pendingCount }}件</span>
                        @else
                            <span class="badge bg-secondary rounded-pill">0</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span>🔐 会員一覧 ＆ パスキー（生体認証）管理</span>
                        <span class="badge bg-dark rounded-pill">管理</span>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- 下側: 今後の拡張モジュール（Coming Soon） -->
<div class="row mt-2">
    <div class="col-12">
        <div class="card p-4 shadow-sm border-0">
            <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3">🚀 追加予定の実務管理モジュール（フェーズ2以降）</h5>
            <p class="text-muted small">これらの機能は現在準備中です。システム共通の「開催年」のコンテキストに基づき、順次追加されます。</p>
            
            <div class="row row-cols-2 row-cols-md-4 g-3">
                <div class="col">
                    <div class="p-3 border rounded text-center h-100 d-flex flex-column justify-content-between">
                        <div>
                            <span class="fs-3 d-block mb-1">🏮</span>
                            <span class="fw-bold small d-block text-dark">ござ市管理</span>
                            <span class="text-muted" style="font-size: 0.75em;">出店者・区画・出店料</span>
                        </div>
                        @if(Auth::user()->isSystemAdmin() || Auth::user()->isKanji())
                            <a href="{{ route('goza.index') }}" class="btn btn-primary btn-sm mt-3">管理画面</a>
                        @else
                            <a href="{{ route('goza.map.index') }}" class="btn btn-outline-primary btn-sm mt-3">地図閲覧</a>
                        @endif
                    </div>
                </div>
                <div class="col">
                    <div class="p-3 border rounded text-center h-100 d-flex flex-column justify-content-between bg-light-subtle opacity-75">
                        <div>
                            <span class="fs-3 d-block mb-1">💰</span>
                            <span class="fw-bold small d-block text-dark">会計管理</span>
                            <span class="text-muted" style="font-size: 0.75em;">実行委員会の予算・収支</span>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary btn-sm mt-3">準備中</span>
                    </div>
                </div>
                <div class="col">
                    <div class="p-3 border rounded text-center h-100 d-flex flex-column justify-content-between bg-light-subtle opacity-75">
                        <div>
                            <span class="fs-3 d-block mb-1">🚧</span>
                            <span class="fw-bold small d-block text-dark">通行止め管理</span>
                            <span class="text-muted" style="font-size: 0.75em;">申請書類・道路閉鎖計画</span>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary btn-sm mt-3">準備中</span>
                    </div>
                </div>
                <div class="col">
                    <div class="p-3 border rounded text-center h-100 d-flex flex-column justify-content-between bg-light-subtle opacity-75">
                        <div>
                            <span class="fs-3 d-block mb-1">🧼</span>
                            <span class="fw-bold small d-block text-dark">衛生管理</span>
                            <span class="text-muted" style="font-size: 0.75em;">検便結果・食品届出</span>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary btn-sm mt-3">準備中</span>
                    </div>
                </div>
                <div class="col">
                    <div class="p-3 border rounded text-center h-100 d-flex flex-column justify-content-between">
                        <div>
                            <span class="fs-3 d-block mb-1">⛺</span>
                            <span class="fw-bold small d-block text-dark">備品管理</span>
                            <span class="text-muted" style="font-size: 0.75em;">テント・机・椅子貸出</span>
                        </div>
                        <a href="{{ route('equipment.index') }}" class="btn btn-outline-primary btn-sm mt-3">管理画面へ</a>
                    </div>
                </div>

                <div class="col">
                    <div class="p-3 border rounded text-center h-100 d-flex flex-column justify-content-between">
                        <div>
                            <span class="fs-3 d-block mb-1">🚨</span>
                            <span class="fw-bold small d-block text-dark">安全管理</span>
                            <span class="text-muted" style="font-size: 0.75em;">警備計画・緊急連絡網</span>
                        </div>
                        <a href="{{ route('safety.index') }}" class="btn btn-outline-primary btn-sm mt-3">計画書閲覧</a>
                    </div>
                </div>
                <div class="col">
                    <div class="p-3 border rounded text-center h-100 d-flex flex-column justify-content-between bg-light-subtle opacity-75">
                        <div>
                            <span class="fs-3 d-block mb-1">📁</span>
                            <span class="fw-bold small d-block text-dark">文書管理</span>
                            <span class="text-muted" style="font-size: 0.75em;">マニュアル・申請書保管</span>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary btn-sm mt-3">準備中</span>
                    </div>
                </div>
                <div class="col">
                    <div class="p-3 border rounded text-center h-100 d-flex flex-column justify-content-between bg-light-subtle opacity-75">
                        <div>
                            <span class="fs-3 d-block mb-1">🎪</span>
                            <span class="fw-bold small d-block text-dark">イベント管理</span>
                            <span class="text-muted" style="font-size: 0.75em;">ステージ出演・スタッフ</span>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary btn-sm mt-3">準備中</span>
                    </div>
                </div>
                <div class="col">
                    <div class="p-3 border rounded text-center h-100 d-flex flex-column justify-content-between bg-light-subtle opacity-75">
                        <div>
                            <span class="fs-3 d-block mb-1">📣</span>
                            <span class="fw-bold small d-block text-dark">広告・協賛管理</span>
                            <span class="text-muted" style="font-size: 0.75em;">協賛金・パンフレット広告</span>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary btn-sm mt-3">準備中</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
