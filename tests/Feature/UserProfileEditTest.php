<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileEditTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $generalUser1;
    protected User $generalUser2;

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

        // 一般会員1を作成
        $this->generalUser1 = User::create([
            'name' => '一般 一郎',
            'name_kana' => 'いっぱん いちろう',
            'email' => 'general1@example.com',
            'password' => bcrypt('password123'),
            'profession' => '自営業',
            'line_display_name' => 'general1_line',
            'roles' => ['general'],
            'status' => 'active',
        ]);

        // 一般会員2を作成
        $this->generalUser2 = User::create([
            'name' => '一般 二郎',
            'name_kana' => 'いっぱん じろう',
            'email' => 'general2@example.com',
            'password' => bcrypt('password123'),
            'profession' => '会社員',
            'line_display_name' => 'general2_line',
            'roles' => ['general'],
            'status' => 'active',
        ]);

        // 年度所属レコードを生成
        $activeYear = session('active_fiscal_year', date('Y'));
        UserYear::create([
            'user_id' => $this->generalUser1->id,
            'fiscal_year' => $activeYear,
            'roles' => ['general'],
            'status' => 'active',
        ]);
    }

    /**
     * 未ログインユーザーのアクセス制限を検証
     */
    public function test_guest_cannot_access_edit_routes(): void
    {
        // 自己編集画面
        $response = $this->get(route('mypage.edit'));
        $response->assertRedirect(route('login'));

        $response = $this->put(route('mypage.update'), []);
        $response->assertRedirect(route('login'));

        // 管理者編集画面
        $response = $this->get(route('admin.users.edit', $this->generalUser1));
        $response->assertRedirect(route('login'));

        $response = $this->put(route('admin.users.update', $this->generalUser1), []);
        $response->assertRedirect(route('login'));
    }

    /**
     * 一般会員による自己プロフィール編集の正常系を検証
     */
    public function test_user_can_edit_own_profile(): void
    {
        $updateData = [
            'name' => '一般 一郎 新名',
            'name_kana' => 'いっぱん いちろう しんめい',
            'email' => 'general1_new@example.com', // メールアドレスの変更も許容
            'profession' => 'デザイナー',
            'affiliation' => '新しい町内会',
            'skills' => ['電気工事', '広報・デザイン'],
            'line_display_name' => 'general1_new_line',
        ];

        $response = $this->actingAs($this->generalUser1)->put(route('mypage.update'), $updateData);

        $response->assertRedirect(route('mypage'));
        $response->assertSessionHas('status', 'プロフィールを更新しました。');

        $this->assertDatabaseHas('comittee_users', [
            'id' => $this->generalUser1->id,
            'name' => '一般 一郎 新名',
            'name_kana' => 'いっぱん いちろう しんめい',
            'email' => 'general1_new@example.com',
            'profession' => 'デザイナー',
            'affiliation' => '新しい町内会',
            'line_display_name' => 'general1_new_line',
        ]);

        $this->generalUser1->refresh();
        $this->assertEquals(['電気工事', '広報・デザイン'], $this->generalUser1->skills);
    }

    /**
     * 自己編集時のメールアドレス重複エラーと自己メール許容を検証
     */
    public function test_user_email_validation_rules_on_self_edit(): void
    {
        // 1. 他人のメールアドレスへの変更は重複エラーになること
        $updateData = [
            'name' => '一般 一郎',
            'name_kana' => 'いっぱん いちろう',
            'email' => 'general2@example.com', // generalUser2 のemail
            'profession' => '自営業',
            'line_display_name' => 'general1_line',
        ];

        $response = $this->actingAs($this->generalUser1)->put(route('mypage.update'), $updateData);
        $response->assertSessionHasErrors(['email']);

        // 2. 自分の現在のメールアドレスのまま変更しない場合は、バリデーションを通過すること
        $updateData['email'] = 'general1@example.com'; // 自分の現在のemail
        $response = $this->actingAs($this->generalUser1)->put(route('mypage.update'), $updateData);
        $response->assertSessionHasNoErrors();
    }

    /**
     * 自己編集時にかながひらがなではない場合のエラーを検証
     */
    public function test_user_name_kana_validation_on_self_edit(): void
    {
        $updateData = [
            'name' => '一般 一郎',
            'name_kana' => 'IPPAN ICHIROU', // 英字
            'email' => 'general1@example.com',
            'profession' => '自営業',
            'line_display_name' => 'general1_line',
        ];

        $response = $this->actingAs($this->generalUser1)->put(route('mypage.update'), $updateData);
        $response->assertSessionHasErrors(['name_kana']);
    }

    /**
     * 一般会員は他人の編集画面にアクセスできないことを検証
     */
    public function test_general_user_cannot_access_admin_edit_routes(): void
    {
        $response = $this->actingAs($this->generalUser1)->get(route('admin.users.edit', $this->generalUser2));
        $response->assertStatus(403);

        $response = $this->actingAs($this->generalUser1)->put(route('admin.users.update', $this->generalUser2), [
            'name' => '改ざん',
            'name_kana' => 'かいざん',
            'email' => 'malicious@example.com',
            'profession' => 'クラッカー',
            'line_display_name' => 'bad_line',
            'roles' => ['general'],
            'status' => 'active',
        ]);
        $response->assertStatus(403);
    }

    /**
     * 管理者はユーザー編集画面を表示し、情報を更新できることを検証
     */
    public function test_admin_can_edit_any_user_profile(): void
    {
        // 編集画面表示
        $response = $this->actingAs($this->admin)->get(route('admin.users.edit', $this->generalUser1));
        $response->assertStatus(200);
        $response->assertViewIs('admin.users_edit');
        $response->assertViewHas('user');
        $response->assertViewHas('activeUsers');

        // 情報更新
        $updateData = [
            'name' => '一郎 編集済',
            'name_kana' => 'いちろう へんしゅうずみ',
            'email' => 'edited_ichiro@example.com',
            'profession' => '専業主夫',
            'affiliation' => '〇〇ボランティア',
            'skills' => ['調理・衛生'],
            'referrer_id' => $this->admin->id,
            'line_display_name' => 'ichiro_line_ok',
            'roles' => ['general', 'kanji'], // ロールを幹事に引き上げ
            'status' => 'suspended', // ステータスを休会に
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $this->generalUser1), $updateData);

        $response->assertRedirect(route('users.show', $this->generalUser1));
        $response->assertSessionHas('status', '会員情報を更新しました。');

        // 会員情報テーブルの更新を検証
        $this->assertDatabaseHas('comittee_users', [
            'id' => $this->generalUser1->id,
            'name' => '一郎 編集済',
            'name_kana' => 'いちろう へんしゅうずみ',
            'email' => 'edited_ichiro@example.com',
            'profession' => '専業主夫',
            'affiliation' => '〇〇ボランティア',
            'referrer_id' => $this->admin->id,
            'line_display_name' => 'ichiro_line_ok',
            'status' => 'suspended',
        ]);

        $this->generalUser1->refresh();
        $this->assertEquals(['general', 'kanji'], $this->generalUser1->roles);

        // 年度在籍テーブルのロールとステータスが連動更新されたことを検証
        $activeYear = session('active_fiscal_year', date('Y'));
        $this->assertDatabaseHas('comittee_user_years', [
            'user_id' => $this->generalUser1->id,
            'fiscal_year' => $activeYear,
            'status' => 'suspended',
        ]);

        $userYear = UserYear::where('user_id', $this->generalUser1->id)
            ->where('fiscal_year', $activeYear)
            ->first();
        $this->assertEquals(['general', 'kanji'], $userYear->roles);
    }
}
