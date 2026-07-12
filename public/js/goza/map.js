document.addEventListener('DOMContentLoaded', function() {
    const appData = document.getElementById('map-app-data');
    if (!appData) return;

    const canEdit = appData.dataset.canEdit === 'true';
    const markersUrl = appData.dataset.markersUrl;
    const uploadBaseUrl = appData.dataset.uploadBaseUrl;
    const csrfToken = appData.dataset.csrfToken;

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

    if (mapWrapper) {
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
    }

    // --- 吸い付き（スナップ）計算ロジック ---
    function applySnap(x, y) {
        const guides = [
            (240 / 800) * 100, // 左通り・左列 (30.0%)
            (284 / 800) * 100, // 左通り・右列 (35.5%)
            (602 / 800) * 100, // 右通り・左列 (75.25%)
            (646 / 800) * 100  // 右通り・右列 (80.75%)
        ];
        const snapThreshold = 3.5;

        let targetX = x;
        let minDiff = Infinity;

        guides.forEach(guide => {
            const diff = Math.abs(x - guide);
            if (diff < snapThreshold && diff < minDiff) {
                minDiff = diff;
                targetX = guide;
            }
        });

        return { x: targetX, y: y };
    }

    // --- マーカーデータ保存API送信 ---
    function saveMarker(data) {
        fetch("/goza/map/markers", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(async res => {
            if (res.status === 201) {
                location.reload(); 
            } else {
                const err = await res.json();
                throw new Error(err.message || '配置に失敗しました。');
            }
        })
        .catch(err => {
            console.error(err);
            alert(err.message || 'マーカーの配置に失敗しました。');
        });
    }

    // --- マーカー情報取得＆表示処理 ---
    function loadMarkers() {
        fetch(markersUrl)
        .then(res => res.json())
        .then(markers => {
            activeMarkers = markers;
            renderMarkers();
            renderWarnings();
        });
    }

    function renderMarkers() {
        if (!markersContainer) return;
        markersContainer.innerHTML = '';
        
        const showGoza = document.getElementById('layer-gozaichi').checked;
        const showFacility = document.getElementById('layer-facility').checked;
        const showWater = document.getElementById('layer-water').checked;
        const showClaim = document.getElementById('layer-claim').checked;

        activeMarkers.forEach(m => {
            if (m.marker_type === 'gozaichi' && !showGoza) return;
            if (m.marker_type === 'facility' && !showFacility) return;
            if (m.marker_type === 'water' && !showWater) return;
            if (m.marker_type === 'claim' && !showClaim) return;

            const pin = document.createElement('div');
            pin.className = `map-pin pin-${m.marker_type} sub-${m.sub_type}`;
            pin.style.left = `${m.x_position}%`;
            pin.style.top = `${m.y_position}%`;
            pin.dataset.id = m.id;
            
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

            if (canEdit) {
                pin.setAttribute('draggable', 'true');
                pin.addEventListener('dragstart', function(e) {
                    e.stopPropagation();
                    draggedElementData = {
                        id: m.id,
                        type: m.marker_type
                    };
                });
            }

            pin.addEventListener('click', function() {
                openEditModal(m);
            });

            markersContainer.appendChild(pin);
        });
    }

    if (canEdit && markersContainer) {
        markersContainer.addEventListener('dragover', function(e) {
            e.preventDefault();
        });

        markersContainer.addEventListener('drop', function(e) {
            if (draggedElementData && draggedElementData.id) {
                e.preventDefault();
                const rect = mapWrapper.getBoundingClientRect();
                let x = ((e.clientX - rect.left) / rect.width) * 100;
                let y = ((e.clientY - rect.top) / rect.height) * 100;

                const snapResult = applySnap(x, y);
                x = snapResult.x;
                y = snapResult.y;

                updateMarkerCoords(draggedElementData.id, x, y);
                draggedElementData = null;
            }
        });
    }

    function updateMarkerCoords(id, x, y) {
        fetch(`/goza/map/markers/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ x_position: x, y_position: y })
        })
        .then(async res => {
            if (!res.ok) {
                const err = await res.json();
                throw new Error(err.message || '移動に失敗しました。');
            }
            return res.json();
        })
        .then(() => {
            loadMarkers();
        })
        .catch(err => {
            console.error(err);
            alert(err.message || 'マーカーの移動に失敗しました。');
        });
    }

    function renderWarnings() {
        if (!dynamicSvgOverlay) return;
        dynamicSvgOverlay.innerHTML = '';
        
        const showWater = document.getElementById('layer-water').checked;
        const showClaim = document.getElementById('layer-claim').checked;
        
        const waterMarkers = activeMarkers.filter(m => m.marker_type === 'water');
        if (showWater) {
            waterMarkers.forEach(wm => {
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

        const claimMarkers = activeMarkers.filter(m => m.marker_type === 'claim');
        if (showClaim) {
            claimMarkers.forEach(cm => {
                const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                circle.setAttribute('cx', `${cm.x_position}%`);
                circle.setAttribute('cy', `${cm.y_position}%`);
                circle.setAttribute('r', '6%');
                circle.setAttribute('fill', 'rgba(220, 38, 38, 0.08)');
                circle.setAttribute('stroke', '#dc2626');
                circle.setAttribute('stroke-width', '1');
                circle.setAttribute('stroke-dasharray', '3,3');
                dynamicSvgOverlay.appendChild(circle);
            });
        }

        activeMarkers.forEach(m => {
            if (m.marker_type === 'gozaichi') {
                const app = m.application;
                if (!app) return;

                let inWaterCircle = false;
                waterMarkers.forEach(wm => {
                    const dist = Math.sqrt(Math.pow(m.x_position - wm.x_position, 2) + Math.pow(m.y_position - wm.y_position, 2));
                    if (dist <= 15) {
                        inWaterCircle = true;
                    }
                });

                let nearClaim = false;
                claimMarkers.forEach(cm => {
                    const dist = Math.sqrt(Math.pow(m.x_position - cm.x_position, 2) + Math.pow(m.y_position - cm.y_position, 2));
                    if (dist <= 6) {
                        nearClaim = true;
                    }
                });

                const pinElement = document.querySelector(`.map-pin[data-id="${m.id}"]`);
                if (pinElement) {
                    if ((m.sub_type === 'B' || m.sub_type === 'A') && !inWaterCircle) {
                        const badge = document.createElement('div');
                        badge.className = 'pin-warning-badge';
                        badge.textContent = '!';
                        badge.title = '保健所指導警告: 給水設備から歩行20m以上離れています！';
                        pinElement.appendChild(badge);
                    }

                    if (nearClaim) {
                        pinElement.style.boxShadow = '0 0 10px #ff3333, 0 2px 6px rgba(0,0,0,0.4)';
                        pinElement.title += ' (⚠️注意: クレーム制限エリアへの接近警告あり)';
                    }
                }
            }
        });
    }

    function openEditModal(m) {
        document.getElementById('modal-marker-id').value = m.id;
        document.getElementById('modal-marker-name').value = m.name;
        document.getElementById('modal-marker-description').value = m.description || '';
        
        document.getElementById('markerModalLabel').textContent = m.marker_type === 'gozaichi' ? '出店店舗の確認' : '配置オブジェクトの編集';
        
        if (m.marker_type === 'gozaichi') {
            document.getElementById('modal-marker-name').setAttribute('readonly', 'readonly');
        } else {
            document.getElementById('modal-marker-name').removeAttribute('readonly');
        }

        markerModal.show();
    }

    if (markerForm) {
        markerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!canEdit) return;

            const id = document.getElementById('modal-marker-id').value;
            const name = document.getElementById('modal-marker-name').value;
            const desc = document.getElementById('modal-marker-description').value;

            fetch(`/goza/map/markers/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name: name, description: desc, x_position: activeMarkers.find(x => x.id == id).x_position, y_position: activeMarkers.find(x => x.id == id).y_position })
            })
            .then(async res => {
                if (!res.ok) {
                    const err = await res.json();
                    throw new Error(err.message || '更新に失敗しました。');
                }
                return res.json();
            })
            .then(() => {
                markerModal.hide();
                loadMarkers();
            })
            .catch(err => {
                console.error(err);
                alert(err.message || 'マーカーの更新に失敗しました。');
            });
        });
    }

    const deleteMarkerBtn = document.getElementById('deleteMarkerBtn');
    if (deleteMarkerBtn) {
        deleteMarkerBtn.addEventListener('click', function() {
            if (!canEdit || !confirm('この配置オブジェクトを削除しますか？')) return;
            const id = document.getElementById('modal-marker-id').value;

            fetch(`/goza/map/markers/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(async res => {
                if (!res.ok) {
                    const err = await res.json();
                    throw new Error(err.message || '削除に失敗しました。');
                }
                return res.json();
            })
            .then(() => {
                markerModal.hide();
                location.reload();
            })
            .catch(err => {
                console.error(err);
                alert(err.message || 'マーカーの削除に失敗しました。');
            });
        });
    }

    ['layer-gozaichi', 'layer-facility', 'layer-water', 'layer-claim'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', () => {
                renderMarkers();
                renderWarnings();
            });
        }
    });

    loadMarkers();

    const uploadMapForm = document.getElementById('uploadMapForm');
    if (uploadMapForm) {
        const submitUploadBtn = document.getElementById('submitUploadBtn');
        const cancelUploadBtn = document.getElementById('cancelUploadBtn');
        const uploadProgress = document.getElementById('uploadProgress');
        const uploadError = document.getElementById('uploadError');

        uploadMapForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(uploadMapForm);
            
            submitUploadBtn.setAttribute('disabled', 'disabled');
            cancelUploadBtn.setAttribute('disabled', 'disabled');
            uploadProgress.classList.remove('d-none');
            uploadError.classList.add('d-none');

            fetch(uploadBaseUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.error || 'アップロードまたは画像変換に失敗しました。');
                }
                return data;
            })
            .then(() => {
                alert('ベースマップの差し替えに成功しました。');
                window.location.reload();
            })
            .catch(err => {
                console.error(err);
                uploadError.textContent = err.message;
                uploadError.classList.remove('d-none');
            })
            .finally(() => {
                submitUploadBtn.removeAttribute('disabled');
                cancelUploadBtn.removeAttribute('disabled');
                uploadProgress.classList.add('d-none');
            });
        });
    }
});
