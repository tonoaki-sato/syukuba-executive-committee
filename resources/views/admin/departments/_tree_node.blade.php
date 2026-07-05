@foreach ($departments as $dept)
    <li class="org-tree-node" data-id="{{ $dept->id }}" draggable="true">
        <div class="card shadow-sm mb-3 org-card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2 px-3">
                <span class="fw-bold text-truncate" title="{{ $dept->name }}">{{ $dept->name }}</span>
                <span class="badge bg-secondary text-uppercase" style="font-size: 0.7rem;">{{ $dept->code }}</span>
            </div>
            <div class="card-body p-2">
                <!-- メンバーリスト -->
                <div class="list-group list-group-flush mb-2" style="font-size: 0.85rem;">
                    @forelse ($dept->members as $member)
                        <div class="list-group-item d-flex justify-content-between align-items-center py-1 px-1 border-0">
                            <div class="text-truncate member-display-name-container">
                                @if ($member->is_leader)
                                    <span class="badge bg-danger px-1 py-0 me-1" title="リーダー" style="font-size: 0.65rem;">L</span>
                                @endif
                                <span class="{{ $member->is_leader ? 'fw-bold text-danger' : '' }}" title="{{ $member->display_name }}">
                                    {{ $member->display_name }}
                                </span>
                                <span class="text-muted small" title="{{ $member->role_name }}">({{ $member->role_name }})</span>
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-link p-0 text-secondary me-1 edit-member-btn"
                                    data-id="{{ $member->id }}"
                                    data-user-id="{{ $member->user_id }}"
                                    data-custom-name="{{ $member->custom_name }}"
                                    data-role-name="{{ $member->role_name }}"
                                    data-is-leader="{{ $member->is_leader ? '1' : '0' }}"
                                    data-sort-order="{{ $member->sort_order }}"
                                    data-bs-toggle="modal" data-bs-target="#editMemberModal"
                                    style="font-size: 0.75rem;">
                                    編集
                                </button>
                                <form action="{{ route('admin.departments.members.destroy', $member->id) }}" method="POST" class="d-inline confirm-delete-form" data-confirm-message="メンバーを削除しますか？">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-link p-0 text-danger" style="font-size: 0.75rem;">削除</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted text-center py-1 small">メンバーなし</div>
                    @endforelse
                </div>
                
                <!-- 部門アクション -->
                <div class="d-flex justify-content-between align-items-center border-top pt-2 px-1">
                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 add-member-btn" 
                        data-dept-id="{{ $dept->id }}"
                        data-dept-name="{{ $dept->name }}"
                        data-bs-toggle="modal" data-bs-target="#addMemberModal"
                        style="font-size: 0.75rem;">
                        + メンバー
                    </button>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 edit-dept-btn"
                            data-id="{{ $dept->id }}"
                            data-code="{{ $dept->code }}"
                            data-name="{{ $dept->name }}"
                            data-category="{{ $dept->category }}"
                            data-parent-id="{{ $dept->parent_id }}"
                            data-sort-order="{{ $dept->sort_order }}"
                            data-bs-toggle="modal" data-bs-target="#editDeptModal"
                            style="font-size: 0.75rem;">
                            編集
                        </button>
                        <form action="{{ route('admin.departments.destroy', $dept->id) }}" method="POST" class="d-inline confirm-delete-form" data-confirm-message="この部門と配下の子部門、およびメンバーが削除されます。よろしいですか？">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1" style="font-size: 0.75rem;">
                                削除
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        @if ($dept->children->isNotEmpty())
            <ul>
                @include('admin.departments._tree_node', ['departments' => $dept->children])
            </ul>
        @endif
    </li>
@endforeach
