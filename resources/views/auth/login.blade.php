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
                    <!-- パスワード認証用送信ボタン (初期非表示) -->
                    <button type="submit" id="btn-submit" class="btn btn-primary btn-lg d-none">
                        ログイン
                    </button>
                </div>
            </form>

            <hr class="text-black-50">

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
