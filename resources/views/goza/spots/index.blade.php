@extends('layouts.app')

@section('title', '出店場所配置（区画割り当て）')

@section('content')
<div class="mb-4">
    <h3 class="fw-bold text-dark mb-1">出店場所配置（区画割り当て）</h3>
    <p class="text-muted small">当選（出店許可）した店舗に対し、会場の区画コードを割り当てます。</p>
</div>

<!-- 警告・ステータスメッセージ -->
@if (session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>⚠️ 警告:</strong> {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <ul class="mb-0 small">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">屋号・団体名</th>
                        <th>出店者氏名</th>
                        <th>希望区画（1区画目/2区画目以降）</th>
                        <th>火気使用</th>
                        <th>現在の区画コード</th>
                        <th>区画の割り当て・変更</th>
                        <th class="pe-4">保健所指導アラート</th>
                    </tr>
                </thead>
                <tbody>
                    @if($applications->count() > 0)
                        @foreach($applications as $app)
                            <tr>
                                <td class="ps-4 fw-bold">{{ $app->shop_name }}</td>
                                <td>{{ $app->exhibitor_name }}</td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $app->section_count }}区画</span>
                                    <span class="small ms-1">
                                        ({{ $app->first_section_type }}
                                        @if($app->section_count > 1) / {{ $app->subsequent_section_type }} @endif)
                                    </span>
                                </td>
                                <td>
                                    @if($app->has_fire)
                                        <span class="badge bg-danger">有 ({{ $app->fire_equipment }})</span>
                                    @else
                                        <span class="badge bg-secondary">無</span>
                                    @endif
                                </td>
                                <td>
                                    @if($app->spot_code)
                                        <span class="badge bg-primary fs-6">{{ $app->spot_code }}</span>
                                    @else
                                        <span class="badge bg-warning text-dark">未配置</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('goza.spots.update', $app->id) }}" method="POST" class="row g-2 align-items-center">
                                        @csrf
                                        @method('PUT')
                                        <div class="col-auto">
                                            <input type="text" name="spot_code" value="{{ old('spot_code', $app->spot_code) }}" class="form-control form-control-sm border-secondary-subtle" placeholder="例: A15, B20-21" style="width: 120px;">
                                        </div>
                                        <div class="col-auto">
                                            <button type="submit" class="btn btn-primary btn-sm">確定</button>
                                        </div>
                                    </form>
                                </td>
                                <td class="pe-4">
                                    @if($app->first_section_type === 'B' || $app->subsequent_section_type === 'B')
                                        <div class="text-danger small fw-bold">
                                            ⚠️ 調理を伴うため3方幕テントが必要です
                                        </div>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                当選済みの出店者がいません。最初に応募管理から「当選（許可）」ステータスに変更してください。
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
