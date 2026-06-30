@extends('layouts.app')

@section('title', 'パスキーのトラブルシューティング')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <!-- ページヘッダー -->
        <div class="text-center my-4 py-3">
            <h2 class="fw-bold text-primary-color mb-2">🔑 パスキーのトラブルシューティング</h2>
            <p class="text-muted">実務管理システムでのパスキー（生体認証・PIN）ログインでお困りの方は、以下の手順をご確認ください。</p>
        </div>

        <!-- ログイン画面に戻るボタン -->
        <div class="mb-4">
            <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm fw-semibold">
                &larr; ログイン画面へ戻る
            </a>
        </div>

        <!-- セクション1: パスキーの正しい手順 -->
        <div class="card shadow-sm border-0 mb-4 overflow-hidden">
            <div class="card-header bg-primary-color text-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-info-circle-fill me-2"></i>1. パスキーの正しい利用手順</h5>
            </div>
            <div class="card-body p-4">
                <p class="text-muted mb-4">
                    パスキーは、お手持ちのスマートフォンやPCの生体認証（指紋・顔認証）または端末のPINコードを使用して、安全かつスピーディにログインする仕組みです。
                </p>

                <div class="row g-4">
                    <!-- 登録手順 -->
                    <div class="col-md-6 border-end-md">
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-primary-color me-2 fs-6">登録</span>
                            <h6 class="fw-bold mb-0 text-dark">パスキーの登録手順</h6>
                        </div>
                        <div class="position-relative ps-3 border-start border-primary-subtle ms-2">
                            <div class="mb-3 position-relative">
                                <span class="step-num bg-light text-primary-color border border-primary-subtle rounded-circle d-inline-flex justify-content-center align-items-center fw-bold" style="width: 24px; height: 24px; font-size: 0.8rem; margin-left: -25px; background: white !important;">1</span>
                                <div class="ms-3">
                                    <strong class="text-dark small">管理者へ登録申請</strong>
                                    <p class="text-muted small mb-0">パスキーはセキュリティのため、システム管理者が発行する「ワンタイム登録URL」からのみ追加登録できます。</p>
                                </div>
                            </div>
                            <div class="mb-3 position-relative">
                                <span class="step-num bg-light text-primary-color border border-primary-subtle rounded-circle d-inline-flex justify-content-center align-items-center fw-bold" style="width: 24px; height: 24px; font-size: 0.8rem; margin-left: -25px; background: white !important;">2</span>
                                <div class="ms-3">
                                    <strong class="text-dark small">登録用URLへのアクセス</strong>
                                    <p class="text-muted small mb-0">管理者から通知されたワンタイムURLに、登録したいデバイス（スマホやPC）のブラウザでアクセスします。</p>
                                </div>
                            </div>
                            <div class="position-relative">
                                <span class="step-num bg-light text-primary-color border border-primary-subtle rounded-circle d-inline-flex justify-content-center align-items-center fw-bold" style="width: 24px; height: 24px; font-size: 0.8rem; margin-left: -25px; background: white !important;">3</span>
                                <div class="ms-3">
                                    <strong class="text-dark small">生体認証の登録</strong>
                                    <p class="text-muted small mb-0">画面の指示に従い、端末の指紋・顔、または暗証番号(PIN)を登録すれば完了です。</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ログイン手順 -->
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-secondary me-2 fs-6">ログイン</span>
                            <h6 class="fw-bold mb-0 text-dark">パスキーでのログイン手順</h6>
                        </div>
                        
                        <div class="mb-3 bg-light p-3 rounded">
                            <strong class="text-dark small d-block mb-1">方法A: 自動提案（推奨）</strong>
                            <p class="text-muted small mb-0">メールアドレス入力欄をクリック・タップすると、ブラウザが保存されたパスキーを提案します。その提案を選択し、生体認証を行うだけでログイン完了です。</p>
                        </div>

                        <div class="bg-light p-3 rounded">
                            <strong class="text-dark small d-block mb-1">方法B: ログインボタンから</strong>
                            <p class="text-muted small mb-0">「パスキーでログイン」ボタンをクリックし、ブラウザの確認ダイアログが立ち上がったら、登録済みの生体認証等を入力します。</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- セクション2: パスキーが大量に重複登録された場合の対処方法 -->
        <div class="card shadow-sm border-0 mb-4 overflow-hidden">
            <div class="card-header bg-danger text-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>2. 大量にパスキーが重複して表示される場合の対処方法</h5>
            </div>
            <div class="card-body p-4">
                <!-- トラブルの説明（ユーザーからの添付画像を反映） -->
                <div class="alert alert-warning border-0 bg-warning-subtle text-dark-emphasis mb-4">
                    <div class="d-flex">
                        <div class="me-3 fs-3">⚠️</div>
                        <div>
                            <h6 class="fw-bold mb-1">このような現象でお困りではありませんか？</h6>
                            <p class="small mb-0">
                                ログイン画面のメールアドレス欄をクリックした際、同じメールアドレスの<strong>「パスキー（Microsoft Password Manager 等）」</strong>が縦にずらりと並んでしまい、どれを選べばいいか分からなくなる現象です。
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 原因の説明 -->
                <h6 class="fw-bold text-dark mb-2">発生原因について</h6>
                <p class="text-muted small mb-4">
                    パスキーの登録が途中で失敗したと思い、管理者に再発行を依頼して<strong>何度も新規登録の手続きをやり直した場合</strong>や、<strong>異なるブラウザやアプリが同じアカウントの情報を重複して保存してしまった場合</strong>に発生します。
                    これを解消するには、お使いのデバイスまたはブラウザのパスワードマネージャーの設定を開き、<strong>不要な重複パスキーを削除</strong>する必要があります。
                </p>

                <!-- デバイス・ブラウザ別の削除手順（タブ切り替え） -->
                <h6 class="fw-bold text-dark mb-3">💻 📱 お使いの環境に合わせた削除手順</h6>
                
                <ul class="nav nav-pills mb-3 text-center d-flex flex-wrap border-bottom pb-2" id="trouble-tab" role="tablist" style="gap: 5px;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active btn-sm" id="pills-windows-tab" data-bs-toggle="pill" data-bs-target="#pills-windows" type="button" role="tab" aria-controls="pills-windows" aria-selected="true">Windows (Edge/システム)</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link btn-sm" id="pills-chrome-tab" data-bs-toggle="pill" data-bs-target="#pills-chrome" type="button" role="tab" aria-controls="pills-chrome" aria-selected="false">Google Chrome</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link btn-sm" id="pills-iphone-tab" data-bs-toggle="pill" data-bs-target="#pills-iphone" type="button" role="tab" aria-controls="pills-iphone" aria-selected="false">iPhone / iPad</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link btn-sm" id="pills-mac-tab" data-bs-toggle="pill" data-bs-target="#pills-mac" type="button" role="tab" aria-controls="pills-mac" aria-selected="false">Mac (Safari)</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link btn-sm" id="pills-android-tab" data-bs-toggle="pill" data-bs-target="#pills-android" type="button" role="tab" aria-controls="pills-android" aria-selected="false">Android (Google)</button>
                    </li>
                </ul>

                <div class="tab-content" id="pills-tabContent">
                    <!-- Windowsの手順 -->
                    <div class="tab-pane fade show active" id="pills-windows" role="tabpanel" aria-labelledby="pills-windows-tab">
                        <div class="bg-light p-3 rounded">
                            <h6 class="fw-bold text-dark small mb-2"><i class="bi bi-microsoft me-1"></i> Microsoft Edge または Windows 11 の設定から削除する</h6>
                            <ol class="small text-muted ps-3 mb-0">
                                <li class="mb-2"><strong>方法A: Edgeブラウザから削除する</strong>
                                    <ul class="ps-3 mt-1">
                                        <li>Edgeを開き、右上の「…」（メニュー）から<strong>「設定」</strong>を選択します。</li>
                                        <li><strong>「プロファイル」 ＞ 「パスワード」</strong>をクリックします。</li>
                                        <li>検索窓に当システムのドメイン名（例: <code>syukuba.home</code>）を入力します。</li>
                                        <li>重複している古いパスキーの右側にある「削除」またはゴミ箱マークをクリックして削除します。</li>
                                    </ul>
                                </li>
                                <li><strong>方法B: Windows 11の設定アプリから削除する（システム保存の場合）</strong>
                                    <ul class="ps-3 mt-1">
                                        <li>キーボードの <code>Windowsキー + I</code> を押して<strong>「設定」</strong>を開きます。</li>
                                        <li>左メニューから<strong>「アカウント」</strong> ＞ <strong>「パスキー」</strong>を選択します。</li>
                                        <li>パスキー一覧から当システムのドメイン名（例: <code>syukuba.home</code>）を探します。</li>
                                        <li>重複している不要なパスキーの右側メニューから<strong>「パスキーの削除」</strong>をクリックします。</li>
                                    </ul>
                                </li>
                            </ol>
                        </div>
                    </div>

                    <!-- Chromeの手順 -->
                    <div class="tab-pane fade" id="pills-chrome" role="tabpanel" aria-labelledby="pills-chrome-tab">
                        <div class="bg-light p-3 rounded">
                            <h6 class="fw-bold text-dark small mb-2"><i class="bi bi-google me-1"></i> Google Chrome / Google パスワードマネージャー</h6>
                            <ol class="small text-muted ps-3 mb-0">
                                <li class="mb-2">Chromeブラウザを開き、右上の<strong>「3つの点」</strong>（メニュー）をクリックし、<strong>「設定」</strong>を開きます。</li>
                                <li class="mb-2">左メニューの<strong>「自動入力とパスワード」</strong>を選択し、<strong>「Google パスワード マネージャー」</strong>をクリックします。</li>
                                <li class="mb-2">左側のメニューから<strong>「設定」</strong>または画面上部の検索バーで、当システムのドメイン名（例: <code>syukuba.home</code>）を検索します。</li>
                                <li class="mb-2">重複して登録されているパスキーが表示されますので、削除したいキーを選択し、<strong>「削除」</strong>をクリックします。</li>
                                <span class="text-danger small">※一番新しいパスキー以外（不要なもの）を削除してください。すべて消してしまった場合は、再度管理者に登録URLの発行を依頼してください。</span>
                            </ol>
                        </div>
                    </div>

                    <!-- iPhoneの手順 -->
                    <div class="tab-pane fade" id="pills-iphone" role="tabpanel" aria-labelledby="pills-iphone-tab">
                        <div class="bg-light p-3 rounded">
                            <h6 class="fw-bold text-dark small mb-2"><i class="bi bi-apple me-1"></i> iPhone または iPad (iCloud キーチェーン)</h6>
                            <ol class="small text-muted ps-3 mb-0">
                                <li class="mb-2">ホーム画面から<strong>「設定」</strong>アプリを開きます。</li>
                                <li class="mb-2">画面を下にスクロールし、<strong>「パスワード」</strong>をタップします（Face ID / Touch ID またはパスコードでロックを解除）。</li>
                                <li class="mb-2">上部の検索バーに当システムのドメイン名（例: <code>syukuba.home</code>）を入力します。</li>
                                <li class="mb-2">該当するアカウントを選択し、詳細画面を開きます。</li>
                                <li class="mb-2">画面下部のパスキーの項目に、登録されているパスキーが一覧で表示されます。</li>
                                <li class="mb-2">不要（重複）なパスキーの行を左へスワイプするか、「編集」をタップして<strong>「パスキーを削除」</strong>します。</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Macの手順 -->
                    <div class="tab-pane fade" id="pills-mac" role="tabpanel" aria-labelledby="pills-mac-tab">
                        <div class="bg-light p-3 rounded">
                            <h6 class="fw-bold text-dark small mb-2"><i class="bi bi-apple me-1"></i> macOS (Safari / iCloud キーチェーン)</h6>
                            <ol class="small text-muted ps-3 mb-0">
                                <li class="mb-2">画面左上のAppleメニューをクリックし、<strong>「システム設定」</strong>を開きます。</li>
                                <li class="mb-2">左側リストの<strong>「パスワード」</strong>をクリックします（生体認証またはMacのログインパスワードを入力）。</li>
                                <li class="mb-2">上部の検索バーで、当システムのドメイン名（例: <code>syukuba.home</code>）を検索します。</li>
                                <li class="mb-2">該当するアカウントの右側にある<strong>「i（情報アイコン）」</strong>をクリックします。</li>
                                <li class="mb-2">重複して保存されている不要なパスキーを選択し、<strong>「パスキーを削除」</strong>をクリックします。</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Androidの手順 -->
                    <div class="tab-pane fade" id="pills-android" role="tabpanel" aria-labelledby="pills-android-tab">
                        <div class="bg-light p-3 rounded">
                            <h6 class="fw-bold text-dark small mb-2"><i class="bi bi-android me-1"></i> Android スマートフォン (Google パスワードマネージャー)</h6>
                            <ol class="small text-muted ps-3 mb-0">
                                <li class="mb-2">端末の<strong>「設定」</strong>アプリを開きます。</li>
                                <li class="mb-2"><strong>「Google」</strong> ＞ <strong>「Google アカウントの管理」</strong>の順にタップします。</li>
                                <li class="mb-2">上部のタブから<strong>「セキュリティ」</strong>を選択し、下部までスクロールして<strong>「パスワード マネージャー」</strong>をタップします。</li>
                                <li class="mb-2">検索バーに当システムのドメイン名（例: <code>syukuba.home</code>）を入力して選択します（指紋やPIN等で認証）。</li>
                                <li class="mb-2">表示された重複するパスキーのうち、不要なものを選択して<strong>「削除」</strong>します。</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="mt-4 p-3 bg-danger-subtle rounded text-danger-emphasis small border border-danger-subtle">
                    <strong>⚠️ ご注意:</strong><br>
                    もし不要なパスキーだけでなく、<strong>すべてのパスキーを誤って削除してしまった場合</strong>は、システムにログインできなくなります。その場合は、システム管理者へ連絡し、パスキー登録用URLを再発行してもらってください。
                </div>
            </div>
        </div>

        <!-- セクション3: その他の解決策 -->
        <div class="card shadow-sm border-0 mb-4 overflow-hidden">
            <div class="card-header bg-secondary text-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-question-circle-fill me-2"></i>3. その他のパスキーに関するお困りごと</h5>
            </div>
            <div class="card-body p-4">
                <div class="accordion accordion-flush" id="otherFaqAccordion">
                    <!-- Q1 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                Q. パスキー認証がうまく起動しない、または「パスキーが見つかりません」と表示される
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#otherFaqAccordion">
                            <div class="accordion-body text-muted small">
                                <p class="mb-2">いくつかの原因が考えられます。</p>
                                <ol class="ps-3 mb-0">
                                    <li class="mb-1"><strong>BluetoothがOFFになっている:</strong> パスワードマネージャーやスマホとPCの同期にBluetoothを使用する場合があります。端末のBluetoothをONにしてください。</li>
                                    <li class="mb-1"><strong>ブラウザのシークレットモードを使用している:</strong> シークレットモード（プライベートブラウズ）では、パスキーが利用できない場合があります。通常のウインドウでお試しください。</li>
                                    <li class="mb-1"><strong>別のデバイスのパスキーを使用したい場合:</strong> 認証ダイアログが表示された際に「別のデバイスを使用」または「他のオプション」を選択し、表示されたQRコードをスマホ等で読み取ることで、他のデバイスのパスキーでログインできます。</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Q2 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Q. パスワード入力でログインしたい
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#otherFaqAccordion">
                            <div class="accordion-body text-muted small">
                                <p class="mb-0">
                                    ログイン画面でメールアドレスを入力後、<strong>「次へ」</strong>をクリックしてください。パスキー認証が起動した場合はそれを<strong>キャンセル</strong>（またはダイアログを閉じる）すると、パスワードの入力フォームが表示されます。
                                    そのまま従来のパスワードを使用してログインが可能です。
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Q3 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Q. 機種変更やデバイスを紛失した場合はどうすればいいですか？
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#otherFaqAccordion">
                            <div class="accordion-body text-muted small">
                                <p class="mb-0">
                                    新しいデバイスに移行した場合、古いデバイスのパスキーは使えなくなります（iCloudやGoogleアカウントで自動同期されている場合を除く）。
                                    システム管理者へ「デバイス変更に伴うパスキー再設定」を依頼してください。管理者が古いキーを無効化（削除）し、新しいデバイスで登録するためのワンタイムURLを発行します。
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center my-5">
            <a href="{{ route('login') }}" class="btn btn-primary px-4 py-2 text-white fw-bold">
                ログイン画面に戻ってログインする
            </a>
        </div>
    </div>
</div>

<style>
    /* 和モダンに調和するタブデザインの調整 */
    .nav-pills .nav-link {
        color: var(--secondary-color);
        background-color: transparent;
        border: 1px solid var(--border-color);
        font-weight: 500;
        transition: all 0.2s ease;
    }
    .nav-pills .nav-link:hover {
        background-color: rgba(28, 45, 55, 0.05);
    }
    .nav-pills .nav-link.active {
        background-color: var(--primary-color) !important;
        color: #ffffff !important;
        border-color: var(--primary-color);
    }
    .border-end-md {
        border-right: 1px solid var(--border-color);
    }
    @media (max-width: 767.98px) {
        .border-end-md {
            border-right: none;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1.5rem;
            margin-bottom: 1rem;
        }
    }
</style>
@endsection
