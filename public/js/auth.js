/**
 * 保土ケ谷宿場まつり実行委員会 実務管理システム
 * WebAuthn (パスキー) 認証用 Vanilla JS スクリプト
 */

document.addEventListener('DOMContentLoaded', () => {
    // ログイン画面の要素
    const loginForm = document.getElementById('login-form');
    const emailInput = document.getElementById('email-input');
    const passwordField = document.getElementById('password-field');
    const passwordInput = document.getElementById('password-input');
    const nextButton = document.getElementById('btn-next');
    const submitButton = document.getElementById('btn-submit');
    const errorAlert = document.getElementById('error-alert');
    // 現在アクティブな WebAuthn 認証要求の AbortController と Promise
    let activeAbortController = null;
    let activePromise = null;

    // パスキー登録画面の要素
    const registerKeyBtn = document.getElementById('btn-register-passkey');
    const registerSuccessAlert = document.getElementById('register-success-alert');

    // 1. Base64URL ⇔ ArrayBuffer 変換ユーティリティ
    function bufferToBase64url(buffer) {
        const bytes = new Uint8Array(buffer);
        let binary = '';
        for (let i = 0; i < bytes.byteLength; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        return btoa(binary)
            .replace(/\+/g, '-')
            .replace(/\//g, '_')
            .replace(/=/g, '');
    }

    function base64urlToBuffer(base64url) {
        const base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');
        const pad = base64.length % 4;
        const padded = pad ? base64 + '='.repeat(4 - pad) : base64;
        const binary = atob(padded);
        const buffer = new Uint8Array(binary.length);
        for (let i = 0; i < binary.length; i++) {
            buffer[i] = binary.charCodeAt(i);
        }
        return buffer.buffer;
    }

    // CSRF トークン取得ヘルパー
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    // エラーメッセージ表示
    function showError(message) {
        if (errorAlert) {
            errorAlert.textContent = message;
            errorAlert.classList.remove('d-none');
        } else {
            alert(message);
        }
    }

    // 2. ログイン画面の挙動制御 (Passkey-First / Conditional UI)
    if (loginForm && emailInput) {
        // CSRFトークンをリクエストヘッダーに自動セットする
        const csrfToken = getCsrfToken();

        // --- A. Conditional UI (オートフィルによる提案ログイン) の監視開始 ---
        if (window.PublicKeyCredential && PublicKeyCredential.isConditionalMediationAvailable) {
            PublicKeyCredential.isConditionalMediationAvailable().then((available) => {
                if (available) {
                    startConditionalLogin(csrfToken);
                }
            });
        }

        // --- B. ID手動入力時の「次へ」ボタン押下フロー ---
        if (nextButton) {
            nextButton.addEventListener('click', async () => {
                const email = emailInput.value.trim();
                if (!email) {
                    showError('メールアドレスを入力してください。');
                    return;
                }

                // エラー表示クリア
                if (errorAlert) errorAlert.classList.add('d-none');

                try {
                    // パスキーの有無をサーバーに非同期問い合わせ
                    const checkResponse = await fetch('/webauthn/login/check', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ email })
                    });

                    if (!checkResponse.ok) {
                        throw new Error('ユーザー情報の確認に失敗しました。');
                    }

                    const checkData = await checkResponse.json();

                    if (checkData.has_passkey) {
                        // パスキーがある場合：パスキー認証ダイアログを起動
                        await executeWebAuthnLogin(email, csrfToken);
                    } else {
                        // パスキーがない場合：エラーを表示
                        showError('パスキーが登録されていません。管理者にパスキー登録URLの発行を依頼してください。');
                    }
                } catch (err) {
                    showError(err.message);
                }
            });
        }

        // --- B2. 「パスキーでログイン」ボタン直接押下フロー ---
        const passkeyLoginButton = document.getElementById('btn-passkey-login');
        if (passkeyLoginButton) {
            passkeyLoginButton.addEventListener('click', async () => {
                if (errorAlert) errorAlert.classList.add('d-none');
                // メールアドレス指定なしで、通常のモーダルパスキー認証を起動
                await executeWebAuthnLogin(null, csrfToken, false);
            });
        }
    }

    // --- C. パスキーログインの実行 (生体認証起動) ---
    async function executeWebAuthnLogin(email, csrfToken, isConditional = false) {
        // すでに実行中の Promise がある場合は、キャンセルしてその完了を待つ
        if (activePromise) {
            if (activeAbortController) {
                activeAbortController.abort();
            }
            try {
                await activePromise;
            } catch (err) {
                // キャンセルによる例外（AbortError）は意図されたものなので無視して次に進む
            }
            // ブラウザが内部状態をクリアするためのわずかな猶予
            await new Promise(resolve => setTimeout(resolve, 50));
        }

        activeAbortController = new AbortController();

        // 実際の一連の処理を非同期 Promise として実行し、参照を保持する
        activePromise = (async () => {
            // サーバーからチャレンジ情報を取得
            const challengeResponse = await fetch('/webauthn/login/challenge', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ email })
            });

            if (!challengeResponse.ok) {
                throw new Error('認証チャレンジの取得に失敗しました。');
            }

            const options = await challengeResponse.json();

            // オプション内のバイナリデータを ArrayBuffer にデコード
            options.publicKey.challenge = base64urlToBuffer(options.publicKey.challenge);
            if (options.publicKey.allowCredentials) {
                options.publicKey.allowCredentials.forEach(cred => {
                    cred.id = base64urlToBuffer(cred.id);
                });
            }

            // credentials.get 用のオプションオブジェクトを作成
            const getCredentialsOptions = {
                publicKey: options.publicKey,
                signal: activeAbortController.signal // キャンセル用シグナルを登録
            };

            // Conditional UI用のメディエーション（仲介）設定
            if (isConditional) {
                getCredentialsOptions.mediation = 'conditional';
            }

            // ブラウザのWebAuthn認証APIを起動
            const assertion = await navigator.credentials.get(getCredentialsOptions);

            // 署名結果データをBase64URLに変換してサーバーへ送信
            const verifyPayload = {
                id: assertion.id,
                clientDataJSON: bufferToBase64url(assertion.response.clientDataJSON),
                authenticatorData: bufferToBase64url(assertion.response.authenticatorData),
                signature: bufferToBase64url(assertion.response.signature),
                userHandle: assertion.response.userHandle ? bufferToBase64url(assertion.response.userHandle) : null
            };

            const verifyResponse = await fetch('/webauthn/login/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(verifyPayload)
            });

            const verifyResult = await verifyResponse.json();

            if (!verifyResponse.ok) {
                // 仮会員等のロックアウトメッセージ
                if (verifyResult.message) {
                    showError(verifyResult.message);
                    return;
                }
                throw new Error(verifyResult.error || 'ログイン検証に失敗しました。');
            }

            // ログイン成功：ダッシュボードへリダイレクト
            window.location.href = verifyResult.redirect;
        })();

        try {
            await activePromise;
        } catch (err) {
            // ユーザーまたはシステムによる明示的なキャンセルの場合はエラー表示をしない
            if (err.name === 'AbortError') {
                console.log('WebAuthn request was aborted.');
                return;
            }

            // オートフィルのキャンセル等によるエラーはログ出力のみ（画面には出さない）
            if (isConditional) {
                console.log('Conditional Auth Error (Expected on Cancel):', err);
            } else {
                showError('認証がキャンセルされたか、失敗しました。: ' + err.message);
            }
        } finally {
            // 状態のクリア
            activePromise = null;
            activeAbortController = null;
        }
    }

    // --- D. オートフィル提案ログインのバックグラウンド監視 ---
    function startConditionalLogin(csrfToken) {
        // メールアドレス未指定（空）でチャレンジを生成して待機
        executeWebAuthnLogin(null, csrfToken, true);
    }

    // 3. パスキー新規登録処理 (管理者発行セッションURLにて動作)
    if (registerKeyBtn) {
        registerKeyBtn.addEventListener('click', async () => {
            const csrfToken = getCsrfToken();
            const token = registerKeyBtn.getAttribute('data-token'); // ワンタイムトークン
            const deviceNameInput = document.getElementById('device-name-input');
            const deviceName = deviceNameInput ? deviceNameInput.value.trim() : 'パスキー';

            // エラー表示クリア
            if (errorAlert) errorAlert.classList.add('d-none');
            registerKeyBtn.disabled = true;
            registerKeyBtn.textContent = '生体認証を起動中...';

            try {
                // 登録用チャレンジ取得
                const challengeResponse = await fetch('/webauthn/register/challenge', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ token })
                });

                let options;
                try {
                    options = await challengeResponse.json();
                } catch (e) {
                    throw new Error('サーバーエラーが発生しました。(Status: ' + challengeResponse.status + ')');
                }

                if (!challengeResponse.ok) {
                    throw new Error(options.error || '登録用チャレンジの取得に失敗しました。');
                }

                // チャレンジおよびユーザーID等を ArrayBuffer に変換
                options.publicKey.challenge = base64urlToBuffer(options.publicKey.challenge);
                options.publicKey.user.id = base64urlToBuffer(options.publicKey.user.id);
                if (options.publicKey.excludeCredentials) {
                    options.publicKey.excludeCredentials.forEach(cred => {
                        cred.id = base64urlToBuffer(cred.id);
                    });
                }

                // デバイスのパスキー作成処理を呼び出し
                const credential = await navigator.credentials.create({
                    publicKey: options.publicKey
                });

                // クレデンシャル登録結果データをBase64URLに変換してサーバーへ送信
                const registerPayload = {
                    clientDataJSON: bufferToBase64url(credential.response.clientDataJSON),
                    attestationObject: bufferToBase64url(credential.response.attestationObject),
                    device_name: deviceName || 'マイデバイス'
                };

                const verifyResponse = await fetch('/webauthn/register/verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(registerPayload)
                });

                let verifyResult;
                try {
                    verifyResult = await verifyResponse.json();
                } catch (e) {
                    throw new Error('サーバーエラーが発生しました。(Status: ' + verifyResponse.status + ')');
                }

                if (!verifyResponse.ok) {
                    throw new Error(verifyResult.error || '公開鍵の登録検証に失敗しました。');
                }

                // 登録成功表示
                if (registerSuccessAlert) {
                    registerSuccessAlert.classList.remove('d-none');
                }
                registerKeyBtn.classList.add('d-none');
                if (deviceNameInput) deviceNameInput.disabled = true;

            } catch (err) {
                showError('登録中にエラーが発生しました: ' + err.message);
                registerKeyBtn.disabled = false;
                registerKeyBtn.textContent = 'このデバイスを登録する';
            }
        });
    }

    // 4. 会議案内文および議事録報告テキストのコピー処理 (CSP準拠)
    const copyTemplateBtn = document.getElementById('btn-copy-template');
    const templateText = document.getElementById('line-template-text');
    if (copyTemplateBtn && templateText) {
        copyTemplateBtn.addEventListener('click', () => {
            templateText.select();
            document.execCommand('copy');
            copyTemplateBtn.textContent = '✅ コピー完了！';
            setTimeout(() => {
                copyTemplateBtn.textContent = '📋 案内文をクリップボードにコピー';
            }, 2000);
        });
    }

    const copyReportBtn = document.getElementById('btn-copy-report');
    const reportText = document.getElementById('line-report-text');
    if (copyReportBtn && reportText) {
        copyReportBtn.addEventListener('click', () => {
            reportText.select();
            document.execCommand('copy');
            copyReportBtn.textContent = '✅ コピー完了！';
            setTimeout(() => {
                copyReportBtn.textContent = '📋 議事録報告テキストをコピー';
            }, 2000);
        });
    }

    const copyAdminUrlBtn = document.getElementById('btn-admin-copy-url');
    const adminUrlText = document.getElementById('admin-copy-url');
    if (copyAdminUrlBtn && adminUrlText) {
        copyAdminUrlBtn.addEventListener('click', () => {
            adminUrlText.select();
            document.execCommand('copy');
            copyAdminUrlBtn.textContent = '✅ コピー完了！';
            copyAdminUrlBtn.classList.replace('btn-success', 'btn-outline-success');
            setTimeout(() => {
                copyAdminUrlBtn.textContent = '📋 URLをコピー';
                copyAdminUrlBtn.classList.replace('btn-outline-success', 'btn-success');
            }, 2000);
        });
    }


});
