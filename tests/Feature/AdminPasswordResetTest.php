<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $otherAdmin;
    protected User $generalUser;
    protected User $targetUser;

    protected function setUp(): void
    {
        parent::setUp();

        // テスト用の管理者を作成
        $this->admin = User::create([
            'name' => '管理者 太郎',
            'name_kana' => 'かんりしゃ たろう',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'profession' => 'エンジニア',
            'line_display_name' => 'admin_line',
            'roles' => ['admin'],
            'status' => 'active',
        ]);

        // もう一人の管理者を作成
        $this->otherAdmin = User::create([
            'name' => '管理者 次郎',
            'name_kana' => 'かんりしゃ じろう',
            'email' => 'other_admin@example.com',
            'password' => bcrypt('password123'),
            'profession' => 'エンジニア',
            'line_display_name' => 'other_admin_line',
            'roles' => ['admin'],
            'status' => 'active',
        ]);

        // テスト用の一般会員を作成
        $this->generalUser = User::create([
            'name' => '一般 三郎',
            'name_kana' => 'いっぱん さぶろう',
            'email' => 'general@example.com',
            'password' => bcrypt('password123'),
            'profession' => '自営業',
            'line_display_name' => 'general_line',
            'roles' => ['general'],
            'status' => 'active',
        ]);

        // リセット対象のユーザーを作成
        $this->targetUser = User::create([
            'name' => '対象 四郎',
            'name_kana' => 'たいしょう しろう',
            'email' => 'target@example.com',
            'password' => bcrypt('oldPassword123'),
            'profession' => '自営業',
            'line_display_name' => 'target_line',
            'roles' => ['general'],
            'status' => 'active',
        ]);
    }

    /**
     * 未ログインのゲストユーザーはパスワードを変更できないことを検証
     */
    public function test_guest_cannot_update_user_password(): void
    {
        $response = $this->post(route('admin.users.password-update', $this->targetUser), [
            'password' => 'newPassword123',
            'password_confirmation' => 'newPassword123',
        ]);

        $response->assertRedirect(route('login'));
        
        // パスワードが変更されていないことを確認
        $this->targetUser->refresh();
        $this->assertTrue(\Hash::check('oldPassword123', $this->targetUser->password));
    }

    /**
     * 一般会員は他ユーザーのパスワードを変更できないことを検証 (403)
     */
    public function test_general_user_cannot_update_other_user_password(): void
    {
        $response = $this->actingAs($this->generalUser)->post(route('admin.users.password-update', $this->targetUser), [
            'password' => 'newPassword123',
            'password_confirmation' => 'newPassword123',
        ]);

        $response->assertStatus(403);

        // パスワードが変更されていないことを確認
        $this->targetUser->refresh();
        $this->assertTrue(\Hash::check('oldPassword123', $this->targetUser->password));
    }

    /**
     * 管理者は他ユーザーのパスワードを強制リセットできることを検証
     */
    public function test_admin_can_update_other_user_password(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.password-update', $this->targetUser), [
            'password' => 'newSecurePassword123',
            'password_confirmation' => 'newSecurePassword123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'ユーザーのパスワードを正常に更新しました。');

        // パスワードが新しいものに変更されていることを確認
        $this->targetUser->refresh();
        $this->assertTrue(\Hash::check('newSecurePassword123', $this->targetUser->password));
        $this->assertFalse(\Hash::check('oldPassword123', $this->targetUser->password));
    }

    /**
     * 管理者が自分自身のパスワードをこの管理者ルートから更新しようとするとブロックされることを検証
     */
    public function test_admin_cannot_update_own_password_via_admin_route(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.password-update', $this->admin), [
            'password' => 'newSecurePassword123',
            'password_confirmation' => 'newSecurePassword123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', '自分自身のパスワードはマイページから変更してください。');

        // パスワードが変更されていないことを確認
        $this->admin->refresh();
        $this->assertTrue(\Hash::check('password123', $this->admin->password));
    }

    /**
     * バリデーションエラーが正しく返却されることを検証
     */
    public function test_update_validation_fails_for_invalid_data(): void
    {
        // 1. 確認用不一致
        $response = $this->actingAs($this->admin)->post(route('admin.users.password-update', $this->targetUser), [
            'password' => 'newSecurePassword123',
            'password_confirmation' => 'mismatched_pass',
        ]);

        $response->assertSessionHasErrors(['password']);

        // 2. 文字数不足
        $response = $this->actingAs($this->admin)->post(route('admin.users.password-update', $this->targetUser), [
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors(['password']);
    }
}
