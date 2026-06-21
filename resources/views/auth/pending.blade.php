<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>承認待ち | 保土ケ谷宿場まつり実行委員会</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f7f5f2;
            color: #2b2b2b;
            font-family: 'Noto Sans JP', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            border: 0;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.05);
        }
        .icon-lock {
            background-color: #f5c885;
            color: #1c2d37;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            margin-bottom: 1.5rem;
        }
        .btn-logout {
            color: #8c1d30;
            text-decoration: none;
            font-weight: 500;
        }
        .btn-logout:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5 text-center">
                <div class="card p-5">
                    <div class="icon-lock">
                        ⌛
                    </div>
                    
                    <h3 class="fw-bold mb-3">承認待ち（仮登録中）</h3>
                    
                    <div class="alert alert-warning text-start mb-4 small" role="alert">
                        <p class="mb-2">実行委員としての登録申請を受け付けました。</p>
                        <p class="mb-0 fw-bold">現在は「仮会員」ステータスとなっており、システム管理者の確認と承認が完了するまで、システムの一切の機能をご利用いただけません。</p>
                    </div>
                    
                    <p class="text-muted small mb-4">
                        承認が完了すると、ご登録いただいたメールアドレス宛てに承認完了と初期設定のご案内メールが自動送信されます。今しばらくお待ちください。
                    </p>
                    
                    <hr class="mb-4">
                    
                    @auth
                        <!-- ログイン中の仮会員のみに表示するフローの解説 -->
                        <div class="card bg-light text-start p-3 mb-4 border border-secondary-subtle">
                            <h6 class="fw-bold text-dark mb-2" style="font-size: 0.9rem;">🔰 承認から利用開始までのステップ</h6>
                            <ol class="small text-muted mb-0 ps-3" style="font-size: 0.82rem;">
                                <li class="mb-2">
                                    <strong class="text-dark">管理者による確認と承認</strong><br>
                                    登録情報をもとに、システム管理者が身元精査の上、承認処理を行います。
                                </li>
                                <li class="mb-2">
                                    <strong class="text-dark">パスキー登録用ワンタイムURLの送信</strong><br>
                                    承認完了と同時に、ご登録のメールアドレス宛てに「パスキー登録の招待メール」が送信されます。
                                </li>
                                <li class="mb-2">
                                    <strong class="text-dark">パスキーの登録（デバイス認証）</strong><br>
                                    招待メールに記載されたワンタイムURLにアクセスし、お手元のデバイス（スマートフォンやPCなど）にてパスキーを登録します。
                                </li>
                                <li>
                                    <strong class="text-dark">本ログイン（利用開始）</strong><br>
                                    パスキー登録が完了すると、ログインが有効化され、ダッシュボードや会議管理などの全機能がご利用いただけるようになります。
                                </li>
                            </ol>
                        </div>

                        <!-- ログイン中の仮会員はログアウトして戻る -->
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary w-100 py-2">
                                ログアウトしてトップ画面に戻る
                            </button>
                        </form>
                    @else
                        <!-- 申請完了直後の未ログイン状態 -->
                        <a href="{{ route('login') }}" class="btn btn-primary w-100 py-2 bg-primary-color border-primary-color">
                            ログイン画面へ
                        </a>
                    @endauth
                </div>
                
                <p class="small text-muted mt-4">&copy; 保土ケ谷宿場まつり実行委員会</p>
            </div>
        </div>
    </div>
</body>
</html>
