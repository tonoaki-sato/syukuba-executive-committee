@extends('layouts.app')

@section('title', $meeting->name)

@section('content')
@php
    $weeks = ['日', '月', '火', '水', '木', '金', '土'];
    $w = $weeks[$meeting->held_at->format('w')];
@endphp

<div class="row">
    <!-- 左側: 会議詳細、出欠登録、議事録表示 -->
    <div class="col-lg-8">
        <!-- 会議基本情報 -->
        <div class="card p-4 shadow-sm border-0 mb-4">
            <div class="d-flex justify-content-between align-items-start mb-3 border-bottom pb-2">
                <div>
                    <span class="badge bg-secondary mb-2">
                        @if($meeting->type === 'board')
                            幹事会
                        @elseif($meeting->type === 'general')
                            総会
                        @else
                            部会
                        @endif
                    </span>
                    <h3 class="fw-bold text-dark mb-0">{{ $meeting->name }}</h3>
                </div>
                <!-- 幹事・管理者向け議事録登録ボタン -->
                @if(Auth::user()->isSystemAdmin() || Auth::user()->isKanji())
                    <a href="{{ route('meetings.minutes', $meeting) }}" class="btn btn-sm btn-outline-primary py-2 px-3 fw-semibold">
                        ✍️ 議事録・写真を編集
                    </a>
                @endif
            </div>

            <div class="row mb-3">
                <div class="col-md-6 mb-2">
                    <span class="text-muted small d-block">⏰ 開催日時</span>
                    <span class="fw-semibold">{{ $meeting->held_at->format('Y-m-d H:i') }}（{{ $w }}）</span>
                </div>
                <div class="col-md-6 mb-2">
                    <span class="text-muted small d-block">📍 開催場所</span>
                    <span class="fw-semibold">{{ $meeting->location }}</span>
                </div>
            </div>

            @if($meeting->agenda)
                <div class="mb-2">
                    <span class="text-muted small d-block mb-1">■ 議題・アジェンダ</span>
                    <div class="bg-light p-3 rounded text-dark small" style="white-space: pre-wrap;">{{ $meeting->agenda }}</div>
                </div>
            @endif
        </div>

        <!-- 自分の出欠回答フォーム -->
        <div class="card p-4 shadow-sm border-0 mb-4">
            <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3">📝 あなたの出欠を回答する</h5>
            
            <form action="{{ route('meetings.attendance', $meeting) }}" method="POST">
                @csrf
                <div class="mb-3 d-flex gap-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" id="status-present" value="present" 
                               {{ ($myAttendance && $myAttendance->status === 'present') ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold text-success" for="status-present">出席する</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" id="status-absent" value="absent" 
                               {{ ($myAttendance && $myAttendance->status === 'absent') ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold text-danger" for="status-absent">欠席する</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" id="status-pending" value="pending" 
                               {{ (!$myAttendance || $myAttendance->status === 'pending') ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold text-warning" for="status-pending">未定・検討中</label>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="note" class="form-label small text-muted">欠席理由・連絡事項（遅刻・早退など）</label>
                    <input type="text" name="note" id="note" class="form-control border-secondary-subtle" 
                           value="{{ $myAttendance ? $myAttendance->note : '' }}" placeholder="例：仕事の都合により欠席します、30分程度遅れて参加します。">
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-secondary bg-primary-color border-primary-color px-4">回答を保存する</button>
                </div>
            </form>
        </div>

        <!-- 議事録とホワイトボード写真 (登録されている場合のみ表示) -->
        <div class="card p-4 shadow-sm border-0 mb-4">
            <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3">📝 会議の議事録（決定事項・書き起こし）</h5>
            
            @if($meeting->minutes)
                <div class="bg-white p-3 border rounded text-dark mb-4" style="white-space: pre-wrap; font-size: 0.95rem;">{{ $meeting->minutes }}</div>
            @else
                <div class="text-center py-4 text-muted bg-light rounded mb-4">
                    <p class="mb-0 small">この会議の議事録はまだ登録されていません。</p>
                </div>
            @endif


        </div>
    </div>

    <!-- 右側: 出欠集計、LINE連絡用テンプレート -->
    <div class="col-lg-4">
        <!-- 出欠集計状況 -->
        <div class="card p-4 shadow-sm border-0 mb-4">
            <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3">📊 出欠集計</h5>
            
            @php
                $presentUsers = $participants->filter(fn($p) => $p->status === 'present');
                $absentUsers = $participants->filter(fn($p) => $p->status === 'absent');
                $pendingUsers = $participants->filter(fn($p) => $p->status === 'pending');
            @endphp

            <div class="row text-center mb-3">
                <div class="col-4">
                    <span class="d-block small text-muted">出席</span>
                    <span class="fs-4 fw-bold text-success">{{ $presentUsers->count() }}</span>
                </div>
                <div class="col-4">
                    <span class="d-block small text-muted">欠席</span>
                    <span class="fs-4 fw-bold text-danger">{{ $absentUsers->count() }}</span>
                </div>
                <div class="col-4">
                    <span class="d-block small text-muted">未回答</span>
                    <span class="fs-4 fw-bold text-warning">{{ $pendingUsers->count() }}</span>
                </div>
            </div>

            <!-- アコーディオン出欠名簿 -->
            <div class="accordion" id="attendanceAccordion">
                <!-- 出席メンバー -->
                <div class="accordion-item border-0">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed px-0 py-2 fw-semibold text-success shadow-none bg-transparent" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePresent" aria-expanded="false">
                            出席予定者 ({{ $presentUsers->count() }}人)
                        </button>
                    </h2>
                    <div id="collapsePresent" class="accordion-collapse collapse" data-bs-parent="#attendanceAccordion">
                        <div class="accordion-body px-0 py-2">
                            <ul class="list-unstyled mb-0 small">
                                @forelse($presentUsers as $p)
                                    <li class="py-1 border-bottom d-flex justify-content-between">
                                        <span>{{ $p->user->name }}</span>
                                        <span class="text-muted" style="font-size: 0.85em;">{{ $p->user->profession }}</span>
                                    </li>
                                @empty
                                    <li class="py-1 text-muted text-center small">出席者はいません。</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- 欠席メンバー -->
                <div class="accordion-item border-0">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed px-0 py-2 fw-semibold text-danger shadow-none bg-transparent" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAbsent" aria-expanded="false">
                            欠席予定者 ({{ $absentUsers->count() }}人)
                        </button>
                    </h2>
                    <div id="collapseAbsent" class="accordion-collapse collapse" data-bs-parent="#attendanceAccordion">
                        <div class="accordion-body px-0 py-2">
                            <ul class="list-unstyled mb-0 small">
                                @forelse($absentUsers as $p)
                                    <li class="py-2 border-bottom">
                                        <div class="d-flex justify-content-between">
                                            <span class="fw-bold">{{ $p->user->name }}</span>
                                            <span class="text-muted" style="font-size: 0.85em;">{{ $p->user->profession }}</span>
                                        </div>
                                        @if($p->note)
                                            <div class="text-danger-emphasis mt-1 bg-danger-subtle p-1 rounded" style="font-size: 0.85em;">
                                                💬 {{ $p->note }}
                                            </div>
                                        @endif
                                    </li>
                                @empty
                                    <li class="py-1 text-muted text-center small">欠席者はいません。</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- 未回答メンバー -->
                <div class="accordion-item border-0">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed px-0 py-2 fw-semibold text-warning shadow-none bg-transparent" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePending" aria-expanded="false">
                            未回答者 ({{ $pendingUsers->count() }}人)
                        </button>
                    </h2>
                    <div id="collapsePending" class="accordion-collapse collapse" data-bs-parent="#attendanceAccordion">
                        <div class="accordion-body px-0 py-2">
                            <ul class="list-unstyled mb-0 small">
                                @forelse($pendingUsers as $p)
                                    <li class="py-1 border-bottom d-flex justify-content-between">
                                        <span>{{ $p->user->name }}</span>
                                        <span class="text-muted" style="font-size: 0.85em;">{{ $p->user->profession }}</span>
                                    </li>
                                @empty
                                    <li class="py-1 text-muted text-center small">全員回答済みです。</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- LINE 案内文コピーエリア -->
        <div class="card p-4 shadow-sm border-0 mb-4">
            <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3">💬 LINEグループ送信用の案内文</h5>
            <div class="mb-3">
                <textarea id="line-template-text" class="form-control bg-light small" rows="7" readonly style="font-size: 0.85rem;">{{ $lineTemplate }}</textarea>
            </div>
            <div class="d-grid">
                <button type="button" id="btn-copy-template" class="btn btn-outline-secondary btn-sm py-2 fw-semibold">
                    📋 案内文をクリップボードにコピー
                </button>
            </div>
        </div>

        <!-- LINE 議事録報告コピーエリア (議事録登録時のみ表示) -->
        @if(session('line_report'))
            <div class="card p-4 shadow-sm border-danger border-2 mb-4">
                <h5 class="fw-bold text-danger border-bottom pb-2 mb-3">💬 【LINE報告用】議事録通知テキスト</h5>
                <p class="text-muted" style="font-size: 0.8em;">議事録の登録が完了しました。以下の要約テキストをコピーしてLINEグループへ貼り付け報告してください。</p>
                <div class="mb-3">
                    <textarea id="line-report-text" class="form-control bg-light small text-danger-emphasis border-danger-subtle" rows="6" readonly style="font-size: 0.85rem;">{{ session('line_report') }}</textarea>
                </div>
                <div class="d-grid">
                    <button type="button" id="btn-copy-report" class="btn btn-danger btn-sm py-2 fw-semibold">
                        📋 議事録報告テキストをコピー
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>

@endsection
