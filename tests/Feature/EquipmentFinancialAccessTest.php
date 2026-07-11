<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentFinancialAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // シードを実行して、テスト用の幹事、一般会員、備品等の初期データを投入する
        $this->seed();

        // 必要なテスト用ユーザーが存在しない場合は作成する
        if (!User::where('email', 'kanji@example.com')->exists()) {
            $kanji = User::create([
                'name' => '幹事 太郎',
                'name_kana' => 'かんじ たろう',
                'email' => 'kanji@example.com',
                'profession' => '有志',
                'line_display_name' => 'kanji_line',
                'roles' => ['kanji', 'general'],
                'status' => 'active',
            ]);
            \App\Models\UserYear::create([
                'user_id' => $kanji->id,
                'fiscal_year' => 2026,
                'roles' => ['kanji', 'general'],
                'status' => 'active',
            ]);
        }

        if (!User::where('email', 'member@example.com')->exists()) {
            $member = User::create([
                'name' => '会員 一郎',
                'name_kana' => 'かいいん いちろう',
                'email' => 'member@example.com',
                'profession' => '有志',
                'line_display_name' => 'member_line',
                'roles' => ['general'],
                'status' => 'active',
            ]);
            \App\Models\UserYear::create([
                'user_id' => $member->id,
                'fiscal_year' => 2026,
                'roles' => ['general'],
                'status' => 'active',
            ]);
        }
    }

    /**
     * 幹事ユーザーは金額情報を閲覧でき、値引き・消費税フォームが表示されること。
     */
    public function test_kanji_can_view_financials_and_update_summary_form()
    {
        // 幹事ユーザー (シードで作成された kanji@example.com)
        $kanji = User::where('email', 'kanji@example.com')->first();

        // テスト間の干渉を防ぐため、一度明示的にログアウトする
        \Illuminate\Support\Facades\Auth::logout();
        session()->forget('active_fiscal_year');

        $response = $this->actingAs($kanji, 'web')
            ->withSession(['active_fiscal_year' => 2026])
            ->get(route('equipment.matrix'));

        $response->assertStatus(200);
        $response->assertSee('単価');
        $response->assertSee('合計金額');
        $response->assertSee('特別値引き');
        $response->assertSee('税込総合請求額');
        $response->assertSee('割引・税金設定を保存する');
    }

    /**
     * 一般会員は金額情報がHTMLソースを含めて完全に非表示であること。
     */
    public function test_general_member_cannot_view_financials()
    {
        // 一般会員 (シードで作成された member@example.com)
        $member = User::where('email', 'member@example.com')->first();

        // テスト間の干渉を防ぐため、一度明示的にログアウトする
        \Illuminate\Support\Facades\Auth::logout();
        session()->forget('active_fiscal_year');

        $response = $this->actingAs($member, 'web')
            ->withSession(['active_fiscal_year' => 2026])
            ->get(route('equipment.matrix'));

        $response->assertStatus(200);
        
        // 金額関係の単語・フォームが表示されていないことを検証
        $response->assertDontSee('単価');
        $response->assertDontSee('合計金額');
        $response->assertDontSee('特別値引き');
        $response->assertDontSee('税込総合請求額');
        $response->assertDontSee('割引・税金設定を保存する');
        $response->assertDontSee('課税対象額');

        // レンタル備品マスタ単価 (シードの8,000円や15,000円など) の金額表記が表示されていないことを検証
        $response->assertDontSee('¥8,000');
        $response->assertDontSee('¥15,000');
        $response->assertDontSee('¥10,000');
        $response->assertDontSee('¥50,000');
        $response->assertDontSee('¥40,000');
    }
}
