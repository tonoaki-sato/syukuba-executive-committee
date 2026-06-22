<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserYear;
use App\Models\PasskeySession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserCreationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $generalUser;

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

        // テスト用の一般会員を作成
        $this->generalUser = User::create([
            'name' => '一般 次郎',
            'name_kana' => 'いっぱん じろう',
            'email' => 'general@example.com',
            'password' => bcrypt('password123'),
            'profession' => '自営業',
            'line_display_name' => 'general_line',
            'roles' => ['general'],
            'status' => 'active',
        ]);
    }

    /**
     * 未ログインのゲストユーザーはアクセスできないことを検証
     */
    public function test_guest_cannot_access_creation_routes(): void
    {
        $response = $this->get(route('admin.users.create'));
        $response->assertRedirect(route('login'));

        $response = $this->post(route('admin.users.store'), []);
        $response->assertRedirect(route('login'));
    }

    /**
     * 一般会員はアクセスできないことを検証 (403)
     */
    public function test_general_user_cannot_access_creation_routes(): void
    {
        $response = $this->actingAs($this->generalUser)->get(route('admin.users.create'));
        $response->assertStatus(403);

        $response = $this->actingAs($this->generalUser)->post(route('admin.users.store'), []);
        $response->assertStatus(403);
    }

    /**
     * 管理者はユーザー追加画面にアクセスできることを検証
     */
    public function test_admin_can_access_creation_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.create'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.users_create');
        $response->assertViewHas('activeUsers');
    }

    /**
     * 管理者がユーザーを自動パスワード生成で直接追加できることを検証
     */
    public function test_admin_can_store_user_with_auto_generated_password(): void
    {
        $postData = [
            'name' => '新規 メンバー',
            'name_kana' => 'しんき めんばー',
            'email' => 'newmember@example.com',
            'auto_generate_password' => '1',
            'profession' => '会社員',
            'affiliation' => '〇〇商店街',
            'skills' => ['電気工事', '調理・衛生'],
            'referrer_id' => $this->admin->id,
            'line_display_name' => 'new_member_line',
            'roles' => ['general', 'kanji'],
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), $postData);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('status');
        $response->assertSessionHas('register_url');
        $response->assertSessionHas('session_user_name', '新規 メンバー');
        $response->assertSessionHas('temporary_password');

        // データベースにユーザーが正しく保存されたことを検証
        $this->assertDatabaseHas('comittee_users', [
            'name' => '新規 メンバー',
            'name_kana' => 'しんき めんばー',
            'email' => 'newmember@example.com',
            'profession' => '会社員',
            'affiliation' => '〇〇商店街',
            'referrer_id' => $this->admin->id,
            'line_display_name' => 'new_member_line',
            'status' => 'active',
            'approved_by' => $this->admin->id,
        ]);

        $createdUser = User::where('email', 'newmember@example.com')->first();
        $this->assertNotNull($createdUser->approved_at);
        $this->assertEquals(['general', 'kanji'], $createdUser->roles);
        $this->assertEquals(['電気工事', '調理・衛生'], $createdUser->skills);

        // 年度所属レコード (UserYear) を検証
        $this->assertDatabaseHas('comittee_user_years', [
            'user_id' => $createdUser->id,
            'fiscal_year' => session('active_fiscal_year', date('Y')),
            'status' => 'active',
        ]);
        
        $userYear = UserYear::where('user_id', $createdUser->id)->first();
        $this->assertEquals(['general', 'kanji'], $userYear->roles);

        // パスキーセッションが作成されていることを検証
        $this->assertDatabaseHas('comittee_passkey_sessions', [
            'user_id' => $createdUser->id,
        ]);
        
        $passkeySession = PasskeySession::where('user_id', $createdUser->id)->first();
        $this->assertFalse($passkeySession->isExpired());
    }

    /**
     * 管理者がユーザーを手動パスワード指定で直接追加できることを検証
     */
    public function test_admin_can_store_user_with_manual_password(): void
    {
        $postData = [
            'name' => '手動 パスユーザー',
            'name_kana' => 'しゅどう ぱすゆーざー',
            'email' => 'manualpass@example.com',
            'password' => 'securePassword123',
            'password_confirmation' => 'securePassword123',
            'profession' => '自営業',
            'line_display_name' => 'manual_pass_line',
            'roles' => ['general'],
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), $postData);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('status');
        $response->assertSessionHas('temporary_password', 'securePassword123');

        // パスワードが一致するか検証
        $createdUser = User::where('email', 'manualpass@example.com')->first();
        $this->assertTrue(\Hash::check('securePassword123', $createdUser->password));
    }

    /**
     * バリデーションエラーの検証
     */
    public function test_store_validation_fails_for_invalid_data(): void
    {
        // 1. かながひらがなではない
        $postData = [
            'name' => 'テスト 太郎',
            'name_kana' => 'TEST TAROU', // 半角英字
            'email' => 'test@example.com',
            'auto_generate_password' => '1',
            'profession' => '自営業',
            'line_display_name' => 'test_line',
            'roles' => ['general'],
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), $postData);
        $response->assertSessionHasErrors(['name_kana']);

        // 2. メールアドレスの重複
        $postData = [
            'name' => '重複 次郎',
            'name_kana' => 'じゅうふく じろう',
            'email' => 'general@example.com', // setUpで作成済みのemail
            'auto_generate_password' => '1',
            'profession' => '自営業',
            'line_display_name' => 'dup_line',
            'roles' => ['general'],
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), $postData);
        $response->assertSessionHasErrors(['email']);

        // 3. 手動パスワード時の確認不一致
        $postData = [
            'name' => '不一致 三郎',
            'name_kana' => 'ふいっち さぶろう',
            'email' => 'mismatch@example.com',
            'password' => 'password123',
            'password_confirmation' => 'mismatch_pass',
            'profession' => '自営業',
            'line_display_name' => 'mismatch_line',
            'roles' => ['general'],
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), $postData);
        $response->assertSessionHasErrors(['password']);
    }
}
