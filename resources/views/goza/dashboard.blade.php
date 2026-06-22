@extends('layouts.app')

@section('title', 'ござ市管理ダッシュボード')

@section('content')
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold text-dark mb-1">ござ市管理ダッシュボード</h3>
            <p class="text-muted small">出店管理、区画配置、当日集金、設定などへのアクセス</p>
        </div>
        <div>
            <span class="badge bg-secondary p-2 fs-6">
                対象年度: {{ $event->fiscal_year }}年
            </span>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- 募集ステータス -->
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm text-center p-3">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small mb-2 fw-bold">募集ステータス</h6>
                @if($event->recruitment_status === 'open')
                    <span class="badge bg-success px-3 py-2 fs-5">募集中</span>
                @else
                    <span class="badge bg-danger px-3 py-2 fs-5">締切済・募集前</span>
                @endif
                <div class="mt-3 small text-muted">
                    @if($event->recruitment_start_at && $event->recruitment_end_at)
                        {{ $event->recruitment_start_at->format('m/d H:i') }} 〜 {{ $event->recruitment_end_at->format('m/d H:i') }}
                    @else
                        期間未設定
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- 応募数 -->
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm text-center p-3">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small mb-2 fw-bold">応募総数</h6>
                <div class="fs-1 fw-bold text-dark">{{ $applicationsCount }}</div>
                <div class="text-muted small">店舗 / 団体</div>
            </div>
        </div>
    </div>

    <!-- 当選数 -->
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm text-center p-3">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small mb-2 fw-bold">当選（出店許可）数</h6>
                <div class="fs-1 fw-bold text-success">{{ $acceptedCount }}</div>
                <div class="text-muted small">店舗 / 団体</div>
            </div>
        </div>
    </div>

    <!-- 集金済み数 -->
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm text-center p-3">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small mb-2 fw-bold">当日集金済み</h6>
                <div class="fs-1 fw-bold text-primary-color">{{ $paidCount }} <span class="fs-5 text-muted">/ {{ $acceptedCount }}</span></div>
                <div class="text-muted small">受領完了</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- ショートカット -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-bold text-dark mb-0">実務メニュー</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <a href="{{ route('goza.applications.index') }}" class="card text-decoration-none border h-100 hover-shadow transition">
                            <div class="card-body d-flex align-items-center p-3">
                                <div class="bg-primary-color text-white rounded p-3 me-3">
                                    📋
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">出店応募管理</h6>
                                    <p class="text-muted small mb-0">応募一覧の閲覧・可否選別、代理登録</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('goza.spots.index') }}" class="card text-decoration-none border h-100 hover-shadow transition">
                            <div class="card-body d-flex align-items-center p-3">
                                <div class="bg-success text-white rounded p-3 me-3">
                                    📍
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">出店場所配置</h6>
                                    <p class="text-muted small mb-0">当選者への区画コード割り当て、重複チェック</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('goza.payments.index') }}" class="card text-decoration-none border h-100 hover-shadow transition">
                            <div class="card-body d-flex align-items-center p-3">
                                <div class="bg-warning text-dark rounded p-3 me-3">
                                    💴
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">当日集金・領収書</h6>
                                    <p class="text-muted small mb-0">料金の自動計算、受領処理、領収書・許可証発行</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    @if(Auth::user()->isSystemAdmin())
                    <div class="col-md-6">
                        <a href="{{ route('goza.settings.index') }}" class="card text-decoration-none border h-100 hover-shadow transition">
                            <div class="card-body d-flex align-items-center p-3">
                                <div class="bg-secondary text-white rounded p-3 me-3">
                                    ⚙️
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">募集設定・料金マスタ</h6>
                                    <p class="text-muted small mb-0">募集期間や単価設定（システム管理者限定）</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 直近の更新履歴 -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-bold text-dark mb-0">最近の動き</h5>
            </div>
            <div class="card-body p-4">
                @if($recentApplications->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentApplications as $recent)
                            <div class="list-group-item px-0 border-0 mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fw-bold text-dark small">{{ $recent->shop_name }}</span>
                                    <span class="text-muted text-end" style="font-size: 0.75rem;">{{ $recent->updated_at->diffForHumans() }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">{{ $recent->exhibitor_name }}</span>
                                    @if($recent->status === 'accepted')
                                        <span class="badge bg-success-subtle text-success small">当選</span>
                                    @elseif($recent->status === 'rejected')
                                        <span class="badge bg-danger-subtle text-danger small">落選</span>
                                    @elseif($recent->status === 'submitted')
                                        <span class="badge bg-primary-subtle text-primary small">応募済</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary small">下書き</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted small text-center my-4">最近登録されたデータはありません。</p>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .hover-shadow:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        border-color: var(--primary-color) !important;
    }
    .transition {
        transition: all 0.25s ease;
    }
</style>
@endsection
