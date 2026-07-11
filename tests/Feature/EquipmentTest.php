<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\EquipmentLoan;
use App\Models\EquipmentMaintenanceLog;
use App\Models\EquipmentStock;
use App\Models\StorageLocation;
use App\Models\User;
use App\Models\UserYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EquipmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $kanjiUser;
    protected User $managerUser;
    protected User $generalUser;
    protected User $temporaryUser;
    protected int $activeYear;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activeYear = 2026;
        session(['active_fiscal_year' => $this->activeYear]);

        // 1. システム管理者
        $this->adminUser = User::create([
            'name' => '管理者', 'name_kana' => 'かんりしゃ', 'email' => 'admin@example.com',
            'profession' => 'IT', 'line_display_name' => 'admin_line',
            'roles' => ['admin', 'general'], 'status' => 'active',
        ]);
        UserYear::create([
            'user_id' => $this->adminUser->id, 'fiscal_year' => $this->activeYear,
            'roles' => ['admin', 'general'], 'status' => 'active',
        ]);

        // 2. 幹事
        $this->kanjiUser = User::create([
            'name' => '幹事', 'name_kana' => 'かんじ', 'email' => 'kanji@example.com',
            'profession' => '店主', 'line_display_name' => 'kanji_line',
            'roles' => ['kanji', 'general'], 'status' => 'active',
        ]);
        UserYear::create([
            'user_id' => $this->kanjiUser->id, 'fiscal_year' => $this->activeYear,
            'roles' => ['kanji', 'general'], 'status' => 'active',
        ]);

        // 3. 新設：備品管理者
        $this->managerUser = User::create([
            'name' => '備品管理', 'name_kana' => 'びひんかんり', 'email' => 'manager@example.com',
            'profession' => 'イベント', 'line_display_name' => 'manager_line',
            'roles' => ['equipment_manager', 'general'], 'status' => 'active',
        ]);
        UserYear::create([
            'user_id' => $this->managerUser->id, 'fiscal_year' => $this->activeYear,
            'roles' => ['equipment_manager', 'general'], 'status' => 'active',
        ]);

        // 4. 一般会員
        $this->generalUser = User::create([
            'name' => '一般', 'name_kana' => 'いっぱん', 'email' => 'general@example.com',
            'profession' => '一般', 'line_display_name' => 'general_line',
            'roles' => ['general'], 'status' => 'active',
        ]);
        UserYear::create([
            'user_id' => $this->generalUser->id, 'fiscal_year' => $this->activeYear,
            'roles' => ['general'], 'status' => 'active',
        ]);

        // 5. 仮登録
        $this->temporaryUser = User::create([
            'name' => '仮登録', 'name_kana' => 'かりとうろく', 'email' => 'temporary@example.com',
            'profession' => '一般', 'line_display_name' => 'temp_line',
            'roles' => ['general'], 'status' => 'temporary',
        ]);
    }

    /**
     * 未ログインおよび仮登録ユーザーのアクセス制限
     */
    public function test_guest_and_temporary_cannot_access_equipment_index(): void
    {
        // ゲスト
        $response = $this->get(route('equipment.index'));
        $response->assertRedirect(route('login'));

        // 仮登録
        $response = $this->actingAs($this->temporaryUser)->get(route('equipment.index'));
        $response->assertRedirect(route('register.pending'));
    }

    /**
     * 一般ユーザーは閲覧のみ可能、且つ金額が秘匿されていることの検証
     */
    public function test_general_user_can_view_index_but_prices_are_hidden(): void
    {
        // テスト用データ登録
        $eq = Equipment::create([
            'fiscal_year' => 2026,
            'ownership_type' => 'rental',
            'name' => 'パイプテント',
            'specifications' => '1.5k x 2k',
            'quantity' => 10,
            'unit' => '張',
            'unit_price' => 8000,
            'category' => '什器・テント',
        ]);

        $response = $this->actingAs($this->generalUser)->get(route('equipment.index'));
        $response->assertStatus(200);

        // 品名や規格は表示されること
        $response->assertSee('パイプテント');
        $response->assertSee('1.5k x 2k');

        // 金額情報は HTML に含まれないことの検証
        $response->assertDontSee('8,000'); // 単価 8,000円 の表記
        $response->assertDontSee('80,000'); // 合計金額 10×8000=80,000円 の表記
        $response->assertDontSee('外部レンタル税込総請求額');

        // ビュー変数内の equipments からも unit_price が隠蔽されているか検証
        $viewEquipments = $response->original->getData()['equipments'];
        $this->assertNull($viewEquipments->first()->unit_price);
    }

    /**
     * 備品管理者、幹事、管理者は金額情報を表示し、各種登録ダイアログにアクセスできること
     */
    public function test_authorized_users_can_see_prices_and_forms(): void
    {
        $eq = Equipment::create([
            'fiscal_year' => 2026,
            'ownership_type' => 'rental',
            'name' => 'パイプテント',
            'specifications' => '1.5k x 2k',
            'quantity' => 10,
            'unit' => '張',
            'unit_price' => 8000,
            'category' => '什器・テント',
        ]);

        foreach ([$this->adminUser, $this->kanjiUser, $this->managerUser] as $user) {
            $response = $this->actingAs($user)->get(route('equipment.index'));
            $response->assertStatus(200);

            // 金額情報が表示されること
            $response->assertSee('8,000');
            $response->assertSee('80,000');
            $response->assertSee('外部レンタル税込総請求額');

            // CUD操作フォーム・ボタンが存在すること
            $response->assertSee('備品マスタ新規登録');
            $response->assertSee('保管場所・倉庫の新規登録');
        }
    }

    /**
     * 一般会員による CUD 操作が 403 Forbidden になることの検証
     */
    public function test_general_user_cannot_perform_cud_operations(): void
    {
        $this->actingAs($this->generalUser);

        // 1. 備品新規追加の拒否
        $response = $this->post(route('equipment.master.store'), [
            'ownership_type' => 'owned',
            'name' => '会議用机',
            'quantity' => 5,
            'unit' => '台',
            'category' => '什器・テント',
        ]);
        $response->assertStatus(403);

        // 2. 保管場所追加の拒否
        $response = $this->post(route('equipment.location.store'), [
            'name' => '新倉庫',
        ]);
        $response->assertStatus(403);
    }

    /**
     * 備品管理者権限での登録、編集、削除、在庫調整、貸出、メンテナンス登録が正常に動作することの検証
     */
    public function test_equipment_manager_can_perform_cud_and_stock_adjustments(): void
    {
        $this->actingAs($this->managerUser);

        // 1. 備品マスタ新規登録
        Storage::fake('public');
        $file = UploadedFile::fake()->create('tent.jpg', 100, 'image/jpeg');

        $response = $this->post(route('equipment.master.store'), [

            'ownership_type' => 'owned',
            'name' => 'ワンタッチテント',
            'specifications' => '3m x 3m',
            'quantity' => 10,
            'unit' => '張',
            'unit_price' => 15000,
            'category' => '什器・テント',
            'image' => $file,
            'description' => 'テスト用説明',
        ]);
        $response->assertRedirect(route('equipment.index'));
        $this->assertDatabaseHas('comittee_equipments', ['name' => 'ワンタッチテント']);
        
        $eq = Equipment::where('name', 'ワンタッチテント')->first();
        $this->assertNotNull($eq->image_path);
        Storage::disk('public')->assertExists($eq->image_path);

        // 2. 保管場所登録
        $response = $this->post(route('equipment.location.store'), [
            'name' => 'テストコミュニティルーム',
        ]);
        $response->assertRedirect(route('equipment.index'));
        $this->assertDatabaseHas('comittee_storage_locations', ['name' => 'テストコミュニティルーム']);
        
        $loc = StorageLocation::where('name', 'テストコミュニティルーム')->first();

        // 3. 在庫の手動調整
        $response = $this->post(route('equipment.stock.adjust'), [
            'equipment_id' => $eq->id,
            'storage_location_id' => $loc->id,
            'quantity' => 8,
        ]);
        $response->assertRedirect(route('equipment.index'));
        $this->assertDatabaseHas('comittee_equipment_stocks', [
            'equipment_id' => $eq->id,
            'storage_location_id' => $loc->id,
            'quantity' => 8,
        ]);

        // 4. 貸出・割当の登録
        $response = $this->post(route('equipment.loan.store'), [
            'equipment_id' => $eq->id,
            'borrower_type' => 'staff',
            'borrower_id' => 999,
            'quantity_requested' => 5,
        ]);
        $response->assertRedirect(route('equipment.index'));
        $this->assertDatabaseHas('comittee_equipment_loans', [
            'equipment_id' => $eq->id,
            'borrower_id' => 999,
            'quantity_requested' => 5,
            'status' => 'pending',
        ]);

        $loan = EquipmentLoan::where('equipment_id', $eq->id)->first();

        // 5. 貸出ステータスの更新
        $response = $this->put(route('equipment.loan.update', $loan->id), [
            'status' => 'loaned',
            'quantity_loaned' => 5,
            'quantity_returned' => 0,
        ]);
        $response->assertRedirect(route('equipment.index'));
        $this->assertDatabaseHas('comittee_equipment_loans', [
            'id' => $loan->id,
            'status' => 'loaned',
            'quantity_loaned' => 5,
        ]);

        // 6. 破損（廃棄・紛失）登録による在庫数自動減算の検証
        // 元の在庫数: 8, 総保有数: 10
        // 廃棄3個
        $response = $this->post(route('equipment.maintenance.store'), [
            'equipment_id' => $eq->id,
            'storage_location_id' => $loc->id,
            'log_type' => 'discard',
            'quantity' => 3,
            'description' => 'テントの足フレーム破損のため廃棄',
        ]);
        $response->assertRedirect(route('equipment.index'));

        // 期待される結果: 在庫数 = 8 - 3 = 5, 総保有数 = 10 - 3 = 7
        $this->assertDatabaseHas('comittee_equipment_stocks', [
            'equipment_id' => $eq->id,
            'storage_location_id' => $loc->id,
            'quantity' => 5,
        ]);
        $this->assertDatabaseHas('comittee_equipments', [
            'id' => $eq->id,
            'quantity' => 7,
        ]);
    }
}
