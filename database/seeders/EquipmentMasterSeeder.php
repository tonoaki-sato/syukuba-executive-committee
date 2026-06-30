<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentLoan;
use App\Models\EquipmentRentalSummary;
use App\Models\StorageLocation;
use App\Models\EquipmentStock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EquipmentMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fiscalYear = 2026;

        // 重複防止のため、レンタル備品・諸経費関連の既存データを削除
        EquipmentLoan::where('fiscal_year', $fiscalYear)->whereHas('equipment', function ($q) {
            $q->where('ownership_type', 'rental');
        })->delete();

        // ござ市の貸出もクリア
        EquipmentLoan::where('fiscal_year', $fiscalYear)->where('borrower_type', 'gozaichi')->delete();

        Equipment::where('fiscal_year', $fiscalYear)->where('ownership_type', 'rental')->delete();
        EquipmentRentalSummary::where('fiscal_year', $fiscalYear)->delete();

        // コミュニティールーム（仮置き場）を取得または作成
        $location = StorageLocation::where('name', 'コミュニティールーム')->first()
            ?? StorageLocation::create([
                'name' => 'コミュニティールーム',
                'contact_person' => '事務局（山田）',
                'notes' => '会館の1階奥の部屋。鍵は事務局のキーボックス内。'
            ]);

        // PDFに登場する追加の部門を登録
        $deptData = [
            ['code' => 'TAX_OFFICE', 'name' => '特設税務署前駐車場', 'category' => 'staff'],
            ['code' => 'NICNIC', 'name' => 'にくにくフェイスペイント', 'category' => 'staff'],
            ['code' => 'SEIREI', 'name' => '聖隷横浜病院', 'category' => 'partner'],
            ['code' => 'EDEN', 'name' => '横浜エデンの園', 'category' => 'partner'],
            ['code' => 'TATAMI', 'name' => '畳ブース', 'category' => 'booth'],
            ['code' => 'TERAI', 'name' => '寺井ブース', 'category' => 'booth'],
            ['code' => 'HUNGRY', 'name' => 'ハングリータイガー', 'category' => 'booth'],
            ['code' => 'MIZUASA', 'name' => '水浅青果', 'category' => 'booth'],
            ['code' => 'YOKOHAMAYA', 'name' => '横濱屋', 'category' => 'booth'],
            ['code' => 'MEITATSU', 'name' => '名達の会', 'category' => 'partner'],
            ['code' => 'TRAFFIC', 'name' => '横浜市交通局', 'category' => 'partner'],
            ['code' => 'KAKIGORI', 'name' => 'かき氷', 'category' => 'booth'],
            ['code' => 'KATABIRA', 'name' => '帷子茶屋', 'category' => 'booth'],
            ['code' => 'MACHIASOBI', 'name' => '街あそび体験', 'category' => 'booth'],
            ['code' => 'TASHIRO', 'name' => 'たしろ保育室', 'category' => 'partner'],
            ['code' => 'BEEDAMA', 'name' => 'ビー玉転がし', 'category' => 'booth'],
            ['code' => 'SMOKE', 'name' => '煙体験ハウス', 'category' => 'partner'],
        ];

        foreach ($deptData as $d) {
            Department::firstOrCreate(
                ['fiscal_year' => $fiscalYear, 'code' => $d['code']],
                ['name' => $d['name'], 'category' => $d['category']]
            );
        }

        // 部門のモデルインスタンスを配列としてロード
        $depts = Department::where('fiscal_year', $fiscalYear)->get()->keyBy('code');

        // 1. レンタル備品マスタおよび個別部門別割当数を定義 (PDFのデータと完全一致)
        $equipmentsData = [
            // --- 什器・テント ---
            [
                'name' => 'パイプテント',
                'specifications' => '1.5k×2k',
                'quantity' => 15,
                'unit' => '張',
                'unit_price' => 8000,
                'category' => '什器・テント',
                'description' => 'レンタルパイプテント',
                'allocations' => [
                    'COMMON' => 3,
                    'STAGE' => 1,
                    'TAX_OFFICE' => 2,
                    'SEIREI' => 2,
                    'EDEN' => 2,
                    'TATAMI' => 1,
                    'TERAI' => 1,
                    'HUNGRY' => 1,
                    'MIZUASA' => 1,
                    'YOKOHAMAYA' => 1,
                ]
            ],
            [
                'name' => '横幕（三方）',
                'specifications' => '5k',
                'quantity' => 2,
                'unit' => '枚',
                'unit_price' => 5000,
                'category' => '什器・テント',
                'description' => 'テント用横幕（三方）',
                'allocations' => [
                    'STAGE' => 1,
                    'SEIREI' => 1,
                ]
            ],
            [
                'name' => '横幕（一方）',
                'specifications' => '2k',
                'quantity' => 2,
                'unit' => '枚',
                'unit_price' => 2000,
                'category' => '什器・テント',
                'description' => 'テント用横幕（一方）',
                'allocations' => [
                    'STAGE' => 1,
                    'SEIREI' => 1,
                ]
            ],
            [
                'name' => '簡易テント 3m',
                'specifications' => '3000×3000',
                'quantity' => 1,
                'unit' => '張',
                'unit_price' => 20000,
                'category' => '什器・テント',
                'description' => '簡易ワンタッチテント',
                'allocations' => [
                    'SEIREI' => 1,
                ]
            ],
            [
                'name' => 'ウエイト',
                'specifications' => '',
                'quantity' => 36,
                'unit' => '個',
                'unit_price' => 500,
                'category' => '什器・テント',
                'description' => 'テント固定用重り',
                'allocations' => []
            ],
            [
                'name' => '杭・ロープ',
                'specifications' => '',
                'quantity' => 28,
                'unit' => '組',
                'unit_price' => 500,
                'category' => '什器・テント',
                'description' => 'テント固定用杭・ロープ',
                'allocations' => []
            ],
            [
                'name' => '大ハンマー',
                'specifications' => '',
                'quantity' => 0,
                'unit' => '本',
                'unit_price' => 2000,
                'category' => '什器・テント',
                'description' => '杭打ち用大ハンマー',
                'allocations' => []
            ],
            [
                'name' => 'ベニヤテーブル',
                'specifications' => '1800×600',
                'quantity' => 65,
                'unit' => '台',
                'unit_price' => 1200,
                'category' => '什器・テント',
                'description' => 'ベニヤテーブル',
                'allocations' => [
                    'COMMON' => 3,
                    'HONJIN' => 3,
                    'MAINTENANCE' => 30,
                    'HYGIENE' => 5,
                    'STAGE' => 5,
                    'TAX_OFFICE' => 4,
                    'SEIREI' => 2,
                    'EDEN' => 2,
                    'TATAMI' => 2,
                    'TERAI' => 2,
                    'HUNGRY' => 2,
                    'MIZUASA' => 2,
                    'YOKOHAMAYA' => 2,
                    'NICNIC' => 1,
                ],
                'gozaichi' => 10 // ござ市貸与分
            ],
            [
                'name' => '折りたたみイス',
                'specifications' => '',
                'quantity' => 120,
                'unit' => '脚',
                'unit_price' => 300,
                'category' => '什器・テント',
                'description' => 'パイプ折りたたみイス',
                'allocations' => [
                    'COMMON' => 8,
                    'HONJIN' => 8,
                    'MAINTENANCE' => 30,
                    'HYGIENE' => 5,
                    'STAGE' => 6,
                    'TAX_OFFICE' => 8,
                    'SEIREI' => 4,
                    'EDEN' => 4,
                    'TATAMI' => 4,
                    'TERAI' => 4,
                    'HUNGRY' => 4,
                    'MIZUASA' => 4,
                    'YOKOHAMAYA' => 4,
                    'NICNIC' => 6,
                    'POLICE' => 6,
                    'MEITATSU' => 2,
                    'TRAFFIC' => 2,
                    'KAKIGORI' => 2,
                    'KATABIRA' => 10,
                    'MACHIASOBI' => 4,
                    'TASHIRO' => 4,
                    'BEEDAMA' => 4,
                    'SMOKE' => 4,
                ],
                'gozaichi' => 10 // ござ市貸与分
            ],
            [
                'name' => '足踏み式給水設備',
                'specifications' => '20Lポリ4個付',
                'quantity' => 5,
                'unit' => '台',
                'unit_price' => 10000,
                'category' => '什器・テント',
                'description' => '足踏み式給水設備',
                'allocations' => [
                    'COMMON' => 5,
                ]
            ],
            [
                'name' => '座卓テーブル',
                'specifications' => '1800×450 H300',
                'quantity' => 5,
                'unit' => '台',
                'unit_price' => 2000,
                'category' => '什器・テント',
                'description' => '座卓テーブル',
                'allocations' => [
                    'KATABIRA' => 5,
                ]
            ],
            [
                'name' => '平台',
                'specifications' => '',
                'quantity' => 6,
                'unit' => '台',
                'unit_price' => 2000,
                'category' => '什器・テント',
                'description' => '木製平台',
                'allocations' => [
                    'KATABIRA' => 6,
                ]
            ],
            [
                'name' => '箱馬',
                'specifications' => '200×300 H400',
                'quantity' => 24,
                'unit' => '台',
                'unit_price' => 1000,
                'category' => '什器・テント',
                'description' => 'ステージ用箱馬',
                'allocations' => [
                    'KATABIRA' => 24,
                ]
            ],
            [
                'name' => 'ステージ',
                'specifications' => '2k×3k H500',
                'quantity' => 0,
                'unit' => '基',
                'unit_price' => 60000,
                'category' => '什器・テント',
                'description' => '簡易組み立てステージ',
                'allocations' => []
            ],
            [
                'name' => '階段',
                'specifications' => '',
                'quantity' => 0,
                'unit' => '台',
                'unit_price' => 800,
                'category' => '什器・テント',
                'description' => 'ステージ用階段',
                'allocations' => []
            ],
            [
                'name' => 'プラシキ',
                'specifications' => '',
                'quantity' => 3,
                'unit' => '枚',
                'unit_price' => 4500,
                'category' => '什器・テント',
                'description' => '養生用プラスチック敷板',
                'allocations' => []
            ],
            [
                'name' => 'かき氷機',
                'specifications' => '',
                'quantity' => 1,
                'unit' => '台',
                'unit_price' => 10000,
                'category' => '什器・テント',
                'description' => '電動かき氷機',
                'allocations' => [
                    'KAKIGORI' => 1,
                ]
            ],
            [
                'name' => '三連コンロ',
                'specifications' => '',
                'quantity' => 0,
                'unit' => '台',
                'unit_price' => 4000,
                'category' => '什器・テント',
                'description' => '鋳物製三連ガスコンロ',
                'allocations' => []
            ],

            // --- 音響・電気 ---
            [
                'name' => 'コードリール',
                'specifications' => '30m',
                'quantity' => 5,
                'unit' => '個',
                'unit_price' => 1000,
                'category' => '音響・電気',
                'description' => '屋外防雨型コードリール',
                'allocations' => [
                    'HYGIENE' => 5,
                ]
            ],
            [
                'name' => '発電機',
                'specifications' => '1000A',
                'quantity' => 1,
                'unit' => '台',
                'unit_price' => 30000,
                'category' => '音響・電気',
                'description' => '防音型発電機 1000A',
                'allocations' => [
                    'SEIREI' => 1,
                ]
            ],
            [
                'name' => 'プロパンガス',
                'specifications' => '20kg 二口出し',
                'quantity' => 0,
                'unit' => '本',
                'unit_price' => 10000,
                'category' => '音響・電気',
                'description' => 'プロパンガス 20kg',
                'allocations' => []
            ],
            [
                'name' => 'プロパンガス',
                'specifications' => '10kg 二口出し',
                'quantity' => 0,
                'unit' => '本',
                'unit_price' => 8000,
                'category' => '音響・電気',
                'description' => 'プロパンガス 10kg',
                'allocations' => []
            ],

            // --- 保安・防災 ---
            [
                'name' => 'ダストボックス',
                'specifications' => 'ホワイト',
                'quantity' => 36,
                'unit' => '個',
                'unit_price' => 3000,
                'category' => '保安・防災',
                'description' => 'ホワイトダストボックス',
                'allocations' => [
                    'COMMON' => 30,
                    'TAX_OFFICE' => 6,
                ]
            ],
            [
                'name' => '消火器',
                'specifications' => '10型',
                'quantity' => 10,
                'unit' => '本',
                'unit_price' => 3000,
                'category' => '保安・防災',
                'description' => '粉末ABC消火器 10型',
                'allocations' => [
                    'COMMON' => 10,
                ],
                'gozaichi' => 3 // ござ市貸与分
            ],
            [
                'name' => '養生シート',
                'specifications' => '1m×50m',
                'quantity' => 0,
                'unit' => '本',
                'unit_price' => 2500,
                'category' => '保安・防災',
                'description' => 'フロア養生シート',
                'allocations' => []
            ],

            // --- 看板・装飾 ---
            [
                'name' => '社名看板',
                'specifications' => 'W900×H300',
                'quantity' => 0,
                'unit' => '枚',
                'unit_price' => 2000,
                'category' => '看板・装飾',
                'description' => '出店社名看板',
                'allocations' => []
            ],
            [
                'name' => '赤毛氈',
                'specifications' => '',
                'quantity' => 6,
                'unit' => '枚',
                'unit_price' => 3000,
                'category' => '看板・装飾',
                'description' => '赤毛氈',
                'allocations' => [
                    'KATABIRA' => 6,
                ]
            ],
            [
                'name' => 'パンチカーペット',
                'specifications' => '1800巾 赤',
                'quantity' => 0,
                'unit' => 'm',
                'unit_price' => 1800,
                'category' => '看板・装飾',
                'description' => 'ロールカーペット 赤',
                'allocations' => []
            ],
            [
                'name' => 'けこみ',
                'specifications' => '',
                'quantity' => 0,
                'unit' => 'm',
                'unit_price' => 500,
                'category' => '看板・装飾',
                'description' => 'ステージけこみ用幕',
                'allocations' => []
            ],
            [
                'name' => 'バックパネル',
                'specifications' => 'W900×H2700',
                'quantity' => 0,
                'unit' => '枚',
                'unit_price' => 2000,
                'category' => '看板・装飾',
                'description' => 'ステージ背面バックパネル',
                'allocations' => []
            ],
            [
                'name' => 'バックパネル自立部材',
                'specifications' => '',
                'quantity' => 0,
                'unit' => '式',
                'unit_price' => 10000,
                'category' => '看板・装飾',
                'description' => 'バックパネル用自立スタンド部材',
                'allocations' => []
            ],
            [
                'name' => '紅白幕',
                'specifications' => 'H1800 W5k',
                'quantity' => 0,
                'unit' => '枚',
                'unit_price' => 3000,
                'category' => '看板・装飾',
                'description' => '紅白幕',
                'allocations' => []
            ],
            [
                'name' => '紅白幕自立部材',
                'specifications' => '',
                'quantity' => 0,
                'unit' => 'スパン',
                'unit_price' => 2000,
                'category' => '看板・装飾',
                'description' => '紅白幕用自立スタンド部材',
                'allocations' => []
            ],
            [
                'name' => 'ステージ演目',
                'specifications' => 'W300×H900',
                'quantity' => 0,
                'unit' => '枚',
                'unit_price' => 1500,
                'category' => '看板・装飾',
                'description' => '演目看板',
                'allocations' => []
            ],
            [
                'name' => 'ステージスケジュール',
                'specifications' => 'W600×H900',
                'quantity' => 0,
                'unit' => '枚',
                'unit_price' => 12000,
                'category' => '看板・装飾',
                'description' => 'ステージ進行スケジュール看板',
                'allocations' => []
            ],

            // --- 諸経費・サービス ---
            [
                'name' => '搬入搬出運搬費',
                'specifications' => 'プロパンガス',
                'quantity' => 0,
                'unit' => '式',
                'unit_price' => 6000,
                'category' => '諸経費・サービス',
                'description' => 'プロパンガス搬入搬出費',
                'allocations' => []
            ],
            [
                'name' => '搬入搬出運搬費',
                'specifications' => '4t',
                'quantity' => 8,
                'unit' => '回',
                'unit_price' => 50000,
                'category' => '諸経費・サービス',
                'description' => '4tトラック搬入搬出費',
                'allocations' => []
            ],
            [
                'name' => '高速代',
                'specifications' => '',
                'quantity' => 1,
                'unit' => '回',
                'unit_price' => 10000,
                'category' => '諸経費・サービス',
                'description' => '運搬車両高速道路通行料金',
                'allocations' => []
            ],
            [
                'name' => '現場管理費',
                'specifications' => '設営・本番・撤去',
                'quantity' => 4,
                'unit' => '式',
                'unit_price' => 40000,
                'category' => '諸経費・サービス',
                'description' => '会場設営現場管理費',
                'allocations' => []
            ],
            [
                'name' => '設営人件費',
                'specifications' => '前日（夕方）',
                'quantity' => 6,
                'unit' => '人',
                'unit_price' => 30000,
                'category' => '諸経費・サービス',
                'description' => '前日設営スタッフ人件費',
                'allocations' => []
            ],
            [
                'name' => '設営人件費',
                'specifications' => '当日（早朝）',
                'quantity' => 4,
                'unit' => '人',
                'unit_price' => 30000,
                'category' => '諸経費・サービス',
                'description' => '当日朝設営スタッフ人件費',
                'allocations' => []
            ],
            [
                'name' => '撤去人件費',
                'specifications' => '当日',
                'quantity' => 12,
                'unit' => '人',
                'unit_price' => 30000,
                'category' => '諸経費・サービス',
                'description' => '撤去人件費',
                'allocations' => []
            ],
            [
                'name' => '交通費',
                'specifications' => '',
                'quantity' => 26,
                'unit' => '回',
                'unit_price' => 1500,
                'category' => '諸経費・サービス',
                'description' => 'スタッフ交通費',
                'allocations' => []
            ],
            [
                'name' => '現場消耗品費',
                'specifications' => '',
                'quantity' => 1,
                'unit' => '-',
                'unit_price' => 40000,
                'category' => '諸経費・サービス',
                'description' => '現場養生・消耗品消耗雑費',
                'allocations' => []
            ],
        ];

        // 備品マスタおよび割当(Loan)のインサート
        foreach ($equipmentsData as $eq) {
            $createdEq = Equipment::create([
                'fiscal_year' => $fiscalYear,
                'ownership_type' => 'rental',
                'name' => $eq['name'],
                'specifications' => $eq['specifications'],
                'quantity' => $eq['quantity'],
                'unit' => $eq['unit'],
                'unit_price' => $eq['unit_price'],
                'category' => $eq['category'],
                'description' => $eq['description'],
            ]);



            // 部門別割当の登録
            foreach ($eq['allocations'] as $deptCode => $qty) {
                if (isset($depts[$deptCode])) {
                    EquipmentLoan::create([
                        'fiscal_year' => $fiscalYear,
                        'equipment_id' => $createdEq->id,
                        'borrower_type' => 'staff',
                        'borrower_id' => $depts[$deptCode]->id,
                        'quantity_requested' => $qty,
                        'quantity_loaned' => $qty,
                        'status' => 'loaned',
                    ]);
                }
            }

            // ござ市割当の登録
            if (isset($eq['gozaichi']) && $eq['gozaichi'] > 0) {
                EquipmentLoan::create([
                    'fiscal_year' => $fiscalYear,
                    'equipment_id' => $createdEq->id,
                    'borrower_type' => 'gozaichi',
                    'borrower_id' => 0,
                    'quantity_requested' => $eq['gozaichi'],
                    'quantity_loaned' => $eq['gozaichi'],
                    'status' => 'loaned',
                ]);
            }
        }

        // 2. 特記事項および特別割引・税率の初期化 (PDF値引額と消費税率)
        EquipmentRentalSummary::create([
            'fiscal_year' => $fiscalYear,
            'special_discount' => 384050,
            'tax_rate' => 10.00,
            'notes' => '機材レンタル割引・特別値引き適用',
        ]);
    }
}
