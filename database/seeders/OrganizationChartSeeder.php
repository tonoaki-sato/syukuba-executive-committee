<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\DepartmentMember;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrganizationChartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fiscalYear = 2026;

        // 既存データの削除（この年度の部門とメンバー）
        Department::where('fiscal_year', $fiscalYear)->delete();

        // ユーザー名の手動マッピング（あだ名やファジーマッチしにくいもの用）
        $aliasMap = [
            'ジョーム' => '佐藤 外亮',
            'ほのか' => '九十九澤ほのか',
            'あやか' => '舩越', // 舩越 彩香
            'ボブ' => '山本能之',
        ];

        // ユーザーを取得するヘルパー関数
        $findUserId = function ($name) use ($aliasMap) {
            $searchName = $aliasMap[$name] ?? $name;

            // 部分一致で検索
            $user = User::where('name', 'like', "%{$searchName}%")->first();
            return $user ? $user->id : null;
        };

        // 部門定義データ (code => [name, category, parent_code, sort_order])
        $departmentsData = [
            // 役員・監査・顧問・相談役
            'ADMIN_GROUP' => ['役員・相談役等', 'staff', null, 10],
            'AUDIT' => ['会計監査', 'staff', 'ADMIN_GROUP', 10],
            'CHAIR' => ['実行委員長', 'staff', 'ADMIN_GROUP', 20],
            'VICE_CHAIR' => ['副実行委員長', 'staff', 'ADMIN_GROUP', 30],
            'ADVISOR' => ['顧問', 'staff', 'ADMIN_GROUP', 40],
            'COUNSELOR' => ['相談役', 'staff', 'ADMIN_GROUP', 50],

            // 総務系
            'SECRETARIAT' => ['総務', 'staff', null, 20],
            'HONJIN' => ['本陣', 'staff', 'SECRETARIAT', 10],
            'RECORD' => ['記録', 'staff', 'SECRETARIAT', 20],
            'SURVEY' => ['調査', 'staff', 'SECRETARIAT', 30],
            'ACCOUNTS' => ['会計', 'staff', 'SECRETARIAT', 40],
            'INQUIRY' => ['問い合わせ・お客様対応', 'staff', 'SECRETARIAT', 50],
            'MEETING' => ['会議', 'staff', 'SECRETARIAT', 60],
            'SCHEDULE' => ['スケジュール管理', 'staff', 'SECRETARIAT', 70],
            'CHARACTER' => ['キャラクター運営・管理', 'staff', 'SECRETARIAT', 80],
            'SUPPORT' => ['会場サポート', 'staff', 'SECRETARIAT', 90],
            'PATROL' => ['巡回', 'staff', 'SECRETARIAT', 100],
            'SAFETY' => ['保全・衛生・保険', 'staff', 'SECRETARIAT', 110],
            'FACILITIES' => ['設営・設備・企画', 'staff', 'SECRETARIAT', 120],
            'VEHICLES' => ['車両管理・誘導', 'staff', 'SECRETARIAT', 130],
            'REGULATION' => ['規制・警備', 'staff', 'SECRETARIAT', 140],

            // 渉外系
            'SHOUGAI' => ['渉外', 'staff', null, 30],
            'SPONSOR' => ['協賛', 'staff', 'SHOUGAI', 10],
            'PERMISSION' => ['許認可', 'staff', 'SHOUGAI', 20],
            'GOZAICHI' => ['ござ市', 'staff', 'SHOUGAI', 30],
            'BOOTH' => ['企業ブース', 'staff', 'SHOUGAI', 40],
            'TOILET' => ['100円トイレ', 'staff', 'SHOUGAI', 50],
            'PARKING' => ['駐車場', 'staff', 'SHOUGAI', 60],

            // イベント系
            'EVENT' => ['イベント', 'staff', null, 40],
            'STAGE_TAX' => ['税務署ステージ', 'staff', 'EVENT', 10],
            'STAGE_SPECIAL' => ['特設', 'staff', 'EVENT', 20],
            'KIDS_VILLAGE' => ['ほどがやキッズ村', 'staff', 'EVENT', 30],
            'EXT_1' => ['外部企画①', 'staff', 'EVENT', 40],
            'EXT_2' => ['外部企画②', 'staff', 'EVENT', 50],
            'EXT_3' => ['外部企画③', 'staff', 'EVENT', 60],

            // 広報系
            'PR' => ['広報', 'staff', null, 50],
            'NEWS' => ['新聞・チラシ', 'staff', 'PR', 10],
            'POSTER' => ['ポスター', 'staff', 'PR', 20],
            'IT' => ['ＩＴ部', 'staff', 'PR', 30],
            'EXT_EVENT' => ['外部イベント', 'staff', 'PR', 40],
            'SIGNBOARD' => ['のぼり・捨て看板等', 'staff', 'PR', 50],
            'DISPLAY' => ['ディスプレイ', 'staff', 'PR', 60],
        ];

        // 部門の作成
        $departments = [];
        foreach ($departmentsData as $code => $data) {
            $parent = null;
            if ($data[2] && isset($departments[$data[2]])) {
                $parent = $departments[$data[2]];
            }

            $dept = Department::create([
                'fiscal_year' => $fiscalYear,
                'code' => $code,
                'name' => $data[0],
                'category' => $data[1],
                'parent_id' => $parent ? $parent->id : null,
                'sort_order' => $data[3],
            ]);
            $departments[$code] = $dept;
        }

        // メンバーの割り当てデータ (dept_code => [[name, role, is_leader, sort_order]])
        $membersData = [
            // 役員・相談役等
            'AUDIT' => [
                ['漆原', '会計監査', false, 1]
            ],
            'CHAIR' => [
                ['寺井', '実行委員長', true, 1]
            ],
            'VICE_CHAIR' => [
                ['佐藤', '副実行委員長', false, 1],
                ['岡本', '副実行委員長', false, 2]
            ],
            'ADVISOR' => [
                ['山本 昇一', '顧問 (前・実行委員長)', false, 1],
                ['望月 聖子', '顧問 (神奈川県議)', false, 2]
            ],
            'COUNSELOR' => [
                ['萩原 繁夫', '相談役 (西口商店街会長)', false, 1],
                ['髙松 隆志', '相談役 (二丁目自治会長)', false, 2]
            ],

            // 総務
            'SECRETARIAT' => [
                ['ジョーム', '総務責任者', true, 1]
            ],
            'HONJIN' => [
                ['ジョーム', '本陣', true, 1],
                ['萩原', '本陣', false, 2],
                ['原', '受付・会場案内', false, 3]
            ],
            'RECORD' => [
                ['堀', '記録責任者', true, 1],
                ['会場サポーター', '写真撮影', false, 2]
            ],
            'SURVEY' => [
                ['堀', '調査責任者', true, 1],
                ['会場サポーター', '来場者数', false, 2],
                ['会場サポーター', 'アンケート', false, 3]
            ],
            'ACCOUNTS' => [
                ['漆原', '会計', true, 1]
            ],
            'INQUIRY' => [
                ['ジョーム', '窓口', true, 1]
            ],
            'MEETING' => [
                ['ジョーム', '司会・進行', true, 1]
            ],
            'SCHEDULE' => [
                ['岡本', 'スケジュール管理', true, 1]
            ],
            'CHARACTER' => [
                ['宿場くん・宿場ちゃん', 'キャラクター', false, 1],
                ['志田', 'アクター', false, 2],
                ['ほのか', 'アクター', false, 3],
                ['斎藤', 'お世話係', false, 4],
                ['みずほ', 'お世話係', false, 5],
                ['堀', 'リニューアル', false, 6]
            ],
            'SUPPORT' => [
                ['ジョーム', '会場サポート責任者', true, 1],
                ['会場サポーター', '配置計画', false, 2],
                ['当日ボランティア募集', '配置計画サポート', false, 3]
            ],
            'PATROL' => [
                ['ジョーム', '巡回責任者', true, 1],
                ['会場サポーター', '会場巡回', false, 2],
                ['当日ボランティア募集', '巡回サポート', false, 3]
            ],
            'SAFETY' => [
                ['有江', '保全責任者', true, 1],
                ['会場サポーター', '保全・衛生', false, 2],
                ['当日ボランティア募集', '保全・衛生サポート', false, 3]
            ],
            'FACILITIES' => [
                ['岡本', '設営・設備責任者', true, 1],
                ['松木', '企画・設営', false, 2],
                ['overcom', '設置・管理・撤去', false, 3]
            ],
            'VEHICLES' => [
                ['松木', '車両管理責任者', true, 1],
                ['荘司', '車両誘導責任者', false, 2],
                ['車両管理', '車両管理', false, 3],
                ['車両誘導', '車両誘導', false, 4]
            ],
            'REGULATION' => [
                ['譲原', '規制・警備責任者', true, 1],
                ['新武蔵警備保障', '通行止め・警備', false, 2]
            ],

            // 渉外
            'SHOUGAI' => [
                ['萩原', '渉外責任者', true, 1]
            ],
            'SPONSOR' => [
                ['山道', '協賛責任者', true, 1],
                ['北川', '協賛担当', false, 2],
                ['中村', '協賛担当', false, 3],
                ['小杉', '協賛担当', false, 4]
            ],
            'PERMISSION' => [
                ['萩原', '許認可責任者', true, 1],
                ['望月', '許認可補佐', false, 2],
                ['警察', '道路使用許可等', false, 3],
                ['消防', '火気使用等', false, 4],
                ['区役所', '後援等', false, 5],
                ['保健所', '食品営業等', false, 6]
            ],
            'GOZAICHI' => [
                ['漆原', 'ござ市責任者', true, 1],
                ['森', 'ござ市担当', false, 2]
            ],
            'BOOTH' => [
                ['寺井', '企業ブース責任者', true, 1]
            ],
            'TOILET' => [
                ['山道', '100円トイレ担当', true, 1]
            ],
            'PARKING' => [
                ['萩原', '駐車場管理責任者', true, 1],
                ['北川駐車場', '駐車場担当', false, 2],
                ['ショウワパーク', '提携駐車場', false, 3],
                ['税務署前タイムズ', '駐車場担当', false, 4],
                ['木内計測前', '駐車場担当', false, 5],
                ['田園', '駐車場担当', false, 6],
                ['横浜銀行', '駐車場担当', false, 7],
                ['大蓮寺', '駐車場担当', false, 8]
            ],

            // イベント
            'EVENT' => [
                ['山道', 'イベント全体責任者', true, 1]
            ],
            'STAGE_TAX' => [
                ['山道', 'ステージ担当', true, 1],
                ['蛇目', 'カラオケ大会', false, 2]
            ],
            'STAGE_SPECIAL' => [
                ['岡本', '特設責任者', true, 1],
                ['漆原', '特設副責任者', false, 2],
                ['帷子町二丁目自治会', '帷子茶屋', false, 3],
                ['保ドック', '愛犬イベント', false, 4],
                ['リリーズ西', 'フェイスペイント', false, 5],
                ['村上', '木工教室', false, 6],
                ['村上', '猿回し', false, 7],
                ['保土ヶ谷警察', '防犯コーナー', false, 8]
            ],
            'KIDS_VILLAGE' => [
                ['岡本', 'キッズ村責任者', true, 1],
                ['斎藤', 'キッズ村副責任者', false, 2],
                ['ぎんがむら（斎藤）', 'お楽しみ魚釣り', false, 3],
                ['区子連（斎藤）', '綿菓子', false, 4],
                ['ぎんがむら（斎藤）', 'お楽しみ魚釣り', false, 5],
                ['プレイパーク（斎藤）', '昔遊び体験', false, 6],
                ['保土ヶ谷消防署（萩原）', '消防車展示等', false, 7],
                ['斎藤', 'キッズ村ステージ', false, 8],
                ['岡本', '村役場', false, 9],
                ['斎藤', '村役場', false, 10],
                ['会場サポーター', '会場準備・片付', false, 11]
            ],
            'EXT_1' => [
                ['まちづくり協議会', '歴史展示等', false, 1],
                ['加藤', '連絡・調整', false, 2]
            ],
            'EXT_2' => [
                ['未定', '外部企画②', false, 1],
                ['未定', '連絡・調整', false, 2]
            ],
            'EXT_3' => [
                ['未定', '外部企画③', false, 1],
                ['未定', '連絡・調整', false, 2]
            ],

            // 広報
            'PR' => [
                ['山道', '広報責任者', true, 1]
            ],
            'NEWS' => [
                ['山道', '新聞・チラシ担当', true, 1]
            ],
            'POSTER' => [
                ['山道', 'ポスター担当', true, 1]
            ],
            'IT' => [
                ['あやか', 'IT担当', true, 1],
                ['ホームページ', 'IT媒体', false, 2],
                ['Facebook', 'IT媒体', false, 3],
                ['Twitter', 'IT媒体', false, 4],
                ['Instagram', 'IT媒体', false, 5],
                ['LINE', 'IT媒体', false, 6]
            ],
            'EXT_EVENT' => [
                ['未定', '外部イベント調整', false, 1]
            ],
            'SIGNBOARD' => [
                ['萩原', '看板等担当', true, 1]
            ],
            'DISPLAY' => [
                ['岡本', 'ディスプレイ担当', true, 1]
            ],
        ];

        // メンバーの登録
        foreach ($membersData as $deptCode => $membersList) {
            if (!isset($departments[$deptCode])) {
                continue;
            }

            $dept = $departments[$deptCode];

            foreach ($membersList as $data) {
                $rawName = $data[0];
                $role = $data[1];
                $isLeader = $data[2];
                $sort = $data[3];

                $userId = $findUserId($rawName);
                $customName = $userId ? null : $rawName;

                DepartmentMember::create([
                    'department_id' => $dept->id,
                    'user_id' => $userId,
                    'custom_name' => $customName,
                    'role_name' => $role,
                    'is_leader' => $isLeader,
                    'sort_order' => $sort,
                ]);
            }
        }
    }
}
