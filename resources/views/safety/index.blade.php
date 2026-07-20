@extends('layouts.app')

@section('title', '安全管理・巡回計画')

@section('content')
<link rel="stylesheet" href="/css/safety.css">

<div class="row">
    <!-- ページタイトルとヘッダー -->
    <div class="col-12 mb-4">
        <div class="card p-4 shadow-sm border-0 safety-header-card">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h3 class="fw-bold text-secondary-color mb-1">🚨 安全管理・巡回業務計画書</h3>
                    <p class="text-muted mb-0 small">
                        保土ケ谷宿場まつり開催期間中の巡回体制、救護所、防災計画、および緊急連絡網を管理・掲示します。
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
                            <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3">1. 巡回業務基本情報</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle small mb-0">
                                    <tbody>
                                        <tr>
                                            <th class="bg-light" style="width: 30%;">対象エリア</th>
                                            <td>
                                                ① メイン通り 約300m（通行止め・歩行者天国）<br>
                                                ② 北側ゲート〜キッズ村 約100m（<strong>非通行止め裏路地</strong>）
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">開催日程</th>
                                            <td>2日間</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">巡回時間</th>
                                            <td>各日 11:00 〜 17:00<br><span class="text-muted" style="font-size: 0.85em;">（主催者配置は 10:00 〜 17:30）</span></td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">想定来場者数</th>
                                            <td>約10,000人 / 日</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">混雑ピーク</th>
                                            <td>お昼前後（11:30 〜 13:30）</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">統括拠点</th>
                                            <td>
                                                <span class="badge bg-danger px-2 mb-1">本陣</span><br>
                                                直通電話: <strong>090-5289-8772</strong><br>
                                                常駐担当: <strong>佐藤 外亮（さとう とのあき） 氏</strong>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 基本方針と業務目的 -->
                    <div class="col-md-6 mb-4">
                        <div class="card p-4 shadow-sm border-0 h-100">
                            <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3">2. 巡回業務基本方針と目的</h5>
                            <div class="list-group list-group-flush small">
                                <div class="list-group-item px-0 py-2 border-0 d-flex align-items-start">
                                    <span class="badge bg-primary me-2">方針1</span>
                                    <div>
                                        <strong>本陣を中心とする情報の一元管理</strong>
                                        <p class="text-muted mb-0">メイン通りと裏路地の状況を本陣（常駐員: 佐藤 外亮 氏）で集約・統括します。</p>
                                    </div>
                                </div>
                                <div class="list-group-item px-0 py-2 border-0 d-flex align-items-start">
                                    <span class="badge bg-primary me-2">方針2</span>
                                    <div>
                                        <strong>裏路地における交通事故防止</strong>
                                        <p class="text-muted mb-0">非通行止めの裏路地において、歩行者と一般車両の接触事故を防ぐための注意喚起・安全確保を行います。</p>
                                    </div>
                                </div>
                                <div class="list-group-item px-0 py-2 border-0 d-flex align-items-start">
                                    <span class="badge bg-primary me-2">方針3</span>
                                    <div>
                                        <strong>外部交通誘導担当との連携</strong>
                                        <p class="text-muted mb-0">各ゲートの外部交通誘導担当と連携し、不審者対策や緊急車両の動線確保を支援します。</p>
                                    </div>
                                </div>
                            </div>
                            <hr class="my-2">
                            <h6 class="fw-bold text-dark small mb-2">【巡回業務の8大目的】</h6>
                            <ul class="text-muted small ps-3 mb-0">
                                <li>立入禁止区域（私有地）への立ち入り防止</li>
                                <li>ごみの清掃・回収（衛生班: 有江 氏との連携）</li>
                                <li>混雑時における来場者の安全確保及び一時的な通行整理</li>
                                <li>通行止め開始前（10:00前）及び解除前後（17:45巡回、18:00車両再開）の安全確保</li>
                                <li>通行止め中における緊急車両の通行確保（道路使用許可の遵守）</li>
                                <li>迷子及び拾得物の本部（本陣）への適切な引継ぎ</li>
                                <li>事故・急病人への初動対応（二次災害防止、本陣・119番連携）</li>
                                <li>危険箇所及び異常事象の早期発見・報告</li>
                            </ul>
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
                                <li class="mb-2"><strong>歩行者への声かけ:</strong> 裏路地を歩行する子どもたちが車道に広がらないよう声かけ（「端を歩いてね」等）を行う。</li>
                                <li class="mb-2"><strong>一般車両の進入時:</strong> 歩行者を一時的に道の端に寄せ、車両に対しては最徐行を促すジェスチャー等で安全に通過させる。</li>
                                <li><strong>警戒強化時間帯:</strong> 特に混雑ピーク時の「キッズ村への行き来」が増える時間帯（お昼前後）は、裏路地での監視を強める。</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: 配置・巡回動線 -->
            <div class="tab-pane fade" id="routes" role="tabpanel" aria-labelledby="routes-tab">
                <div class="row">
                    <!-- 人員配置計画 -->
                    <div class="col-lg-6 mb-4">
                        <div class="card p-4 shadow-sm border-0 h-100">
                            <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3">3. 主催者側 人員配置計画</h5>
                            <p class="text-muted small mb-3">
                                ※メイン通りの両端（北側・南側ゲート）の通行規制・誘導は、外部の交通誘導担当へ委託します。
                            </p>
                            
                            <div class="p-3 border border-primary rounded-3 bg-primary-subtle bg-opacity-10 mb-3">
                                <h6 class="fw-bold text-primary mb-1">📌 本陣（統括拠点）常駐</h6>
                                <p class="small text-muted mb-0">
                                    <strong>常駐1名確定（佐藤 外亮 氏）</strong><br>
                                    直通電話（090-5289-8772）にて、現場巡回員からの報告受信・通報・一元指揮を担当します。
                                </p>
                            </div>

                            <div class="p-3 border border-secondary rounded-3 bg-light">
                                <h6 class="fw-bold text-secondary mb-1">🏃 巡回員・その他配置</h6>
                                <p class="small text-muted mb-0">
                                    <strong>当日までの募集状況に応じて人員を割り振ります。</strong><br>
                                    募集結果に基づき、本陣の指示のもとで各巡回担当エリア（メイン通り南側・北側、裏路地、救護所等）へ柔軟に要員を配置します。（※具体的な頭数・人数の数値は固定せず運用）
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- 巡回ルートと基本行動 -->
                    <div class="col-lg-6 mb-4">
                        <div class="card p-4 shadow-sm border-0 h-100">
                            <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3">4. 巡回員の基本行動 & 安全衛生規定</h5>
                            <div class="small">
                                <h6 class="fw-bold text-dark mb-2">【巡回員の基本行動】</h6>
                                <ol class="text-muted ps-3 mb-3">
                                    <li>巡回は<strong>原則2名1組</strong>で実施し、単独行動は行わない。</li>
                                    <li>巡回中は、腕章その他指定された識別用品を着用する。</li>
                                    <li>来場者、出展者及び地域住民に対しては、<strong>公平かつ丁寧な対応</strong>を心掛ける（威圧的な態度をとらない）。</li>
                                    <li>判断に迷う場合又は対応が困難な場合は、自己判断を避け、速やかに本陣（090-5289-8772）へ連絡する。</li>
                                    <li>巡回中は、私的な買い物、飲食、長時間私語その他巡回業務に支障を及ぼす行為は行わない。</li>
                                </ol>

                                <h6 class="fw-bold text-dark mb-2">【健康・熱中症対策・安全管理】</h6>
                                <ul class="text-muted ps-3 mb-0">
                                    <li>夏場や長時間の屋外巡回に備え、こまめな水分・塩分補給、日よけ対策を行う。</li>
                                    <li>巡回員自身の安全を十分確保した上で対応を行う。</li>
                                    <li>巡回員自身に体調異変が生じた場合は、無理をせず直ちに本陣へ連絡し交代を要請する。</li>
                                </ul>
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
        GateN["【北側ゲート（メイン通り北端）】<br>(外部交通誘導担当)"]
        Honjin["【本陣】<br>(佐藤 外亮 氏 常駐 / 090-5289-8772)"]
        GateS["【南側ゲート（メイン通り南端）】<br>(外部交通誘導担当)"]
        
        GateN <-->|B班巡回| Honjin
        Honjin <-->|A班巡回| GateS
    end
    
    Kids["【キッズ村入口方面】<br>(C班が裏路地巡回)"] <-->|"導線: 裏路地 約100m (非通行止め/車両注意)"| GateN

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
                    <!-- 緊急連絡先一覧 -->
                    <div class="col-lg-6 mb-4">
                        <div class="card p-4 shadow-sm border-0 h-100">
                            <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3">5. 緊急連絡先一覧表</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle small mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>役割 / 拠点</th>
                                            <th>担当者</th>
                                            <th>電話番号 / 連絡先</th>
                                            <th>備考</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th class="bg-light">本部（本陣直通）</th>
                                            <td><strong>佐藤 外亮 氏</strong></td>
                                            <td><strong class="text-danger">090-5289-8772</strong></td>
                                            <td>事故・トラブル・迷子・全般統括</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">衛生班（ゴミ対応）</th>
                                            <td>有江 氏</td>
                                            <td>本陣経由 または 窓口</td>
                                            <td>大量ゴミ・危険物発生時</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">救護所</th>
                                            <td>救護担当</td>
                                            <td>本陣経由 (090-5289-8772)</td>
                                            <td>※会場内に固定の救護所を設置</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">保土ケ谷警察署</th>
                                            <td>警察窓口</td>
                                            <td>
                                                <strong>110</strong>（緊急・事件事故）<br>または <strong>045-335-0110</strong>（代表/相談）
                                            </td>
                                            <td>※事態の緊急度に応じて適切な番号を選択</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">保土ケ谷消防署</th>
                                            <td>消防窓口</td>
                                            <td><strong>119</strong>（火災・救急通報）</td>
                                            <td>本陣より即時通報（救護支援）</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 連絡手段と通信ルール -->
                    <div class="col-lg-6 mb-4">
                        <div class="card p-4 shadow-sm border-0 h-100">
                            <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3">6. 連絡手段と通信ルール</h5>
                            <div class="p-3 bg-light rounded-3 mb-3 small">
                                <h6 class="fw-bold text-dark mb-2">📱 使用端末と連絡ルール</h6>
                                <ul class="mb-0 text-muted ps-3">
                                    <li class="mb-2"><strong>使用端末:</strong> 巡回員各自の携帯電話（スマートフォン）を使用します。</li>
                                    <li class="mb-2"><strong>基本方針:</strong> <strong>「巡回員から本陣携帯（佐藤 外亮 氏: 090-5289-8772）への連絡」を基本</strong>とします。</li>
                                    <li><strong>制限事項:</strong> 巡回員同士の直接連絡、および本陣から巡回員への個別の呼び出し・連絡は<strong>原則行わない方針</strong>です。</li>
                                </ul>
                            </div>
                            <div class="alert alert-info border-info mb-0 p-3 small">
                                <strong>💡 連絡時の注意点:</strong><br>
                                発信時は「氏名・現在地・発生事象（迷子/急病/ゴミ/トラブル等）」を簡潔に本陣へ伝えてください。
                            </div>
                        </div>
                    </div>

                    <!-- エスカレーションフロー -->
                    <div class="col-12 mb-4">
                        <div class="card p-4 shadow-sm border-0 mermaid-card">
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                <h5 class="fw-bold text-secondary-color mb-0">7. 緊急時エスカレーションフロー</h5>
                                <button type="button" class="btn btn-outline-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#escalationFlowModal">
                                    🔍 拡大表示
                                </button>
                            </div>
                            <div class="mermaid-diagram text-center overflow-auto py-2">
flowchart TD
    Patrol["【現場巡回員】<br>(事象発見・初期対応)"] -->|"携帯発信 (直通: 090-5289-8772)"| Honjin["【本陣】<br>(常駐員: 佐藤 外亮 氏)"]
    Honjin -->|"重大事案報告・指示仰ぎ"| Chairs["【実行委員長・副委員長】<br>(組織統括・重大判断)"]
    
    Honjin <-->|"連携・通報"| Police["警察署 (110 / 045-335-0110)"]
    Honjin <-->|"要請・通報"| Fire["消防署 (119通報)"]
    Honjin <-->|"連絡"| Sanitation["衛生班 (担当: 有江 氏)"]
    Honjin <-->|"案内・要請"| FirstAid["会場内救護所 (固定設置)"]
    Honjin <-->|"連携"| GuideStaff["外部交通誘導担当 (ゲート窓口)"]

    %% スタイル設定
    style Patrol fill:#d1e7dd,stroke:#badbcc,stroke-width:2px,color:#0f5132
    style Honjin fill:#f8d7da,stroke:#f5c2c7,stroke-width:2px,color:#842029
    style Chairs fill:#fff3cd,stroke:#ffecb5,stroke-width:2px,color:#664d03
    style Police fill:#e2e3e5,stroke:#d3d3d4,stroke-width:2px,color:#41464b
    style Fire fill:#f8d7da,stroke:#f5c2c7,stroke-width:2px,color:#842029
    style Sanitation fill:#cff4fc,stroke:#b6effb,stroke-width:2px,color:#055160
    style FirstAid fill:#e2d9f3,stroke:#d2c4ec,stroke-width:2px,color:#563d7c
    style GuideStaff fill:#e2e3e5,stroke:#d3d3d4,stroke-width:2px,color:#41464b
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
                            <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3">8. トラブル別対処マニュアル</h5>
                            <p class="text-muted small">有事の際、巡回員および本陣がとるべき具体的な初期対応と連絡先です。</p>
                            
                            <div class="accordion manual-accordion" id="manualAccordion">
                                
                                <!-- 1. 私有地への立入り -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingOne">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                            🚫 1. 立入禁止区域（私有地）への立入り
                                        </button>
                                    </h2>
                                    <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#manualAccordion">
                                        <div class="accordion-body small text-muted">
                                            <p class="mb-2"><strong>対応方法:</strong></p>
                                            <ol class="mb-2">
                                                <li>巡回中に立入禁止区域（私有地）へ立ち入っている人を発見した場合は、速やかに丁寧な声掛けを行う。</li>
                                                <li>来場者には立入禁止区域であることを丁寧に説明し、通路等へ移動していただくようお願いする。</li>
                                            </ol>
                                            <p class="mb-2"><strong>【声掛け例】:</strong> 「申し訳ありません。こちらは私有地のため立ち入りはできません。恐れ入りますが、通路へお戻りくださいますようお願いいたします。」</p>
                                            <ul class="mb-2">
                                                <li>指示に従わない場合又はトラブルとなるおそれがある場合は、無理に対応せず本陣へ連絡する。</li>
                                                <li>巡回員は威圧的な態度をとらず丁寧な対応を心掛ける。</li>
                                            </ul>
                                            <div class="manual-phone-box">
                                                <strong>緊急連絡先:</strong> 本陣（佐藤 外亮 氏）: 090-5289-8772
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. ごみの清掃 -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingTwo">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                            🧹 2. ごみの清掃
                                        </button>
                                    </h2>
                                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#manualAccordion">
                                        <div class="accordion-body small text-muted">
                                            <p class="mb-2"><strong>対応方法:</strong></p>
                                            <ol class="mb-2">
                                                <li>巡回中に、まつりにより発生したごみを発見した場合は、可能な範囲で回収する。</li>
                                                <li>回収したごみは、定められたごみの分別方法に従い、最寄りのごみ箱へ廃棄する。</li>
                                                <li>大量のごみ、危険物（割れたガラス等）又はごみ箱のあふれ等、自ら対応が困難な場合は、衛生班へ連絡する。</li>
                                            </ol>
                                            <p class="mb-2"><strong>留意事項:</strong> 巡回の本来の目的は安全確認であるため、ごみの回収に長時間従事せず、必要に応じて衛生班へ引き継ぐ。</p>
                                            <div class="manual-phone-box">
                                                <strong>緊急連絡先:</strong><br>
                                                ・衛生班（担当: 有江 氏）: 本陣経由または衛生連絡窓口<br>
                                                ・本陣直通: 090-5289-8772
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. 混雑時通行整理 -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingThree">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                            🚶 3. 混雑時における来場者の安全確保及び一時的な通行整理
                                        </button>
                                    </h2>
                                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#manualAccordion">
                                        <div class="accordion-body small text-muted">
                                            <p class="mb-2"><strong>対応方法:</strong></p>
                                            <ol class="mb-2">
                                                <li>巡回中は、来場者の滞留状況及び通路の混雑状況を常に確認する。</li>
                                                <li>混雑により安全な通行に支障があると判断した場合は、必要に応じて一時的な通行整理を行う。</li>
                                                <li>高齢者、車いす利用者、ベビーカー利用者、小さなお子様連れなど、通行に配慮が必要な来場者を見かけた場合は誘導を行う。</li>
                                            </ol>
                                            <p class="mb-2"><strong>【声掛け例】:</strong> 「恐れ入ります。安全確保のため、こちらへお進みください。」「立ち止まらず、ゆっくりお進みいただきますようお願いいたします。」</p>
                                            <p class="mb-0">※巡回員が行う通行整理は、混雑時における一時的な安全確保を目的とするものであり、交通整理を行うものではありません。</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- 4. 通行止め開始前及び解除前後 -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingFour">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                            🚧 4. 通行止め開始前及び解除前後の安全確保
                                        </button>
                                    </h2>
                                    <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#manualAccordion">
                                        <div class="accordion-body small text-muted">
                                            <p class="mb-2"><strong>対応方法:</strong></p>
                                            <ol class="mb-2">
                                                <li>通行止め開始前（10:00 まで）は、出展準備に伴い車道へはみ出して作業を行っている出展者がいないか確認し、歩道等での作業を丁寧に声掛けする。</li>
                                                <li>17:45 頃から通行止め解除に向けた巡回を開始し、出展者へ 18:00 から車両通行が再開されることを周知する。</li>
                                                <li>車両通行再開後は、車両及び歩行者双方の安全を確認しながら対応する。</li>
                                            </ol>
                                            <div class="manual-phone-box">
                                                <strong>緊急連絡先:</strong> 本陣直通: 090-5289-8772
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 5. 緊急車両の通行確保 -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingFive">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                            🚑 5. 通行止め中における緊急車両の通行確保
                                        </button>
                                    </h2>
                                    <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#manualAccordion">
                                        <div class="accordion-body small text-muted">
                                            <p class="mb-2"><strong>対応方法:</strong></p>
                                            <ol class="mb-2">
                                                <li>通行止め時間中は、緊急車両がいつでも通行できる状態を維持することを常に意識して巡回する（道路使用許可の重要条件）。</li>
                                                <li>緊急車両の通行要請があった場合は、本陣と連携し、来場者及び出展者へ速やかに声掛けを行い、通行経路を確保する。</li>
                                                <li>必要に応じて巡回員が先行し、緊急車両の接近を周知するとともに、安全な通行を支援する。</li>
                                            </ol>
                                            <div class="manual-phone-box">
                                                <strong>緊急連絡先:</strong> 本陣直通: 090-5289-8772
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 6. 迷子及び拾得物 -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingSix">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                                            👧 6. 迷子及び拾得物の本部（本陣）への引継ぎ
                                        </button>
                                    </h2>
                                    <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#manualAccordion">
                                        <div class="accordion-body small text-muted">
                                            <p class="mb-2"><strong>対応方法:</strong></p>
                                            <ul class="mb-2">
                                                <li><strong>迷子発見時:</strong> 落ち着いて声を掛け、安全な場所で保護した上、速やかに本陣へ引き継ぐ。</li>
                                                <li><strong>拾得物取扱時:</strong> 拾得場所・時刻を確認し、巡回員が長期間保管せず、速やかに本陣へ引き継ぐ。</li>
                                                <li>巡回員自身は迷子・拾得物の受付管理を継続せず、すべて本陣（佐藤 外亮 氏）へ引き渡す。</li>
                                            </ul>
                                            <div class="manual-phone-box">
                                                <strong>緊急連絡先:</strong> 本陣（迷子・拾得物窓口）: 090-5289-8772
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 7. 事故・急病人初動対応（119番手順） -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingSeven">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSeven" aria-expanded="false" aria-controls="collapseSeven">
                                            🏥 7. 事故・急病人への初動対応（119番通報手順）
                                        </button>
                                    </h2>
                                    <div id="collapseSeven" class="accordion-collapse collapse" aria-labelledby="headingSeven" data-bs-parent="#manualAccordion">
                                        <div class="accordion-body small text-muted">
                                            <p class="mb-2"><strong>対応手順:</strong></p>
                                            <ol class="mb-2">
                                                <li>周囲の安全を確認し、二次災害防止に努める。</li>
                                                <li>傷病者の意識・状況を確認し、直ちに本陣（090-5289-8772）へ状況を報告する。</li>
                                                <li>119番通報が必要な場合は本陣が実施し、現場巡回員は「救急車の現場誘導担当」の手配および会場内「救護所」「AED設置場所」への案内・接続を行う。</li>
                                                <li>応急手当は自身が実施可能な範囲にとどめ、無理な救護活動は行わない。</li>
                                            </ol>
                                            <div class="manual-phone-box">
                                                <strong>緊急連絡先:</strong><br>
                                                ・本陣（佐藤 外亮 氏）: 090-5289-8772<br>
                                                ・消防署（救急・火災）: 119（本陣通報）<br>
                                                ・救護所: 会場内固定救護所（本陣案内）
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 8. 危険箇所・火災・不審物・地震 -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingEight">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEight" aria-expanded="false" aria-controls="collapseEight">
                                            🔥 8. 危険箇所・火災・不審物・地震等の発生
                                        </button>
                                    </h2>
                                    <div id="collapseEight" class="accordion-collapse collapse" aria-labelledby="headingEight" data-bs-parent="#manualAccordion">
                                        <div class="accordion-body small text-muted">
                                            <p class="mb-2"><strong>対応方法:</strong></p>
                                            <ul class="mb-2">
                                                <li><strong>危険箇所:</strong> 可能な範囲で応急的な安全確保を行い、放置せず速やかに本陣へ報告する。</li>
                                                <li><strong>火災発生:</strong> 「火事だ！」と叫び周囲へ周知、初期消火が困難な場合は直ちに避難誘導し本陣（消防119）へ報告。</li>
                                                <li><strong>不審物:</strong> 絶対に触らず動かさない。周囲10〜20mを立ち入り禁止にし、本陣へ報告して警察（110/045-335-0110）通報を依頼。</li>
                                                <li><strong>地震・突風:</strong> 「頭部保護・商品やテントから離れる」よう声掛けを行い、揺れ収まり後に本陣の指示に従い落ち着いて避難誘導する。</li>
                                            </ul>
                                            <div class="manual-phone-box">
                                                <strong>緊急連絡先:</strong><br>
                                                ・本陣（緊急指揮）: 090-5289-8772<br>
                                                ・保土ケ谷警察署: 110 または 045-335-0110<br>
                                                ・保土ケ谷消防署: 119
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- 避難誘導計画および検討課題 -->
                    <div class="col-lg-5 mb-4">
                        <div class="card p-4 shadow-sm border-0 h-100">
                            <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3">9. 緊急避難誘導計画</h5>
                            
                            <div class="mb-3 small">
                                <h6 class="fw-bold text-dark mb-1">避難誘導の3大原則</h6>
                                <ol class="text-muted ps-3 mb-0">
                                    <li><strong>パニック防止:</strong> 落ち着いた声で「走らない・押さない・戻らない」を指示。</li>
                                    <li><strong>分散避難の徹底:</strong> 一方への集中を避け、横の側道へ流すよう誘導。</li>
                                    <li><strong>要配慮者支援:</strong> 高齢者・子ども・車椅子利用者等を優先支援。</li>
                                </ol>
                            </div>

                            <hr class="my-3">

                            <h5 class="fw-bold text-secondary-color border-bottom pb-2 mb-3">10. 運用上の検討課題</h5>
                            <div class="p-3 bg-light rounded-3 small">
                                <p class="text-muted mb-2">今後のマニュアルおよびWebサイト改訂に向けた継続確認事項です。</p>
                                <ul class="text-muted ps-3 mb-0">
                                    <li class="mb-2"><strong>① 会場エリアマップの作成添付:</strong> 本陣、救護所、AED、ゴミステーション、通行止め区間を明示したレイアウト図の準備。</li>
                                    <li><strong>② 巡回シフト枠組の策定:</strong> 交代時間・休憩時間割等の運用ルールの整理。</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- 避難誘導体制図 -->
                    <div class="col-12 mb-4">
                        <div class="card p-4 shadow-sm border-0 mermaid-card">
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                <h5 class="fw-bold text-secondary-color mb-0">避難指示発令時フロー</h5>
                                <button type="button" class="btn btn-outline-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#evacuationFlowModal">
                                    🔍 拡大表示
                                </button>
                            </div>
                            <div class="mermaid-diagram text-center">
flowchart TD
    Honjin_Inst["【本陣 (佐藤 外亮 氏)】<br>避難指示・一斉放送"] --> PatA_Action["【巡回A班】<br>メイン南側を南側ゲート・側道へ誘導"]
    Honjin_Inst --> PatB_Action["【巡回B班】<br>メイン北側を北側ゲートへ誘導"]
    Honjin_Inst --> PatC_Action["【巡回C班】<br>裏路地・キッズ村方面の安全確保"]
    Honjin_Inst --> Gate_Action["【外部交通誘導担当】<br>バリケード撤去・通路完全開放"]
    
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

<!-- 緊急時エスカレーションフロー 拡大表示モーダル -->
<div class="modal fade" id="escalationFlowModal" tabindex="-1" aria-labelledby="escalationFlowModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title fw-bold text-secondary-color" id="escalationFlowModalLabel">🚨 緊急時エスカレーションフロー (拡大図)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-white p-4">
                    <div class="mermaid-diagram-modal text-center" id="modal-mermaid-diagram" style="min-height: 380px;"></div>
                    <textarea id="escalation-mermaid-code" class="d-none">
flowchart TD
    Patrol["【現場巡回員】<br>(事象発見・初期対応)"] -->|"携帯発信 (直通: 090-5289-8772)"| Honjin["【本陣】<br>(常駐員: 佐藤 外亮 氏)"]
    Honjin -->|"重大事案報告・指示仰ぎ"| Chairs["【実行委員長・副委員長】<br>(組織統括・重大判断)"]
    
    Honjin <-->|"連携・通報"| Police["警察署 (110 / 045-335-0110)"]
    Honjin <-->|"要請・通報"| Fire["消防署 (119通報)"]
    Honjin <-->|"連絡"| Sanitation["衛生班 (担当: 有江 氏)"]
    Honjin <-->|"案内・要請"| FirstAid["会場内救護所 (固定設置)"]
    Honjin <-->|"連携"| GuideStaff["外部交通誘導担当 (ゲート窓口)"]

    %% スタイル設定
    style Patrol fill:#d1e7dd,stroke:#badbcc,stroke-width:2px,color:#0f5132
    style Honjin fill:#f8d7da,stroke:#f5c2c7,stroke-width:2px,color:#842029
    style Chairs fill:#fff3cd,stroke:#ffecb5,stroke-width:2px,color:#664d03
    style Police fill:#e2e3e5,stroke:#d3d3d4,stroke-width:2px,color:#41464b
    style Fire fill:#f8d7da,stroke:#f5c2c7,stroke-width:2px,color:#842029
    style Sanitation fill:#cff4fc,stroke:#b6effb,stroke-width:2px,color:#055160
    style FirstAid fill:#e2d9f3,stroke:#d2c4ec,stroke-width:2px,color:#563d7c
    style GuideStaff fill:#e2e3e5,stroke:#d3d3d4,stroke-width:2px,color:#41464b
                    </textarea>
            </div>
            <div class="modal-footer bg-light border-top">
                <button type="button" class="btn btn-secondary px-4 fw-semibold" data-bs-dismiss="modal">閉じる</button>
            </div>
        </div>
    </div>
</div>

<!-- 避難指示発令時フロー 拡大表示モーダル -->
<div class="modal fade" id="evacuationFlowModal" tabindex="-1" aria-labelledby="evacuationFlowModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title fw-bold text-secondary-color" id="evacuationFlowModalLabel">🚨 避難指示発令時フロー (拡大図)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-white p-4">
                <div class="text-center overflow-auto">
                    <div class="mermaid-diagram-modal text-center" id="modal-evacuation-diagram" style="min-height: 380px;"></div>
                    <textarea id="evacuation-mermaid-code" class="d-none">
flowchart TD
    Honjin_Inst["【本陣 (佐藤 外亮 氏)】<br>避難指示・一斉放送"] --> PatA_Action["【巡回A班】<br>メイン南側を南側ゲート・側道へ誘導"]
    Honjin_Inst --> PatB_Action["【巡回B班】<br>メイン北側を北側ゲートへ誘導"]
    Honjin_Inst --> PatC_Action["【巡回C班】<br>裏路地・キッズ村方面の安全確保"]
    Honjin_Inst --> Gate_Action["【外部交通誘導担当】<br>バリケード撤去・通路完全開放"]
    
    %% スタイル設定
    style Honjin_Inst fill:#f8d7da,stroke:#f5c2c7,stroke-width:2px,color:#842029
    style PatA_Action fill:#d1e7dd,stroke:#badbcc,stroke-width:2px,color:#0f5132
    style PatB_Action fill:#d1e7dd,stroke:#badbcc,stroke-width:2px,color:#0f5132
    style PatC_Action fill:#cff4fc,stroke:#b6effb,stroke-width:2px,color:#055160
    style Gate_Action fill:#e2e3e5,stroke:#d3d3d4,stroke-width:2px,color:#41464b
                    </textarea>
                </div>
            </div>
            <div class="modal-footer bg-light border-top">
                <button type="button" class="btn btn-secondary px-4 fw-semibold" data-bs-dismiss="modal">閉じる</button>
            </div>
        </div>
    </div>
</div>

<!-- Mermaid JS (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
<!-- 安全管理用 JS -->
<script src="/js/safety.js"></script>
@endsection
