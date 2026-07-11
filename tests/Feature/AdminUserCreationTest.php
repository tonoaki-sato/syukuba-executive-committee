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
     * 管理者がユーザーを直接追加できることを検証
     */
    public function test_admin_can_store_user(): void
    {
        $postData = [
            'name' => '新規 メンバー',
            'name_kana' => 'しんき めんばー',
            'email' => 'newmember@example.com',
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


    }

    /**
     * 管理者が既存会員のパスキー登録用セッション（URL）を再発行できることを検証
     */
    public function test_admin_can_reissue_passkey_session(): void
    {
        // 既存のキーを作成しておく
        $credentialId = 'dummy-credential-id-for-reissue-test';
        \App\Models\WebAuthnKey::create([
            'user_id' => $this->generalUser->id,
            'credential_id' => $credentialId,
            'public_key' => 'dummy-public-key',
            'device_name' => 'Test Device',
        ]);

        $this->assertDatabaseHas('comittee_webauthn_keys', [
            'user_id' => $this->generalUser->id,
            'credential_id' => $credentialId,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.users.passkey-session', $this->generalUser));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('status', 'パスキー登録用セッションを発行しました。');
        $response->assertSessionHas('session_user_name', $this->generalUser->name);
        $response->assertSessionHas('register_url');

        // DBにセッションレコードが存在することを検証
        $this->assertDatabaseHas('comittee_passkey_sessions', [
            'user_id' => $this->generalUser->id,
        ]);

        // 既存のキーが自動的に削除されたことを検証
        $this->assertDatabaseMissing('comittee_webauthn_keys', [
            'user_id' => $this->generalUser->id,
            'credential_id' => $credentialId,
        ]);
    }

    /**
     * 一般会員はパスキー登録用セッション（URL）を再発行できないことを検証
     */
    public function test_general_user_cannot_reissue_passkey_session(): void
    {
        $response = $this->actingAs($this->generalUser)->post(route('admin.users.passkey-session', $this->admin));
        $response->assertStatus(403);
    }
}
