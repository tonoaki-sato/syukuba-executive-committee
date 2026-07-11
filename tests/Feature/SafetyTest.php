<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SafetyTest extends TestCase
{
    use RefreshDatabase;

    protected User $approvedUser;
    protected User $temporaryUser;

    protected function setUp(): void
    {
        parent::setUp();

        // 承認済の一般会員
        $this->approvedUser = User::create([
            'name' => '一般 一郎',
            'name_kana' => 'いっぱん いちろう',
            'email' => 'general@example.com',
            'profession' => '自営業',
            'line_display_name' => 'general_line',
            'roles' => ['general'],
            'status' => 'active',
        ]);

        // 年度所属レコードを生成
        $activeYear = session('active_fiscal_year', date('Y'));
        UserYear::create([
            'user_id' => $this->approvedUser->id,
            'fiscal_year' => $activeYear,
            'roles' => ['general'],
            'status' => 'active',
        ]);

        // 仮登録ユーザー
        $this->temporaryUser = User::create([
            'name' => '仮 太郎',
            'name_kana' => 'かり たろう',
            'email' => 'temporary@example.com',
            'profession' => '学生',
            'line_display_name' => 'temporary_line',
            'roles' => ['general'],
            'status' => 'temporary',
        ]);
    }

    /**
     * 未ログインユーザーのアクセス制限を検証
     */
    public function test_guest_cannot_access_safety_route(): void
    {
        $response = $this->get(route('safety.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * 仮登録（未承認）ユーザーのアクセス制限を検証
     */
    public function test_temporary_user_cannot_access_safety_route(): void
    {
        $response = $this->actingAs($this->temporaryUser)->get(route('safety.index'));
        $response->assertRedirect(route('register.pending'));
    }

    /**
     * 承認済一般ユーザーは安全管理画面にアクセスできる
     */
    public function test_approved_user_can_access_safety_route(): void
    {
        $response = $this->actingAs($this->approvedUser)->get(route('safety.index'));
        $response->assertStatus(200);
        $response->assertSee('安全管理・警備巡回計画書');
        $response->assertSee('基本情報');
        $response->assertSee('トラブル別対処マニュアル');
    }
}
