@extends('layouts.app')

@section('title', '組織図管理')

@section('content')
<!-- CSP対応外部CSSロード -->
<link rel="stylesheet" href="{{ asset('css/departments.css') }}">

<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="h3 text-secondary mb-1">
            <i class="bi bi-diagram-3-fill"></i> 組織図管理（{{ $fiscalYear }}年度）
        </h2>
        <p class="text-muted small">
            部門の構成およびメンバーの役割分担を管理します。ドラッグ＆ドロップで部門の親子関係を直感的に変更できます。
        </p>
    </div>
    
    <div class="col-md-6 d-flex justify-content-md-end align-items-center gap-2 flex-wrap">
        <!-- 年度切り替え -->
        <div class="d-flex align-items-center">
            <span class="text-muted small me-2">年度切り替え:</span>
            <select id="fiscal-year-select" class="form-select form-select-sm fiscal-year-select">
                @foreach ($availableYears as $year)
                    <option value="{{ $year }}" {{ $year == $fiscalYear ? 'selected' : '' }}>{{ $year }}年度</option>
                @endforeach
            </select>
        </div>

        <!-- 前年度コピーボタン -->
        <form action="{{ route('admin.departments.copy') }}" method="POST" class="confirm-delete-form" data-confirm-message="前年度の全部門とメンバー情報をコピーします。{{ $fiscalYear }}年度の既存データがある場合は実行できません。よろしいですか？">
            @csrf
            <input type="hidden" name="fiscal_year" value="{{ $fiscalYear }}">
            <button type="submit" class="btn btn-sm btn-outline-secondary">
                前年度からコピー
            </button>
        </form>

        <!-- 新規部門作成ボタン -->
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createDeptModal">
            + 部門の追加
        </button>
    </div>
</div>

<!-- 最上位（親なし）へのドラッグドロップゾーン -->
<div id="drop-root-zone" class="alert alert-warning text-center py-3 mb-3 d-none border-2 drop-root-zone-styled">
    <strong class="text-dark">ここに部門カードをドロップして、最上位（親なし）に移動します</strong>
</div>

<!-- 組織図ツリーコンテナ -->
<div class="org-tree-container shadow-sm p-4 bg-white border rounded">
    @if ($departments->isEmpty())
        <div class="text-center py-5 text-muted">
            <p class="mb-2">当年度（{{ $fiscalYear }}年度）の組織図データが登録されていません。</p>
            <p class="small">右上の「部門の追加」から新規に作成するか、「前年度からコピー」を実行してください。</p>
        </div>
    @else
        <div class="org-tree">
            <ul>
                @include('admin.departments._tree_node', ['departments' => $departments])
            </ul>
        </div>
    @endif
</div>

<!-- -------------------- モーダル定義 -------------------- -->

<!-- 新規部門追加モーダル -->
<div class="modal fade" id="createDeptModal" tabindex="-1" aria-labelledby="createDeptModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.departments.store') }}" method="POST">
            @csrf
            <input type="hidden" name="fiscal_year" value="{{ $fiscalYear }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createDeptModalLabel">新規部門の追加</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="code" class="form-label small fw-bold">部門コード (一意)</label>
                        <input type="text" name="code" id="code" class="form-control" placeholder="例: SECRETARIAT" required>
                        <div class="form-text small">半角英数字とアンダースコア推奨。</div>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label small fw-bold">部門名</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="例: 総務" required>
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label small fw-bold">カテゴリ</label>
                        <select name="category" id="category" class="form-select" required>
                            <option value="staff" selected>実行委員会 (staff)</option>
                            <option value="partner">外部連携・サポート (partner)</option>
                            <option value="booth">出店関連 (booth)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="parent_id" class="form-label small fw-bold">親部門 (任意)</label>
                        <select name="parent_id" id="parent_id" class="form-select">
                            <option value="">最上位 (親なし)</option>
                            @foreach ($allDepartments as $d)
                                <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="sort_order" class="form-label small fw-bold">表示順</label>
                        <input type="number" name="sort_order" id="sort_order" class="form-control" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">キャンセル</button>
                    <button type="submit" class="btn btn-primary btn-sm">作成する</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- 部門編集モーダル -->
<div class="modal fade" id="editDeptModal" tabindex="-1" aria-labelledby="editDeptModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editDeptForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editDeptModalLabel">部門の編集</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_code" class="form-label small fw-bold">部門コード (一意)</label>
                        <input type="text" name="code" id="edit_code" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_name" class="form-label small fw-bold">部門名</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_category" class="form-label small fw-bold">カテゴリ</label>
                        <select name="category" id="edit_category" class="form-select" required>
                            <option value="staff">実行委員会 (staff)</option>
                            <option value="partner">外部連携・サポート (partner)</option>
                            <option value="booth">出店関連 (booth)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_parent_id" class="form-label small fw-bold">親部門 (任意)</label>
                        <select name="parent_id" id="edit_parent_id" class="form-select">
                            <option value="">最上位 (親なし)</option>
                            @foreach ($allDepartments as $d)
                                <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_sort_order" class="form-label small fw-bold">表示順</label>
                        <input type="number" name="sort_order" id="edit_sort_order" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">キャンセル</button>
                    <button type="submit" class="btn btn-primary btn-sm">保存する</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- メンバー追加モーダル -->
<div class="modal fade" id="addMemberModal" tabindex="-1" aria-labelledby="addMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="addMemberForm" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addMemberModalLabel"><span id="target-dept-name" class="text-primary-color"></span> へのメンバー追加</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold d-block">追加タイプ</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="member_type" id="type_user" value="user" checked>
                            <label class="form-check-label small" for="type_user">登録会員から選択</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="member_type" id="type_custom" value="custom">
                            <label class="form-check-label small" for="type_custom">名前を直接入力（非会員・募集枠）</label>
                        </div>
                    </div>

                    <!-- 会員選択用フィールド -->
                    <div class="mb-3" id="user_select_container">
                        <label for="user_search" class="form-label small fw-bold">会員名検索</label>
                        <input type="text" id="user_search" class="form-control" placeholder="名前を入力して検索..." list="user_list">
                        <datalist id="user_list">
                            @foreach($users as $user)
                                <option data-id="{{ $user->id }}" value="{{ $user->name }} ({{ $user->profession }})"></option>
                            @endforeach
                        </datalist>
                        <input type="hidden" name="user_id" id="selected_user_id">
                    </div>

                    <!-- 直接入力用フィールド -->
                    <div class="mb-3 d-none" id="custom_name_container">
                        <label for="custom_name" class="form-label small fw-bold">氏名またはプレースホルダー名</label>
                        <input type="text" name="custom_name" id="custom_name" class="form-control" placeholder="例: 当日ボランティア募集, 横浜銀行">
                    </div>

                    <div class="mb-3">
                        <label for="role_name" class="form-label small fw-bold">役割・担当業務</label>
                        <input type="text" name="role_name" id="role_name" class="form-control" placeholder="例: 本陣受付, 会場設営, 連絡調整" required>
                    </div>

                    <div class="mb-3 form-check form-switch pt-2">
                        <input class="form-check-input" type="checkbox" name="is_leader" id="is_leader" value="1">
                        <label class="form-check-label small fw-bold" for="is_leader">部門リーダー（責任者）に指定する</label>
                    </div>

                    <div class="mb-3">
                        <label for="member_sort_order" class="form-label small fw-bold">表示順</label>
                        <input type="number" name="sort_order" id="member_sort_order" class="form-control" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">キャンセル</button>
                    <button type="submit" class="btn btn-primary btn-sm">追加する</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- メンバー編集モーダル -->
<div class="modal fade" id="editMemberModal" tabindex="-1" aria-labelledby="editMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editMemberForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editMemberModalLabel">メンバー情報の編集</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold d-block">タイプ</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="edit_member_type" id="edit_type_user" value="user">
                            <label class="form-check-label small" for="edit_type_user">登録会員から選択</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="edit_member_type" id="edit_type_custom" value="custom">
                            <label class="form-check-label small" for="edit_type_custom">名前を直接入力（非会員・募集枠）</label>
                        </div>
                    </div>

                    <!-- 会員選択用フィールド -->
                    <div class="mb-3" id="edit_user_select_container">
                        <label for="edit_user_search" class="form-label small fw-bold">会員名検索</label>
                        <input type="text" id="edit_user_search" class="form-control" placeholder="名前を入力して検索..." list="edit_user_list">
                        <datalist id="edit_user_list">
                            @foreach($users as $user)
                                <option data-id="{{ $user->id }}" value="{{ $user->name }} ({{ $user->profession }})"></option>
                            @endforeach
                        </datalist>
                        <input type="hidden" name="user_id" id="edit_selected_user_id">
                    </div>

                    <!-- 直接入力用フィールド -->
                    <div class="mb-3 d-none" id="edit_custom_name_container">
                        <label for="edit_custom_name" class="form-label small fw-bold">氏名またはプレースホルダー名</label>
                        <input type="text" name="custom_name" id="edit_custom_name" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="edit_role_name" class="form-label small fw-bold">役割・担当業務</label>
                        <input type="text" name="role_name" id="edit_role_name" class="form-control" required>
                    </div>

                    <div class="mb-3 form-check form-switch pt-2">
                        <input class="form-check-input" type="checkbox" name="is_leader" id="edit_is_leader" value="1">
                        <label class="form-check-label small fw-bold" for="edit_is_leader">部門リーダー（責任者）に指定する</label>
                    </div>

                    <div class="mb-3">
                        <label for="edit_member_sort_order" class="form-label small fw-bold">表示順</label>
                        <input type="number" name="sort_order" id="edit_member_sort_order" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">キャンセル</button>
                    <button type="submit" class="btn btn-primary btn-sm">保存する</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- CSP対応外部JSロード -->
<script src="{{ asset('js/departments.js') }}"></script>
@endsection
