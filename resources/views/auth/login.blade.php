@extends('layouts.app')

@section('title', 'ログイン')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="col-md-5">
        <div class="card p-4 shadow-sm border-0">
            <div class="text-center mb-4">
                <!-- まつりのロゴマークなどの代わりに、デザインされたヘッダ -->
                <div class="d-inline-block bg-primary-color text-white p-3 rounded-circle mb-3" style="width: 70px; height: 70px; line-height: 40px; font-size: 1.5rem; font-weight: bold;">
                    宿
                </div>
                <h4 class="fw-bold text-dark mb-1">実務管理システム</h4>
                <p class="text-muted small">保土ケ谷宿場まつり実行委員会 会員ログイン</p>
            </div>

            <!-- エラー表示用アラート (JSから制御) -->
            <div id="error-alert" class="alert alert-danger d-none" role="alert"></div>

            <!-- Laravelのバリデーションエラー -->
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="login-form" action="{{ route('login') }}" method="POST" class="needs-validation" novalidate>
                @csrf
                
                <!-- メールアドレス入力（Conditional UI対応） -->
                <div class="mb-4">
                    <label for="email-input" class="form-label fw-semibold small">メールアドレス</label>
                    <input type="email" name="email" id="email-input" class="form-control form-control-lg border-secondary-subtle" 
                           placeholder="yourname@example.com" required 
                           autocomplete="username webauthn" autofocus>
                </div>

                <!-- ログインボタン制御 -->
                <div class="d-grid gap-2 mb-4">
                    <!-- メールアドレス検証＆パスキーログイン起動ボタン -->
                    <button type="button" id="btn-next" class="btn btn-primary btn-lg">
                        ログイン
                    </button>
                    <!-- パスキーで直接ログインするボタン -->
                    <button type="button" id="btn-passkey-login" class="btn btn-outline-primary btn-lg">
                        保存されたパスキーを選択してログイン
                    </button>
                </div>
            </form>

            <hr class="text-black-50">

            <!-- パスキー認証お困りガイド -->
            <div class="accordion accordion-flush mb-3" id="passkeyHelpAccordion">
                <div class="accordion-item bg-transparent border-0">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed px-0 py-2 small text-muted bg-transparent border-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHelp" aria-expanded="false" aria-controls="collapseHelp" style="box-shadow: none;">
                            💡 パスキーログインの手順とお困りの方へ
                        </button>
                    </h2>
                    <div id="collapseHelp" class="accordion-collapse collapse" data-bs-parent="#passkeyHelpAccordion">
                        <div class="accordion-body px-0 pt-2 pb-1 small text-muted" style="line-height: 1.6;">
                            <div class="fw-bold mb-2 text-dark">🔑 ログイン手順:</div>
                            <ol class="ps-3 mb-3">
                                <li>「パスキーでログイン」ボタンを押すか、メールアドレス入力欄をタップして保存されたキーを選択します。</li>
                                <li>指紋・顔認証、または端末のPIN（暗証番号）を入力して本人確認を行います。</li>
                            </ol>
                            
                            <div class="fw-bold mb-2 text-dark">⚙️ パスワードマネージャーが表示された場合:</div>
                            <p class="mb-3">
                                1PasswordやBitwarden等の拡張機能が起動しログインできない場合は、ダイアログ内の<strong>「他のオプション」</strong>や<strong>「別の方法を試す」</strong>等をクリックし、端末本体の生体認証（Touch ID, Windows Hello 等）に切り替えてください。
                            </p>

                            <div class="fw-bold mb-2 text-dark">🛡️ パスキーを未登録、または利用できない場合:</div>
                            <p class="mb-3">
                                パスキーの登録がお済みでない場合はログインできません。システム管理者に連絡し、「パスキー登録URL」の発行を依頼してください。
                            </p>

                            <div class="mt-3 pt-2 border-top">
                                <a href="{{ route('passkey.troubleshooting') }}" class="fw-bold text-primary-color text-decoration-none">
                                    ⚙️ パスキーの重複削除など詳細なトラブルシューティングはこちら &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-3">
                <p class="small text-muted mb-2">まだアカウントをお持ちでない方</p>
                <a href="{{ route('register') }}" class="btn btn-outline-secondary btn-sm w-100 py-2 fw-semibold">
                    新規会員の登録申請（仮登録）を行う
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
