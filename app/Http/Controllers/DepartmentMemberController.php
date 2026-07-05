<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DepartmentMember;
use Illuminate\Http\Request;

class DepartmentMemberController extends Controller
{
    /**
     * 部門へのメンバー新規追加
     */
    public function store(Request $request, Department $department)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:comittee_users,id',
            'custom_name' => 'required_without:user_id|nullable|string|max:100',
            'role_name' => 'required|string|max:100',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['department_id'] = $department->id;
        $validated['is_leader'] = $request->has('is_leader');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $validated['user_id'] = $validated['user_id'] ?? null;
        $validated['custom_name'] = $validated['custom_name'] ?? null;

        // 会員が選択されている場合、カスタム名は不要にする
        if ($validated['user_id']) {
            $validated['custom_name'] = null;
        }

        DepartmentMember::create($validated);

        return back()->with('success', 'メンバーを追加しました。');
    }

    /**
     * 部門メンバーの更新
     */
    public function update(Request $request, DepartmentMember $member)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:comittee_users,id',
            'custom_name' => 'required_without:user_id|nullable|string|max:100',
            'role_name' => 'required|string|max:100',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_leader'] = $request->has('is_leader');
        $validated['sort_order'] = $validated['sort_order'] ?? $member->sort_order;

        $validated['user_id'] = $validated['user_id'] ?? null;
        $validated['custom_name'] = $validated['custom_name'] ?? null;

        if ($validated['user_id']) {
            $validated['custom_name'] = null;
        }

        $member->update($validated);

        return back()->with('success', 'メンバー情報を更新しました。');
    }

    /**
     * 部門メンバーの削除
     */
    public function destroy(DepartmentMember $member)
    {
        $member->delete();

        return back()->with('success', 'メンバーを削除しました。');
    }
}
