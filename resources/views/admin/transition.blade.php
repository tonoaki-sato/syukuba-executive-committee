@extends('layouts.app')

@section('title', '新年度への移行・引き継ぎ')

@section('content')
<div class="row justify-content-center">
    <div class="col-12">
        <div class="mb-4">
            <h3 class="fw-bold text-dark mb-1">新年度への移行・引き継ぎ</h3>
            <p class="text-muted small">
            現在の活動対象年 <span class="fw-bold text-danger">{{ $activeYear }}年</span> から、新しい活動年 <span class="fw-bold text-success">{{ $targetYear }}年</span> へ会員情報を引き継ぎます。
            </p>
        </div>

        <!-- バリデーションエラー -->
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card p-4 shadow-sm border-0">
            <form action="{{ route('admin.users.transition-execute') }}" method="POST" onsubmit="return confirm('本当に選択したメンバーを指定した年に引き継ぎますか？（移行完了後、アクティブな年が自動的に {{ $targetYear }} 年に切り替わります）');">
                @csrf
                <input type="hidden" name="target_year" value="{{ $targetYear }}">

                <div class="alert alert-info py-2 px-3 mb-4 small" role="alert">
                    <strong>💡 会員の引き継ぎについて:</strong>
                    <ul class="mb-0 mt-1" style="padding-left: 1.25rem;">
                        <li>チェックを入れた会員のみ、{{ $targetYear }}年の活動メンバーとして引き継がれます。</li>
                        <li>新年度移行の際、各メンバーの新しい年における役割（ロール）を個別に変更して引き継ぐことができます。</li>
                        <li>移行されなかったメンバーは、{{ $targetYear }}年の会議スケジュールや出欠自動アサインの対象から除外されます（過去の{{ $activeYear }}年の名簿や会議アーカイブはそのまま残ります）。</li>
                    </ul>
                </div>

                @if($activeUsers->count() > 0)
                    <div class="table-responsive mb-4">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center w-10">移行する</th>
                                    <th class="w-30">会員名 (かな)</th>
                                    <th class="w-20">本業 / LINE名</th>
                                    <th class="w-15">前の年の役職</th>
                                    <th class="w-25">新しい年の役割（ロール設定）</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activeUsers as $user)
                                    @php
                                        // 前年度の所属レコードからロールを取得
                                        $userYear = $user->userYears->first();
                                        $roles = $userYear ? $userYear->roles : ['general'];
                                    @endphp
                                    <tr>
                                        <!-- 移行チェックボックス -->
                                        <td class="text-center">
                                            <input class="form-check-input" type="checkbox" name="users[]" value="{{ $user->id }}" id="user-check-{{ $user->id }}" checked style="transform: scale(1.2);">
                                        </td>
                                        
                                        <!-- 会員名 -->
                                        <td>
                                            <label class="form-check-label d-block fw-bold text-dark" for="user-check-{{ $user->id }}">{{ $user->name }}</label>
                                            <span class="text-muted small">{{ $user->name_kana }}</span>
                                        </td>
                                        
                                        <!-- 出自・LINE -->
                                        <td>
                                            <span class="d-block small fw-semibold text-dark">{{ $user->profession }}</span>
                                            <span class="text-primary-color small">LINE: {{ $user->line_display_name }}</span>
                                        </td>
                                        
                                        <!-- 前年度役職 -->
                                        <td>
                                            @if($userYear)
                                                @foreach($userYear->roles as $role)
                                                    @if($role === 'admin')
                                                        <span class="badge badge-admin badge-sm mb-1 d-block w-75 text-center">システム管理</span>
                                                    @elseif($role === 'kanji')
                                                        <span class="badge badge-kanji badge-sm mb-1 d-block w-75 text-center text-dark">幹事</span>
                                                    @elseif($role === 'equipment_manager')
                                                        <span class="badge bg-primary badge-sm mb-1 d-block w-75 text-center">備品管理</span>
                                                    @else
                                                        <span class="badge badge-general badge-sm mb-1 d-block w-75 text-center">一般会員</span>
                                                    @endif
                                                @endforeach
                                            @else
                                                <span class="text-muted small">未登録</span>
                                            @endif
                                        </td>
                                        
                                        <!-- 新年度の役割 -->
                                        <td>
                                            <div class="d-flex flex-column gap-1">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="roles[{{ $user->id }}][]" value="general" id="role-general-{{ $user->id }}" {{ in_array('general', $roles) ? 'checked' : '' }}>
                                                    <label class="form-check-label small" for="role-general-{{ $user->id }}">一般会員</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="roles[{{ $user->id }}][]" value="kanji" id="role-kanji-{{ $user->id }}" {{ in_array('kanji', $roles) ? 'checked' : '' }}>
                                                    <label class="form-check-label small text-warning-emphasis fw-semibold" for="role-kanji-{{ $user->id }}">幹事</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="roles[{{ $user->id }}][]" value="admin" id="role-admin-{{ $user->id }}" {{ in_array('admin', $roles) ? 'checked' : '' }}>
                                                    <label class="form-check-label small text-danger fw-semibold" for="role-admin-{{ $user->id }}">管理者</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="roles[{{ $user->id }}][]" value="equipment_manager" id="role-equipment-{{ $user->id }}" {{ in_array('equipment_manager', $roles) ? 'checked' : '' }}>
                                                    <label class="form-check-label small text-primary fw-semibold" for="role-equipment-{{ $user->id }}">備品管理</label>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mb-4 p-3 bg-light border rounded">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="copy_gozaichi" id="copy_gozaichi" value="1" checked>
                            <label class="form-check-label fw-bold text-dark" for="copy_gozaichi">
                                ござ市関連データ（料金マスタ等）を引き継ぐ
                            </label>
                            <div class="form-text small">
                                前年度の料金設定マスタを新規年度にコピーします。チェックを外した場合、移行先年度のイベント設定時に初期値（デフォルト単価）が設定されます。
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary me-md-2 px-4">キャンセル</a>
                        <button type="submit" class="btn btn-primary px-5">移行・引き継ぎを実行する</button>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <p class="mb-0">現在の活動年（{{ $activeYear }}年）に所属するアクティブな会員がいません。引き継ぐデータがありません。</p>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary mt-3">戻る</a>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection
