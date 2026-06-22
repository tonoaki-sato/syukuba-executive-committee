<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '実務管理システム') | 保土ケ谷宿場まつり実行委員会</title>
    
    <!-- Google Fonts (Outfit, Outfit, Outfit or Noto Sans JP) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;700&family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <style>
        /* 和モダンをベースにしたプレミアムカラーパレット (CSSインライン禁止につき、共通CSSはlayouts/app内に配置) */
        :root {
            --primary-color: #8c1d30; /* 深いエンジ色 (宿場まつりの活気と伝統) */
            --primary-hover: #6d1524;
            --secondary-color: #1c2d37; /* 濃紺・鉄紺 (フォーマル、信頼感) */
            --bg-color: #f7f5f2; /* 柔らかい和紙風の淡いグレー・ベージュ */
            --card-bg: #ffffff;
            --text-color: #2b2b2b;
            --border-color: #e5dfd5;
            --font-family: 'Noto Sans JP', 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: var(--font-family);
            font-size: 0.95rem;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            background-color: var(--secondary-color) !important;
            border-bottom: 3px solid var(--primary-color);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-family: 'Noto Sans JP', sans-serif;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: #ffffff !important;
        }

        .navbar-brand span {
            color: #f5c885; /* 金色・山吹色 */
            font-size: 0.8em;
            display: block;
            font-weight: 300;
        }

        .nav-link {
            color: #e0e5e8 !important;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .nav-link:hover, .nav-link.active {
            color: #f5c885 !important;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 500;
            padding: 0.5rem 1.25rem;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(140, 29, 48, 0.2);
        }

        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid var(--border-color);
            font-weight: 700;
            color: var(--secondary-color);
            padding: 1.25rem;
        }

        footer {
            margin-top: auto;
            background-color: #111b22;
            color: #8c9fae;
            border-top: 1px solid #1c2d37;
            padding: 1.5rem 0;
            font-size: 0.85rem;
        }
        
        /* バッジカラー */
        .badge-admin { background-color: #dc3545; }
        .badge-kanji { background-color: #fd7e14; }
        .badge-general { background-color: #198754; }
        
        /* ユーティリティ */
        .text-primary-color { color: var(--primary-color) !important; }
        .bg-primary-color { background-color: var(--primary-color) !important; }
    </style>
</head>
<body>

    <!-- 共通ヘッダーナビゲーション -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                保土ケ谷宿場まつり
                <span>実行委員会 実務管理システム</span>
            </a>
            
            @auth
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-link-item">
                        <a class="nav-link {{ Route::is('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">ポータル</a>
                    </li>
                    <li class="nav-link-item ms-lg-2">
                        <a class="nav-link {{ Route::is('meetings.*') ? 'active' : '' }}" href="{{ route('meetings.index') }}">会議管理</a>
                    </li>
                    
                    @if(Auth::user()->isSystemAdmin() || Auth::user()->isKanji())
                    <li class="nav-item dropdown ms-lg-2">
                        <a class="nav-link dropdown-toggle {{ Request::is('goza*') ? 'active' : '' }}" href="#" id="gozaDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            ござ市管理
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="gozaDropdown">
                            <li><a class="dropdown-item {{ Route::is('goza.index') ? 'active' : '' }}" href="{{ route('goza.index') }}">ダッシュボード</a></li>
                            <li><a class="dropdown-item {{ Route::is('goza.applications.*') ? 'active' : '' }}" href="{{ route('goza.applications.index') }}">出店応募管理</a></li>
                            <li><a class="dropdown-item {{ Route::is('goza.spots.*') ? 'active' : '' }}" href="{{ route('goza.spots.index') }}">出店場所配置</a></li>
                            <li><a class="dropdown-item {{ Route::is('goza.payments.*') ? 'active' : '' }}" href="{{ route('goza.payments.index') }}">当日集金・受領</a></li>
                            <li><a class="dropdown-item {{ Route::is('goza.map.index') ? 'active' : '' }}" href="{{ route('goza.map.index') }}">出店場所地図配置</a></li>
                            @if(Auth::user()->isSystemAdmin())
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item {{ Route::is('goza.settings.*') ? 'active' : '' }}" href="{{ route('goza.settings.index') }}">募集設定・料金マスタ</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif
                    
                    <!-- システム管理者限定メニュー -->
                    @if(Auth::user()->isSystemAdmin())
                    <li class="nav-item dropdown ms-lg-3">
                        <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            管理者メニュー
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="adminDropdown">
                            <li><a class="dropdown-value dropdown-item {{ Route::is('admin.users.pending') ? 'active' : '' }}" href="{{ route('admin.users.pending') }}">承認待ち一覧</a></li>
                            <li><a class="dropdown-value dropdown-item {{ Route::is('admin.users.index') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">会員一覧・パスキー管理</a></li>
                        </ul>
                    </li>
                    @endif
                </ul>
                
                <!-- 開催年（年度）切り替え機能 -->
                <div class="d-flex align-items-center me-lg-4 my-2 my-lg-0">
                    <span class="text-light-emphasis me-2 text-white-50 small">対象年:</span>
                    @if(Auth::user()->isSystemAdmin() || Auth::user()->isKanji())
                        <!-- 管理者・幹事はセレクトボックスで切り替え可能 -->
                        <form action="{{ route('fiscal-year.change') }}" method="POST" class="d-inline">
                            @csrf
                            <select name="fiscal_year" onchange="this.form.submit()" class="form-select form-select-sm bg-dark text-white border-secondary small">
                                @php
                                    $currentYear = (int)date('Y');
                                    $activeYear = (int)session('active_fiscal_year', $currentYear);
                                @endphp
                                @for($y = 2026; $y <= $currentYear; $y++)
                                    <option value="{{ $y }}" {{ $y === $activeYear ? 'selected' : '' }}>
                                        {{ $y }}年
                                    </option>
                                @endphp
                                @endfor
                            </select>
                        </form>
                    @else
                        <!-- 一般会員はテキスト表示のみ -->
                        <span class="badge bg-secondary">
                            {{ session('active_fiscal_year', date('Y')) }}年
                        </span>
                    @endif
                </div>

                <!-- ログインユーザー情報 & アクション -->
                <div class="d-flex align-items-center ms-lg-2">
                    <div class="text-end text-lg-start me-3 text-white-50 small">
                        <span class="d-block text-white fw-bold">{{ Auth::user()->name }} 様</span>
                        @if(Auth::user()->isSystemAdmin())
                            <span class="badge badge-admin badge-sm">システム管理</span>
                        @elseif(Auth::user()->isKanji())
                            <span class="badge badge-kanji badge-sm">幹事</span>
                        @else
                            <span class="badge badge-general badge-sm">一般会員</span>
                        @endif
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" id="userMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            メニュー
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark" aria-labelledby="userMenuButton">
                            <li><a class="dropdown-item" href="{{ route('mypage') }}">マイページ</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">ログアウト</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            @endauth
        </div>
    </nav>

    <!-- メインコンテンツ -->
    <main class="py-4 my-2 flex-grow-1">
        <div class="container">
            <!-- フラッシュメッセージ -->
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    @if (session('status') === 'password-updated')
                        パスワードが正常に更新されました。
                    @elseif (session('status') === 'user-approved')
                        会員を承認しました。
                    @elseif (session('status') === 'user-rejected')
                        会員の申請を却下しました。
                    @elseif (session('status') === 'passkey-deleted')
                        パスキーを無効化しました。
                    @elseif (session('status') === 'user-deleted')
                        会員アカウントを完全に削除しました。
                    @elseif (session('status') === 'meeting-created')
                        新しい会議がスケジュールされました。
                    @elseif (session('status') === 'attendance-updated')
                        出欠の回答を更新しました。
                    @elseif (session('status') === 'minutes-updated')
                        議事録とホワイトボード写真を更新しました。
                    @else
                        {{ session('status') }}
                    @endif
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- フッター -->
    <footer class="text-center">
        <div class="container">
            <p class="mb-1">&copy; {{ date('Y') }} 保土ケ谷宿場まつり実行委員会. All Rights Reserved.</p>
            <p class="small mb-0 text-white-50">実務管理総合システム (v1.0-alpha)</p>
        </div>
    </footer>

    <!-- Bootstrap JS (CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <!-- 認証関連のVanilla JS -->
    <script src="/js/auth.js"></script>
</body>
</html>
