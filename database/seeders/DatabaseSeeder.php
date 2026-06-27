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

        // 4. 新設：テスト用備品管理者 (システム管理者が承認)
        $equipmentManager = User::create([
            'name' => '備品 司',
            'name_kana' => 'びひん つかさ',
            'email' => 'equipment@example.com',
            'password' => Hash::make('password'),
            'profession' => 'イベント設営会社勤務',
            'affiliation' => '設営協力会',
            'skills' => ['設営・運搬', '安全管理'],
            'roles' => ['equipment_manager', 'general'],
            'referrer_id' => $admin->id,
            'line_display_name' => 'つかさ＠備品管理',
            'status' => 'active',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        // 2026年度の所属を登録
        UserYear::create([
            'user_id' => $equipmentManager->id,
            'fiscal_year' => $fiscalYear,
            'roles' => ['equipment_manager', 'general'],
            'status' => 'active',
        ]);

        // 5. 保管場所初期データ
        $room = \App\Models\StorageLocation::create([
            'name' => 'コミュニティールーム',
            'contact_person' => '事務局（山田）',
            'notes' => '会館の1階奥の部屋。鍵は事務局のキーボックス内。',
        ]);

        $underground = \App\Models\StorageLocation::create([
            'name' => '番所地下倉庫',
            'contact_person' => '幹事会（佐藤）',
            'notes' => '歴史資料館の地下スペース。鍵は佐藤が管理。',
        ]);

        // 6. 備品初期データ（所有・レンタル）
        $tentOwned = \App\Models\Equipment::create([
            'ownership_type' => 'owned',
            'name' => 'ワンタッチテント',
            'specifications' => '3m × 3m',
            'quantity' => 10,
            'unit' => '張',
            'unit_price' => 15000,
            'category' => '什器・テント',
            'description' => '実行委所有のテント。青色フレーム。',
        ]);

        $deskOwned = \App\Models\Equipment::create([
            'ownership_type' => 'owned',
            'name' => '会議用長机',
            'specifications' => '180cm × 45cm',
            'quantity' => 30,
            'unit' => '台',
            'unit_price' => 5000,
            'category' => '什器・テント',
            'description' => '木目調折りたたみ式長机。',
        ]);

        $chairOwned = \App\Models\Equipment::create([
            'ownership_type' => 'owned',
            'name' => 'プラスチック丸椅子',
            'specifications' => '高さ45cm',
            'quantity' => 100,
            'unit' => '脚',
            'unit_price' => 1200,
            'category' => '什器・テント',
            'description' => 'スタッキング可能な丸椅子（青・赤混在）。',
        ]);

        // レンタル備品（単価・合計金額あり）
        $pipeTentRental = \App\Models\Equipment::create([
            'ownership_type' => 'rental',
            'name' => 'パイプテント',
            'specifications' => '1.5k x 2k',
            'quantity' => 15,
            'unit' => '張',
            'unit_price' => 8000,
            'category' => '什器・テント',
            'description' => 'イベント会社（保土ケ谷レンタル）から手配。',
        ]);

        $generatorRental = \App\Models\Equipment::create([
            'ownership_type' => 'rental',
            'name' => '防音型発電機',
            'specifications' => '2.5kVA',
            'quantity' => 3,
            'unit' => '台',
            'unit_price' => 15000,
            'category' => '音響・電気',
            'description' => 'ガソリン仕様の防音型インバーター発電機。',
        ]);

        // 7. 拠点別在庫初期データ
        \App\Models\EquipmentStock::create([
            'equipment_id' => $tentOwned->id,
            'storage_location_id' => $room->id,
            'quantity' => 4,
        ]);
        \App\Models\EquipmentStock::create([
            'equipment_id' => $tentOwned->id,
            'storage_location_id' => $underground->id,
            'quantity' => 6,
        ]);

        \App\Models\EquipmentStock::create([
            'equipment_id' => $deskOwned->id,
            'storage_location_id' => $room->id,
            'quantity' => 15,
        ]);
        \App\Models\EquipmentStock::create([
            'equipment_id' => $deskOwned->id,
            'storage_location_id' => $underground->id,
            'quantity' => 15,
        ]);

        \App\Models\EquipmentStock::create([
            'equipment_id' => $chairOwned->id,
            'storage_location_id' => $underground->id,
            'quantity' => 100,
        ]);

        // レンタル品はデフォルトで「コミュニティールーム（仮置き）」に全数配置
        \App\Models\EquipmentStock::create([
            'equipment_id' => $pipeTentRental->id,
            'storage_location_id' => $room->id,
            'quantity' => 15,
        ]);
        \App\Models\EquipmentStock::create([
            'equipment_id' => $generatorRental->id,
            'storage_location_id' => $room->id,
            'quantity' => 3,
        ]);
    }
}

