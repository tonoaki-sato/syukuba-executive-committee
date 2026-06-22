<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserYear;
use App\Models\GozaichiEvent;
use App\Models\GozaichiApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GozaichiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $kanji;
    protected User $general;
    protected GozaichiEvent $event;

    protected function setUp(): void
    {
        parent::setUp();

        // 共通のアクティブ年度設定
        session(['active_fiscal_year' => 2026]);

        // テストユーザー作成
        $this->admin = User::create([
            'name' => '管理者',
            'name_kana' => 'かんりしゃ',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'profession' => '行政',
            'line_display_name' => 'admin_line',
            'roles' => ['admin'],
            'status' => 'active',
        ]);

        $this->kanji = User::create([
            'name' => '幹事',
            'name_kana' => 'かんじ',
            'email' => 'kanji@example.com',
            'password' => bcrypt('password123'),
            'profession' => '自営業',
            'line_display_name' => 'kanji_line',
            'roles' => ['kanji'],
            'status' => 'active',
        ]);

        $this->general = User::create([
            'name' => '一般',
            'name_kana' => 'いっぱん',
            'email' => 'general@example.com',
            'password' => bcrypt('password123'),
            'profession' => '会社員',
            'line_display_name' => 'general_line',
            'roles' => ['general'],
            'status' => 'active',
        ]);

        // 年度所属レコード生成
        UserYear::create([
            'user_id' => $this->kanji->id,
            'fiscal_year' => 2026,
            'roles' => ['kanji'],
            'status' => 'active',
        ]);

        UserYear::create([
            'user_id' => $this->general->id,
            'fiscal_year' => 2026,
            'roles' => ['general'],
            'status' => 'active',
        ]);

        // イベント年度データ初期生成
        $this->event = GozaichiEvent::create([
            'fiscal_year' => 2026,
            'recruitment_status' => 'open',
            'is_active' => true,
        ]);

        // デフォルト料金マスタ登録
        $defaults = [
            'member_1st' => 2000,
            'member_general_2nd' => 3000,
            'member_A_2nd' => 4000,
            'member_B_2nd' => 5000,
            'general_1st' => 6000,
            'general_A_1st' => 8000,
            'general_B_1st' => 10000,
            'general_2nd' => 6000,
            'general_A_2nd' => 8000,
            'general_B_2nd' => 10000,
            'tent' => 4500,
            'weight' => 500,
            'desk' => 2500,
            'chair' => 500,
            'trash_45' => 500,
            'trash_70' => 700,
        ];
        
        foreach ($defaults as $key => $val) {
            $this->event->feeSettings()->create([
                'fee_key' => $key,
                'fee_value' => $val,
            ]);
        }
    }

    /**
     * アクセス制御のテスト
     */
    public function test_access_control(): void
    {
        // 1. 未ログイン: ログイン画面へリダイレクト
        $response = $this->get(route('goza.index'));
        $response->assertRedirect(route('login'));

        // 2. 一般会員: 403 Forbidden
        $response = $this->actingAs($this->general)->get(route('goza.index'));
        $response->assertStatus(403);

        // 3. 幹事: 200 OK
        $response = $this->actingAs($this->kanji)->get(route('goza.index'));
        $response->assertStatus(200);

        // 4. 管理者: 200 OK
        $response = $this->actingAs($this->admin)->get(route('goza.index'));
        $response->assertStatus(200);
    }

    /**
     * 料金自動計算ロジックのテスト
     */
    public function test_fee_calculation(): void
    {
        // 加盟店、2区画希望（1区画目B、2区画目A）、テント1張、ウエイト4個、ゴミ袋45Lを3枚
        $app = GozaichiApplication::create([
            'event_id' => $this->event->id,
            'shop_name' => 'テスト加盟店',
            'exhibitor_name' => '山田二郎',
            'is_member' => true,
            'section_count' => 2,
            'first_section_type' => 'B',
            'subsequent_section_type' => 'A',
            'rentals' => [
                'tent' => 1,
                'weight' => 4,
                'desk' => 0,
                'chair' => 0,
                'trash_bag_45' => 3,
                'trash_bag_70' => 0,
            ],
            'status' => 'submitted',
        ]);

        $app->calculateFees();

        // 出店料: 加盟店1区画目 2,000円 ＋ 2区画目(A) 4,000円 ＝ 6,000円
        $this->assertEquals(6000, $app->exhibition_fee);

        // 備品料: テント1(4,500) ＋ ウエイト4(500*4=2,000) ＝ 6,500円
        $this->assertEquals(6500, $app->equipment_fee);

        // ゴミ袋料: 加盟店なので無料枠なし 45L*3(500*3=1500) = 1500円
        $this->assertEquals(1500, $app->trash_bag_fee);

        // 総合計: 6,000 + 6,500 + 1,500 ＝ 14,000円
        $this->assertEquals(14000, $app->total_fee);

        // 一般（非加盟）出店者、1区画（一般）、ゴミ袋45Lを3枚
        $app2 = GozaichiApplication::create([
            'event_id' => $this->event->id,
            'shop_name' => 'テスト一般店',
            'exhibitor_name' => '佐藤三郎',
            'is_member' => false,
            'section_count' => 1,
            'first_section_type' => 'general',
            'rentals' => [
                'tent' => 0,
                'weight' => 0,
                'desk' => 0,
                'chair' => 0,
                'trash_bag_45' => 3,
                'trash_bag_70' => 0,
            ],
            'status' => 'submitted',
        ]);

        $app2->calculateFees();

        // 出店料: 一般1区画目(general) 6,000円
        $this->assertEquals(6000, $app2->exhibition_fee);

        // ゴミ袋料: 一般は45L×2枚無料なので、有料分は1枚＝500円
        $this->assertEquals(500, $app2->trash_bag_fee);

        // 総合計: 6,000 + 0 + 500 = 6,500円
        $this->assertEquals(6500, $app2->total_fee);
    }

    /**
     * バリデーション制限（発電機、重複区画）のテスト
     */
    public function test_validation_restrictions(): void
    {
        $appData = [
            'shop_name' => 'コンロ焼きそば',
            'exhibitor_name' => '鈴木四郎',
            'is_member' => '0',
            'section_count' => '1',
            'first_section_type' => 'B',
            'has_fire' => '1',
            'fire_equipment' => 'ガスコンロ、発電機', // 発電機を含む
            'fire_equipment_count' => '1',
            'fire_fuel' => 'LPガス',
            'has_food' => '1',
            'has_food_pledge' => '1',
        ];

        // 発電機制限によるバリデーションエラー
        $response = $this->actingAs($this->kanji)->post(route('goza.applications.store'), $appData);
        $response->assertSessionHasErrors(['fire_equipment']);

        // 正常なデータで登録
        $appData['fire_equipment'] = 'ガスコンロ';
        $response = $this->actingAs($this->kanji)->post(route('goza.applications.store'), $appData);
        $response->assertRedirect(route('goza.applications.index'));

        // 重複区画コードチェック
        $app1 = GozaichiApplication::create([
            'event_id' => $this->event->id,
            'shop_name' => '先行配置店',
            'exhibitor_name' => '佐藤',
            'is_member' => false,
            'first_section_type' => 'general',
            'status' => 'accepted',
            'spot_code' => 'A15',
        ]);

        $app2 = GozaichiApplication::create([
            'event_id' => $this->event->id,
            'shop_name' => '重複希望店',
            'exhibitor_name' => '鈴木',
            'is_member' => false,
            'first_section_type' => 'general',
            'status' => 'accepted',
            'spot_code' => null,
        ]);

        // A15に配置しようとして重複エラー
        $response = $this->actingAs($this->kanji)->put(route('goza.spots.update', $app2->id), [
            'spot_code' => 'A15',
        ]);
        $response->assertSessionHasErrors(['spot_code']);
    }

    /**
     * 新年度引き継ぎ処理のテスト
     */
    public function test_fiscal_year_transition(): void
    {
        // 移行先の2027年向け引き継ぎ
        $response = $this->actingAs($this->admin)->post(route('admin.users.transition-execute'), [
            'target_year' => 2027,
            'users' => [$this->kanji->id],
            'roles' => [$this->kanji->id => ['kanji']],
            'copy_gozaichi' => '1', // ござ市引き継ぎON
        ]);

        $response->assertRedirect(route('admin.users.index'));

        // 2027年用のイベントと料金マスタがコピーされていることを検証
        $targetEvent = GozaichiEvent::where('fiscal_year', 2027)->first();
        $this->assertNotNull($targetEvent);

        $sourceFees = $this->event->fees;
        $targetFees = $targetEvent->fees;

        $this->assertEquals($sourceFees['member_1st'], $targetFees['member_1st']);
        $this->assertEquals($sourceFees['general_B_1st'], $targetFees['general_B_1st']);
        $this->assertEquals($sourceFees['trash_45'], $targetFees['trash_45']);
    }
}
