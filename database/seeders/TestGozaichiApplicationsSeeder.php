<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GozaichiEvent;
use App\Models\GozaichiApplication;

class TestGozaichiApplicationsSeeder extends Seeder
{
    public function run(): void
    {
        $fiscalYear = 2026;
        $event = GozaichiEvent::where('fiscal_year', $fiscalYear)->first();
        if (!$event) {
            $event = GozaichiEvent::create([
                'fiscal_year' => $fiscalYear,
                'recruitment_status' => 'open',
                'is_active' => true,
            ]);
            
            // Add default fee settings
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
                $event->feeSettings()->create([
                    'fee_key' => $key,
                    'fee_value' => $val,
                ]);
            }
        }

        $shops = [
            [
                'shop_name' => 'たこ焼き たこちゃん',
                'exhibitor_name' => '山田 太郎',
                'is_member' => false,
                'section_count' => 1,
                'first_section_type' => 'B',
                'status' => 'accepted',
            ],
            [
                'shop_name' => '富士宮焼きそば本舗',
                'exhibitor_name' => '鈴木 一郎',
                'is_member' => true,
                'section_count' => 2,
                'first_section_type' => 'B',
                'subsequent_section_type' => 'B',
                'status' => 'accepted',
            ],
            [
                'shop_name' => 'クレープ・ド・フランス',
                'exhibitor_name' => 'マリー・デュポン',
                'is_member' => false,
                'section_count' => 1,
                'first_section_type' => 'A',
                'status' => 'accepted',
            ],
            [
                'shop_name' => 'オーガニックひまわり弁当',
                'exhibitor_name' => '佐藤 花子',
                'is_member' => false,
                'section_count' => 1,
                'first_section_type' => 'A',
                'status' => 'accepted',
            ],
            [
                'shop_name' => '手作り木工・アクセサリー工房',
                'exhibitor_name' => '高橋 二郎',
                'is_member' => false,
                'section_count' => 1,
                'first_section_type' => 'general',
                'status' => 'accepted',
            ],
            [
                'shop_name' => '昔懐かしの射的・輪投げ',
                'exhibitor_name' => '田中 三郎',
                'is_member' => true,
                'section_count' => 2,
                'first_section_type' => 'general',
                'subsequent_section_type' => 'general',
                'status' => 'accepted',
            ],
        ];

        foreach ($shops as $shop) {
            // 重複登録を避けるため、shop_nameで存在確認する
            if (!GozaichiApplication::where('event_id', $event->id)->where('shop_name', $shop['shop_name'])->exists()) {
                GozaichiApplication::create(array_merge([
                    'event_id' => $event->id,
                ], $shop));
            }
        }
    }
}
