@extends('layouts.app')

@section('title', 'パスキー登録')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="col-md-6 col-lg-5">
        <div class="card p-4 shadow-sm border-0">
            <div class="text-center mb-4">
                <div class="d-inline-block bg-primary-color text-white p-3 rounded-circle mb-3" style="width: 70px; height: 70px; line-height: 40px; font-size: 1.8rem;">
                    🛡️
                </div>
                <h4 class="fw-bold text-dark mb-1">パスキー（生体認証）の設定</h4>
                <p class="text-muted small">このデバイスの指紋・顔認証やPINコードをログイン情報として登録します。</p>
            </div>

            <!-- エラー表示用アラート (JSから制御) -->
            <div id="error-alert" class="alert alert-danger d-none" role="alert"></div>

            <!-- 成功時のアラート -->
            <div id="register-success-alert" class="alert alert-success d-none text-center" role="alert">
                <h6 class="alert-heading fw-bold mb-2">🎉 パスキーの登録が完了しました！</h6>
                <p class="mb-3 small">次回より、メールアドレスを入力してデバイスのロックを解除するだけで、安全かつパスワードなしでログインできるようになります。</p>
                <hr>
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-success btn-sm w-100">ポータル画面へ戻る</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-success btn-sm w-100">ログイン画面へ</a>
                @endauth
            </div>

            <!-- 登録用フォーム（パスキー登録ボタン） -->
            <div class="card-body p-0" id="passkey-registration-form">
                <div class="mb-4">
                    <label for="device-name-input" class="form-label fw-semibold small">このデバイスの名前（識別用）</label>
                    <input type="text" id="device-name-input" class="form-control border-secondary-subtle" 
                           value="{{ request()->header('User-Agent') ? (str_contains(request()->header('User-Agent'), 'iPhone') ? '自分のiPhone' : (str_contains(request()->header('User-Agent'), 'Android') ? '自分のAndroid' : 'マイパソコン')) : 'マイデバイス' }}" 
                           placeholder="例: 私のスマホ、自宅PC" required>
                    <div class="form-text small">後から管理画面でどの端末のキーかを判別するための名前です。</div>
                </div>

                <div class="d-grid mb-3">
                    <!-- ワンタイムトークンを data-token に埋め込む -->
                    <button type="button" id="btn-register-passkey" data-token="{{ $token }}" class="btn btn-primary btn-lg">
                        このデバイスを登録する
                    </button>
                </div>
                
                <div class="text-center">
                    @auth
                        <a href="{{ route('dashboard') }}" class="small text-muted text-decoration-none">登録をスキップしてポータルへ</a>
                    @else
                        <a href="{{ route('login') }}" class="small text-muted text-decoration-none">ログイン画面に戻る</a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
