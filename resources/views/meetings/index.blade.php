@extends('layouts.app')

@section('title', '会議一覧')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">会議管理</h3>
                <p class="text-muted small">
                    現在表示中の年: <span class="fw-bold text-primary-color">{{ $activeYear }}年</span>
                </p>
            </div>
            
            <!-- 幹事・管理者のみ会議登録可能 -->
            @if(Auth::user()->isSystemAdmin() || Auth::user()->isKanji())
                <a href="{{ route('meetings.create') }}" class="btn btn-primary">
                    ➕ 会議スケジュールを新規登録
                </a>
            @endif
        </div>

        <div class="card p-4 shadow-sm border-0">
            @if($meetings->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="w-15">会議タイプ</th>
                                <th class="w-30">会議名</th>
                                <th class="w-20">日時</th>
                                <th class="w-20">場所</th>
                                <th class="w-15 text-center">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($meetings as $meeting)
                                @php
                                    $weeks = ['日', '月', '火', '水', '木', '金', '土'];
                                    $w = $weeks[$meeting->held_at->format('w')];
                                    
                                    // 出欠カウントの取得
                                    $presentCount = $meeting->participants()->where('status', 'present')->count();
                                    $absentCount = $meeting->participants()->where('status', 'absent')->count();
                                    $pendingCount = $meeting->participants()->where('status', 'pending')->count();
                                @endphp
                                <tr>
                                    <td>
                                        @if($meeting->type === 'board')
                                            <span class="badge bg-danger">幹事会</span>
                                        @elseif($meeting->type === 'general')
                                            <span class="badge bg-success">総会</span>
                                        @elseif($meeting->type === 'subcommittee')
                                            <span class="badge bg-primary">部会</span>
                                        @else
                                            <span class="badge bg-info">作業</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $meeting->name }}</div>
                                        <div class="text-muted small" style="font-size: 0.8em;">
                                            出席: <span class="text-success fw-bold">{{ $presentCount }}</span> | 
                                            欠席: <span class="text-danger fw-bold">{{ $absentCount }}</span> | 
                                            未回答: <span class="text-warning fw-bold">{{ $pendingCount }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span>{{ $meeting->held_at->format('Y-m-d H:i') }}</span>
                                        <span class="text-muted small">（{{ $w }}）</span>
                                    </td>
                                    <td>{{ $meeting->location }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('meetings.show', $meeting) }}" class="btn btn-outline-secondary btn-sm">
                                            出欠・詳細
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <p class="mb-0">この年（{{ $activeYear }}年）に登録されている会議予定はありません。</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
