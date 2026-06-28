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

                <!-- パスワード入力（初期状態は非表示、パスキーがない場合のみJSで表示） -->
                <div id="password-field" class="mb-4 d-none">
                    <label for="password-input" class="form-label fw-semibold small">パスワード</label>
                    <input type="password" name="password" id="password-input" class="form-control form-control-lg border-secondary-subtle" 
                           placeholder="••••••••" autocomplete="current-password">
                </div>

                <!-- ログインボタン制御 -->
                <div class="d-grid gap-2 mb-4">
                    <!-- 「次へ」ボタン (パスキー有無判定) -->
                    <button type="button" id="btn-next" class="btn btn-primary btn-lg">
                        次へ
                    </button>
                    <!-- パスキーで直接ログインするボタン -->
                    <button type="button" id="btn-passkey-login" class="btn btn-outline-primary btn-lg">
                        パスキーでログイン
                    </button>
                    <!-- パスワード認証用送信ボタン (初期非表示) -->
                    <button type="submit" id="btn-submit" class="btn btn-primary btn-lg d-none">
                        ログイン
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
                            <p class="mb-0">
                                メールアドレスを入力して「次へ」をクリックし、従来通り「パスワードを入力してログイン」を行ってください。
                            </p>
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
