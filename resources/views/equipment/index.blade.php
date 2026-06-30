@extends('layouts.app')

@section('title', '備品管理台帳')

@section('content')
<link rel="stylesheet" href="/css/equipment.css">

<div class="row">
    <div class="col-12">
        <!-- ページヘッダー -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">⛺ 備品・倉庫管理</h3>
                <p class="text-muted small mb-0">
                    現在の表示年度: <span class="fw-bold text-primary-color">{{ $year }}年度</span>
                </p>
            </div>
            
            <div class="d-flex gap-2">
                @if($canManage)
                    <!-- 前年度コピーボタン -->
                    <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#copyYearModal">
                        📋 前年度データ引き継ぎ
                    </button>
                    <!-- 備品登録ボタン -->
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEquipmentModal">
                        ➕ 備品マスタ新規登録
                    </button>
                @endif
            </div>
        </div>

        <!-- タブナビゲーション -->
        <ul class="nav nav-tabs nav-tabs-wamon mb-4" id="equipmentTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard" type="button" role="tab" aria-controls="dashboard" aria-selected="true">📊 ダッシュボード</button>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" href="{{ route('equipment.matrix') }}">📋 部門別コスト配分</a>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="ledger-tab" data-bs-toggle="tab" data-bs-target="#ledger" type="button" role="tab" aria-controls="ledger" aria-selected="false">⛺ 備品・倉庫台帳</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="loans-tab" data-bs-toggle="tab" data-bs-target="#loans" type="button" role="tab" aria-controls="loans" aria-selected="false">🔄 貸出・割当状況</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="maintenance-tab" data-bs-toggle="tab" data-bs-target="#maintenance" type="button" role="tab" aria-controls="maintenance" aria-selected="false">🛠️ 破損・状態管理</button>
            </li>
        </ul>

        <!-- タブコンテンツ -->
        <div class="tab-content" id="equipmentTabContent">
            
            <!-- 📊 ダッシュボードタブ -->
            <div class="tab-pane fade show active" id="dashboard" role="tabpanel" aria-labelledby="dashboard-tab">
                <div class="row g-4 mb-4">
                    <!-- 所有備品総数 -->
                    <div class="col-md-3">
                        <div class="card p-3 shadow-sm border-0 h-100 summary-panel">
                            <span class="text-muted small">実行委員会所有 備品定義数</span>
                            <span class="fs-2 fw-bold text-dark mt-2">{{ $equipments->where('ownership_type', 'owned')->count() }} <span class="fs-6 fw-normal text-muted">種類</span></span>
                            <span class="small text-muted mt-2">総計: {{ $equipments->where('ownership_type', 'owned')->sum('quantity') }} 個</span>
                        </div>
                    </div>
                    <!-- レンタル備品数 -->
                    <div class="col-md-3">
                        <div class="card p-3 shadow-sm border-0 h-100 summary-panel">
                            <span class="text-muted small">レンタル手配 備品定義数</span>
                            <span class="fs-2 fw-bold text-dark mt-2">{{ $equipments->where('ownership_type', 'rental')->count() }} <span class="fs-6 fw-normal text-muted">種類</span></span>
                            <span class="small text-muted mt-2">総計: {{ $equipments->where('ownership_type', 'rental')->sum('quantity') }} 個</span>
                        </div>
                    </div>
                    <!-- レンタル手配総額（管理者・幹事・備品管理のみ表示） -->
                    @if($canManage)
                        <div class="col-md-4">
                            <div class="card p-3 shadow-sm border-0 h-100 summary-panel" style="border-left-color: #8c1d30;">
                                <span class="text-muted small">外部レンタル税込総請求額</span>
                                <span class="fs-2 fw-bold text-danger mt-2">¥{{ number_format($rentalGrandTotal) }}</span>
                                <div class="small text-muted mt-1" style="font-size: 0.8em;">
                                    内訳：見積 ¥{{ number_format($rentalSubtotal) }}
                                    @if($rentalDiscount > 0)
                                         / 値引 -¥{{ number_format($rentalDiscount) }}
                                    @endif
                                    / 税 ¥{{ number_format($rentalTax) }}
                                </div>
                            </div>
                        </div>
                    @endif
                    <!-- 紛失・破損アラート -->
                    <div class="col-md-2">
                        @php
                            $lostCount = $maintenanceLogs->whereIn('log_type', ['discard', 'lost'])->sum('quantity');
                        @endphp
                        <div class="card p-3 shadow-sm border-0 h-100 summary-panel {{ $lostCount > 0 ? 'stock-alert' : '' }}">
                            <span class="text-muted small">今年度 紛失・廃棄数</span>
                            <span class="fs-2 fw-bold text-dark mt-2">{{ $lostCount }} <span class="fs-6 fw-normal text-muted">個</span></span>
                            <span class="small text-muted mt-2">※実在庫から自動減算済</span>
                        </div>
                    </div>
                </div>

                <!-- 保管場所別の在庫状況（ヒートマップ調カード） -->
                <div class="card p-4 shadow-sm border-0 mb-4">
                    <h5 class="fw-bold text-dark mb-3">📍 拠点・倉庫別 在庫概況</h5>
                    <div class="row g-3">
                        @foreach($locations as $loc)
                            @php
                                $locStockCount = $stocks->where('storage_location_id', $loc->id)->sum('quantity');
                            @endphp
                            <div class="col-md-6 col-lg-4">
                                <div class="p-3 border rounded bg-light-subtle h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold text-dark">{{ $loc->name }}</span>
                                        <span class="badge bg-secondary stock-badge">{{ $locStockCount }} 個保管中</span>
                                    </div>
                                    <p class="text-muted small mb-0">{{ Str::limit($loc->notes, 80) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- ⛺ 備品・倉庫台帳タブ -->
            <div class="tab-pane fade" id="ledger" role="tabpanel" aria-labelledby="ledger-tab">
                <div class="row">
                    <!-- 左側：備品一覧カード -->
                    <div class="col-lg-9 col-12">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-dark mb-0">⛺ 登録備品マスタ一覧</h5>
                            @if($canManage)
                                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#adjustStockModal">
                                    🔄 在庫数量の手動調整
                                </button>
                            @endif
                        </div>

                        <div class="row g-3">
                            @foreach($equipments as $eq)
                                <div class="col-md-6 col-lg-4">
                                    <div class="card h-100 equipment-card shadow-sm">
                                        <!-- 所有区分バッジ -->
                                        @if($eq->ownership_type === 'owned')
                                            <span class="ownership-badge bg-success text-white">実行委所有</span>
                                        @else
                                            <span class="ownership-badge bg-warning text-dark">外部レンタル</span>
                                        @endif

                                        <!-- 備品画像 -->
                                        <div class="equipment-img-container">
                                            @if($eq->image_path)
                                                <img src="/storage/{{ $eq->image_path }}" class="equipment-img" alt="{{ $eq->name }}">
                                            @else
                                                <div class="text-muted small text-center p-3">
                                                    <span class="fs-1 d-block mb-1">⛺</span>
                                                    画像未登録
                                                </div>
                                            @endif
                                        </div>

                                        <!-- カードボディ -->
                                        <div class="card-body p-3">
                                            <h6 class="fw-bold text-dark mb-1">{{ $eq->name }}</h6>
                                            <div class="text-muted small mb-2">{{ $eq->specifications ?? '規格なし' }}</div>
                                            
                                            <!-- カテゴリ & 総保有数 -->
                                            <div class="d-flex justify-content-between text-muted small border-bottom pb-2 mb-2">
                                                <span>分類: {{ $eq->category }}</span>
                                                <span class="fw-bold text-dark">総数: {{ $eq->quantity }} {{ $eq->unit }}</span>
                                            </div>

                                            <!-- 金額（単価・合計金額）（管理者・幹事・備品管理のみ表示） -->
                                            @if($canManage)
                                                <div class="d-flex justify-content-between text-muted small pb-2 mb-2 border-bottom">
                                                    <span>単価: ¥{{ number_format($eq->unit_price ?? 0) }}</span>
                                                    <span class="fw-bold text-danger">総額: ¥{{ number_format($eq->total_amount) }}</span>
                                                </div>
                                            @endif

                                            <!-- 倉庫別保管在庫 -->
                                            <div class="small">
                                                <div class="fw-semibold text-secondary mb-1">保管内訳:</div>
                                                @php
                                                    $eqStocks = $stocks->where('equipment_id', $eq->id);
                                                @endphp
                                                @if($eqStocks->count() > 0)
                                                    <ul class="list-unstyled mb-0 ps-1 text-muted" style="font-size: 0.85em;">
                                                        @foreach($eqStocks as $st)
                                                            @if($st->quantity > 0)
                                                                <li>📍 {{ $st->location->name ?? '不明' }}: <strong>{{ $st->quantity }}</strong> {{ $eq->unit }}</li>
                                                            @endif
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <span class="text-muted small">在庫未配置</span>
                                                @endif
                                            </div>

                                            <!-- 備考 -->
                                            @if($eq->description)
                                                <p class="text-muted small border-top pt-2 mt-2 mb-0" style="font-size: 0.8em;">
                                                    {{ Str::limit($eq->description, 60) }}
                                                </p>
                                            @endif
                                        </div>

                                        <!-- カードフッター (編集・削除権限者のみ) -->
                                        @if($canManage)
                                            <div class="card-footer bg-transparent border-0 d-flex gap-2 p-3 pt-0">
                                                <button class="btn btn-outline-secondary btn-sm flex-fill btn-edit-equipment"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editEquipmentModal"
                                                        data-id="{{ $eq->id }}"
                                                        data-name="{{ $eq->name }}"
                                                        data-specifications="{{ $eq->specifications }}"
                                                        data-quantity="{{ $eq->quantity }}"
                                                        data-unit="{{ $eq->unit }}"
                                                        data-unit_price="{{ $eq->unit_price }}"
                                                        data-category="{{ $eq->category }}"
                                                        data-ownership_type="{{ $eq->ownership_type }}"
                                                        data-description="{{ $eq->description }}"
                                                        data-image="{{ $eq->image_path }}">
                                                    編集
                                                </button>
                                                <form action="{{ route('equipment.master.destroy', $eq->id) }}" method="POST" class="d-inline flex-fill" onsubmit="return confirm('この備品定義を削除しますか？関連する在庫や貸出履歴も削除されます。');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">削除</button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- 右側：保管場所・倉庫管理 -->
                    <div class="col-lg-3 col-12 mt-4 mt-lg-0">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-dark mb-0">📍 保管場所（倉庫）</h5>
                            @if($canManage)
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addLocationModal">
                                    ➕ 追加
                                </button>
                            @endif
                        </div>

                        <div class="list-group shadow-sm border-0 rounded">
                            @foreach($locations as $loc)
                                <div class="list-group-item p-3 border-light">
                                    <h6 class="fw-bold text-dark mb-1">{{ $loc->name }}</h6>
                                    @if($loc->contact_person)
                                        <div class="small text-muted mb-1">管理者: {{ $loc->contact_person }}</div>
                                    @endif
                                    @if($loc->notes)
                                        <p class="text-muted small mb-0" style="font-size: 0.8em;">{{ $loc->notes }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🔄 貸出・割当状況タブ -->
            <div class="tab-pane fade" id="loans" role="tabpanel" aria-labelledby="loans-tab">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark mb-0">🔄 当日割当・現場貸出状況</h5>
                    @if($canManage)
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addLoanModal">
                            ➕ 新規貸出・割当の登録
                        </button>
                    @endif
                </div>

                <div class="card p-4 shadow-sm border-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle wamon-table">
                            <thead>
                                <tr>
                                    <th>備品名</th>
                                    <th>借用者区分</th>
                                    <th>借用者情報</th>
                                    <th>希望数</th>
                                    <th>実貸出数</th>
                                    <th>返却済数</th>
                                    <th>ステータス</th>
                                    <th>備考</th>
                                    @if($canManage)
                                        <th class="text-center">操作</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loans as $loan)
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-dark">{{ $loan->equipment->name ?? '削除された備品' }}</span>
                                            <span class="text-muted small d-block">{{ $loan->equipment->specifications ?? '' }}</span>
                                        </td>
                                        <td>
                                            @if($loan->borrower_type === 'gozaichi')
                                                <span class="badge bg-info-subtle text-info">ござ市出店者</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">実行委員会</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($loan->borrower_type === 'gozaichi')
                                                <span>出店応募ID: {{ $loan->borrower_id }}</span>
                                            @else
                                                <span>会員ID / 部署: {{ $loan->borrower_id }}</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold text-dark">{{ $loan->quantity_requested }}</td>
                                        <td class="text-primary fw-bold">{{ $loan->quantity_loaned }}</td>
                                        <td class="text-success fw-bold">{{ $loan->quantity_returned }}</td>
                                        <td>
                                            @if($loan->status === 'pending')
                                                <span class="badge bg-warning text-dark">準備中 / 未引渡</span>
                                            @elseif($loan->status === 'loaned')
                                                <span class="badge bg-primary text-white">現場貸出中</span>
                                            @elseif($loan->status === 'returned')
                                                <span class="badge bg-success text-white">返却完了</span>
                                            @elseif($loan->status === 'partial')
                                                <span class="badge bg-info text-dark">一部返却</span>
                                            @elseif($loan->status === 'lost')
                                                <span class="badge bg-danger text-white">紛失 / 破損</span>
                                            @endif
                                        </td>
                                        <td><span class="text-muted small">{{ $loan->notes ?? '-' }}</span></td>
                                        @if($canManage)
                                            <td class="text-center">
                                                <button class="btn btn-outline-primary btn-sm btn-update-loan"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#updateLoanModal"
                                                        data-id="{{ $loan->id }}"
                                                        data-status="{{ $loan->status }}"
                                                        data-requested="{{ $loan->quantity_requested }}"
                                                        data-loaned="{{ $loan->quantity_loaned }}"
                                                        data-returned="{{ $loan->quantity_returned }}"
                                                        data-notes="{{ $loan->notes }}"
                                                        data-equipment_name="{{ $loan->equipment->name ?? '' }}">
                                                    ステータス更新
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $canManage ? 9 : 8 }}" class="text-center text-muted p-4">
                                            今年度の貸出割当データは現在ありません。
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 🛠️ 破損・状態管理タブ -->
            <div class="tab-pane fade" id="maintenance" role="tabpanel" aria-labelledby="maintenance-tab">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark mb-0">🛠️ 破損・補充・廃棄履歴</h5>
                    @if($canManage)
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addMaintenanceModal">
                            ➕ メンテナンス・破損補充の登録
                        </button>
                    @endif
                </div>

                <div class="card p-4 shadow-sm border-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle wamon-table">
                            <thead>
                                <tr>
                                    <th>記録日時</th>
                                    <th>備品名</th>
                                    <th>場所</th>
                                    <th>区分</th>
                                    <th>数量</th>
                                    <th>理由・詳細内容</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($maintenanceLogs as $log)
                                    <tr>
                                        <td class="text-muted small">{{ $log->recorded_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <span class="fw-bold text-dark">{{ $log->equipment->name ?? '削除された備品' }}</span>
                                        </td>
                                        <td>{{ $log->location->name ?? '特定場所なし' }}</td>
                                        <td>
                                            @if($log->log_type === 'repair')
                                                <span class="badge bg-info text-dark">要修理</span>
                                            @elseif($log->log_type === 'discard')
                                                <span class="badge bg-danger text-white">廃棄</span>
                                            @elseif($log->log_type === 'lost')
                                                <span class="badge bg-warning text-dark">紛失</span>
                                            @elseif($log->log_type === 'replenish')
                                                <span class="badge bg-success text-white">新規購入補充</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold text-dark">{{ $log->quantity }}</td>
                                        <td><span class="text-muted small">{{ $log->description }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted p-4">
                                            今年度のメンテナンス・破損・補充ログは記録されていません。
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ==================== 管理者用 各種モーダルダイアログ ==================== -->
@if($canManage)
    
    <!-- 1. 備品登録モーダル -->
    <div class="modal fade" id="addEquipmentModal" tabindex="-1" aria-labelledby="addEquipmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('equipment.master.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="addEquipmentModalLabel">➕ 備品マスタ新規登録</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">所有区分 <span class="text-danger">*</span></label>
                            <select name="ownership_type" class="form-select" required>
                                <option value="owned">実行委員会所有 (owned)</option>
                                <option value="rental">外部イベント会社レンタル (rental)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">品名 (備品名) <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="例: パイプテント" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">規格・寸法 (レンタル品は必須)</label>
                            <input type="text" name="specifications" class="form-control" placeholder="例: 1.5k x 2k">
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">初期総数量 <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" class="form-control" min="0" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">単位 <span class="text-danger">*</span></label>
                                <input type="text" name="unit" class="form-control" placeholder="例: 張、台、個" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">単価 (金額非公開対象)</label>
                            <input type="number" name="unit_price" class="form-control" placeholder="例: 8000" min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">分類カテゴリ <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                <option value="什器・テント">什器・テント</option>
                                <option value="音響・電気">音響・電気</option>
                                <option value="保安・防災">保安・防災</option>
                                <option value="看板・装飾">看板・装飾</option>
                                <option value="その他">その他</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">備品画像</label>
                            <input type="file" name="image" class="form-control equipment-image-input" data-preview="addPreview" accept="image/*">
                            <div class="image-preview-box mt-2" id="addPreview">
                                <span class="text-muted small">クリックして画像を選択</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">仕様・備考</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="仕様やレンタル元、配備時の注意点など"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                        <button type="submit" class="btn btn-primary">登録する</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 2. 備品編集モーダル -->
    <div class="modal fade" id="editEquipmentModal" tabindex="-1" aria-labelledby="editEquipmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="editEquipmentModalLabel">🛠️ 備品情報の編集</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">所有区分 <span class="text-danger">*</span></label>
                            <select name="ownership_type" class="form-select" required>
                                <option value="owned">実行委員会所有 (owned)</option>
                                <option value="rental">外部イベント会社レンタル (rental)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">品名 (備品名) <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">規格・寸法</label>
                            <input type="text" name="specifications" class="form-control">
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">総数量 <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" class="form-control" min="0" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">単位 <span class="text-danger">*</span></label>
                                <input type="text" name="unit" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">単価 (金額非公開対象)</label>
                            <input type="number" name="unit_price" class="form-control" min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">分類カテゴリ <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                <option value="什器・テント">什器・テント</option>
                                <option value="音響・電気">音響・電気</option>
                                <option value="保安・防災">保安・防災</option>
                                <option value="看板・装飾">看板・装飾</option>
                                <option value="その他">その他</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">備品画像 (変更する場合のみ選択)</label>
                            <input type="file" name="image" class="form-control equipment-image-input" data-preview="editImagePreview" accept="image/*">
                            <div class="image-preview-box mt-2" id="editImagePreview">
                                <span class="text-muted small">画像未登録</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">仕様・備考</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                        <button type="submit" class="btn btn-primary">更新する</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 3. 保管場所追加モーダル -->
    <div class="modal fade" id="addLocationModal" tabindex="-1" aria-labelledby="addLocationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('equipment.location.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="addLocationModalLabel">➕ 保管場所・倉庫の新規登録</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">場所・倉庫名 <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="例: 番所地下倉庫" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">担当者・管理者</label>
                            <input type="text" name="contact_person" class="form-control" placeholder="例: 幹事（鈴木）">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">備考（鍵の場所等）</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="鍵の保管場所や、立入時の注意点など"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                        <button type="submit" class="btn btn-primary">登録する</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 4. 在庫数量手動調整モーダル -->
    <div class="modal fade" id="adjustStockModal" tabindex="-1" aria-labelledby="adjustStockModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('equipment.stock.adjust') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="adjustStockModalLabel">🔄 倉庫在庫の手動調整</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">対象の備品 <span class="text-danger">*</span></label>
                            <select name="equipment_id" class="form-select" required>
                                @foreach($equipments as $eq)
                                    <option value="{{ $eq->id }}">{{ $eq->name }} ({{ $eq->specifications ?? '規格なし' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">保管場所 <span class="text-danger">*</span></label>
                            <select name="storage_location_id" class="form-select" required>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">調整後 在庫数量 <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control" min="0" required>
                            <div class="form-text text-muted small">棚卸結果など、指定場所に現存する正確な数を入力してください。</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                        <button type="submit" class="btn btn-primary">調整を実行</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 5. 新規貸出登録モーダル -->
    <div class="modal fade" id="addLoanModal" tabindex="-1" aria-labelledby="addLoanModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('equipment.loan.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="addLoanModalLabel">➕ 新規貸出・割当の登録</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">対象の備品 <span class="text-danger">*</span></label>
                            <select name="equipment_id" class="form-select" required>
                                @foreach($equipments as $eq)
                                    <option value="{{ $eq->id }}">{{ $eq->name }} (在庫総数: {{ $eq->quantity }} {{ $eq->unit }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">借用者区分 <span class="text-danger">*</span></label>
                            <select name="borrower_type" class="form-select" required>
                                <option value="gozaichi">ござ市出店者 (gozaichi)</option>
                                <option value="staff">実行委員会・部署 (staff)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">借用者ID (出店応募ID or 会員ID/部署コード) <span class="text-danger">*</span></label>
                            <input type="number" name="borrower_id" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">希望割当数量 <span class="text-danger">*</span></label>
                            <input type="number" name="quantity_requested" class="form-control" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">備考</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="引き渡し場所や特記事項など"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                        <button type="submit" class="btn btn-primary">登録する</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 6. 貸出ステータス更新モーダル -->
    <div class="modal fade" id="updateLoanModal" tabindex="-1" aria-labelledby="updateLoanModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="updateLoanModalLabel">🔄 貸出ステータスの更新</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3 p-3 bg-light rounded">
                            <div class="small mb-1">対象備品: <strong id="loanEquipmentName" class="text-dark"></strong></div>
                            <div class="small">希望数量: <strong id="loanRequestedQty" class="text-dark"></strong></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">貸出ステータス <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="pending">準備中 / 未引渡</option>
                                <option value="loaned">現場貸出中</option>
                                <option value="returned">返却完了</option>
                                <option value="partial">一部返却</option>
                                <option value="lost">紛失 / 破損</option>
                            </select>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">実引渡数量 <span class="text-danger">*</span></label>
                                <input type="number" name="quantity_loaned" class="form-control" min="0" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">返却済数量 <span class="text-danger">*</span></label>
                                <input type="number" name="quantity_returned" class="form-control" min="0" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">備考（特記・紛失時の経緯など）</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                        <button type="submit" class="btn btn-primary">ステータス更新</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 7. 破損・補充登録モーダル -->
    <div class="modal fade" id="addMaintenanceModal" tabindex="-1" aria-labelledby="addMaintenanceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('equipment.maintenance.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="addMaintenanceModalLabel">🛠️ 破損・補充の登録</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">対象の備品 <span class="text-danger">*</span></label>
                            <select name="equipment_id" class="form-select" required>
                                @foreach($equipments as $eq)
                                    <option value="{{ $eq->id }}">{{ $eq->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">影響を受けた保管場所</label>
                            <select name="storage_location_id" class="form-select">
                                <option value="">特定場所なし</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text text-muted small">※廃棄・紛失・補充などの場合、指定した場所の在庫数量が自動で増減します。</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">区分 <span class="text-danger">*</span></label>
                            <select name="log_type" class="form-select" required>
                                <option value="repair">要修理 (repair)</option>
                                <option value="discard">廃棄 (discard - 在庫自動減算)</option>
                                <option value="lost">紛失 (lost - 在庫自動減算)</option>
                                <option value="replenish">新規購入補充 (replenish - 在庫自動加算)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">数量 <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">原因・対応内容 (詳細) <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="3" placeholder="経緯や業者修理依頼の詳細など" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                        <button type="submit" class="btn btn-primary">登録する</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 8. 前年度引き継ぎ確認モーダル -->
    <div class="modal fade" id="copyYearModal" tabindex="-1" aria-labelledby="copyYearModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('equipment.copy-year') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="copyYearModalLabel">📋 前年度データ引き継ぎ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-dark small">
                            前年度の備品定義および保管場所の構成をコピーして本年度用に引き継ぎます。<br>
                            本操作は、年度を切り替えて新規にまつりの準備を進める際に行う操作です。
                        </p>
                        <div class="alert alert-warning small">
                            <strong>注意:</strong> すでに登録済みの本年度の備品定義に影響はありませんが、同一の名称の備品がある場合は重複して登録される可能性があります。
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                        <button type="submit" class="btn btn-success">移行を実行する</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endif

<script src="/js/equipment.js"></script>
@endsection
