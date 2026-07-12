document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('whiteboard_image_ai');
    const analyzeButton = document.getElementById('btn-analyze-whiteboard');
    const minutesTextarea = document.getElementById('minutes');
    const loader = document.getElementById('ai-loader');
    const errorMessage = document.getElementById('ai-error-message');
    const submitButton = document.querySelector('button[type="submit"]');

    if (!fileInput || !analyzeButton || !minutesTextarea) {
        return;
    }

    // ファイル選択時のボタン制御
    fileInput.addEventListener('change', function () {
        if (fileInput.files.length > 0) {
            analyzeButton.removeAttribute('disabled');
            analyzeButton.classList.remove('btn-secondary');
            analyzeButton.classList.add('btn-primary');
        } else {
            analyzeButton.setAttribute('disabled', 'disabled');
            analyzeButton.classList.remove('btn-primary');
            analyzeButton.classList.add('btn-secondary');
        }
    });

    // 解析実行
    analyzeButton.addEventListener('click', function () {
        const file = fileInput.files[0];
        if (!file) {
            return;
        }

        // すでに入力がある場合の上書き確認
        if (minutesTextarea.value.trim() !== '') {
            const confirmOverwrite = confirm('すでに議事録が入力されています。AIが生成した下書きで上書きしてもよろしいですか？（現在の入力内容は失われます）');
            if (!confirmOverwrite) {
                return;
            }
        }

        const analyzeUrl = analyzeButton.getAttribute('data-url');
        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

        // UI状態の更新（処理中）
        analyzeButton.setAttribute('disabled', 'disabled');
        fileInput.setAttribute('disabled', 'disabled');
        if (submitButton) {
            submitButton.setAttribute('disabled', 'disabled');
        }
        if (loader) {
            loader.classList.remove('d-none');
        }
        if (errorMessage) {
            errorMessage.classList.add('d-none');
            errorMessage.textContent = '';
        }

        const formData = new FormData();
        formData.append('image', file);

        fetch(analyzeUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            return response.json().then(data => {
                if (!response.ok) {
                    throw new Error(data.message || '解析に失敗しました。');
                }
                return data;
            });
        })
        .then(data => {
            if (data.success && data.minutes_draft) {
                minutesTextarea.value = data.minutes_draft;
                // ファイル入力をリセット
                fileInput.value = '';
                analyzeButton.setAttribute('disabled', 'disabled');
                analyzeButton.classList.remove('btn-primary');
                analyzeButton.classList.add('btn-secondary');
            } else {
                throw new Error('解析結果が空でした。');
            }
        })
        .catch(err => {
            console.error('AI Analysis Error:', err);
            if (errorMessage) {
                errorMessage.textContent = err.message || '通信エラーが発生しました。お手数ですが、時間をおいて再度お試しください。';
                errorMessage.classList.remove('d-none');
            }
        })
        .finally(() => {
            // UI状態の復帰
            fileInput.removeAttribute('disabled');
            if (submitButton) {
                submitButton.removeAttribute('disabled');
            }
            if (loader) {
                loader.classList.add('d-none');
            }
            // 画像がまだ選択されていれば活性状態に戻す
            if (fileInput.files.length > 0) {
                analyzeButton.removeAttribute('disabled');
                analyzeButton.classList.remove('btn-secondary');
                analyzeButton.classList.add('btn-primary');
            }
        });
    });
});
