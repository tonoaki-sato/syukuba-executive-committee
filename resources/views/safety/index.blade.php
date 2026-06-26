@extends('layouts.app')

@section('title', '安全管理・警備巡回計画')

@section('content')
<link rel="stylesheet" href="/css/safety.css">

<div class="row">
    <!-- ページタイトルとヘッダー -->
    <div class="col-12 mb-4">
        <div class="card p-4 shadow-sm border-0 safety-header-card">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h3 class="fw-bold text-secondary-color mb-1">🚨 安全管理・警備巡回計画書</h3>
                    <p class="text-muted mb-0 small">
                        保土ケ谷宿場まつり開催期間中の警備体制、救護所、防災計画、および緊急連絡網を管理・掲示します。
                    </p>
                </div>
                <div class="mt-2 mt-md-0">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">ポータルへ戻る</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- タブナビゲーション -->
<div class="row">
    <div class="col-12">
        <ul class="nav nav-tabs safety-tabs border-bottom mb-4" id="safetyTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">📋 計画概要</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="routes-tab" data-bs-toggle="tab" data-bs-target="#routes" type="button" role="tab" aria-controls="routes" aria-selected="false">🗺️ 配置・巡回動線</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="escalation-tab" data-bs-toggle="tab" data-bs-target="#escalation" type="button" role="tab" aria-controls="escalation" aria-selected="false">📞 連絡・対応体制</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual" type="button" role="tab" aria-controls="manual" aria-selected="false">⚡ 緊急対処マニュアル</button>
            </li>
        </ul>

        <div class="tab-content" id="safetyTabContent">
            
            <!-- Tab 1: 計画概要 -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                <div class="row">
                    <!-- 基本情報 -->
                    <div class="col-md-6 mb-4">
                        <div class="card p-4 shadow-sm border-0 h-100">
                            <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3">1. 警備基本情報</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle small mb-0">
                                    <tbody>
                                        <tr>
                                            <th class="bg-light" style="width: 30%;">対象エリア</th>
                                            <td>
                                                ① メイン通り 約300m（通行止め・歩行者天国）<br>
                                                ② 北側ゲート〜キッズ村 約100m（<strong>非通行止め裏路地</strong>）<br>
                                                ③ 小学校跡地（キッズ村）
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">開催日程</th>
                                            <td>2日間</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">警備時間</th>
                                            <td>各日 11:00 〜 17:00<br><span class="text-muted" style="font-size: 0.85em;">（主催者配置は 10:00 〜 17:30）</span></td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">想定来場者数</th>
                                            <td>約10,000人 / 日 <br><span class="text-muted" style="font-size: 0.85em;">（家族連れの多くがキッズ村へ移動と想定）</span></td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">混雑ピーク</th>
                                            <td>お昼前後（11:30 〜 13:30）</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">統括拠点</th>
                                            <td><span class="badge bg-danger px-2">本陣</span>（メイン通り中央付近に設営）</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 基本方針 -->
                    <div class="col-md-6 mb-4">
                        <div class="card p-4 shadow-sm border-0 h-100">
                            <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3">2. 警備基本方針</h5>
                            <div class="list-group list-group-flush small">
                                <div class="list-group-item px-0 py-3 d-flex align-items-start border-0">
                                    <span class="fs-4 me-3">🏢</span>
                                    <div>
                                        <h6 class="fw-bold mb-1">本陣を中心とする情報の一元管理</h6>
                                        <p class="text-muted mb-0">飛び地（キッズ村）とメイン通りの状況を本陣でリアルタイムに集約・統括します。</p>
                                    </div>
                                </div>
                                <div class="list-group-item px-0 py-3 d-flex align-items-start border-0">
                                    <span class="fs-4 me-3">🚗</span>
                                    <div>
                                        <h6 class="fw-bold mb-1">裏路地における交通事故防止</h6>
                                        <p class="text-muted mb-0">通行止めではない裏路地（100m）において、歩行者（特に児童・幼児）と一般車両の接触事故を防ぐための警戒・誘導を徹底します。</p>
                                    </div>
                                </div>
                                <div class="list-group-item px-0 py-3 d-flex align-items-start border-0">
                                    <span class="fs-4 me-3">🤝</span>
                                    <div>
                                        <h6 class="fw-bold mb-1">北側・南側ゲートとのスムーズな連携</h6>
                                        <p class="text-muted mb-0">外部委託の各ゲート警備員と緊密に連携し、不審者対策や緊急車両の動線確保を行います。</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 警告情報 -->
                    <div class="col-12 mb-4">
                        <div class="alert alert-warning border-warning shadow-sm p-4 mb-0" role="alert">
                            <h5 class="alert-heading fw-bold d-flex align-items-center">
                                <span class="fs-4 me-2">⚠️</span> 裏路地（非通行止め区間）での一般車両対策
                            </h5>
                            <hr class="border-warning opacity-25">
                            <ul class="mb-0 small leading-relaxed">
                                <li class="mb-2"><strong>歩行者への声かけ:</strong> 巡回C班は、裏路地を歩行する子どもたちが車道に広がらないよう声かけ（「端を歩いてね」等）を行う。</li>
                                <li class="mb-2"><strong>一般車両の進入時:</strong> 歩行者を一時的に道の端に寄せ、車両に対しては最徐行を促すジェスチャー等で安全に通過させる。</li>
                                <li><strong>警戒強化時間帯:</strong> 特に混雑ピーク時の「キッズ村への行き来」が増える時間帯（お昼前後）は、裏路地の中間付近での監視を強める。</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: 配置・巡回動線 -->
            <div class="tab-pane fade" id="routes" role="tabpanel" aria-labelledby="routes-tab">
                <div class="row">
                    <!-- 人員配置比較 -->
                    <div class="col-lg-6 mb-4">
                        <div class="card p-4 shadow-sm border-0 h-100">
                            <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3">3. 主催者側 人員配置計画</h5>
                            <p class="text-muted small">
                                ※メイン通りの両端（北側・南側ゲート）の通行規制・誘導は、外部の警備保障会社へ委託します。
                            </p>
                            
                            <div class="row g-3">
                                <!-- 推奨案 -->
                                <div class="col-12">
                                    <div class="p-3 border border-success rounded-3 bg-success-subtle bg-opacity-10 h-100">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="fw-bold text-success mb-0">🟢 【推奨案】増員配置（計10名）</h6>
                                            <span class="badge bg-success">安全最優先</span>
                                        </div>
                                        <ul class="mb-0 small text-muted">
                                            <li><strong>本陣（統括拠点）:</strong> 2名 <span class="badge badge-staff">指揮、連絡担当</span></li>
                                            <li><strong>救護班（救護所常駐）:</strong> 2名 <span class="badge badge-staff">救護処置、本陣指示による出動</span></li>
                                            <li><strong>メイン通り巡回（A・B班）:</strong> 4名 <span class="badge badge-staff">2名1組 × 2個班（本陣より南側・北側を分担）</span></li>
                                            <li><strong>キッズ・裏路地巡回（C班）:</strong> 2名 <span class="badge badge-staff">1組（北側ゲート ⇆ 裏路地 ⇆ キッズ村を常時往復）</span></li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- 現状維持案 -->
                                <div class="col-12">
                                    <div class="p-3 border border-secondary rounded-3 bg-light h-100">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="fw-bold text-secondary mb-0">⚫ 【現状維持案】人員スプリット配置（計8名）</h6>
                                            <span class="badge bg-secondary text-white">予算優先</span>
                                        </div>
                                        <ul class="mb-0 small text-muted">
                                            <li><strong>本陣 / 救護班:</strong> 各2名（計4名）</li>
                                            <li><strong>メイン通り巡回（A班）:</strong> 2名 <span class="badge bg-secondary text-white">1組で南側ゲート〜北側ゲート全域をカバー</span></li>
                                            <li><strong>キッズ・裏路地巡回（C班）:</strong> 2名 <span class="badge bg-secondary text-white">1組で裏路地・キッズ村をカバー</span></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 各班の動線詳細 -->
                    <div class="col-lg-6 mb-4">
                        <div class="card p-4 shadow-sm border-0 h-100">
                            <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3">4. 巡回ルートと各班の動線</h5>
                            <div class="list-group list-group-flush small">
                                <div class="list-group-item px-0 py-3 border-0">
                                    <span class="badge badge-route-a mb-2 px-2 py-1">巡回A班</span>
                                    <p class="mb-1 fw-bold text-dark">本陣 ⇆ 南側ゲート（メイン通り南端）を往復</p>
                                    <p class="text-muted mb-0 small">メイン通り南側の出店密集地帯の安全確保、迷子・急病者の早期発見に注力します。</p>
                                </div>
                                <div class="list-group-item px-0 py-3 border-0">
                                    <span class="badge badge-route-b mb-2 px-2 py-1">巡回B班</span>
                                    <p class="mb-1 fw-bold text-dark">本陣 ⇆ 北側ゲート（メイン通り北端）を往復</p>
                                    <p class="text-muted mb-0 small">メイン通り北側の安全確保のほか、北側ゲートでの外部委託員との連携を行います。</p>
                                </div>
                                <div class="list-group-item px-0 py-3 border-0">
                                    <span class="badge badge-route-c mb-2 px-2 py-1">巡回C班</span>
                                    <p class="mb-1 fw-bold text-dark">北側ゲート ⇆ 裏路地 ⇆ キッズ村を往復</p>
                                    <p class="text-muted mb-0 small">非通行止めの裏路地での車輛注意喚起と、小学校跡地「キッズ村」内の安全監視を常時行います。</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- エリアおよび巡回配置図 -->
                    <div class="col-12 mb-4">
                        <div class="card p-4 shadow-sm border-0 mermaid-card">
                            <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3">エリア・巡回動線配置図</h5>
                            <div class="mermaid-diagram text-center">
flowchart TD
    subgraph MainStreet ["メイン通り（約300m・歩行者天国）"]
        GateN["【北側ゲート（メイン通り北端）】<br>(外部警備会社)"]
        Honjin["【本陣】<br>(統括拠点)"]
        GateS["【南側ゲート（メイン通り南端）】<br>(外部警備会社)"]
        
        GateN -- "B班巡回" &lt;--&gt; Honjin
        Honjin -- "A班巡回" &lt;--&gt; GateS
    end
    
    Kids["【キッズ村】 小学校跡地<br>(C班が巡回警戒)"] -- "導線: 裏路地 約100m (C班往復巡回/車両通行あり)" &lt;--&gt; GateN

    %% スタイル設定
    style Honjin fill:#f8d7da,stroke:#f5c2c7,stroke-width:2px,color:#842029
    style Kids fill:#cff4fc,stroke:#b6effb,stroke-width:2px,color:#055160
    style GateN fill:#e2e3e5,stroke:#d3d3d4,stroke-width:2px,color:#41464b
    style GateS fill:#e2e3e5,stroke:#d3d3d4,stroke-width:2px,color:#41464b
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 3: 連絡・対応体制 -->
            <div class="tab-pane fade" id="escalation" role="tabpanel" aria-labelledby="escalation-tab">
                <div class="row">
                    <!-- 通信ツールプラン -->
                    <div class="col-lg-6 mb-4">
                        <div class="card p-4 shadow-sm border-0 h-100">
                            <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3">5. 連絡体制（通信プラン）</h5>
                            <p class="text-muted small">
                                飛び地（100m先）があり、裏路地や校舎の影による電波遮蔽が懸念されるため、トランシーバー以外の通信手段を整備します。
                            </p>
                            
                            <div class="card border-0 bg-light p-3 mb-3">
                                <h6 class="fw-bold text-primary-color mb-2">📱 プランA：スマートフォンIP無線アプリの活用（推奨）</h6>
                                <p class="small text-muted mb-2">
                                    手持ちのスマートフォンに専用アプリ（Buddycom、Aldio、LiME等）をインストールし、有線・Bluetoothのイヤホンマイクを使用します。
                                </p>
                                <ul class="small mb-0 text-muted">
                                    <li><strong>メリット:</strong> 4G/5G回線を使用するため、100m離れたキッズ村や裏路地、建物内でもクリアに通話可能。写真送信やGPS位置特定も容易。</li>
                                    <li><strong>デメリット:</strong> データ通信量とバッテリー消費があるため、モバイルバッテリーの携行が必須。</li>
                                </ul>
                            </div>
                            
                            <div class="card border-0 bg-light p-3">
                                <h6 class="fw-bold text-secondary-color mb-2">💬 プランB：ビジネスチャットの常時グループ通話</h6>
                                <p class="small text-muted mb-2">
                                    無料の既存ツール（LINE、Slack、Discord）の通話機能を使用します。
                                </p>
                                <ul class="small mb-0 text-muted">
                                    <li><strong>メリット:</strong> 追加コストが不要。写真や地図（位置情報）の共有が非常に簡単。</li>
                                    <li><strong>デメリット:</strong> 混雑時に回線が混み合うと接続が不安定になりやすい。バッテリー消費が非常に激しい。</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- エスカレーションフロー -->
                    <div class="col-lg-6 mb-4">
                        <div class="card p-4 shadow-sm border-0 h-100 mermaid-card">
                            <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3">6. 緊急時エスカレーションフロー</h5>
                            <div class="mermaid-diagram text-center">
flowchart TD
    Honjin["【本陣】<br>(統括・情報集約)"] -- "連絡ツール (一斉通話)" &lt;--&gt; PatA["巡回A班<br>(メイン南)"]
    Honjin -- "連絡ツール (一斉通話)" &lt;--&gt; PatB["巡回B班<br>(メイン北)"]
    Honjin -- "連絡ツール (一斉通話)" &lt;--&gt; PatC["巡回C班<br>(キッズ・裏路地)"]
    Honjin -- "連絡ツール (一斉通話)" &lt;--&gt; Aid["救護班<br>(救護所常駐)"]
    
    Honjin -- "ホットライン" &lt;--&gt; SecCompany["外部警備会社 現場責任者<br>(北側・南側ゲート窓口)"]
    Honjin -- "緊急通報" &lt;--&gt; External["警察・消防・救急車"]

    %% スタイル設定
    style Honjin fill:#f8d7da,stroke:#f5c2c7,stroke-width:2px,color:#842029
    style PatA fill:#d1e7dd,stroke:#badbcc,stroke-width:2px,color:#0f5132
    style PatB fill:#d1e7dd,stroke:#badbcc,stroke-width:2px,color:#0f5132
    style PatC fill:#cff4fc,stroke:#b6effb,stroke-width:2px,color:#055160
    style Aid fill:#e2d9f3,stroke:#d2c4ec,stroke-width:2px,color:#563d7c
    style External fill:#f8d7da,stroke:#f5c2c7,stroke-width:2px,color:#842029
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 4: 緊急対処マニュアル -->
            <div class="tab-pane fade" id="manual" role="tabpanel" aria-labelledby="manual-tab">
                <div class="row">
                    <!-- トラブル対処アコーディオン -->
                    <div class="col-lg-7 mb-4">
                        <div class="card p-4 shadow-sm border-0">
                            <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3">7. トラブル別対処マニュアル</h5>
                            <p class="text-muted small">有事の際、現場警備員および本陣がとるべき具体的な初期対応と連絡先です。</p>
                            
                            <div class="accordion manual-accordion" id="manualAccordion">
                                
                                <!-- 1. 迷子 -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingOne">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                            👧 1. 迷子（児童・高齢者）の発生・保護
                                        </button>
                                    </h2>
                                    <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#manualAccordion">
                                        <div class="accordion-body small text-muted">
                                            <p class="mb-2"><strong>現場の対応:</strong></p>
                                            <ul class="mb-2">
                                                <li>迷子らしき人物を保護した、または保護者から申告があった場合、その場で名前・年齢・服装・特徴を確認。</li>
                                                <li>本陣へ連絡して特徴を共有。自力で探さず、原則として対象者を本陣（または本陣迷子係）へ同伴・保護する。</li>
                                                <li>本陣は各巡回班に特徴を周知し、必要に応じて会場内アナウンスを手配する。</li>
                                            </ul>
                                            <div class="manual-phone-box">
                                                <strong>緊急連絡先:</strong><br>
                                                ・本陣（迷子保護担当）: 090-XXXX-XXXX（サンプル）<br>
                                                ・保土ケ谷警察署（代表/迷子届）: 045-335-0110
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. 急病者・負傷者 -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingTwo">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                            🚑 2. 急病者・負傷者の発生（熱中症、転倒等）
                                        </button>
                                    </h2>
                                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#manualAccordion">
                                        <div class="accordion-body small text-muted">
                                            <p class="mb-2"><strong>現場の対応:</strong></p>
                                            <ul class="mb-2">
                                                <li>意識と呼吸の有無を確認。大声で周囲に協力を求めつつ、本陣へ「発生場所」「症状」を即時報告。</li>
                                                <li>意識がある場合は、日陰の安全な場所へ移動させ、水分補給等の応急処置を行う。</li>
                                                <li>意識がない、または重傷の場合は、本陣へ119番通報および救護班の急行を要請する。</li>
                                            </ul>
                                            <div class="manual-phone-box">
                                                <strong>緊急連絡先:</strong><br>
                                                ・救護所（救護班携帯）: 090-YYYY-YYYY（サンプル）<br>
                                                ・消防署（救命救急）: 119（本陣が通報）<br>
                                                ・保土ケ谷消防署（代表/事前調整用）: 045-331-0119
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. 出店での火災 -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingThree">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                            🔥 3. 出店での火災発生
                                        </button>
                                    </h2>
                                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#manualAccordion">
                                        <div class="accordion-body small text-muted">
                                            <p class="mb-2"><strong>現場の対応:</strong></p>
                                            <ul class="mb-2">
                                                <li>「火事だ！」と大声で叫び周囲に知らせ、近くの出店に配備されている消火器を借りて初期消火。</li>
                                                <li>本陣へ「発生場所（店名・番号）」を即時報告。</li>
                                                <li>出店者に対し「プロパンガス・電気・火気の即時停止」を指示。</li>
                                                <li>消火が困難な場合、直ちに初期消火を断念し、来場者を火元から遠ざけ、南北のゲートおよび側道へ避難誘導する。</li>
                                            </ul>
                                            <div class="manual-phone-box">
                                                <strong>緊急連絡先:</strong><br>
                                                ・消防署（火災通報）: 119（即時通報）<br>
                                                ・本陣（緊急指揮）: 090-XXXX-XXXX（サンプル）
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 4. 喧嘩・暴行・不審者 -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingFour">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                            👮 4. 喧嘩・暴行・不審者・盗難（スリ）
                                        </button>
                                    </h2>
                                    <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#manualAccordion">
                                        <div class="accordion-body small text-muted">
                                            <p class="mb-2"><strong>現場の対応:</strong></p>
                                            <ul class="mb-2">
                                                <li>喧嘩等が発生した場合、警備員自身が当事者の間に割って入るなど直接的な介入は行わず、身の安全を確保する。</li>
                                                <li>速やかに本陣へ「発生場所」「当事者の特徴・人数」「武器の有無」を報告し、警察への通報を要請する。</li>
                                                <li>警察が到着するまで、第三者の巻き込み防止と、可能であれば状況の撮影・記録を行う。</li>
                                            </ul>
                                            <div class="manual-phone-box">
                                                <strong>緊急連絡先:</strong><br>
                                                ・警察署（事件通報）: 110（即時通報）<br>
                                                ・保土ケ谷警察署（代表）: 045-335-0110
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 5. 不審物 -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingFive">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                            📦 5. 不審物の発見（放置バッグ、不審な箱等）
                                        </button>
                                    </h2>
                                    <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#manualAccordion">
                                        <div class="accordion-body small text-muted">
                                            <p class="mb-2"><strong>現場の対応:</strong></p>
                                            <ul class="mb-2">
                                                <li><strong>絶対に触らない、動かさない。</strong></li>
                                                <li>本陣へ「発見場所」「形状・大きさ」「煙や臭いの有無」を報告。</li>
                                                <li>不審物の周囲およそ10m〜20mを立ち入り禁止区域にし、来場者を遠ざける。</li>
                                                <li>本陣は警察へ通報し、指示を仰ぐ。</li>
                                            </ul>
                                            <div class="manual-phone-box">
                                                <strong>緊急連絡先:</strong><br>
                                                ・警察署（不審物通報）: 110
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 6. 車両の誤進入 -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingSix">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                                            🚗 6. 車両の誤進入（裏路地などからの侵入）
                                        </button>
                                    </h2>
                                    <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#manualAccordion">
                                        <div class="accordion-body small text-muted">
                                            <p class="mb-2"><strong>現場の対応:</strong></p>
                                            <ul class="mb-2">
                                                <li>規制エリア内に一般車両が入ってきた場合、警備員は直ちに車両を安全に停止させる。</li>
                                                <li>運転手に「ここはまつり用の通行止め規制区間である」ことを告げ、安全を確認しながらバック等で規制エリア外（裏路地の交差点外など）へ退出するよう促す。</li>
                                                <li>運転手が指示に従わない、または無理に進入しようとする場合は、速やかに本陣へ報告し、外部警備会社および警察へ連携する。</li>
                                            </ul>
                                            <div class="manual-phone-box">
                                                <strong>緊急連絡先:</strong><br>
                                                ・外部警備会社現場責任者: 090-ZZZZ-ZZZZ（サンプル）<br>
                                                ・本陣（緊急指揮）: 090-XXXX-XXXX<br>
                                                ・保土ケ谷警察署（代表）: 045-335-0110
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 7. 地震の発生 -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingSeven">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSeven" aria-expanded="false" aria-controls="collapseSeven">
                                            🌋 7. 地震の発生（自然災害）
                                        </button>
                                    </h2>
                                    <div id="collapseSeven" class="accordion-collapse collapse" aria-labelledby="headingSeven" data-bs-parent="#manualAccordion">
                                        <div class="accordion-body small text-muted">
                                            <p class="mb-2"><strong>現場の対応:</strong></p>
                                            <ul class="mb-2">
                                                <li>来場者に対し、「落ち着いてその場にかがむ」「出店のテント、看板、商品、電柱などから離れる（頭部を保護する）」よう大声で呼びかける。</li>
                                                <li>出店者に対し、二次災害（火災・ガス漏れ）防止のため、コンロやガスボンベの即時消火・バルブ閉鎖を指示する。</li>
                                                <li>揺れが収まり次第、本陣から全体の被害状況の報告およびイベント継続・中止の指示を受ける。</li>
                                                <li>中止・避難指示が出た場合、本陣が指定する一時避難場所（小学校跡地「キッズ村」グラウンド、または近隣の「保土ケ谷小学校」）へ、来場者を落ち着かせて誘導する。</li>
                                            </ul>
                                            <div class="manual-phone-box">
                                                <strong>緊急連絡先:</strong><br>
                                                ・本陣（災害対策本部）: 090-XXXX-XXXX<br>
                                                ・保土ケ谷区役所（地域防災担当）: 045-334-6262（代表）<br>
                                                ・横浜市 災害不要ダイヤル: 045-671-4410
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 8. 突風・強風・雷 -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingEight">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEight" aria-expanded="false" aria-controls="collapseEight">
                                            ⚡ 8. 突風・強風・ゲリラ雷雨の発生
                                        </button>
                                    </h2>
                                    <div id="collapseEight" class="accordion-collapse collapse" aria-labelledby="headingEight" data-bs-parent="#manualAccordion">
                                        <div class="accordion-body small text-muted">
                                            <p class="mb-2"><strong>現場の対応:</strong></p>
                                            <ul class="mb-2">
                                                <li>各出店に対し、テントのウエイト（重り）の補強、飛びやすいのぼり旗・看板などの即時撤去・固定を指示する。</li>
                                                <li>来場者に対し、テントの倒壊や飛来物から身を守るため、頑丈な建物内や本陣テントなどへ一時避難するよう促す。</li>
                                                <li>雷が発生している場合、木の下や金属製のポール、テントの骨組みの近くから速やかに離れるよう誘導する。</li>
                                                <li>本陣は気象警報（竜巻注意情報、雷注意報等）を随時監視し、一時中断や中止の判断を各班に伝達する。</li>
                                            </ul>
                                            <div class="manual-phone-box">
                                                <strong>緊急連絡先:</strong><br>
                                                ・本陣（気象情報監視）: 090-XXXX-XXXX
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- 避難誘導計画 -->
                    <div class="col-lg-5 mb-4">
                        <div class="card p-4 shadow-sm border-0 h-100">
                            <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3">8. 緊急避難誘導計画</h5>
                            
                            <div class="mb-3 small">
                                <h6 class="fw-bold text-dark mb-1">避難誘導の3大原則</h6>
                                <ol class="text-muted ps-3 mb-0">
                                    <li><strong>パニック防止（落ち着いたアナウンス）:</strong> 拡声器で低く落ち着いた声で「走らない・押さない・戻らない」を指示。</li>
                                    <li><strong>分散避難の徹底:</strong> 直線300mに約1万人がいるため、一方への集中を避け、横の側道へ流すよう誘導。</li>
                                    <li><strong>キッズ村の活用:</strong> 小学校跡地グラウンドを「一時避難場所」として有効活用。</li>
                                </ol>
                            </div>
                            
                            <div class="mb-3 small">
                                <h6 class="fw-bold text-dark mb-1">災害時要配慮者の支援</h6>
                                <p class="text-muted mb-0">
                                    ベビーカー・乳幼児連れ、高齢者、車椅子利用者、妊婦、外国人などの要配慮者を発見した場合は、個別に優先誘導を行います。車椅子の段差越え等の移動を補助し、困難な場合は直ちに本陣へ救護応援を要請します。外国人にはジェスチャーや多言語表示で視覚的に指示を行います。
                                </p>
                            </div>

                            <div class="small">
                                <h6 class="fw-bold text-dark mb-2">避難指示時の班別役割分担</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm small align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>担当</th>
                                                <th>誘導先・アクション</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <th class="small">本陣</th>
                                                <td class="small">避難指示発令、外部警備員へのゲート全開指示。</td>
                                            </tr>
                                            <tr>
                                                <th class="small">A班</th>
                                                <td class="small">本陣より南側の来場者を「南側ゲート・側道」へ誘導。</td>
                                            </tr>
                                            <tr>
                                                <th class="small">B班</th>
                                                <td class="small">本陣より北側の来場者を「北側ゲート」へ誘導、滞留防止。</td>
                                            </tr>
                                            <tr>
                                                <th class="small">C班</th>
                                                <td class="small">キッズ村来場者を「跡地グラウンド中央」へ集約。避難者の受け入れ。</td>
                                            </tr>
                                            <tr>
                                                <th class="small">救護班</th>
                                                <td class="small">救護所付近の要配慮者の避難支援、負傷者の対応。</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 避難誘導体制図 -->
                    <div class="col-12 mb-4">
                        <div class="card p-4 shadow-sm border-0 mermaid-card">
                            <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3">避難指示発令時フロー</h5>
                            <div class="mermaid-diagram text-center">
flowchart TD
    Honjin_Inst["【本陣】<br>避難指示・一斉放送"] --> PatA_Action["【巡回A班】<br>メイン南側を南側ゲート・側道へ誘導"]
    Honjin_Inst --> PatB_Action["【巡回B班】<br>メイン北側を北側ゲートへ誘導"]
    Honjin_Inst --> PatC_Action["【巡回C班】<br>キッズ村広場へ集約・安全確保"]
    Honjin_Inst --> Gate_Action["【外部警備員（ゲート）】<br>バリケード撤去・通路完全開放"]
    
    %% スタイル設定
    style Honjin_Inst fill:#f8d7da,stroke:#f5c2c7,stroke-width:2px,color:#842029
    style PatA_Action fill:#d1e7dd,stroke:#badbcc,stroke-width:2px,color:#0f5132
    style PatB_Action fill:#d1e7dd,stroke:#badbcc,stroke-width:2px,color:#0f5132
    style PatC_Action fill:#cff4fc,stroke:#b6effb,stroke-width:2px,color:#055160
    style Gate_Action fill:#e2e3e5,stroke:#d3d3d4,stroke-width:2px,color:#41464b
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
            
        </div>
    </div>
</div>

<!-- Mermaid JS (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
<!-- 安全管理用 JS -->
<script src="/js/safety.js"></script>
@endsection
