@extends('layouts.app')

@section('title', '出店応募者一覧・選別')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h3 class="fw-bold text-dark mb-1">出店応募者一覧・選別</h3>
        <p class="text-muted small">応募状況の確認および当選・落選の選別</p>
    </div>
    <div>
        <a href="{{ route('goza.applications.create') }}" class="btn btn-primary d-flex align-items-center">
            <span class="me-1">＋</span> 新規応募登録（代理入力）
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">屋号・団体名</th>
                        <th>出店者氏名</th>
                        <th>加盟</th>
                        <th>区画種類/数</th>
                        <th>紹介者</th>
                        <th>火気/食品</th>
                        <th>ステータス</th>
                        <th class="pe-4 text-end">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @if($applications->count() > 0)
                        @foreach($applications as $app)
                            <tr>
                                <td class="ps-4 fw-bold">
                                    <a href="{{ route('goza.applications.show', $app->id) }}" class="text-dark text-decoration-none hover-link">
                                        {{ $app->shop_name }}
                                    </a>
                                </td>
                                <td>{{ $app->exhibitor_name }}</td>
                                <td>
                                    @if($app->is_member)
                                        <span class="badge bg-success-subtle text-success">加盟店</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">一般</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="small fw-semibold">{{ $app->first_section_type }}</span>
                                    @if($app->section_count > 1)
                                        <span class="small text-muted">(+{{ $app->subsequent_section_type }}等)</span>
                                    @endif
                                    <span class="badge bg-light text-dark ms-1">{{ $app->section_count }}区画</span>
                                </td>
                                <td class="small">
                                    @if($app->introducer_name)
                                        {{ $app->introducer_name }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        @if($app->has_fire)
                                            <span class="badge bg-danger" title="火気使用あり: {{ $app->fire_equipment }}">火</span>
                                        @endif
                                        @if($app->has_food)
                                            <span class="badge bg-warning text-dark" title="食品取扱あり">食</span>
                                        @endif
                                        @if(!$app->has_fire && !$app->has_food)
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <form action="{{ route('goza.applications.updateStatus', $app->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <select name="status" onchange="this.form.submit()" class="form-select form-select-sm d-inline-block w-auto border-secondary-subtle">
                                            <option value="draft" {{ $app->status === 'draft' ? 'selected' : '' }}>下書き</option>
                                            <option value="submitted" {{ $app->status === 'submitted' ? 'selected' : '' }}>応募済</option>
                                            <option value="accepted" {{ $app->status === 'accepted' ? 'selected' : '' }}>当選（許可）</option>
                                            <option value="rejected" {{ $app->status === 'rejected' ? 'selected' : '' }}>落選</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="{{ route('goza.applications.show', $app->id) }}" class="btn btn-outline-secondary btn-sm px-3">
                                        詳細
                                    </a>
                                    <a href="{{ route('goza.applications.edit', $app->id) }}" class="btn btn-outline-primary btn-sm px-2 ms-1">
                                        編集
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                応募者データが登録されていません。
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .hover-link:hover {
        color: var(--primary-color) !important;
        text-decoration: underline !important;
    }
</style>
@endsection
