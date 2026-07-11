// 安全管理モジュール用 JavaScript

console.log("[Safety debug] safety.js loaded, typeof mermaid:", typeof mermaid);

document.addEventListener('DOMContentLoaded', function () {
    console.log("[Safety debug] DOMContentLoaded fired, typeof mermaid:", typeof mermaid);
    // Mermaid の初期化 (自動描画は無効にする)
    if (typeof mermaid !== 'undefined') {
        console.log("[Safety debug] Initializing Mermaid...");
        mermaid.initialize({
            startOnLoad: false,  // 自動描画を防ぐ
            theme: 'default',
            securityLevel: 'loose',
            flowchart: {
                useWidth: true,
                htmlLabels: true
            }
        });
        
        // 初期状態でアクティブなタブの中の Mermaid 要素を描画する（もしあれば）
        const activeTabPane = document.querySelector('.tab-pane.active');
        if (activeTabPane) {
            console.log("[Safety debug] Active tab pane found:", activeTabPane.id);
            renderMermaidInContainer(activeTabPane);
        }
    } else {
        console.warn("[Safety debug] Mermaid is undefined during DOMContentLoaded!");
    }


    // Bootstrap タブ切り替え時の Mermaid 描画制御
    const tabElList = document.querySelectorAll('button[data-bs-toggle="tab"]');
    console.log("[Safety debug] Registered tab elements count:", tabElList.length);
    tabElList.forEach(tabEl => {
        tabEl.addEventListener('shown.bs.tab', function (event) {
            console.log("[Safety debug] Tab shown event fired for:", event.target.id);
            if (typeof mermaid !== 'undefined') {
                // 切り替え先のタブコンテナを取得
                const targetId = event.target.getAttribute('data-bs-target');
                const targetPane = document.querySelector(targetId);
                console.log("[Safety debug] Target pane selector:", targetId, "Found pane:", !!targetPane);
                if (targetPane) {
                    renderMermaidInContainer(targetPane);
                }
            }
        });
    });

    // 指定されたコンテナ内の未描画の Mermaid 要素をレンダリングする関数
    function renderMermaidInContainer(container) {
        const mermaidElements = container.querySelectorAll('.mermaid-diagram');
        const unrenderedElements = [];
        
        mermaidElements.forEach(el => {
            // すでに処理済みか確認（Mermaid が処理すると data-processed 属性が付く）
            if (!el.hasAttribute('data-processed')) {
                unrenderedElements.push(el);
            }
        });
        
        if (unrenderedElements.length > 0) {
            unrenderedElements.forEach(el => {
                // Mermaidテーマ適用のために一時的にクラスを付与
                el.classList.add('mermaid');
                
                // innerHTMLから文字列を取得し、正規表現でエンティティをデコードする
                // これによりDOMパーサーが改行や<br>タグを消去するのを防ぐ
                let rawHtml = el.innerHTML;
                let decodedText = rawHtml
                    .replace(/&lt;/g, '<')
                    .replace(/&gt;/g, '>')
                    .replace(/&amp;/g, '&');
                
                el.textContent = decodedText;
            });

            // Mermaid v10 の run を使って特定のノード群のみを描画
            mermaid.run({
                nodes: unrenderedElements
            }).catch(err => {
                console.error("Mermaid rendering failed:", err);
            });
        }
    }

    // モーダル表示時のMermaidレンダリング制御
    const modalEl = document.getElementById('escalationFlowModal');
    if (modalEl) {
        modalEl.addEventListener('shown.bs.modal', function () {
            console.log("[Safety debug] Modal shown, rendering modal Mermaid...");
            const modalMermaidEl = document.getElementById('modal-mermaid-diagram');
            const codeEl = document.getElementById('escalation-mermaid-code');
            if (modalMermaidEl && codeEl && !modalMermaidEl.hasAttribute('data-processed')) {
                modalMermaidEl.classList.add('mermaid');
                modalMermaidEl.textContent = codeEl.value.trim();

                mermaid.run({
                    nodes: [modalMermaidEl]
                }).catch(err => {
                    console.error("Modal Mermaid rendering failed:", err);
                });
            }
        });
    }

    // 避難指示発令時フロー モーダル表示時のMermaidレンダリング制御
    const evacModalEl = document.getElementById('evacuationFlowModal');
    if (evacModalEl) {
        evacModalEl.addEventListener('shown.bs.modal', function () {
            console.log("[Safety debug] Evacuation Modal shown, rendering modal Mermaid...");
            const modalMermaidEl = document.getElementById('modal-evacuation-diagram');
            const codeEl = document.getElementById('evacuation-mermaid-code');
            if (modalMermaidEl && codeEl && !modalMermaidEl.hasAttribute('data-processed')) {
                modalMermaidEl.classList.add('mermaid');
                modalMermaidEl.textContent = codeEl.value.trim();

                mermaid.run({
                    nodes: [modalMermaidEl]
                }).catch(err => {
                    console.error("Evacuation Modal Mermaid rendering failed:", err);
                });
            }
        });
    }
});
