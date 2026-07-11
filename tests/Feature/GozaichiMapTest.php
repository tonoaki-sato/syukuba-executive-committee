<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserYear;
use App\Models\GozaichiEvent;
use App\Models\GozaichiApplication;
use App\Models\GozaichiMapMarker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GozaichiMapTest extends TestCase
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
            'profession' => '行政',
            'line_display_name' => 'admin_line',
            'roles' => ['admin'],
            'status' => 'active',
        ]);

        $this->kanji = User::create([
            'name' => '幹事',
            'name_kana' => 'かんじ',
            'email' => 'kanji@example.com',
            'profession' => '自営業',
            'line_display_name' => 'kanji_line',
            'roles' => ['kanji'],
            'status' => 'active',
        ]);

        $this->general = User::create([
            'name' => '一般',
            'name_kana' => 'いっぱん',
            'email' => 'general@example.com',
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
     * 地図機能のアクセス制御テスト
     */
    public function test_map_access_control(): void
    {
        // 1. 未ログイン: ログイン画面へリダイレクト
        $this->get(route('goza.map.index'))->assertRedirect(route('login'));
        $this->get(route('goza.map.pdf'))->assertRedirect(route('login'));
        $this->get(route('goza.map.markers'))->assertRedirect(route('login'));

        // 2. 一般会員: 閲覧画面、PDF、マーカー取得はアクセス可能 (200), 編集APIは403
        $this->actingAs($this->general)->get(route('goza.map.index'))->assertStatus(200);
        $this->actingAs($this->general)->get(route('goza.map.pdf'))->assertStatus(200);
        $this->actingAs($this->general)->get(route('goza.map.markers'))->assertStatus(200);

        $this->actingAs($this->general)->post(route('goza.map.storeMarker'), [
            'marker_type' => 'facility',
            'sub_type' => 'trash',
            'x_position' => 50.0,
            'y_position' => 50.0,
            'name' => 'テストゴミ箱',
        ])->assertStatus(403);

        // 3. 幹事会員: すべての操作が可能
        $this->actingAs($this->kanji)->get(route('goza.map.index'))->assertStatus(200);
        $this->actingAs($this->kanji)->get(route('goza.map.markers'))->assertStatus(200);

        // 4. 管理者: すべての操作が可能
        $this->actingAs($this->admin)->get(route('goza.map.index'))->assertStatus(200);
    }

    /**
     * マーカーのCRUD操作のテスト
     */
    public function test_marker_crud_operations(): void
    {
        $this->actingAs($this->kanji);

        // 1. マーカー作成 (Store)
        $response = $this->postJson(route('goza.map.storeMarker'), [
            'marker_type' => 'facility',
            'sub_type' => 'trash',
            'x_position' => 12.34,
            'y_position' => 56.78,
            'name' => 'ゴミ箱A',
            'description' => 'ゴミ箱のテスト説明',
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'marker_type' => 'facility',
            'sub_type' => 'trash',
            'x_position' => 12.34,
            'y_position' => 56.78,
            'name' => 'ゴミ箱A',
        ]);

        $this->assertDatabaseHas('comittee_map_markers', [
            'name' => 'ゴミ箱A',
            'marker_type' => 'facility',
            'x_position' => 12.34,
            'fiscal_year' => 2026,
        ]);

        $markerId = $response->json('id');

        // 2. マーカー更新 (Update)
        $updateResponse = $this->putJson(route('goza.map.updateMarker', $markerId), [
            'x_position' => 22.33,
            'y_position' => 44.55,
            'name' => 'ゴミ箱A（更新）',
            'description' => '説明変更',
        ]);

        $updateResponse->assertStatus(200);
        $this->assertDatabaseHas('comittee_map_markers', [
            'id' => $markerId,
            'name' => 'ゴミ箱A（更新）',
            'x_position' => 22.33,
            'y_position' => 44.55,
        ]);

        // 3. マーカー削除 (Delete)
        $deleteResponse = $this->deleteJson(route('goza.map.deleteMarker', $markerId));
        $deleteResponse->assertStatus(200);
        $deleteResponse->assertJson(['success' => true]);

        $this->assertDatabaseMissing('comittee_map_markers', [
            'id' => $markerId,
        ]);
    }

    /**
     * ござ市応募データの紐付けマーカーテスト
     */
    public function test_gozaichi_marker_linking(): void
    {
        $this->actingAs($this->kanji);

        // ござ市応募データの作成
        $app = GozaichiApplication::create([
            'event_id' => $this->event->id,
            'shop_name' => '焼きそば屋台',
            'exhibitor_name' => 'たろう',
            'is_member' => false,
            'section_count' => 1,
            'first_section_type' => 'B', // 火器あり
            'status' => 'accepted',
        ]);

        // 1. 応募データに紐づけてマーカーを保存
        $response = $this->postJson(route('goza.map.storeMarker'), [
            'marker_type' => 'gozaichi',
            'x_position' => 45.6,
            'y_position' => 78.9,
            'name' => '仮の名前', // これは上書きされるはず
            'application_id' => $app->id,
        ]);

        $response->assertStatus(201);
        
        // 自動で屋号と区分が設定されることを確認
        $this->assertDatabaseHas('comittee_map_markers', [
            'application_id' => $app->id,
            'marker_type' => 'gozaichi',
            'sub_type' => 'B',
            'name' => '焼きそば屋台', // shop_name
            'x_position' => 45.6,
        ]);

        // 2. 同じ応募データで別の位置にマーカーを追加配置した場合、古いマーカーは削除されることを確認
        $response2 = $this->postJson(route('goza.map.storeMarker'), [
            'marker_type' => 'gozaichi',
            'x_position' => 10.0,
            'y_position' => 20.0,
            'name' => '新しい名前',
            'application_id' => $app->id,
        ]);

        $response2->assertStatus(201);

        // 新しいマーカーのみDBに存在することを確認 (1店舗1ピン制限のテスト)
        $this->assertDatabaseHas('comittee_map_markers', [
            'application_id' => $app->id,
            'x_position' => 10.0,
            'y_position' => 20.0,
        ]);

        $this->assertDatabaseMissing('comittee_map_markers', [
            'application_id' => $app->id,
            'x_position' => 45.6,
        ]);
    }
}
