@extends('layouts.app')

@section('title', '出店場所地図配置')

@php
    $mapVersionFile = public_path('images/map_base_version.txt');
    $mapVersion = file_exists($mapVersionFile) ? trim(file_get_contents($mapVersionFile)) : time();
@endphp

@section('content')

<div id="map-app-data"
     class="d-none"
     data-can-edit="{{ $canEdit ? 'true' : 'false' }}"
     data-markers-url="{{ route('goza.map.markers') }}"
     data-upload-base-url="{{ route('admin.map.uploadBase') }}"
     data-csrf-token="{{ csrf_token() }}">
</div>

<div class="mb-3 d-flex justify-content-between align-items-center">
    <div>
        <h3 class="fw-bold text-dark mb-1">出店場所地図配置</h3>
        <p class="text-muted small">会場マップ上での出店ブース・付帯設備・給水設備等の配置調整</p>
    </div>
    <div class="d-flex gap-2">
        @if(auth()->user()->isSystemAdmin())
            <button type="button" class="btn btn-dark d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#uploadMapModal">
                🗺️ ベースマップ差し替え(PDF)
            </button>
        @endif
        <a href="{{ route('goza.map.pdf') }}" target="_blank" class="btn btn-outline-dark d-flex align-items-center">
            🖨️ 配置図PDFプレビュー
        </a>
    </div>

</div>

<div class="row g-4">
    <!-- 操作パネル (幹事・管理者のみ編集可能) -->
    <div class="col-lg-3">
        @if($canEdit)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="fw-bold text-dark mb-0">新規オブジェクト配置</h6>
                    <span class="text-muted small" style="font-size: 0.75rem;">地図上にドラッグ＆ドロップしてください</span>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex flex-column gap-2">
                        <!-- 設備アイテム (ドラッグ可能) -->
                        <div class="drag-item d-flex align-items-center border rounded p-2" draggable="true" data-type="facility" data-sub="trash">
                            <span class="fs-4 me-2">🗑️</span>
                            <div>
                                <div class="fw-bold small">ごみ箱</div>
                                <div class="text-muted" style="font-size: 0.7rem;">回収用ゴミステーション</div>
                            </div>
                        </div>
                        <div class="drag-item d-flex align-items-center border rounded p-2" draggable="true" data-type="facility" data-sub="speaker">
                            <span class="fs-4 me-2">🔊</span>
                            <div>
                                <div class="fw-bold small">音響スピーカー</div>
                                <div class="text-muted" style="font-size: 0.7rem;">放送用スピーカー</div>
                            </div>
                        </div>
                        <div class="drag-item d-flex align-items-center border rounded p-2" draggable="true" data-type="facility" data-sub="toilet">
                            <span class="fs-4 me-2">🚾</span>
                            <div>
                                <div class="fw-bold small">仮設トイレ</div>
                                <div class="text-muted" style="font-size: 0.7rem;">まつり用トイレ</div>
                            </div>
                        </div>
                        <div class="drag-item d-flex align-items-center border rounded p-2" draggable="true" data-type="facility" data-sub="cone">
                            <span class="fs-4 me-2">⚠️</span>
                            <div>
                                <div class="fw-bold small">三角コーン</div>
                                <div class="text-muted" style="font-size: 0.7rem;">車両通行止め等の仕切り</div>
                            </div>
                        </div>
                        <div class="drag-item d-flex align-items-center border rounded p-2" draggable="true" data-type="water" data-sub="water">
                            <span class="fs-4 me-2">🚰</span>
                            <div>
                                <div class="fw-bold small">給水（手洗い）設備</div>
                                <div class="text-muted" style="font-size: 0.7rem;">保健所指定 (歩行20m制限)</div>
                            </div>
                        </div>
                        <div class="drag-item d-flex align-items-center border rounded p-2" draggable="true" data-type="claim" data-sub="warning">
                            <span class="fs-4 me-2">🛑</span>
                            <div>
                                <div class="fw-bold small">過去クレーム（注意点）</div>
                                <div class="text-muted" style="font-size: 0.7rem;">住民要望や配置禁止箇所</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 未配置の出店者リスト -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="fw-bold text-dark mb-0">未配置の出店者 (当選)</h6>
                    <span class="text-muted small" style="font-size: 0.75rem;">地図にドラッグして配置します</span>
                </div>
                <div class="card-body p-3 overflow-auto" style="max-height: 350px;">
                    @if($unplacedApplications->count() > 0)
                        <div class="d-flex flex-column gap-2" id="unplacedList">
                            @foreach($unplacedApplications as $app)
                                <div class="drag-item d-flex align-items-center border rounded p-2" draggable="true" data-type="gozaichi" data-app-id="{{ $app->id }}" data-sub="{{ $app->first_section_type }}">
                                    @if($app->first_section_type === 'B')
                                        <span class="badge bg-danger me-2">火B</span>
                                    @elseif($app->first_section_type === 'A')
                                        <span class="badge bg-success me-2">食A</span>
                                    @else
                                        <span class="badge bg-dark me-2">般</span>
                                    @endif
                                    <div style="flex: 1; min-width: 0;">
                                        <div class="fw-bold small text-truncate">{{ $app->shop_name }}</div>
                                        <div class="text-muted text-truncate" style="font-size: 0.7rem;">{{ $app->exhibitor_name }} ({{ $app->section_count }}区画)</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted small text-center my-3">すべての当選店舗が配置済みです。</p>
                    @endif
                </div>
            </div>
        @else
            <!-- 一般会員向けのヘルプ -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-3">
                    <div class="alert alert-info py-2 px-3 small mb-0">
                        ℹ️ <strong>閲覧モード:</strong><br>
                        一般会員の方は地図の閲覧のみ可能です。出店位置や備品の配置編集は行えません。
                    </div>
                </div>
            </div>
        @endif

        <!-- レイヤー切り替え -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h6 class="fw-bold text-dark mb-0">表示レイヤー設定</h6>
            </div>
            <div class="card-body p-3">
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" id="layer-gozaichi" checked>
                    <label class="form-check-label small" for="layer-gozaichi">ござ市出店者 (赤・緑・黒)</label>
                </div>
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" id="layer-facility" checked>
                    <label class="form-check-label small" for="layer-facility">付帯設備 (ゴミ箱、スピーカー等)</label>
                </div>
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" id="layer-water" checked>
                    <label class="form-check-label small" for="layer-water">保健所給水設備 & カバー円(20m)</label>
                </div>
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" id="layer-claim" checked>
                    <label class="form-check-label small" for="layer-claim">過去クレーム（注意エリア）</label>
                </div>
            </div>
        </div>
    </div>

    <!-- 地図表示エリア -->
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-2 position-relative bg-white" style="overflow-x: auto;">
                <div id="mapWrapper" class="position-relative border mx-auto" style="width: 800px; height: 1130px; background-image: url('{{ asset('images/map_base.png') }}?v={{ $mapVersion }}'); background-size: 100% 100%; background-repeat: no-repeat; user-select: none;">

                    
                    <!-- スナップガイド線などのオーバーレイ表示 -->
                    <svg width="800" height="1130" viewBox="0 0 800 1130" xmlns="http://www.w3.org/2000/svg" class="position-absolute top-0 start-0 w-100 h-100" style="pointer-events: none; z-index: 1;">
                        <!-- スナップガイド線 (JSで吸い付き判定を行う基準線、デバッグ用に点線で薄く描画) -->
                        <line x1="240" y1="0" x2="240" y2="1130" stroke="#8c1d30" stroke-width="1" stroke-dasharray="2,2" opacity="0.3"/>
                        <line x1="284" y1="0" x2="284" y2="1130" stroke="#8c1d30" stroke-width="1" stroke-dasharray="2,2" opacity="0.3"/>
                        <line x1="602" y1="0" x2="602" y2="1130" stroke="#8c1d30" stroke-width="1" stroke-dasharray="2,2" opacity="0.3"/>
                        <line x1="646" y1="0" x2="646" y2="1130" stroke="#8c1d30" stroke-width="1" stroke-dasharray="2,2" opacity="0.3"/>
                    </svg>

                    <!-- SVGカバー円（保健所の給水カバー、注意警告の範囲）の動的表示用レイヤー -->
                    <svg id="dynamicSvgOverlay" width="800" height="1130" viewBox="0 0 800 1130" class="position-absolute top-0 start-0 w-100 h-100" style="pointer-events: none; z-index: 2;"></svg>

                    <!-- マーカー（ピン）配置用のコンテナ -->
                    <div id="markersContainer" class="position-absolute top-0 start-0 w-100 h-100" style="z-index: 3;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- マーカー編集用モーダル -->
<div class="modal fade" id="markerModal" tabindex="-1" aria-labelledby="markerModalLabel" aria-hidden="true" style="z-index: 9999;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="markerModalLabel">オブジェクトの編集</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="markerForm">
                <div class="modal-body">
                    <input type="hidden" id="modal-marker-id">
                    <div class="mb-3">
                        <label for="modal-marker-name" class="form-label fw-bold small">名称</label>
                        <input type="text" class="form-control" id="modal-marker-name" required>
                    </div>
                    <div class="mb-3">
                        <label for="modal-marker-description" class="form-label fw-bold small">詳細説明・注意書き</label>
                        <textarea class="form-control" id="modal-marker-description" rows="3" placeholder="クレーム詳細や備品メモなど"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    @if($canEdit)
                        <button type="button" class="btn btn-danger" id="deleteMarkerBtn">削除</button>
                        <button type="submit" class="btn btn-primary">保存</button>
                    @else
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ページ内独自のCSSスタイル -->
<style>
    .drag-item {
        cursor: grab;
        background-color: #fff;
        transition: all 0.2s ease;
        user-select: none;
    }
    .drag-item:hover {
        border-color: var(--primary-color) !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .drag-item:active {
        cursor: grabbing;
    }
    
    /* マップ上の配置マーカーピンのCSS */
    .map-pin {
        position: absolute;
        width: 32px;
        height: 32px;
        margin-left: -16px;
        margin-top: -16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        border-radius: 50%;
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        cursor: pointer;
        z-index: 10;
        transition: transform 0.15s ease;
    }
    .map-pin:hover {
        transform: scale(1.15);
        z-index: 100;
    }
    
    /* マーカータイプ別スタイル */
    .pin-gozaichi {
        border: 2px solid #fff;
    }
    .pin-gozaichi.sub-B { background-color: #dc3545; color: #fff; } /* 赤：火器あり */
    .pin-gozaichi.sub-A { background-color: #198754; color: #fff; } /* 緑：食品のみ */
    .pin-gozaichi.sub-general { background-color: #212529; color: #fff; } /* 黒：一般 */
    
    .pin-facility { background-color: #fff; border: 2px solid #fd7e14; color: #fd7e14; }
    .pin-water { background-color: #e0f2fe; border: 2px solid #0284c7; color: #0284c7; }
    .pin-event { background-color: #fffbeb; border: 2px solid #d97706; color: #d97706; }
    .pin-claim { background-color: #fef2f2; border: 2px solid #dc2626; color: #dc2626; font-weight: bold; }
    
    /* ピン内の警告マーク */
    .pin-warning-badge {
        position: absolute;
        top: -6px;
        right: -6px;
        width: 14px;
        height: 14px;
        background-color: #ffc107;
        color: #000;
        border-radius: 50%;
        font-size: 10px;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }
</style>

<script src="/js/goza/map.js" defer></script>

<!-- ベースマップ差し替え用モーダル (管理者のみ) -->
@if(auth()->user()->isSystemAdmin())
<div class="modal fade" id="uploadMapModal" tabindex="-1" aria-labelledby="uploadMapModalLabel" aria-hidden="true" style="z-index: 9999;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="uploadMapModalLabel">ベースマップPDFの差し替え</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="uploadMapForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning py-2 px-3 small">
                        ⚠️ <strong>重要:</strong> 最新の配置用平面図PDFをアップロードしてください。アップロード後に高解像度画像（PNG）へ自動変換され、地図の背景画像が差し替わります。
                    </div>
                    <div class="mb-3">
                        <label for="map_pdf" class="form-label fw-bold small">平面図PDFファイル (最大10MB)</label>
                        <input type="file" class="form-control" id="map_pdf" name="map_pdf" accept="application/pdf" required>
                        <div class="form-text text-muted" style="font-size: 0.75rem;">※.pdf 形式のみ対応しています。変換には数秒かかります。</div>
                    </div>
                    
                    <!-- 進行中表示 -->
                    <div id="uploadProgress" class="d-none text-center py-3">
                        <div class="spinner-border text-primary mb-2" role="status"></div>
                        <div class="small fw-bold text-primary">PDFを画像に変換中...</div>
                        <div class="text-muted small">この処理には数秒かかります。画面を閉じずにお待ちください。</div>
                    </div>
                    
                    <!-- エラーメッセージ表示 -->
                    <div id="uploadError" class="alert alert-danger d-none py-2 px-3 small mt-2"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelUploadBtn">キャンセル</button>
                    <button type="submit" class="btn btn-primary" id="submitUploadBtn">アップロードして差し替え</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
