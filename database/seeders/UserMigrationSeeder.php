<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserYear;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class UserMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = base_path('docs/users.json');

        if (!File::exists($jsonPath)) {
            $this->command->error("移行対象のJSONファイルが見つかりません: {$jsonPath}");
            return;
        }

        $jsonData = json_decode(File::get($jsonPath), true);
        $userData = null;

        // PHPMyAdminエクスポート構造からusersテーブルのデータを検索
        foreach ($jsonData as $element) {
            if (isset($element['type']) && $element['type'] === 'table' && $element['name'] === 'users') {
                $userData = $element['data'];
                break;
            }
        }

        if (empty($userData)) {
            $this->command->error("JSON内にusersテーブルのデータが見つかりませんでした。");
            return;
        }

        $this->command->info(count($userData) . " 件のユーザーデータを移行中...");

        foreach ($userData as $row) {
            // 氏名の全角スペースを半角スペースに標準化
            $name = str_replace('　', ' ', $row['name']);
            $name = trim($name);

            // 名義カナを全角カタカナから全角ひらがなへ変換し、スペースを半角に標準化
            $kana = mb_convert_kana($row['kana'], "c", "UTF-8");
            $kana = str_replace('　', ' ', $kana);
            $kana = trim($kana);

            // ロールのマッピング設定 (section => roles)
            $roles = ['general'];
            if ($row['section'] === 'manager') {
                $roles = ['admin', 'general'];
            } elseif ($row['section'] === 'secretary') {
                $roles = ['kanji', 'general'];
            }

            // 本業・職業を一律で「有志」に設定
            $profession = '有志';

            // comittee_users の登録・更新 (email をユニークキーとして UPSERT)
            $user = User::updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $name,
                    'name_kana' => $kana,
                    'password' => $row['password'], // 暗号化されたハッシュを引き継ぎ
                    'profession' => $profession,
                    'affiliation' => null,
                    'skills' => null,
                    'roles' => $roles,
                    'referrer_text' => $row['reason'],
                    'line_display_name' => $name, // 初期値として氏名を設定
                    'status' => 'active',
                    'approved_by' => 1, // 初期管理者のID
                    'approved_at' => $row['email_verified_at'] ?? now(),
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                ]
            );

            // comittee_user_years (2026年度所属) の登録・更新
            UserYear::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'fiscal_year' => 2026
                ],
                [
                    'roles' => $roles,
                    'status' => 'active',
                ]
            );
        }

        $this->command->info("ユーザーデータの移行が完了しました。");
    }
}
