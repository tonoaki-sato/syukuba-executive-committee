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

        // 5.5 部門（グループ）初期データ
        $deptCommon = \App\Models\Department::create([
            'fiscal_year' => $fiscalYear,
            'code' => 'COMMON',
            'name' => 'まつり共通',
            'category' => 'staff',
        ]);

        $deptHonjin = \App\Models\Department::create([
            'fiscal_year' => $fiscalYear,
            'code' => 'HONJIN',
            'name' => '本陣',
            'category' => 'staff',
        ]);

        $deptMaint = \App\Models\Department::create([
            'fiscal_year' => $fiscalYear,
            'code' => 'MAINTENANCE',
            'name' => '保守/設備管理',
            'category' => 'staff',
        ]);

        $deptHygiene = \App\Models\Department::create([
            'fiscal_year' => $fiscalYear,
            'code' => 'HYGIENE',
            'name' => '食品衛生対応',
            'category' => 'staff',
        ]);

        $deptStage = \App\Models\Department::create([
            'fiscal_year' => $fiscalYear,
            'code' => 'STAGE',
            'name' => 'イベント(ステージ等)',
            'category' => 'staff',
        ]);

        $deptPolice = \App\Models\Department::create([
            'fiscal_year' => $fiscalYear,
            'code' => 'POLICE',
            'name' => '保土ケ谷警察署',
            'category' => 'partner',
        ]);

        $deptBooth1 = \App\Models\Department::create([
            'fiscal_year' => $fiscalYear,
            'code' => 'BOOTH-1',
            'name' => 'ブース1',
            'category' => 'booth',
        ]);

        $deptBooth2 = \App\Models\Department::create([
            'fiscal_year' => $fiscalYear,
            'code' => 'BOOTH-2',
            'name' => 'ブース2',
            'category' => 'booth',
        ]);

        $deptBooth3 = \App\Models\Department::create([
            'fiscal_year' => $fiscalYear,
            'code' => 'BOOTH-3',
            'name' => 'ブース3',
            'category' => 'booth',
        ]);

        // 6. 備品初期データ（所有・レンタル）
        $tentOwned = \App\Models\Equipment::create([
            'fiscal_year' => $fiscalYear,
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
            'fiscal_year' => $fiscalYear,
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
            'fiscal_year' => $fiscalYear,
            'ownership_type' => 'owned',
            'name' => 'プラスチック丸椅子',
            'specifications' => '高さ45cm',
            'quantity' => 100,
            'unit' => '脚',
            'unit_price' => 1200,
            'category' => '什器・テント',
            'description' => 'スタッキング可能な丸椅子（青・赤混在）。',
        ]);


        // レンタル備品および諸経費マスタ、部門割当、特別値引き初期データのロード (PDFデータ準拠)
        $this->call(EquipmentMasterSeeder::class);

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

        // 8. 貸出・割当のテストデータ
        // まつり共通 (COMMON)
        \App\Models\EquipmentLoan::create([
            'fiscal_year' => $fiscalYear,
            'equipment_id' => $deskOwned->id,
            'borrower_type' => 'staff',
            'borrower_id' => $deptCommon->id,
            'quantity_requested' => 8,
            'quantity_loaned' => 8,
            'status' => 'loaned',
        ]);

        // 本陣 (HONJIN)
        \App\Models\EquipmentLoan::create([
            'fiscal_year' => $fiscalYear,
            'equipment_id' => $deskOwned->id,
            'borrower_type' => 'staff',
            'borrower_id' => $deptHonjin->id,
            'quantity_requested' => 3,
            'quantity_loaned' => 3,
            'status' => 'loaned',
        ]);

        // 9. 外部ユーザーデータの移行インポート
        $this->call(UserMigrationSeeder::class);
    }
}

