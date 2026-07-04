# ユーザーデータ移行仕様書

別サイトのユーザーテーブル（JSONデータ）から、本システムのユーザー管理テーブルへデータをインポートするためのユーザー移行シーダー（Seeder）の設計仕様書です。

---

## 1. 目的と概要
現在稼働している別サイトのユーザーデータを `docs/users.json` から抽出し、本システム（保土ケ谷宿場まつり実行委員会 実務管理システム）の会員データベースに移行します。移行にあたっては、基本プロフィール情報だけでなく、本システムの仕様である**「年度別の所属・ロール管理（`comittee_user_years`）」**にも適合するようにデータを自動生成します。

---

## 2. 移行先テーブル構造

移行先のデータベースでは、以下の2つのテーブルにデータを格納します。

### 2.1 会員基本情報テーブル (`comittee_users`)
ユーザーのID、氏名、連絡先、認証用パスワードなどの基本情報を管理します。

| カラム名 | 型 | NULL | 本システムでの役割・初期値 |
| :--- | :--- | :---: | :--- |
| `id` | bigint | No | 主キー (移行元 `users.json` の `id` をそのまま維持) |
| `name` | varchar(255) | No | 氏名 (例: 「佐藤 外亮」) |
| `name_kana` | varchar(255) | No | 氏名カナ (全角カタカナからひらがなへ自動変換して格納) |
| `email` | varchar(255) | No | メールアドレス (ユニーク制約) |
| `password` | varchar(255) | No | 暗号化されたパスワード。移行元の bcrypt ハッシュをそのまま移行。 |
| `profession` | varchar(255) | No | 本業・職業。必須項目のため、移行元の `reason` から設定（空の場合は「実行委員(移行)」） |
| `affiliation` | varchar(255) | Yes | 所属部会・町内会名など。移行元 `reason` の一部から設定（任意） |
| `skills` | text | Yes | 得意分野・スキル。移行時は NULL または空値。 |
| `roles` | json | Yes | デフォルトロール（移行元 `section` から自動変換） |
| `referrer_text` | varchar(255) | Yes | 紹介経由・志望動機。移行元 `reason` の内容を設定。 |
| `line_display_name`| varchar(255) | No | LINE表示名。必須のため初期値として移行元の `name`（氏名）を設定。 |
| `status` | enum | No | 会員ステータス。一律 `active`（有効）として移行。 |
| `approved_by` | bigint | Yes | 承認者ID。移行システム管理者（ID: 1）を設定。 |
| `approved_at` | timestamp | Yes | 承認日時。移行実行日時。 |

### 2.2 年度別所属テーブル (`comittee_user_years`)
本システムでは、会員が「どの年度」に「どの権限（一般、幹事、管理者など）」で所属しているかを年度ごとに管理します。

| カラム名 | 型 | NULL | 移行時の値 |
| :--- | :--- | :---: | :--- |
| `id` | bigint | No | 主キー (自動採番) |
| `user_id` | bigint | No | `comittee_users.id` への外部キー |
| `fiscal_year` | unsigned int | No | 対象年度 (現在の実行対象年度 `2026` を設定) |
| `roles` | json | No | その年度におけるロール (移行元 `section` に対応するロール) |
| `status` | enum | No | その年度のステータス。一律 `active` を設定。 |

---

## 3. 移行元データ (JSON) とのマッピング設計

### 3.1 マッピング詳細ルール

| 移行元 `users.json` フィールド | 移行先テーブル.カラム名 | 変換ロジック・補完ルール |
| :--- | :--- | :--- |
| `id` | `comittee_users.id` | そのまま整数値としてインポート（PK不整合防止のため） |
| `name` | `comittee_users.name` <br> `comittee_users.line_display_name` | 氏名をそのまま格納します。LINE名にも初期値としてコピー。 |
| `kana` | `comittee_users.name_kana` | 全角カタカナおよび全角スペースを**「全角ひらがな」**および**「半角スペース」**に変換して格納します。<br>（例: `サトウ トノアキ` ➔ `さとう とのあき`） |
| `email` | `comittee_users.email` | メールアドレスをそのまま移行します。 |
| `password` | `comittee_users.password` | 移行元の暗号化ハッシュ（`$2y$` で始まる形式）をそのまま格納します。 |
| `section` | `comittee_users.roles` <br> `comittee_user_years.roles` | 権限カテゴリに応じて以下のようにロールを設定します：<br>- `manager` ➔ `["admin", "general"]`<br>- `secretary` ➔ `["kanji", "general"]`<br>- `normal` ➔ `["general"]` |
| `reason` | `comittee_users.referrer_text` <br> `comittee_users.profession` | `reason` の内容を `referrer_text` (紹介・動機) にそのまま代入。<br>また、移行先で必須の `profession` には、`reason` の内容をコピー（ただし空値の場合は `実行委員(移行)` と補完）。 |
| `created_at` | `comittee_users.created_at` | 移行元レコードの作成日時を引き継ぎます。 |
| `updated_at` | `comittee_users.updated_at` | 移行元レコードの更新日時を引き継ぎます。 |

---

## 4. シーダー（Seeder）の実装方針

1. **データの配置**: 
   - 提供された `docs/users.json` をシーダーから直接読み込みます。
2. **インポート処理フロー**:
   - `users.json` ファイルが存在することを確認し、読み込みます。
   - phpMyAdmin出力構造（`[{"type": "table", "data": [...]}]`）から実際のデータ配列（4番目の要素 `data`）を抽出します。
   - 既存の `comittee_users` データを衝突させないため、シーダー実行時はDBのユーザー情報をクリア（TruncateまたはFresh）する、もしくは `updateOrCreate` を使ってUPSERT処理を行います（重複防止と再現性の担保のため）。
   - ひらがな変換にはPHPの `mb_convert_kana($kana, "c", "UTF-8")` を使用します。
   - `comittee_users` 登録後、自動的に `2026` 年度用の `comittee_user_years` レコードをロール定義付きでインサートします。

---

## 5. 検証項目
- シーダー実行コマンド `php artisan db:seed --class=UserMigrationSeeder` がエラーなく完走すること。
- インポートされたユーザー情報において、氏名カナが「ひらがな」に変換されていること。
- インポートされたユーザーで、本システムへのパスワードログインが正常にできること（移行元のパスワードハッシュが一致すること）。
