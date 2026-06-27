// 備品管理機能専用JavaScript

document.addEventListener('DOMContentLoaded', function () {
    // 1. 画像アップロードのリアルタイムプレビュー
    const imageInputs = document.querySelectorAll('.equipment-image-input');
    imageInputs.forEach(input => {
        input.addEventListener('change', function (e) {
            const previewId = this.dataset.preview;
            const previewContainer = document.getElementById(previewId);
            if (!previewContainer) return;

            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    previewContainer.innerHTML = `<img src="${event.target.result}" class="img-fluid" alt="プレビュー">`;
                };
                reader.readAsDataURL(file);
            } else {
                previewContainer.innerHTML = '<span class="text-muted small">クリックして画像を選択</span>';
            }
        });
    });

    // 2. 備品マスタ編集モーダルへのデータ流し込み
    const editButtons = document.querySelectorAll('.btn-edit-equipment');
    const editModal = document.getElementById('editEquipmentModal');
    if (editModal) {
        const form = editModal.querySelector('form');
        editButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const spec = this.dataset.specifications;
                const qty = this.dataset.quantity;
                const unit = this.dataset.unit;
                const price = this.dataset.unit_price;
                const category = this.dataset.category;
                const ownership = this.dataset.ownership_type;
                const desc = this.dataset.description;
                const imagePath = this.dataset.image;

                // アクションURLの書き換え
                form.action = `/equipment/master/update/${id}`;

                // 値のセット
                form.querySelector('[name="name"]').value = name;
                form.querySelector('[name="specifications"]').value = spec || '';
                form.querySelector('[name="quantity"]').value = qty;
                form.querySelector('[name="unit"]').value = unit;
                form.querySelector('[name="category"]').value = category;
                form.querySelector('[name="ownership_type"]').value = ownership;
                form.querySelector('[name="description"]').value = desc || '';
                
                // 金額（単価）フィールド（存在する場合のみ）
                const priceInput = form.querySelector('[name="unit_price"]');
                if (priceInput) {
                    priceInput.value = price || '';
                }

                // 既存画像のプレビュー表示
                const previewContainer = document.getElementById('editImagePreview');
                if (previewContainer) {
                    if (imagePath) {
                        previewContainer.innerHTML = `<img src="/storage/${imagePath}" class="img-fluid" alt="現在の画像">`;
                    } else {
                        previewContainer.innerHTML = '<span class="text-muted small">画像未登録</span>';
                    }
                }
            });
        });
    }

    // 3. 貸出ステータス更新モーダルへのデータ流し込み
    const loanButtons = document.querySelectorAll('.btn-update-loan');
    const loanModal = document.getElementById('updateLoanModal');
    if (loanModal) {
        const form = loanModal.querySelector('form');
        loanButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                const status = this.dataset.status;
                const req = this.dataset.requested;
                const loaned = this.dataset.loaned;
                const returned = this.dataset.returned;
                const notes = this.dataset.notes;
                const eqName = this.dataset.equipment_name;

                // アクションURL書き換え
                form.action = `/equipment/loan/update/${id}`;

                // 情報表示
                document.getElementById('loanEquipmentName').textContent = eqName;
                document.getElementById('loanRequestedQty').textContent = req;

                // 値のセット
                form.querySelector('[name="status"]').value = status;
                form.querySelector('[name="quantity_loaned"]').value = loaned;
                form.querySelector('[name="quantity_returned"]').value = returned;
                form.querySelector('[name="notes"]').value = notes || '';
            });
        });
    }

    // 4. タブ切り替え時の遅延表示や将来の Mermaid.js 用イベントハンドラ
    const tabElements = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabElements.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (event) {
            // タブ切り替え後の処理（必要に応じて描画調整などを入れる）
            const targetId = event.target.getAttribute('data-bs-target');
            if (targetId === '#loans') {
                // 貸出タブ表示時の追加アクションがあれば記述
            }
        });
    });
});
