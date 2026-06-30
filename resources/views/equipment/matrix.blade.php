@extends('layouts.app')

@section('title', '部門別コスト配分マトリクス')

@section('content')
<link rel="stylesheet" href="/css/equipment.css">
<style>
    .matrix-table-container {
        overflow-x: auto;
        max-width: 100%;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .table-matrix th, .table-matrix td {
        vertical-align: middle;
        text-align: center;
        border-color: #f0f0f0;
        font-size: 0.9rem;
    }
    .table-matrix th.sticky-col, .table-matrix td.sticky-col {
        position: sticky;
        left: 0;
        background-color: #fff;
        z-index: 10;
        text-align: left;
        border-right: 2px solid #ddd;
        min-width: 180px;
    }
    .table-matrix th.sticky-col {
        background-color: #f8f9fa;
        z-index: 11;
    }
    .table-matrix tr:hover td.sticky-col {
        background-color: #f1f3f5;
    }
    .category-divider {
        background-color: #fdf6f0 !important;
        font-weight: bold;
        color: #8c1d30;
        text-align: left !important;
        font-size: 0.95rem;
    }
    .cell-number {
        font-weight: 600;
        color: #495057;
    }
    .cell-amount {
        font-size: 0.75rem;
        color: #8c1d30;
        display: block;
        font-weight: normal;
    }
    .total-row {
        background-color: #f8f9fa;
        font-weight: bold;
    }
    .grand-total-section {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border-left: 4px solid #8c1d30;
    }
</style>

<div class="row">
    <div class="col-12">
        <!-- ページヘッダー -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">📋 部門別コスト配分マトリクス</h3>
                <p class="text-muted small mb-0">
                    表示年度: <span class="fw-bold text-primary-color">{{ $fiscalYear }}年度</span>
                </p>
            </div>
            <div>
                <a href="{{ route('equipment.index') }}" class="btn btn-outline-secondary">
                    ⛺ 備品・倉庫台帳に戻る
                </a>
            </div>
        </div>

        <!-- タブナビゲーション -->
        <ul class="nav nav-tabs nav-tabs-wamon mb-4">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('equipment.index') }}">📊 ダッシュボード</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('equipment.matrix') }}">📋 部門別コスト配分</a>
            </li>
        </ul>

        @if(session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- マトリクス表カード -->
        <div class="card border-0 shadow-sm p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0">外部レンタル機材・諸経費 部門配分マトリクス</h5>
                <span class="text-muted small">※一般会員には割当数量のみが表示されます。</span>
            </div>

            <div class="matrix-table-container">
                <table class="table table-bordered table-hover table-matrix mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="sticky-col">レンタル品名 / 諸経費項目</th>
                            <th scope="col" style="min-width: 80px;">手配総数</th>
                            @if($canManage)
                                <th scope="col" style="min-width: 90px;">単価</th>
                            @endif
                            
                            <!-- 部門ヘッダー -->
                            @foreach($departments as $dept)
                                <th scope="col" style="min-width: 100px;">{{ $dept->name }}</th>
                            @endforeach

                            <!-- ござ市用貸与品 -->
                            <th scope="col" style="min-width: 110px;">※ござ市出店用</th>

                            @if($canManage)
                                <th scope="col" style="min-width: 120px;" class="table-danger">合計金額</th>
                            @else
                                <th scope="col" style="min-width: 100px;" class="table-secondary">未割当残数</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $classifiedEquipments = $equipments->groupBy(function($item) {
                                return $item->category === '諸経費・サービス' ? '諸経費・サービス' : 'レンタル機材・資材';
                            });
                        @endphp

                        @foreach(['レンタル機材・資材', '諸経費・サービス'] as $groupName)
                            @if(isset($classifiedEquipments[$groupName]))
                                <!-- グループ区切り行 -->
                                <tr>
                                    <td colspan="{{ 2 + ($canManage ? 1 : 0) + $departments->count() + 1 + 1 }}" class="category-divider">
                                        {{ $groupName }}
                                    </td>
                                </tr>

                                @foreach($classifiedEquipments[$groupName] as $eq)
                                    @php
                                        // 割当済み数量の合計
                                        $staffAllocatedSum = 0;
                                        foreach($departments as $d) {
                                            $loan = $loans->get($eq->id . '-' . $d->id)?->first();
                                            $staffAllocatedSum += $loan ? $loan->quantity_requested : 0;
                                        }
                                        $gozaAllocatedSum = $gozaichiLoans->get($eq->id)?->sum('quantity_requested') ?? 0;
                                        $totalAllocated = $staffAllocatedSum + $gozaAllocatedSum;
                                        $unallocated = max(0, $eq->quantity - $totalAllocated);
                                    @endphp
                                    <tr>
                                        <!-- 品名 -->
                                        <td class="sticky-col fw-bold">
                                            {{ $eq->name }}
                                            @if($eq->specifications)
                                                <small class="text-muted d-block" style="font-size: 0.75rem;">{{ $eq->specifications }}</small>
                                            @endif
                                        </td>
                                        
                                        <!-- 手配総数 -->
                                        <td>
                                            <span class="cell-number">{{ $eq->quantity }}</span>
                                            <small class="text-muted">{{ $eq->unit }}</small>
                                        </td>

                                        @if($canManage)
                                            <!-- Price -->
                                            <td class="text-end">
                                                ¥{{ number_format($eq->unit_price ?? 0) }}
                                            </td>
                                        @endif

                                        <!-- 各部門のセル -->
                                        @foreach($departments as $dept)
                                            @php
                                                $loan = $loans->get($eq->id . '-' . $dept->id)?->first();
                                                $qty = $loan ? $loan->quantity_requested : 0;
                                            @endphp
                                            <td>
                                                @if($qty > 0)
                                                    <span class="cell-number text-primary">{{ $qty }}</span>
                                                    <small class="text-muted">{{ $eq->unit }}</small>
                                                    @if($canManage)
                                                        <span class="cell-amount">¥{{ number_format($qty * ($eq->unit_price ?? 0)) }}</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        @endforeach

                                        <!-- ござ市 -->
                                        <td>
                                            @if($gozaAllocatedSum > 0)
                                                <span class="cell-number text-warning">{{ $gozaAllocatedSum }}</span>
                                                <small class="text-muted">{{ $eq->unit }}</small>
                                                @if($canManage)
                                                    <span class="cell-amount">¥{{ number_format($gozaAllocatedSum * ($eq->unit_price ?? 0)) }}</span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <!-- 右端集計 or 未割当残数 -->
                                        @if($canManage)
                                            <td class="text-end fw-bold table-danger">
                                                ¥{{ number_format($eq->total_amount) }}
                                            </td>
                                        @else
                                            <td>
                                                @if($unallocated > 0)
                                                    <span class="badge bg-warning text-dark">{{ $unallocated }}</span>
                                                @else
                                                    <span class="badge bg-success">配分済</span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach

                        <!-- 合計行（幹事・管理者のみ金額合計を表示） -->
                        <tr class="total-row">
                            <td class="sticky-col">割当合計</td>
                            <td>-</td>
                            @if($canManage)
                                <td>-</td>
                            @endif

                            <!-- 部門別合計 -->
                            @foreach($departments as $dept)
                                @php
                                    $deptQtySum = 0;
                                    $deptAmountSum = 0;
                                    foreach($equipments as $e) {
                                        $loan = $loans->get($e->id . '-' . $dept->id)?->first();
                                        $q = $loan ? $loan->quantity_requested : 0;
                                        $deptQtySum += $q;
                                        $deptAmountSum += $q * ($e->unit_price ?? 0);
                                    }
                                @endphp
                                <td>
                                    <span class="cell-number text-dark">{{ $deptQtySum }}</span>
                                    @if($canManage)
                                        <span class="cell-amount fw-bold">¥{{ number_format($deptAmountSum) }}</span>
                                    @endif
                                </td>
                            @endforeach

                            <!-- ござ市合計 -->
                            @php
                                $gozaQtySum = 0;
                                $gozaAmountSum = 0;
                                foreach($equipments as $e) {
                                    $gQty = $gozaichiLoans->get($e->id)?->sum('quantity_requested') ?? 0;
                                    $gozaQtySum += $gQty;
                                    $gozaAmountSum += $gQty * ($e->unit_price ?? 0);
                                }
                            @endphp
                            <td>
                                <span class="cell-number text-dark">{{ $gozaQtySum }}</span>
                                @if($canManage)
                                    <span class="cell-amount fw-bold">¥{{ number_format($gozaAmountSum) }}</span>
                                @endif
                            </td>

                            <!-- 総合計 -->
                            @if($canManage)
                                <td class="text-end text-danger fw-bold table-danger">
                                    ¥{{ number_format($equipments->sum('total_amount')) }}
                                </td>
                            @else
                                <td>-</td>
                            @endif
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 料金概要・値引き・消費税編集セクション（幹事・管理者のみ表示） -->
        @if($canManage)
            <div class="row g-4 mb-4">
                <!-- 料金集計パネル -->
                <div class="col-lg-6 col-12">
                    <div class="card border-0 shadow-sm p-4 grand-total-section h-100">
                        <h5 class="fw-bold text-dark mb-4">🧾 レンタル費用最終集計</h5>
                        
                        @php
                            $subtotal = $equipments->sum('total_amount');
                            $discount = $summary->special_discount;
                            $taxable = max(0, $subtotal - $discount);
                            $tax = floor($taxable * ($summary->tax_rate / 100));
                            $grandTotal = $taxable + $tax;
                        @endphp

                        <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                            <span class="text-muted">レンタル見積総額 (税別)</span>
                            <span class="fw-bold fs-5">¥{{ number_format($subtotal) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 border-bottom pb-2 text-primary">
                            <span>特別値引き</span>
                            <span class="fw-bold fs-5">-¥{{ number_format($discount) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                            <span class="text-muted">課税対象額</span>
                            <span>¥{{ number_format($taxable) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                            <span class="text-muted">消費税額 ({{ number_format($summary->tax_rate, 1) }}%)</span>
                            <span>¥{{ number_format($tax) }}</span>
                        </div>
                        <div class="d-flex justify-content-between pt-2 text-danger">
                            <span class="fw-bold fs-5">税込総合請求額</span>
                            <span class="fw-bold fs-3">¥{{ number_format($grandTotal) }}</span>
                        </div>

                        @if($summary->notes)
                            <div class="mt-3 p-3 bg-light rounded text-muted small">
                                <strong>備考：</strong>{{ $summary->notes }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- 値引き・消費税設定更新フォーム -->
                <div class="col-lg-6 col-12">
                    <div class="card border-0 shadow-sm p-4 h-100">
                        <h5 class="fw-bold text-dark mb-3">⚙️ 値引き・消費税率設定</h5>
                        <form action="{{ route('equipment.rental-summary.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="mb-3">
                                <label for="special_discount" class="form-label fw-bold text-secondary">特別値引き額 (税別・円)</label>
                                <div class="input-group">
                                    <span class="input-group-text">¥</span>
                                    <input type="number" class="form-control" id="special_discount" name="special_discount" value="{{ old('special_discount', $summary->special_discount) }}" min="0" required>
                                </div>
                                <div class="form-text">PDF見積の内訳表に記載された特別値引き額を入力します。</div>
                            </div>

                            <div class="mb-3">
                                <label for="tax_rate" class="form-label fw-bold text-secondary">適用消費税率 (%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" id="tax_rate" name="tax_rate" value="{{ old('tax_rate', $summary->tax_rate) }}" min="0" max="99.99" required>
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="form-text">標準税率は 10.00% です。</div>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label fw-bold text-secondary">集計に関する特記事項</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes', $summary->notes) }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mt-2">
                                💾 割引・税金設定を保存する
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
