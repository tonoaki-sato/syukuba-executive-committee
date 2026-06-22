@extends('layouts.app')

@section('title', '当日集金・領収書発行')

@section('content')
<div class="mb-4">
    <h3 class="fw-bold text-dark mb-1">当日集金・領収書・許可証発行</h3>
    <p class="text-muted small">出店当日の料金集金、受領処理、および帳票印刷を行います。</p>
</div>

<!-- 検索・フィルタ -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('goza.payments.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm border-secondary-subtle" placeholder="屋号、氏名、区画コードで検索...">
            </div>
            <div class="col-md-3">
                <select name="is_paid" class="form-select form-select-sm border-secondary-subtle">
                    <option value="">すべての支払い状況</option>
                    <option value="0" {{ request('is_paid') === '0' ? 'selected' : '' }}>未入金</option>
                    <option value="1" {{ request('is_paid') === '1' ? 'selected' : '' }}>入金済み</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm px-4">検索</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">区画</th>
                        <th>屋号・団体名</th>
                        <th>出店料</th>
                        <th>備品貸出料</th>
                        <th>ゴミ袋料</th>
                        <th>総合計</th>
                        <th>状況</th>
                        <th class="pe-4">アクション・集金処理</th>
                    </tr>
                </thead>
                <tbody>
                    @if($applications->count() > 0)
                        @foreach($applications as $app)
                            <tr>
                                <td class="ps-4">
                                    @if($app->spot_code)
                                        <span class="badge bg-primary fs-6">{{ $app->spot_code }}</span>
                                    @else
                                        <span class="badge bg-warning text-dark">未配置</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $app->shop_name }}</div>
                                    <div class="text-muted small">{{ $app->exhibitor_name }}</div>
                                </td>
                                <td>{{ number_format($app->exhibition_fee) }} 円</td>
                                <td>
                                    @if($app->equipment_fee_override !== null)
                                        <span class="text-danger fw-bold" title="手動調整済み">{{ number_format($app->equipment_fee_override) }} 円</span>
                                        <div class="text-muted" style="font-size: 0.75rem; text-decoration: line-through;">(通常: {{ number_format($app->equipment_fee) }} 円)</div>
                                    @else
                                        {{ number_format($app->equipment_fee) }} 円
                                    @endif
                                </td>
                                <td>{{ number_format($app->trash_bag_fee) }} 円</td>
                                <td class="fw-bold text-dark fs-5">{{ number_format($app->total_fee) }} 円</td>
                                <td>
                                    @if($app->is_paid)
                                        <span class="badge bg-success">受領済</span>
                                        @if($app->permit_issued)
                                            <span class="badge bg-dark mt-1 d-block w-100">許可証交付済</span>
                                        @else
                                            <span class="badge bg-secondary mt-1 d-block w-100">許可証未交付</span>
                                        @endif
                                    @else
                                        <span class="badge bg-danger">未受領</span>
                                    @endif
                                </td>
                                <td class="pe-4">
                                    @if($app->is_paid)
                                        <div class="d-flex gap-1 flex-wrap">
                                            <a href="{{ route('goza.payments.receipt', $app->id) }}" target="_blank" class="btn btn-outline-success btn-sm px-2">
                                                📄 領収書
                                            </a>
                                            <a href="{{ route('goza.payments.permit', $app->id) }}" target="_blank" class="btn btn-outline-dark btn-sm px-2">
                                                🎫 許可証
                                            </a>
                                            <!-- 許可証の交付トグルのみの簡易更新用フォーム -->
                                            <form action="{{ route('goza.payments.receive', $app->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="equipment_fee_override" value="{{ $app->equipment_fee_override }}">
                                                <input type="hidden" name="permit_issued" value="{{ $app->permit_issued ? '0' : '1' }}">
                                                <button type="submit" class="btn btn-light btn-sm border text-secondary">
                                                    {{ $app->permit_issued ? '未交付に戻す' : '交付済みにする' }}
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <!-- 受領アクションフォーム -->
                                        <form action="{{ route('goza.payments.receive', $app->id) }}" method="POST" class="row g-2 align-items-center">
                                            @csrf
                                            @method('PUT')
                                            <div class="col-auto">
                                                <input type="number" name="equipment_fee_override" value="{{ $app->equipment_fee_override ?? $app->equipment_fee }}" class="form-control form-control-sm border-secondary-subtle" placeholder="備品料調整(任意)" style="width: 130px;" title="実務上の事情で備品料を調整する場合は上書き入力">
                                            </div>
                                            <div class="col-auto">
                                                <div class="form-check form-check-inline m-0 small">
                                                    <input class="form-check-input" type="checkbox" name="permit_issued" id="permit_issued_{{ $app->id }}" value="1" checked>
                                                    <label class="form-check-label small" for="permit_issued_{{ $app->id }}">許可証も交付</label>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <button type="submit" class="btn btn-warning btn-sm fw-bold">受領</button>
                                            </div>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                出店者がいません。
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
