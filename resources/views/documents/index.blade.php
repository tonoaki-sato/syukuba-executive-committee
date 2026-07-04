@extends('layouts.app')

@section('title', '文書・資料管理')

@section('content')
<style>
    /* ドキュメント管理専用のプレミアムスタイル */
    .document-card {
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
    }
    .document-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05) !important;
        border-color: var(--primary-color) !important;
    }
    .document-icon {
        font-size: 2.2rem;
        background-color: var(--bg-color);
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        color: var(--primary-color);
    }
    .document-header-card {
        background: linear-gradient(135deg, var(--secondary-color) 0%, #2a3d4a 100%);
        color: #ffffff;
        border-left: 5px solid var(--primary-color);
    }
    .badge-category {
        font-size: 0.75rem;
        padding: 0.35em 0.8em;
        border-radius: 20px;
        background-color: var(--bg-color);
        color: var(--secondary-color);
        border: 1px solid var(--border-color);
    }
</style>

<div class="row">
    <!-- ページヘッダー -->
    <div class="col-12 mb-4">
        <div class="card p-4 shadow-sm border-0 document-header-card">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h3 class="fw-bold mb-1 text-white">📁 文書・資料管理</h3>
                    <p class="text-white-50 mb-0 small">
                        保土ケ谷宿場まつり実行委員会メンバー向けに共有されている、マニュアル、申請書、組織図などの資料一覧です。
                    </p>
                </div>
                <div class="mt-2 mt-md-0">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-sm">ポータルへ戻る</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    @if(count($documents) > 0)
        @foreach($documents as $key => $doc)
            <div class="col-md-6 mb-4">
                <div class="card p-4 shadow-sm h-100 document-card">
                    <div class="d-flex align-items-start">
                        <div class="document-icon me-3 shadow-sm">
                            {{ $doc['icon'] }}
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge badge-category">{{ $doc['category'] }}</span>
                                <span class="text-muted small">PDF形式</span>
                            </div>
                            <h5 class="fw-bold text-secondary-color mb-2">
                                {{ $doc['name'] }}
                            </h5>
                            <p class="text-muted small mb-4">
                                {{ $doc['description'] }}
                            </p>
                            <div class="d-flex justify-content-end">
                                <a href="{{ route('documents.show', $key) }}" target="_blank" class="btn btn-primary btn-sm px-4">
                                    📄 開く (別タブ)
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="col-12 text-center py-5 text-muted bg-white rounded shadow-sm border">
            <span class="fs-1 d-block mb-3">📭</span>
            <p class="mb-0">現在、公開されている文書はありません。</p>
        </div>
    @endif
</div>
@endsection
