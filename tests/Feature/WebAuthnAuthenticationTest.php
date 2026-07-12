<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WebAuthnKey;
use App\Models\PasskeySession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;
use ReflectionClass;
use App\Http\Controllers\WebAuthnController;

class WebAuthnAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected User $temporaryUser;
    protected User $suspendedUser;
    protected $webAuthnMock;

    protected function setUp(): void
    {
        parent::setUp();

        // 共通テストユーザーの作成
        $this->user = User::create([
            'name' => '一般 会員',
            'name_kana' => 'いっぱん かいいん',
            'email' => 'member@example.com',
            'profession' => '自営業',
            'line_display_name' => 'member_line',
            'roles' => ['general'],
            'status' => 'active',
        ]);

        $this->admin = User::create([
            'name' => '管理者 太郎',
            'name_kana' => 'かんりしゃ たろう',
            'email' => 'admin@example.com',
            'profession' => 'エンジニア',
            'line_display_name' => 'admin_line',
            'roles' => ['admin', 'general'],
            'status' => 'active',
        ]);

        $this->temporaryUser = User::create([
            'name' => '仮 会員',
            'name_kana' => 'かり かいいん',
            'email' => 'temporary@example.com',
            'profession' => '会社員',
            'line_display_name' => 'temp_line',
            'roles' => ['general'],
            'status' => 'temporary',
        ]);

        $this->suspendedUser = User::create([
            'name' => '休会 会員',
            'name_kana' => 'きゅうかい かいいん',
            'email' => 'suspended@example.com',
            'profession' => '無職',
            'line_display_name' => 'suspended_line',
            'roles' => ['general'],
            'status' => 'suspended',
        ]);

        // WebAuthnController の webAuthn プロパティをモックに差し替える
        $this->setupWebAuthnMock();
    }

    protected function setupWebAuthnMock(): void
    {
        $controller = new WebAuthnController();
        $this->webAuthnMock = Mockery::mock(\lbuchs\WebAuthn\WebAuthn::class);

        $reflector = new ReflectionClass($controller);
        $property = $reflector->getProperty('webAuthn');
        $property->setAccessible(true);
        $property->setValue($controller, $this->webAuthnMock);

        $this->app->instance(WebAuthnController::class, $controller);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Helper to encode to Base64URL
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // --- ログインチャレンジ生成テスト ---

    public function test_login_challenge_generation_without_email(): void
    {
        $this->webAuthnMock->shouldReceive('getGetArgs')
            ->once()
            ->with([], 60, true, true)
            ->andReturn(['dummy_args' => true]);

        $this->webAuthnMock->shouldReceive('getChallenge')
            ->once()
            ->andReturn('dummy_challenge_123');

        $response = $this->postJson('/webauthn/login/challenge');

        $response->assertStatus(200)
            ->assertJson(['dummy_args' => true]);

        $this->assertEquals($this->base64UrlEncode('dummy_challenge_123'), session('webauthn_login_challenge'));
    }

    public function test_login_challenge_generation_with_email(): void
    {
        // ユーザーにパスキーを登録
        WebAuthnKey::create([
            'user_id' => $this->user->id,
            'credential_id' => 'dummy_cred_id_base64url',
            'public_key' => 'dummy_pem_key',
            'device_name' => 'My Phone',
            'aaguid' => 'dummy_aaguid',
            'counter' => 0,
        ]);

        $this->webAuthnMock->shouldReceive('getGetArgs')
            ->once()
            ->with(Mockery::on(function ($allowedCredentialIds) {
                return count($allowedCredentialIds) === 1 && !empty($allowedCredentialIds[0]);
            }), 60, true, true)
            ->andReturn(['dummy_args' => true]);

        $this->webAuthnMock->shouldReceive('getChallenge')
            ->once()
            ->andReturn('dummy_challenge_123');

        $response = $this->postJson('/webauthn/login/challenge', [
            'email' => 'member@example.com'
        ]);

        $response->assertStatus(200)
            ->assertJson(['dummy_args' => true]);
    }

    // --- ログイン検証テスト ---

    public function test_login_verification_success(): void
    {
        // パスキーをDBに登録
        WebAuthnKey::create([
            'user_id' => $this->user->id,
            'credential_id' => 'dummy_cred_id_base64url',
            'public_key' => 'dummy_pem_key',
            'device_name' => 'My Phone',
            'aaguid' => 'dummy_aaguid',
            'counter' => 10,
        ]);

        session(['webauthn_login_challenge' => $this->base64UrlEncode('active_challenge_123')]);

        $this->webAuthnMock->shouldReceive('processGet')
            ->once()
            ->with(
                'decoded_client_data',
                'decoded_auth_data',
                'decoded_sig',
                'dummy_pem_key',        // 第4引数: 公開鍵PEM
                'active_challenge_123', // 第5引数: チャレンジ
                10,                     // 第6引数: カウンター
                false                   // 第7引数: ユーザー検証 (デバイス互換性のために緩和)
            )
            ->andReturn(true);

        $this->webAuthnMock->shouldReceive('getSignatureCounter')
            ->once()
            ->andReturn(11);

        $postData = [
            'id' => 'dummy_cred_id_base64url',
            'clientDataJSON' => $this->base64UrlEncode('decoded_client_data'),
            'authenticatorData' => $this->base64UrlEncode('decoded_auth_data'),
            'signature' => $this->base64UrlEncode('decoded_sig'),
        ];

        $response = $this->postJson('/webauthn/login/verify', $postData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'redirect' => route('dashboard')
            ]);

        $this->assertTrue(\Auth::check());
        $this->assertEquals($this->user->id, \Auth::id());

        // カウンターと最終使用日時が更新されたことを検証
        $key = WebAuthnKey::where('credential_id', 'dummy_cred_id_base64url')->first();
        $this->assertEquals(11, $key->counter);
        $this->assertNotNull($key->last_used_at);
    }

    public function test_login_verification_invalid_challenge(): void
    {
        // セッションにチャレンジが存在しない
        $response = $this->postJson('/webauthn/login/verify', [
            'id' => 'dummy_cred_id_base64url',
            'clientDataJSON' => 'abc',
            'authenticatorData' => 'def',
            'signature' => 'ghi',
        ]);

        $response->assertStatus(400)
            ->assertJson(['error' => 'セッション有効期限切れ、または不正なチャレンジです。']);
    }

    public function test_login_verification_unregistered_key(): void
    {
        session(['webauthn_login_challenge' => 'active_challenge_123']);

        // DB未登録のcredential_idでアクセス
        $response = $this->postJson('/webauthn/login/verify', [
            'id' => 'unregistered_key_base64url',
            'clientDataJSON' => $this->base64UrlEncode('decoded_client_data'),
            'authenticatorData' => $this->base64UrlEncode('decoded_auth_data'),
            'signature' => $this->base64UrlEncode('decoded_sig'),
        ]);

        $response->assertStatus(400)
            ->assertJson(['error' => '登録されていないパスキーです。']);
    }

    public function test_login_verification_temporary_user_lockout(): void
    {
        // 仮会員にパスキーを登録
        WebAuthnKey::create([
            'user_id' => $this->temporaryUser->id,
            'credential_id' => 'temp_cred_id',
            'public_key' => 'dummy_pem_key',
            'device_name' => 'My Phone',
            'aaguid' => 'dummy_aaguid',
            'counter' => 0,
        ]);

        session(['webauthn_login_challenge' => $this->base64UrlEncode('active_challenge_123')]);

        $this->webAuthnMock->shouldReceive('processGet')->andReturn(true);
        $this->webAuthnMock->shouldReceive('getSignatureCounter')->andReturn(1);

        $postData = [
            'id' => 'temp_cred_id',
            'clientDataJSON' => $this->base64UrlEncode('decoded_client_data'),
            'authenticatorData' => $this->base64UrlEncode('decoded_auth_data'),
            'signature' => $this->base64UrlEncode('decoded_sig'),
        ];

        $response = $this->postJson('/webauthn/login/verify', $postData);

        // 仮会員は403を返す仕様
        $response->assertStatus(403)
            ->assertJson([
                'status' => 'temporary',
                'message' => 'アカウントはシステム管理者の承認待ちです。承認されるまでログインできません。'
            ]);

        $this->assertFalse(\Auth::check());
    }

    public function test_login_verification_suspended_user_lockout(): void
    {
        // 休会ユーザーにパスキーを登録
        WebAuthnKey::create([
            'user_id' => $this->suspendedUser->id,
            'credential_id' => 'suspended_cred_id',
            'public_key' => 'dummy_pem_key',
            'device_name' => 'My Phone',
            'aaguid' => 'dummy_aaguid',
            'counter' => 0,
        ]);

        session(['webauthn_login_challenge' => $this->base64UrlEncode('active_challenge_123')]);

        $this->webAuthnMock->shouldReceive('processGet')->andReturn(true);
        $this->webAuthnMock->shouldReceive('getSignatureCounter')->andReturn(1);

        $postData = [
            'id' => 'suspended_cred_id',
            'clientDataJSON' => $this->base64UrlEncode('decoded_client_data'),
            'authenticatorData' => $this->base64UrlEncode('decoded_auth_data'),
            'signature' => $this->base64UrlEncode('decoded_sig'),
        ];

        $response = $this->postJson('/webauthn/login/verify', $postData);

        $response->assertStatus(403)
            ->assertJson(['error' => 'このアカウントは現在ご利用いただけません。']);

        $this->assertFalse(\Auth::check());
    }

    public function test_login_check_passkey_exists(): void
    {
        // 1. パスキーなし
        $response = $this->postJson('/webauthn/login/check', ['email' => 'member@example.com']);
        $response->assertStatus(200)
            ->assertJson([
                'has_passkey' => false,
                'status' => 'active'
            ]);

        // 2. パスキーあり
        WebAuthnKey::create([
            'user_id' => $this->user->id,
            'credential_id' => 'dummy_cred_id',
            'public_key' => 'dummy_pem_key',
            'device_name' => 'My Phone',
            'aaguid' => 'dummy_aaguid',
            'counter' => 0,
        ]);

        $response = $this->postJson('/webauthn/login/check', ['email' => 'member@example.com']);
        $response->assertStatus(200)
            ->assertJson([
                'has_passkey' => true,
                'status' => 'active'
            ]);
    }

    // --- パスキー登録チャレンジ生成テスト ---

    public function test_register_challenge_generation_for_logged_in_user(): void
    {
        $this->webAuthnMock->shouldReceive('getCreateArgs')
            ->once()
            ->with((string)$this->admin->id, $this->admin->email, $this->admin->name, 60, true, false)
            ->andReturn(['dummy_register_args' => true]);

        $this->webAuthnMock->shouldReceive('getChallenge')
            ->once()
            ->andReturn('register_challenge_123');

        // ログイン中の管理者自身が呼び出す
        $response = $this->actingAs($this->admin)->postJson('/webauthn/register/challenge');

        $response->assertStatus(200)
            ->assertJson(['dummy_register_args' => true]);

        $this->assertEquals($this->base64UrlEncode('register_challenge_123'), session('webauthn_register_challenge'));
        $this->assertNull(session('webauthn_register_token'));
    }

    public function test_register_challenge_generation_with_valid_token(): void
    {
        // パスキーセッションを発行
        $session = PasskeySession::create([
            'user_id' => $this->user->id,
            'token' => 'valid_token_xyz',
            'expires_at' => now()->addHours(24),
        ]);

        $this->webAuthnMock->shouldReceive('getCreateArgs')
            ->once()
            ->with((string)$this->user->id, $this->user->email, $this->user->name, 60, true, false)
            ->andReturn(['dummy_register_args' => true]);

        $this->webAuthnMock->shouldReceive('getChallenge')
            ->once()
            ->andReturn('register_challenge_123');

        // ログイン状態でトークンを指定して呼び出す
        $response = $this->actingAs($this->user)->postJson('/webauthn/register/challenge', [
            'token' => 'valid_token_xyz'
        ]);

        $response->assertStatus(200)
            ->assertJson(['dummy_register_args' => true]);

        $this->assertEquals($this->base64UrlEncode('register_challenge_123'), session('webauthn_register_challenge'));
        $this->assertEquals('valid_token_xyz', session('webauthn_register_token'));
    }

    public function test_register_challenge_generation_with_invalid_token(): void
    {
        // ログイン状態で存在しないトークン
        $response = $this->actingAs($this->user)->postJson('/webauthn/register/challenge', [
            'token' => 'invalid_token_xyz'
        ]);

        $response->assertStatus(400)
            ->assertJson(['error' => '登録期限が切れているか、無効なURLです。']);

        // 期限切れトークン
        $expiredSession = PasskeySession::create([
            'user_id' => $this->user->id,
            'token' => 'expired_token_123',
            'expires_at' => now()->subHour(),
        ]);

        $response = $this->actingAs($this->user)->postJson('/webauthn/register/challenge', [
            'token' => 'expired_token_123'
        ]);

        $response->assertStatus(400)
            ->assertJson(['error' => '登録期限が切れているか、無効なURLです。']);
    }

    // --- パスキー登録検証テスト ---

    public function test_register_verification_success(): void
    {
        // 事前に古い鍵を登録しておく
        $oldCredentialId = 'old-dummy-credential-id';
        WebAuthnKey::create([
            'user_id' => $this->user->id,
            'credential_id' => $oldCredentialId,
            'public_key' => 'old-dummy-public-key',
            'device_name' => 'Old Device',
        ]);

        $session = PasskeySession::create([
            'user_id' => $this->user->id,
            'token' => 'valid_token_xyz',
            'expires_at' => now()->addHours(24),
        ]);

        session([
            'webauthn_register_challenge' => $this->base64UrlEncode('register_challenge_123'),
            'webauthn_register_token' => 'valid_token_xyz'
        ]);

        $dummyRegisterData = new \stdClass();
        $dummyRegisterData->credentialId = 'cred_id_binary_xyz';
        $dummyRegisterData->credentialPublicKey = 'public_key_pem_xyz';
        $dummyRegisterData->AAGUID = 'aaguid_xyz';
        $dummyRegisterData->signatureCounter = 5;

        $this->webAuthnMock->shouldReceive('processCreate')
            ->once()
            ->with(
                'decoded_client_data',
                'decoded_attestation_object',
                'register_challenge_123',
                true,
                true,
                false
            )
            ->andReturn($dummyRegisterData);

        $postData = [
            'clientDataJSON' => $this->base64UrlEncode('decoded_client_data'),
            'attestationObject' => $this->base64UrlEncode('decoded_attestation_object'),
            'device_name' => 'テスト仮想デバイス',
        ];

        // ログイン状態でポストする
        $response = $this->actingAs($this->user)->postJson('/webauthn/register/verify', $postData);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // DBに新しいWebAuthnKeyが登録されたことを検証
        $expectedCredId = $this->base64UrlEncode('cred_id_binary_xyz');
        $this->assertDatabaseHas('comittee_webauthn_keys', [
            'user_id' => $this->user->id,
            'credential_id' => $expectedCredId,
            'public_key' => 'public_key_pem_xyz',
            'device_name' => 'テスト仮想デバイス',
            'aaguid' => 'aaguid_xyz',
            'counter' => 5,
        ]);

        // 古い鍵が削除されたことを検証
        $this->assertDatabaseMissing('comittee_webauthn_keys', [
            'user_id' => $this->user->id,
            'credential_id' => $oldCredentialId,
        ]);

        // トークンセッションが削除されたことを検証
        $this->assertDatabaseMissing('comittee_passkey_sessions', [
            'token' => 'valid_token_xyz'
        ]);

        // セッションからチャレンジやトークンがクリアされたことを検証
        $this->assertNull(session('webauthn_register_challenge'));
        $this->assertNull(session('webauthn_register_token'));
    }

    public function test_register_verification_duplicate_credential(): void
    {
        // 既に同じcredential_idを持つキーが存在する状態
        $credIdBinary = 'cred_id_binary_xyz';
        $credIdBase64Url = $this->base64UrlEncode($credIdBinary);

        WebAuthnKey::create([
            'user_id' => $this->admin->id, // 別のユーザーが登録済み
            'credential_id' => $credIdBase64Url,
            'public_key' => 'some_pem',
            'device_name' => 'Existing Device',
            'aaguid' => 'aaguid',
            'counter' => 0,
        ]);

        session([
            'webauthn_register_challenge' => $this->base64UrlEncode('register_challenge_123'),
            'webauthn_register_token' => null // ログイン状態での追加登録
        ]);

        $dummyRegisterData = new \stdClass();
        $dummyRegisterData->credentialId = $credIdBinary;
        $dummyRegisterData->credentialPublicKey = 'public_key_pem_xyz';
        $dummyRegisterData->AAGUID = 'aaguid_xyz';
        $dummyRegisterData->signatureCounter = 5;

        $this->webAuthnMock->shouldReceive('processCreate')->andReturn($dummyRegisterData);

        $postData = [
            'clientDataJSON' => $this->base64UrlEncode('decoded_client_data'),
            'attestationObject' => $this->base64UrlEncode('decoded_attestation_object'),
            'device_name' => '新規デバイス',
        ];

        // ログイン中の一般ユーザーが同じデバイスを登録しようとする
        $response = $this->actingAs($this->user)->postJson('/webauthn/register/verify', $postData);

        // 重複エラーが発生することを検証
        $response->assertStatus(400)
            ->assertJson(['error' => 'このデバイスは既に登録されています。']);
    }

    public function test_passkey_troubleshooting_page_accessible(): void
    {
        $response = $this->get('/passkey/troubleshooting');

        $response->assertStatus(200)
            ->assertSee('パスキーのトラブルシューティング')
            ->assertSee('重複して表示される場合の対処方法');
    }

    /**
     * Artisanコマンド passkey:issue-url の動作検証
     */
    public function test_artisan_passkey_issue_url_success(): void
    {
        // 既存のキーを作成しておく
        $credentialId = 'dummy-credential-id-for-artisan-test';
        WebAuthnKey::create([
            'user_id' => $this->user->id,
            'credential_id' => $credentialId,
            'public_key' => 'dummy-public-key',
            'device_name' => 'Test Device',
        ]);

        $this->assertDatabaseHas('comittee_webauthn_keys', [
            'user_id' => $this->user->id,
            'credential_id' => $credentialId,
        ]);

        // コマンドを実行
        $this->artisan('passkey:issue-url', ['--email' => 'member@example.com'])
            ->expectsOutputToContain('パスキー登録用URLの発行に成功しました')
            ->assertExitCode(0);

        // 既存のキーが削除されたことを検証
        $this->assertDatabaseMissing('comittee_webauthn_keys', [
            'user_id' => $this->user->id,
            'credential_id' => $credentialId,
        ]);

        // ワンタイムセッションが発行されたことを検証
        $this->assertDatabaseHas('comittee_passkey_sessions', [
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * Artisanコマンド passkey:issue-url の異常系検証（ユーザー不在）
     */
    public function test_artisan_passkey_issue_url_fail_user_not_found(): void
    {
        $this->artisan('passkey:issue-url', ['--email' => 'notfound@example.com'])
            ->expectsOutputToContain('指定されたメールアドレスのユーザーが存在しません')
            ->assertExitCode(1);
    }
}
