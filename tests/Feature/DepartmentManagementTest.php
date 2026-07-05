<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\DepartmentMember;
use App\Models\User;
use App\Models\UserYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $generalUser;

    protected function setUp(): void
    {
        parent::setUp();

        // システム管理者ユーザー
        $this->adminUser = User::create([
            'name' => '管理 太郎',
            'name_kana' => 'かんり たろう',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'profession' => '役員',
            'line_display_name' => 'admin_line',
            'roles' => ['admin', 'general'],
            'status' => 'active',
        ]);

        UserYear::create([
            'user_id' => $this->adminUser->id,
            'fiscal_year' => 2026,
            'roles' => ['admin', 'general'],
            'status' => 'active',
        ]);

        // 一般会員ユーザー
        $this->generalUser = User::create([
            'name' => '一般 次郎',
            'name_kana' => 'いっぱん じろう',
            'email' => 'general@example.com',
            'password' => bcrypt('password123'),
            'profession' => '会社員',
            'line_display_name' => 'general_line',
            'roles' => ['general'],
            'status' => 'active',
        ]);

        UserYear::create([
            'user_id' => $this->generalUser->id,
            'fiscal_year' => 2026,
            'roles' => ['general'],
            'status' => 'active',
        ]);
    }

    /**
     * 未ログインユーザーは組織図管理にアクセスできない
     */
    public function test_guest_cannot_access_departments(): void
    {
        $response = $this->get(route('admin.departments.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * 一般ユーザーは組織図管理にアクセスできない
     */
    public function test_general_user_cannot_access_departments(): void
    {
        $response = $this->actingAs($this->generalUser)->get(route('admin.departments.index'));
        $response->assertStatus(403); // 403 Forbidden が返ることを検証
    }

    /**
     * 管理者ユーザーは組織図管理にアクセスできる
     */
    public function test_admin_can_access_departments(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.departments.index'));
        $response->assertStatus(200);
        $response->assertSee('組織図管理');
    }

    /**
     * 部門の新規作成テスト
     */
    public function test_admin_can_create_department(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.departments.store'), [
            'fiscal_year' => 2026,
            'code' => 'TEST_DEPT',
            'name' => 'テスト部門',
            'category' => 'staff',
            'parent_id' => null,
            'sort_order' => 10,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comittee_departments', [
            'fiscal_year' => 2026,
            'code' => 'TEST_DEPT',
            'name' => 'テスト部門',
        ]);
    }

    /**
     * 部門の更新テスト
     */
    public function test_admin_can_update_department(): void
    {
        $dept = Department::create([
            'fiscal_year' => 2026,
            'code' => 'OLD_DEPT',
            'name' => '古い部門',
            'category' => 'staff',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->put(route('admin.departments.update', $dept->id), [
            'name' => '新しい部門名',
            'code' => 'NEW_DEPT',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comittee_departments', [
            'id' => $dept->id,
            'name' => '新しい部門名',
            'code' => 'NEW_DEPT',
        ]);
    }

    /**
     * AJAXを介した部門の親子階層変更テスト
     */
    public function test_admin_can_update_department_hierarchy_via_ajax(): void
    {
        $parent = Department::create([
            'fiscal_year' => 2026,
            'code' => 'PARENT_DEPT',
            'name' => '親部門',
            'category' => 'staff',
        ]);

        $child = Department::create([
            'fiscal_year' => 2026,
            'code' => 'CHILD_DEPT',
            'name' => '子部門',
            'category' => 'staff',
        ]);

        $response = $this->actingAs($this->adminUser)->putJson(route('admin.departments.update', $child->id), [
            'parent_id' => $parent->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('comittee_departments', [
            'id' => $child->id,
            'parent_id' => $parent->id,
        ]);
    }

    /**
     * 部門の削除テスト
     */
    public function test_admin_can_delete_department(): void
    {
        $dept = Department::create([
            'fiscal_year' => 2026,
            'code' => 'DEL_DEPT',
            'name' => '削除対象部門',
            'category' => 'staff',
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('admin.departments.destroy', $dept->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('comittee_departments', ['id' => $dept->id]);
    }

    /**
     * メンバーの割り当てテスト (会員ユーザーの場合)
     */
    public function test_admin_can_add_member_as_user(): void
    {
        $dept = Department::create([
            'fiscal_year' => 2026,
            'code' => 'MEMBER_DEPT',
            'name' => 'メンバー追加先部門',
            'category' => 'staff',
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('admin.departments.members.store', $dept->id), [
            'user_id' => $this->generalUser->id,
            'role_name' => '担当者',
            'is_leader' => 0,
            'sort_order' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comittee_department_members', [
            'department_id' => $dept->id,
            'user_id' => $this->generalUser->id,
            'role_name' => '担当者',
            'custom_name' => null,
        ]);
    }

    /**
     * メンバーの割り当てテスト (直接入力・非会員の場合)
     */
    public function test_admin_can_add_member_as_custom_name(): void
    {
        $dept = Department::create([
            'fiscal_year' => 2026,
            'code' => 'MEMBER_DEPT_2',
            'name' => 'メンバー追加先部門2',
            'category' => 'staff',
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('admin.departments.members.store', $dept->id), [
            'custom_name' => '外部ボランティアA',
            'role_name' => '会場整理',
            'is_leader' => 0,
            'sort_order' => 2,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comittee_department_members', [
            'department_id' => $dept->id,
            'user_id' => null,
            'custom_name' => '外部ボランティアA',
            'role_name' => '会場整理',
        ]);
    }

    /**
     * メンバーの削除テスト
     */
    public function test_admin_can_delete_member(): void
    {
        $dept = Department::create([
            'fiscal_year' => 2026,
            'code' => 'MEMBER_DEPT_3',
            'name' => 'メンバー削除部門',
            'category' => 'staff',
        ]);

        $member = DepartmentMember::create([
            'department_id' => $dept->id,
            'user_id' => $this->generalUser->id,
            'role_name' => 'リーダー',
            'is_leader' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('admin.departments.members.destroy', $member->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('comittee_department_members', ['id' => $member->id]);
    }

    /**
     * 前年度組織図データのコピーテスト
     */
    public function test_admin_can_copy_organization_from_previous_year(): void
    {
        // 前年度（2025年度）の親・子部門
        $prevParent = Department::create([
            'fiscal_year' => 2025,
            'code' => 'PREV_PARENT',
            'name' => '前年親部門',
            'category' => 'staff',
            'sort_order' => 1,
        ]);

        $prevChild = Department::create([
            'fiscal_year' => 2025,
            'code' => 'PREV_CHILD',
            'name' => '前年子部門',
            'category' => 'staff',
            'parent_id' => $prevParent->id,
            'sort_order' => 2,
        ]);

        // 前年度メンバー
        DepartmentMember::create([
            'department_id' => $prevChild->id,
            'user_id' => $this->generalUser->id,
            'role_name' => '前年役職',
            'is_leader' => true,
        ]);

        // コピーを実行 (2026年度へ)
        $response = $this->actingAs($this->adminUser)->post(route('admin.departments.copy'), [
            'fiscal_year' => 2026,
        ]);

        $response->assertRedirect();

        // 2026年度に部門が正しく複製されているか
        $newParent = Department::where('fiscal_year', 2026)->where('code', 'PREV_PARENT')->first();
        $newChild = Department::where('fiscal_year', 2026)->where('code', 'PREV_CHILD')->first();

        $this->assertNotNull($newParent);
        $this->assertNotNull($newChild);
        
        // 親子関係が維持されているか
        $this->assertEquals($newParent->id, $newChild->parent_id);

        // メンバーが複製されているか
        $this->assertDatabaseHas('comittee_department_members', [
            'department_id' => $newChild->id,
            'user_id' => $this->generalUser->id,
            'role_name' => '前年役職',
            'is_leader' => true,
        ]);
    }
}
