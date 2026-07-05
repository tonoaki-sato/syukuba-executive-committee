/* 組織図管理機能 カスタムスクリプト (CSP対応: インラインJS禁止) */

document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. 年度切り替えイベント ---
    const fiscalYearSelect = document.getElementById('fiscal-year-select');
    if (fiscalYearSelect) {
        fiscalYearSelect.addEventListener('change', function() {
            location.href = '?fiscal_year=' + this.value;
        });
    }

    // --- 2. 削除フォームの確認ダイアログ (イベントデリゲーション) ---
    document.addEventListener('submit', function(e) {
        if (e.target && e.target.classList.contains('confirm-delete-form')) {
            const message = e.target.getAttribute('data-confirm-message') || '削除してもよろしいですか？';
            if (!confirm(message)) {
                e.preventDefault();
            }
        }
    });

    // --- 3. 部門編集モーダルの値セット ---
    const editDeptButtons = document.querySelectorAll('.edit-dept-btn');
    const editDeptForm = document.getElementById('editDeptForm');
    const editCodeInput = document.getElementById('edit_code');
    const editNameInput = document.getElementById('edit_name');
    const editCategoryInput = document.getElementById('edit_category');
    const editParentInput = document.getElementById('edit_parent_id');
    const editSortInput = document.getElementById('edit_sort_order');

    editDeptButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const code = this.getAttribute('data-code');
            const name = this.getAttribute('data-name');
            const category = this.getAttribute('data-category');
            const parentId = this.getAttribute('data-parent-id');
            const sortOrder = this.getAttribute('data-sort-order');

            // フォームのアクションURLを更新
            editDeptForm.action = `/admin/departments/${id}`;

            // 値をセット
            editCodeInput.value = code;
            editNameInput.value = name;
            editCategoryInput.value = category;
            editParentInput.value = parentId || '';
            editSortInput.value = sortOrder || 0;

            // 自身を親部門の選択肢から消して無限ループを防ぐ
            Array.from(editParentInput.options).forEach(option => {
                if (option.value === id) {
                    option.disabled = true;
                } else {
                    option.disabled = false;
                }
            });
        });
    });

    // --- 4. メンバー追加モーダルの値セット & タイプ切替 ---
    const addMemberButtons = document.querySelectorAll('.add-member-btn');
    const addMemberForm = document.getElementById('addMemberForm');
    const targetDeptName = document.getElementById('target-dept-name');
    const radioUser = document.getElementById('type_user');
    const radioCustom = document.getElementById('type_custom');
    const userContainer = document.getElementById('user_select_container');
    const customContainer = document.getElementById('custom_name_container');
    const userSearch = document.getElementById('user_search');
    const selectedUserId = document.getElementById('selected_user_id');

    addMemberButtons.forEach(button => {
        button.addEventListener('click', function() {
            const deptId = this.getAttribute('data-dept-id');
            const deptName = this.getAttribute('data-dept-name');

            // フォームのアクションURLを更新
            addMemberForm.action = `/admin/departments/${deptId}/members`;
            targetDeptName.textContent = deptName;

            // フォーム初期化
            addMemberForm.reset();
            selectedUserId.value = '';
            userContainer.classList.remove('d-none');
            customContainer.classList.add('d-none');
        });
    });

    // 追加タイプ切り替え処理
    const handleTypeChange = (isUser, userCont, customCont, searchEl, idEl, customNameInputId) => {
        const customNameEl = document.getElementById(customNameInputId);
        if (isUser) {
            userCont.classList.remove('d-none');
            customCont.classList.add('d-none');
            customNameEl.required = false;
            searchEl.required = true;
        } else {
            userCont.classList.add('d-none');
            customCont.classList.remove('d-none');
            customNameEl.required = true;
            searchEl.required = false;
            idEl.value = '';
        }
    };

    if (radioUser && radioCustom) {
        radioUser.addEventListener('change', () => handleTypeChange(true, userContainer, customContainer, userSearch, selectedUserId, 'custom_name'));
        radioCustom.addEventListener('change', () => handleTypeChange(false, userContainer, customContainer, userSearch, selectedUserId, 'custom_name'));
    }

    // 会員リストのオートコンプリートID設定
    if (userSearch) {
        userSearch.addEventListener('input', function() {
            const val = this.value;
            const options = document.getElementById('user_list').options;
            selectedUserId.value = '';
            for (let i = 0; i < options.length; i++) {
                if (options[i].value === val) {
                    selectedUserId.value = options[i].getAttribute('data-id');
                    break;
                }
            }
        });
    }

    // --- 5. メンバー編集モーダルの値セット & タイプ切替 ---
    const editMemberButtons = document.querySelectorAll('.edit-member-btn');
    const editMemberForm = document.getElementById('editMemberForm');
    const editRadioUser = document.getElementById('edit_type_user');
    const editRadioCustom = document.getElementById('edit_type_custom');
    const editUserContainer = document.getElementById('edit_user_select_container');
    const editCustomContainer = document.getElementById('edit_custom_name_container');
    const editUserSearch = document.getElementById('edit_user_search');
    const editSelectedUserId = document.getElementById('edit_selected_user_id');
    const editCustomName = document.getElementById('edit_custom_name');
    const editRoleName = document.getElementById('edit_role_name');
    const editIsLeader = document.getElementById('edit_is_leader');
    const editMemberSort = document.getElementById('edit_member_sort_order');

    editMemberButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const userId = this.getAttribute('data-user-id');
            const customName = this.getAttribute('data-custom-name');
            const roleName = this.getAttribute('data-role-name');
            const isLeader = this.getAttribute('data-is-leader') === '1';
            const sortOrder = this.getAttribute('data-sort-order');

            editMemberForm.action = `/admin/departments/members/${id}`;
            editRoleName.value = roleName;
            editIsLeader.checked = isLeader;
            editMemberSort.value = sortOrder || 0;

            if (userId) {
                editRadioUser.checked = true;
                editSelectedUserId.value = userId;
                // datalistオプションからユーザー名を探してセット
                const options = document.getElementById('edit_user_list').options;
                let foundName = '';
                for (let i = 0; i < options.length; i++) {
                    if (options[i].getAttribute('data-id') === userId) {
                        foundName = options[i].value;
                        break;
                    }
                }
                editUserSearch.value = foundName;
                editCustomName.value = '';
                handleTypeChange(true, editUserContainer, editCustomContainer, editUserSearch, editSelectedUserId, 'edit_custom_name');
            } else {
                editRadioCustom.checked = true;
                editCustomName.value = customName;
                editUserSearch.value = '';
                editSelectedUserId.value = '';
                handleTypeChange(false, editUserContainer, editCustomContainer, editUserSearch, editSelectedUserId, 'edit_custom_name');
            }
        });
    });

    if (editRadioUser && editRadioCustom) {
        editRadioUser.addEventListener('change', () => handleTypeChange(true, editUserContainer, editCustomContainer, editUserSearch, editSelectedUserId, 'edit_custom_name'));
        editRadioCustom.addEventListener('change', () => handleTypeChange(false, editUserContainer, editCustomContainer, editUserSearch, editSelectedUserId, 'edit_custom_name'));
    }

    if (editUserSearch) {
        editUserSearch.addEventListener('input', function() {
            const val = this.value;
            const options = document.getElementById('edit_user_list').options;
            editSelectedUserId.value = '';
            for (let i = 0; i < options.length; i++) {
                if (options[i].value === val) {
                    editSelectedUserId.value = options[i].getAttribute('data-id');
                    break;
                }
            }
        });
    }

    // --- 6. ドラッグ＆ドロップによる階層並び替え ---
    const nodes = document.querySelectorAll('.org-tree-node');
    const dropRootZone = document.getElementById('drop-root-zone');
    let draggedNode = null;

    nodes.forEach(node => {
        node.addEventListener('dragstart', function(e) {
            e.stopPropagation();
            draggedNode = this;
            this.classList.add('dragging');
            
            if (this.getAttribute('data-parent-id') !== '') {
                dropRootZone.classList.remove('d-none');
            }
        });

        node.addEventListener('dragend', function(e) {
            e.stopPropagation();
            this.classList.remove('dragging');
            dropRootZone.classList.add('d-none');
            dropRootZone.classList.remove('drag-enter');
            draggedNode = null;
            
            nodes.forEach(n => n.classList.remove('drag-over'));
        });

        node.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (draggedNode && draggedNode !== this && !draggedNode.contains(this)) {
                this.classList.add('drag-over');
            }
        });

        node.addEventListener('dragleave', function(e) {
            e.stopPropagation();
            this.classList.remove('drag-over');
        });

        node.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove('drag-over');

            if (!draggedNode || draggedNode === this || draggedNode.contains(this)) {
                return;
            }

            const draggedId = draggedNode.getAttribute('data-id');
            const targetId = this.getAttribute('data-id');

            updateDepartmentParent(draggedId, targetId);
        });
    });

    if (dropRootZone) {
        dropRootZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-enter');
        });

        dropRootZone.addEventListener('dragleave', function() {
            this.classList.remove('drag-enter');
        });

        dropRootZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-enter');

            if (!draggedNode) return;

            const draggedId = draggedNode.getAttribute('data-id');
            updateDepartmentParent(draggedId, null);
        });
    }

    function updateDepartmentParent(departmentId, parentId) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        fetch(`/admin/departments/${departmentId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                parent_id: parentId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('部門の階層移動に失敗しました。');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('通信エラーにより部門の移動ができませんでした。');
        });
    }
});
