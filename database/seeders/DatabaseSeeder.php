<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserYear;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $fiscalYear = 2026;

        // 1. 初期システム管理者
        $admin = User::create([
            'name' => 'システム 管理者',
            'name_kana' => 'しすてむ かんりしゃ',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'profession' => '事務局長（ITサポート）',
            'affiliation' => '宿場まつり事務局',
            'skills' => ['事務・会計', '広報・デザイン'],
            'roles' => ['admin', 'general'],
            'line_display_name' => '管理者＠事務局',
            'status' => 'active',
            'approved_by' => null,
            'approved_at' => now(),
        ]);

        // 2026年度の所属を登録
        UserYear::create([
            'user_id' => $admin->id,
            'fiscal_year' => $fiscalYear,
            'roles' => ['admin', 'general'],
            'status' => 'active',
        ]);

        // 2. テスト用幹事会員 (システム管理者が承認)
        $kanji = User::create([
            'name' => '幹事 太郎',
            'name_kana' => 'かんじ たろう',
            'email' => 'kanji@example.com',
            'password' => Hash::make('password'),
            'profession' => '電気工事店経営',
            'affiliation' => '宿場商店会',
            'skills' => ['電気工事', '音響・映像', '設営・運搬'],
            'roles' => ['kanji', 'general'],
            'referrer_id' => $admin->id,
            'line_display_name' => 'たろう＠電気屋',
            'status' => 'active',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        // 2026年度の所属を登録
        UserYear::create([
            'user_id' => $kanji->id,
            'fiscal_year' => $fiscalYear,
            'roles' => ['kanji', 'general'],
            'status' => 'active',
        ]);

        // 3. テスト用一般会員 (幹事の紹介、システム管理者が承認)
        $member = User::create([
            'name' => '一般 次郎',
            'name_kana' => 'いっぱん じろう',
            'email' => 'member@example.com',
            'password' => Hash::make('password'),
            'profession' => '飲食店店主',
            'affiliation' => '宿場町内会',
            'skills' => ['調理・衛生'],
            'roles' => ['general'],
            'referrer_id' => $kanji->id,
            'line_display_name' => 'じろう＠飲食店',
            'status' => 'active',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        // 2026年度の所属を登録
        UserYear::create([
            'user_id' => $member->id,
            'fiscal_year' => $fiscalYear,
            'roles' => ['general'],
            'status' => 'active',
        ]);
    }
}
