@extends('layouts.app')

@section('title', '出店場所地図配置')

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center">
    <div>
        <h3 class="fw-bold text-dark mb-1">出店場所地図配置</h3>
        <p class="text-muted small">会場マップ上での出店ブース・付帯設備・給水設備等の配置調整</p>
    </div>
    <div>
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
                <div id="mapWrapper" class="position-relative border mx-auto" style="width: 800px; height: 1130px; background-image: url('{{ asset('images/map_base.png') }}'); background-size: 100% 100%; background-repeat: no-repeat; user-select: none;">
                    
                    <!-- スナップガイド線などのオーバーレイ表示 -->
                    <svg width="800" height="1130" viewBox="0 0 800 1130" xmlns="http://www.w3.org/2000/svg" class="position-absolute top-0 start-0 w-100 h-100" style="pointer-events: none; z-index: 1;">
                        <!-- スナップガイド線 (JSで吸い付き判定を行う基準線、デバッグ用に点線で薄く描画) -->
                        <line x1="365" y1="0" x2="365" y2="1130" stroke="#8c1d30" stroke-width="1" stroke-dasharray="2,2" opacity="0.3"/>
                        <line x1="435" y1="0" x2="435" y2="1130" stroke="#8c1d30" stroke-width="1" stroke-dasharray="2,2" opacity="0.3"/>
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

<!-- 配置・吸い付き・警告ロジックのJavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const canEdit = @json($canEdit);
        const mapWrapper = document.getElementById('mapWrapper');
        const markersContainer = document.getElementById('markersContainer');
        const dynamicSvgOverlay = document.getElementById('dynamicSvgOverlay');
        const markerModal = new bootstrap.Modal(document.getElementById('markerModal'));
        const markerForm = document.getElementById('markerForm');
        
        let activeMarkers = [];
        let draggedElementData = null;

        // --- ドラッグ＆ドロップ用データの捕捉 ---
        document.querySelectorAll('.drag-item').forEach(item => {
            item.addEventListener('dragstart', function(e) {
                draggedElementData = {
                    type: this.dataset.type,
                    sub: this.dataset.sub,
                    appId: this.dataset.appId || null,
                    name: this.querySelector('.fw-bold').textContent
                };
            });
        });

        mapWrapper.addEventListener('dragover', function(e) {
            if (!canEdit) return;
            e.preventDefault();
        });

        mapWrapper.addEventListener('drop', function(e) {
            if (!canEdit || !draggedElementData) return;
            e.preventDefault();

            // ドロップされた位置の％座標を計算
            const rect = mapWrapper.getBoundingClientRect();
            let x = ((e.clientX - rect.left) / rect.width) * 100;
            let y = ((e.clientY - rect.top) / rect.height) * 100;

            // スナップ（吸い付き）効果
            const snapResult = applySnap(x, y);
            x = snapResult.x;
            y = snapResult.y;

            // マーカー保存リクエスト
            saveMarker({
                marker_type: draggedElementData.type,
                sub_type: draggedElementData.sub,
                x_position: x,
                y_position: y,
                name: draggedElementData.name,
                application_id: draggedElementData.appId
            });

            draggedElementData = null;
        });

        // --- 吸い付き（スナップ）計算ロジック ---
        // 道路に沿ったガイドライン（X座標365、または435付近）に吸い付ける
        function applySnap(x, y) {
            // 地図の画像サイズにおける道路沿いのライン比率
            const leftGuidePercent = (365 / 800) * 100; // 約45.6%
            const rightGuidePercent = (435 / 800) * 100; // 約54.3%
            const snapThreshold = 3.5; // 吸い付く閾値（約3%以内）

            let targetX = x;
            
            if (Math.abs(x - leftGuidePercent) < snapThreshold) {
                targetX = leftGuidePercent;
            } else if (Math.abs(x - rightGuidePercent) < snapThreshold) {
                targetX = rightGuidePercent;
            }

            return { x: targetX, y: y };
        }

        // --- マーカーデータ保存API送信 ---
        function saveMarker(data) {
            fetch("{{ route('goza.map.storeMarker') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            })
            .then(res => {
                if (res.status === 201) {
                    // 保存成功したらリロードまたは非同期再取得
                    location.reload(); 
                } else {
                    alert('マーカーの配置に失敗しました。');
                }
            });
        }

        // --- マーカー情報取得＆表示処理 ---
        function loadMarkers() {
            fetch("{{ route('goza.map.markers') }}")
            .then(res => res.json())
            .then(markers => {
                activeMarkers = markers;
                renderMarkers();
                renderWarnings();
            });
        }

        function renderMarkers() {
            markersContainer.innerHTML = '';
            
            // レイヤーのチェックボックス状態
            const showGoza = document.getElementById('layer-gozaichi').checked;
            const showFacility = document.getElementById('layer-facility').checked;
            const showWater = document.getElementById('layer-water').checked;
            const showClaim = document.getElementById('layer-claim').checked;

            activeMarkers.forEach(m => {
                // レイヤーによるフィルタリング
                if (m.marker_type === 'gozaichi' && !showGoza) return;
                if (m.marker_type === 'facility' && !showFacility) return;
                if (m.marker_type === 'water' && !showWater) return;
                if (m.marker_type === 'claim' && !showClaim) return;

                const pin = document.createElement('div');
                pin.className = `map-pin pin-${m.marker_type} sub-${m.sub_type}`;
                pin.style.left = `${m.x_position}%`;
                pin.style.top = `${m.y_position}%`;
                pin.dataset.id = m.id;
                
                // 表示用アイコン
                let icon = '📍';
                if (m.marker_type === 'gozaichi') icon = m.sub_type === 'B' ? '🔥' : (m.sub_type === 'A' ? '🥗' : '🛍️');
                else if (m.marker_type === 'facility') {
                    if (m.sub_type === 'trash') icon = '🗑️';
                    else if (m.sub_type === 'speaker') icon = '🔊';
                    else if (m.sub_type === 'toilet') icon = '🚾';
                    else if (m.sub_type === 'cone') icon = '🚧';
                }
                else if (m.marker_type === 'water') icon = '🚰';
                else if (m.marker_type === 'claim') icon = '⚠️';
                
                pin.innerHTML = icon;
                pin.title = m.name;

                // 幹事・管理者のみピンのドラッグ（再移動）を許可
                if (canEdit) {
                    pin.setAttribute('draggable', 'true');
                    pin.addEventListener('dragstart', function(e) {
                        e.stopPropagation();
                        // 地図上で再移動中のドラッグ
                        draggedElementData = {
                            id: m.id,
                            type: m.marker_type
                        };
                    });
                }

                // マーカークリック時のモーダル表示
                pin.addEventListener('click', function() {
                    openEditModal(m);
                });

                markersContainer.appendChild(pin);
            });
        }

        // 地図上でのマーカーのドラッグ＆ドロップ移動処理
        if (canEdit) {
            markersContainer.addEventListener('dragover', function(e) {
                e.preventDefault();
            });

            markersContainer.addEventListener('drop', function(e) {
                if (draggedElementData && draggedElementData.id) {
                    e.preventDefault();
                    const rect = mapWrapper.getBoundingClientRect();
                    let x = ((e.clientX - rect.left) / rect.width) * 100;
                    let y = ((e.clientY - rect.top) / rect.height) * 100;

                    // 吸い付き補正
                    const snapResult = applySnap(x, y);
                    x = snapResult.x;
                    y = snapResult.y;

                    // API経由で座標のみ更新
                    updateMarkerCoords(draggedElementData.id, x, y);
                    draggedElementData = null;
                }
            });
        }

        function updateMarkerCoords(id, x, y) {
            fetch(`{{ url('/goza/map/markers') }}/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ x_position: x, y_position: y })
            })
            .then(res => res.json())
            .then(() => {
                loadMarkers(); // 位置を再ロード
            });
        }

        // --- 保健所の給水制限カバー円 & 警告の動的描画 ---
        function renderWarnings() {
            dynamicSvgOverlay.innerHTML = '';
            
            const showWater = document.getElementById('layer-water').checked;
            const showClaim = document.getElementById('layer-claim').checked;
            
            // 1. 給水設備のカバー円を描画
            const waterMarkers = activeMarkers.filter(m => m.marker_type === 'water');
            if (showWater) {
                waterMarkers.forEach(wm => {
                    // 歩行距離20mに相当する地図上の円（20mは画像上で約直径250px＝半径15%）
                    const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                    circle.setAttribute('cx', `${wm.x_position}%`);
                    circle.setAttribute('cy', `${wm.y_position}%`);
                    circle.setAttribute('r', '15%');
                    circle.setAttribute('fill', 'rgba(2, 132, 199, 0.05)');
                    circle.setAttribute('stroke', '#0284c7');
                    circle.setAttribute('stroke-width', '1.5');
                    circle.setAttribute('stroke-dasharray', '5,5');
                    dynamicSvgOverlay.appendChild(circle);
                });
            }

            // 2. クレーム多発地点の警告範囲を描画
            const claimMarkers = activeMarkers.filter(m => m.marker_type === 'claim');
            if (showClaim) {
                claimMarkers.forEach(cm => {
                    const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                    circle.setAttribute('cx', `${cm.x_position}%`);
                    circle.setAttribute('cy', `${cm.y_position}%`);
                    circle.setAttribute('r', '6%'); // クレーム範囲
                    circle.setAttribute('fill', 'rgba(220, 38, 38, 0.08)');
                    circle.setAttribute('stroke', '#dc2626');
                    circle.setAttribute('stroke-width', '1');
                    circle.setAttribute('stroke-dasharray', '3,3');
                    dynamicSvgOverlay.appendChild(circle);
                });
            }

            // 3. 各出店者（火器・食品）が給水カバー円に含まれているかチェック＆接近警告判定
            activeMarkers.forEach(m => {
                if (m.marker_type === 'gozaichi') {
                    const app = m.application;
                    if (!app) return;

                    let inWaterCircle = false;
                    
                    // 給水設備のいずれかから15%以内にあるか
                    waterMarkers.forEach(wm => {
                        const dist = Math.sqrt(Math.pow(m.x_position - wm.x_position, 2) + Math.pow(m.y_position - wm.y_position, 2));
                        if (dist <= 15) {
                            inWaterCircle = true;
                        }
                    });

                    // クレーム警戒エリアへの接近確認 (6%以内)
                    let nearClaim = false;
                    claimMarkers.forEach(cm => {
                        const dist = Math.sqrt(Math.pow(m.x_position - cm.x_position, 2) + Math.pow(m.y_position - cm.y_position, 2));
                        if (dist <= 6) {
                            nearClaim = true;
                        }
                    });

                    const pinElement = document.querySelector(`.map-pin[data-id="${m.id}"]`);
                    if (pinElement) {
                        // 給水制限の警告表示 (火器使用飲食B、または火器なし食品Aのみ対象)
                        if ((m.sub_type === 'B' || m.sub_type === 'A') && !inWaterCircle) {
                            const badge = document.createElement('div');
                            badge.className = 'pin-warning-badge';
                            badge.textContent = '!';
                            badge.title = '保健所指導警告: 給水設備から歩行20m以上離れています！';
                            pinElement.appendChild(badge);
                        }

                        // クレームエリア警告表示
                        if (nearClaim) {
                            pinElement.style.boxShadow = '0 0 10px #ff3333, 0 2px 6px rgba(0,0,0,0.4)';
                            pinElement.title += ' (⚠️注意: クレーム制限エリアへの接近警告あり)';
                        }
                    }
                }
            });
        }

        // --- モーダル表示・更新・削除 ---
        function openEditModal(m) {
            document.getElementById('modal-marker-id').value = m.id;
            document.getElementById('modal-marker-name').value = m.name;
            document.getElementById('modal-marker-description').value = m.description || '';
            
            document.getElementById('markerModalLabel').textContent = m.marker_type === 'gozaichi' ? '出店店舗の確認' : '配置オブジェクトの編集';
            
            // ござ市ピンの場合は名前の直接編集をさせない（屋号固定）
            if (m.marker_type === 'gozaichi') {
                document.getElementById('modal-marker-name').setAttribute('readonly', 'readonly');
            } else {
                document.getElementById('modal-marker-name').removeAttribute('readonly');
            }

            markerModal.show();
        }

        // マーカー更新
        markerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!canEdit) return;

            const id = document.getElementById('modal-marker-id').value;
            const name = document.getElementById('modal-marker-name').value;
            const desc = document.getElementById('modal-marker-description').value;

            fetch(`{{ url('/goza/map/markers') }}/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ name: name, description: desc, x_position: activeMarkers.find(x => x.id == id).x_position, y_position: activeMarkers.find(x => x.id == id).y_position })
            })
            .then(res => res.json())
            .then(() => {
                markerModal.hide();
                loadMarkers();
            });
        });

        // マーカー削除
        document.getElementById('deleteMarkerBtn').addEventListener('click', function() {
            if (!canEdit || !confirm('この配置オブジェクトを削除しますか？')) return;
            const id = document.getElementById('modal-marker-id').value;

            fetch(`{{ url('/goza/map/markers') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(() => {
                markerModal.hide();
                location.reload(); // リロードして未配置リストに戻す
            });
        });

        // レイヤー切り替えトグルの監視
        ['layer-gozaichi', 'layer-facility', 'layer-water', 'layer-claim'].forEach(id => {
            document.getElementById(id).addEventListener('change', () => {
                renderMarkers();
                renderWarnings();
            });
        });

        // 初回ロード
        loadMarkers();
    });
</script>
@endsection
