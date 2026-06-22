# 保土ケ谷宿場まつり実行委員会 実務管理総合システム ござ市管理機能 テスト仕様書

本書は、ござ市管理機能仕様書（[gozaichi_specifications.md](file:///opt/project/syukuba-executive-committee/docs/gozaichi_specifications.md)）に基づき、ござ市管理機能を対象としたテストケースおよび検証手順を定義する。

---

## 1. テスト概要

本テストでは、ござ市管理機能における以下の観点を網羅的に検証する。

1. **アクセス制御（セキュリティ）**: `gozaichi` / `admin` ロールに基づく画面・操作へのアクセス許可と拒否
2. **出店応募CRUD**: 応募データの登録・閲覧・編集・ステータス変更の正常動作とバリデーション
3. **料金計算ロジック**: 出店料・備品貸出料・ゴミ袋料の自動計算の正確性
4. **区画配置**: 区画コードの割り当てと重複防止チェック
5. **集金・証書発行**: 支払い受領処理および領収書・出店許可証の発行フロー
6. **募集設定・イベント管理**: 募集期間・ステータスの管理と管理者限定アクセス
7. **データ整合性**: テーブル間のリレーション、年度連携、JSON カラム構造の正確性

テストは、**「自動テスト（Featureテスト）」**と**「手動テスト（ブラックボックステスト）」**の双方を用いて実施する。

---

## 2. 自動テスト仕様（Featureテスト）

### 2.1 テストクラス一覧

| テストクラス名 | 物理ファイル | テスト対象 |
| :--- | :--- | :--- |
| `GozaichiAccessControlTest` | `tests/Feature/GozaichiAccessControlTest.php` | アクセス制御・権限チェック |
| `GozaichiApplicationTest` | `tests/Feature/GozaichiApplicationTest.php` | 出店応募の CRUD・バリデーション |
| `GozaichiFeeCalculationTest` | `tests/Feature/GozaichiFeeCalculationTest.php` | 料金計算ロジック |
| `GozaichiSpotAssignmentTest` | `tests/Feature/GozaichiSpotAssignmentTest.php` | 区画配置・重複防止 |
| `GozaichiPaymentTest` | `tests/Feature/GozaichiPaymentTest.php` | 集金受領・領収書・許可証 |
| `GozaichiSettingsTest` | `tests/Feature/GozaichiSettingsTest.php` | 募集設定・イベント管理 |

### 2.2 `GozaichiAccessControlTest` — アクセス制御テスト

テストコマンド：`php artisan test --filter=GozaichiAccessControlTest`

| No | テストケース名 | 検証内容（テスト条件・操作） | 期待値（結果） |
| :--- | :--- | :--- | :--- |
| 1 | `test_guest_cannot_access_goza_routes` | 未ログインのゲストが以下のURLにアクセスする：<br>・`GET /goza`<br>・`GET /goza/applications`<br>・`GET /goza/applications/create`<br>・`GET /goza/settings` | すべてログイン画面（`/login`）へリダイレクトされること。 |
| 2 | `test_general_user_cannot_access_goza_routes` | ロールが `['general']` のみのユーザーが `/goza` 配下の全ルートにアクセスする。 | すべて `403 Forbidden` となること。 |
| 3 | `test_kanji_user_cannot_access_goza_routes` | ロールが `['kanji']` のみのユーザーが `/goza` 配下の全ルートにアクセスする。 | すべて `403 Forbidden` となること。 |
| 4 | `test_gozaichi_user_can_access_dashboard` | ロールに `gozaichi` を含むユーザーが `GET /goza` にアクセスする。 | ステータス `200 OK` でダッシュボードが表示されること。 |
| 5 | `test_gozaichi_user_can_access_applications` | ロールに `gozaichi` を含むユーザーが `GET /goza/applications` にアクセスする。 | ステータス `200 OK` で応募一覧が表示されること。 |
| 6 | `test_admin_can_access_all_goza_routes` | ロールに `admin` を含むユーザーがダッシュボード、応募一覧、作成画面、配置画面、集金画面、設定画面すべてにアクセスする。 | すべてステータス `200 OK` となること。 |
| 7 | `test_gozaichi_user_cannot_access_settings` | ロールが `['gozaichi']`（`admin` なし）のユーザーが `GET /goza/settings` にアクセスする。 | `403 Forbidden` となること（設定画面は管理者限定）。 |
| 8 | `test_temporary_user_cannot_access_goza_routes` | ステータスが `temporary`（仮会員）でロールに `gozaichi` を含むユーザーが `/goza` にアクセスする。 | `403 Forbidden` または承認待ち画面へリダイレクトされること。 |

---

### 2.3 `GozaichiApplicationTest` — 出店応募 CRUD テスト

テストコマンド：`php artisan test --filter=GozaichiApplicationTest`

| No | テストケース名 | 検証内容（テスト条件・操作） | 期待値（結果） |
| :--- | :--- | :--- | :--- |
| 9 | `test_can_view_application_create_form` | ござ市担当ユーザーが `GET /goza/applications/create` にアクセスする。 | ステータス `200 OK` で応募フォームが表示されること。 |
| 10 | `test_can_store_basic_application` | 必須項目のみを入力して応募を送信する：<br>・屋号: `テスト屋`, 出店者名: `テスト太郎`<br>・加盟: なし, 区画数: 1, 1区画目種類: `general`<br>・火気: 無, 食品: 無 | ・応募一覧にリダイレクトされること。<br>・`comittee_gozaichi_applications` に `status='draft'` でレコードが保存されること。<br>・`event_id` がアクティブなイベントIDと一致すること。 |
| 11 | `test_can_store_application_with_fire_and_food` | 火気使用「有」、食品取扱「有」で応募を送信する：<br>・使用器具: `カセットコンロ`, 台数: 2, 燃料: `カセットガス`<br>・食品衛生誓約: チェック済み<br>・区画種類: `B`（火器使用飲食） | ・`has_fire = true`, `fire_equipment`, `fire_fuel` が正しく保存されること。<br>・`has_food = true`, `has_food_pledge = true` が保存されること。 |
| 12 | `test_can_store_application_with_rentals` | 備品希望を含む応募を送信する：<br>テント: 1, ウエイト: 4, 机: 2, 椅子: 3, ゴミ袋45L: 1, ゴミ袋70L: 0 | `rentals` JSON カラムに `{"tent":1, "weight":4, "desk":2, "chair":3, "trash_bag_45":1, "trash_bag_70":0}` が保存されること。 |
| 13 | `test_can_store_multi_section_application` | 希望区画数 3, 1区画目種類 `B`, 2区画目以降 `A` で応募を送信する。 | ・`section_count = 3`, `first_section_type = 'B'`, `subsequent_section_type = 'A'` が保存されること。 |
| 14 | `test_validation_fails_for_missing_required_fields` | 屋号・出店者名・加盟状況を空で送信する。 | バリデーションエラーが返却され、`shop_name`, `exhibitor_name`, `is_member` にエラーメッセージが表示されること。 |
| 15 | `test_validation_fails_for_fire_without_details` | 火気使用「有」で、使用器具・燃料を空のまま送信する。 | `fire_equipment`, `fire_fuel` のバリデーションエラーが返却されること。 |
| 16 | `test_validation_fails_for_food_without_pledge` | 食品取扱「有」で、食品衛生誓約チェック未チェックで送信する。 | `has_food_pledge` のバリデーションエラー（誓約必須）が返却されること。 |
| 17 | `test_validation_fails_for_multi_section_without_subsequent_type` | 希望区画数 2 で、2区画目以降の種類を未選択のまま送信する。 | `subsequent_section_type` のバリデーションエラーが返却されること。 |
| 18 | `test_validation_fails_for_section_count_out_of_range` | 希望区画数に `0` または `4` を送信する。 | バリデーションエラー（`min:1`, `max:3`）が返却されること。 |
| 19 | `test_can_update_application_status_to_accepted` | ござ市担当ユーザーが、`submitted` ステータスの応募を `accepted` に変更する。 | ・`comittee_gozaichi_applications.status` が `'accepted'` に更新されること。 |
| 20 | `test_can_update_application_status_to_rejected` | ござ市担当ユーザーが、`submitted` ステータスの応募を `rejected` に変更する。 | ・`comittee_gozaichi_applications.status` が `'rejected'` に更新されること。 |
| 21 | `test_can_view_application_detail` | 登録済みの応募データに対して `GET /goza/applications/{id}` にアクセスする。 | ステータス `200 OK` で、屋号・出店者名・区画種類・備品希望数等の全情報が表示されること。 |
| 22 | `test_can_edit_application` | 登録済みの応募データを編集画面で更新する：<br>屋号を変更、区画数を変更する。 | 更新後のデータがDBに正しく反映されていること。 |

---

### 2.4 `GozaichiFeeCalculationTest` — 料金計算ロジックテスト

テストコマンド：`php artisan test --filter=GozaichiFeeCalculationTest`

#### 出店料計算ケース

| No | テストケース名 | 入力条件 | 期待値（計算結果） |
| :--- | :--- | :--- | :--- |
| 23 | `test_member_1_section_general` | 加盟=加盟, 区画数=1, 1区画目=`general` | 出店料 = **2,000円** |
| 24 | `test_member_1_section_B` | 加盟=加盟, 区画数=1, 1区画目=`B` | 出店料 = **2,000円**（加盟は種類に関わらず一律） |
| 25 | `test_member_2_sections_general` | 加盟=加盟, 区画数=2, 1区画目=`general`, 2区画目=`general` | 出店料 = 2,000 + 3,000 = **5,000円** |
| 26 | `test_member_3_sections_B` | 加盟=加盟, 区画数=3, 1区画目=`B`, 2区画目=`B` | 出店料 = 2,000 + (5,000 × 2) = **12,000円** |
| 27 | `test_member_2_sections_mixed` | 加盟=加盟, 区画数=2, 1区画目=`B`, 2区画目=`A` | 出店料 = 2,000 + 4,000 = **6,000円** |
| 28 | `test_nonmember_1_section_general` | 加盟=なし, 区画数=1, 1区画目=`general` | 出店料 = **6,000円** |
| 29 | `test_nonmember_1_section_A` | 加盟=なし, 区画数=1, 1区画目=`A` | 出店料 = **8,000円** |
| 30 | `test_nonmember_1_section_B` | 加盟=なし, 区画数=1, 1区画目=`B` | 出店料 = **10,000円** |
| 31 | `test_nonmember_2_sections_B` | 加盟=なし, 区画数=2, 1区画目=`B`, 2区画目=`B` | 出店料 = 10,000 + 10,000 = **20,000円** |
| 32 | `test_nonmember_3_sections_general` | 加盟=なし, 区画数=3, 1区画目=`general`, 2区画目=`general` | 出店料 = 6,000 + (6,000 × 2) = **18,000円** |

#### 備品貸出料計算ケース

| No | テストケース名 | 入力条件 | 期待値（計算結果） |
| :--- | :--- | :--- | :--- |
| 33 | `test_equipment_fee_basic` | テント=1, ウエイト=4, 机=1, 椅子=2 | 備品料 = 4,500 + 2,000 + 2,500 + 1,000 = **10,000円** |
| 34 | `test_equipment_fee_tent_only` | テント=2, 他=0 | 備品料 = 4,500 × 2 = **9,000円** |
| 35 | `test_equipment_fee_no_rentals` | すべて=0 | 備品料 = **0円** |
| 36 | `test_equipment_fee_override` | テント=1, ウエイト=4 の自動計算値は 6,500円<br>手動上書き金額 = 5,000円 | 備品料 = **5,000円**（上書き値を優先） |

#### ゴミ袋料計算ケース

| No | テストケース名 | 入力条件 | 期待値（計算結果） |
| :--- | :--- | :--- | :--- |
| 37 | `test_trash_fee_nonmember_within_free` | 加盟=なし, ゴミ袋45L=2, ゴミ袋70L=0 | ゴミ袋料 = **0円**（初期付与2枚の範囲内） |
| 38 | `test_trash_fee_nonmember_with_extra_45` | 加盟=なし, ゴミ袋45L=4, ゴミ袋70L=0 | ゴミ袋料 = (4-2) × 500 = **1,000円** |
| 39 | `test_trash_fee_nonmember_with_70L` | 加盟=なし, ゴミ袋45L=2, ゴミ袋70L=3 | ゴミ袋料 = 0 + (3 × 700) = **2,100円** |
| 40 | `test_trash_fee_member_no_free` | 加盟=加盟, ゴミ袋45L=2, ゴミ袋70L=0 | ゴミ袋料 = 2 × 500 = **1,000円**（加盟者には初期付与なし） |
| 41 | `test_trash_fee_member_both` | 加盟=加盟, ゴミ袋45L=1, ゴミ袋70L=2 | ゴミ袋料 = 500 + 1,400 = **1,900円** |

#### 総合計テストケース

| No | テストケース名 | 入力条件 | 期待値（計算結果） |
| :--- | :--- | :--- | :--- |
| 42 | `test_total_fee_member_full` | 加盟, 2区画(`B`), テント1+ウエイト4+机1, ゴミ袋45L=0 | 出店料: 2,000+5,000=7,000<br>備品料: 4,500+2,000+2,500=9,000<br>ゴミ袋: 0<br>合計 = **16,000円** |
| 43 | `test_total_fee_nonmember_full` | 非加盟, 1区画(`B`), テント1+ウエイト4, ゴミ袋45L=4+70L=1 | 出店料: 10,000<br>備品料: 4,500+2,000=6,500<br>ゴミ袋: (4-2)×500+1×700=1,700<br>合計 = **18,200円** |

---

### 2.5 `GozaichiSpotAssignmentTest` — 区画配置テスト

テストコマンド：`php artisan test --filter=GozaichiSpotAssignmentTest`

| No | テストケース名 | 検証内容（テスト条件・操作） | 期待値（結果） |
| :--- | :--- | :--- | :--- |
| 44 | `test_can_assign_spot_to_accepted_application` | `accepted` ステータスの応募に対して区画コード `A15` を割り当てる。 | `comittee_gozaichi_applications.spot_code` が `'A15'` に更新されること。 |
| 45 | `test_cannot_assign_duplicate_spot` | 応募Aに `A15` を割り当て済みの状態で、応募Bに同じ `A15` を割り当てる。 | バリデーションエラーが返却され、重複割り当てが拒否されること。 |
| 46 | `test_cannot_assign_spot_to_non_accepted` | `submitted` ステータス（未当選）の応募に区画コードを割り当てる。 | エラーが返却され、割り当てが拒否されること。 |
| 47 | `test_fire_type_B_shows_tent_warning` | 区画種類 `B`（火器使用飲食）の応募に区画を割り当てる際のレスポンスを確認する。 | レスポンス内に「3方幕テントが必要」の警告メッセージが含まれること。 |

---

### 2.6 `GozaichiPaymentTest` — 集金・受領テスト

テストコマンド：`php artisan test --filter=GozaichiPaymentTest`

| No | テストケース名 | 検証内容（テスト条件・操作） | 期待値（結果） |
| :--- | :--- | :--- | :--- |
| 48 | `test_can_view_payment_list` | ござ市担当ユーザーが `GET /goza/payments` にアクセスする。 | ステータス `200 OK` で、当選済み出店者の一覧と支払い状況が表示されること。 |
| 49 | `test_can_receive_payment` | 未払い（`is_paid=false`）の応募に対して `PUT /goza/payments/{id}/receive` を実行する。 | ・`is_paid = true` に更新されること。<br>・`payment_received_at` に現在日時が記録されること。<br>・`exhibition_fee`, `equipment_fee`, `trash_bag_fee`, `total_fee` が正しく計算・記録されていること。 |
| 50 | `test_cannot_receive_payment_twice` | 支払い済み（`is_paid=true`）の応募に対して再度 `PUT /goza/payments/{id}/receive` を実行する。 | エラーが返却され、二重受領が防止されること。 |
| 51 | `test_can_view_receipt` | 支払い済みの応募に対して `GET /goza/payments/{id}/receipt` にアクセスする。 | ステータス `200 OK` で、屋号名、出店料・備品料・ゴミ袋料の内訳、総合計、受領日時が表示された領収書画面が返却されること。 |
| 52 | `test_cannot_view_receipt_before_payment` | 未払いの応募に対して `GET /goza/payments/{id}/receipt` にアクセスする。 | `404 Not Found` またはエラー画面が表示されること（未払い時は領収書を発行できない）。 |
| 53 | `test_can_mark_permit_issued` | 支払い済みの応募に対して出店許可証の交付済みフラグを `true` に更新する。 | `comittee_gozaichi_applications.permit_issued` が `true` に更新されること。 |

---

### 2.7 `GozaichiSettingsTest` — 募集設定・イベント管理テスト

テストコマンド：`php artisan test --filter=GozaichiSettingsTest`

| No | テストケース名 | 検証内容（テスト条件・操作） | 期待値（結果） |
| :--- | :--- | :--- | :--- |
| 54 | `test_admin_can_view_settings` | 管理者が `GET /goza/settings` にアクセスする。 | ステータス `200 OK` で、募集期間・ステータス設定画面が表示されること。 |
| 55 | `test_admin_can_update_recruitment_period` | 管理者が募集開始日時と終了日時を設定して保存する。 | `comittee_gozaichi_events` に `recruitment_start_at` と `recruitment_end_at` が正しく保存されること。 |
| 56 | `test_admin_can_open_recruitment` | 管理者が募集ステータスを `closed` から `open` に変更する。 | `recruitment_status` が `'open'` に更新されること。 |
| 57 | `test_admin_can_close_recruitment` | 管理者が募集ステータスを `open` から `closed` に変更する。 | `recruitment_status` が `'closed'` に更新されること。 |
| 58 | `test_gozaichi_user_cannot_update_settings` | ござ市担当ユーザー（`admin` なし）が `PUT /goza/settings` を送信する。 | `403 Forbidden` となること。 |
| 59 | `test_event_fiscal_year_uniqueness` | 既に存在する `fiscal_year` と同じ値でイベントを作成する。 | バリデーションエラー（ユニーク制約違反）が返却されること。 |

---

## 3. 手動テスト仕様（UI・運用検証）

### 3.1 テスト前提条件
- **テスト環境URL**: `https://www.syukuba.home`
- **検証アカウント**:
  - システム管理者（`admin` ロール保持）
  - ござ市担当（`gozaichi` ロール保持）
  - 一般会員（`general` のみ）

### 3.2 手動テストシナリオ

#### シナリオ 1: アクセス制御と画面導線の確認
1. **一般会員**としてログインする。
2. ブラウザのアドレスバーに `/goza` を入力してアクセスする。
3. **期待値**: `403 Forbidden` エラー画面が表示されること。
4. **ござ市担当**としてログインし直す。
5. 共通ヘッダーに「ござ市管理」メニューリンクが表示されることを確認する。
6. 「ござ市管理」をクリックし、ダッシュボード（`/goza`）が表示されることを確認する。
7. ダッシュボードから「出店応募者一覧」「出店場所配置」「当日集金」の各画面に遷移できることを確認する。
8. 「募集設定」リンクにアクセスする。
9. **期待値**: ござ市担当ユーザーでは `403 Forbidden` となること（管理者限定）。

#### シナリオ 2: 出店応募の登録から選別までの一連フロー
1. **ござ市担当**としてログインし、出店応募一覧画面（`/goza/applications`）へ移動する。
2. 「新規応募登録」ボタンをクリックし、応募フォーム画面を表示する。
3. 以下の情報で応募を登録する：
   - 屋号: `テスト焼き鳥店`, 出店者名: `宿場 花子`
   - 加盟: なし（一般）, 希望区画数: 2
   - 1区画目: `B`（火器使用飲食）, 2区画目以降: `B`
   - 火気使用: 有, 使用器具: `炭火焼きグリル`, 台数: 1, 燃料: `木炭`
   - 食品取扱: 有, 食品衛生誓約: チェック
   - テント: 1張, ウエイト: 4個
4. 登録が完了し、一覧画面にリダイレクトされることを確認する。
5. 応募ステータスを「応募済」→「当選（出店許可）」に変更し、保存が反映されることを確認する。

#### シナリオ 3: 区画配置と保健所アラートの確認
1. シナリオ 2 で当選させた `テスト焼き鳥店` の区画配置画面を開く。
2. 区画コードとして `B20` を入力する。
3. **期待値**: 区画種類が `B`（火器使用飲食）のため、「**調理を伴うため3方幕テントが必要です**」の警告メッセージが表示されること。
4. 配置を確定し、`spot_code` が `B20` と記録されることを確認する。
5. 別の出店者に同じ `B20` を入力する。
6. **期待値**: 重複エラーが表示され、登録が拒否されること。

#### シナリオ 4: 料金計算の画面上でのリアルタイムプレビュー確認
1. 当日集金画面（`/goza/payments`）を開く。
2. シナリオ 2 の `テスト焼き鳥店`（非加盟, 2区画, 種類B, テント1, ウエイト4）を選択する。
3. **期待される料金プレビュー**:
   - 出店料: 10,000 + 10,000 = **20,000円**
   - 備品料: 4,500 + 2,000 = **6,500円**
   - ゴミ袋料: 初期付与45L×2枚で **0円**（追加なしの場合）
   - 総合計: **26,500円**
4. 画面上の計算結果が上記と一致していることを確認する。

#### シナリオ 5: 集金受領と領収書の発行
1. シナリオ 4 の出店者に対して「受領」ボタンをクリックする。
2. **期待値**:
   - 支払いステータスが「支払済」に更新されること。
   - 受領日時が記録されること。
3. 「領収書を印刷」をクリックする。
4. **期待値**: 印刷プレビュー画面が表示され、屋号名、料金内訳、総合計が正しく記載されていること。
5. 「出店許可証を交付しました」チェックを入れる。
6. **期待値**: `permit_issued` が `true` に記録されること。

#### シナリオ 6: 募集設定と年度連携の確認
1. **システム管理者**としてログインし、募集設定画面（`/goza/settings`）を開く。
2. 募集期間（開始: `2026-08-01 00:00`, 終了: `2026-09-15 23:59`）を設定して保存する。
3. 募集ステータスを「募集中」に変更する。
4. 共通ヘッダーの年度切り替えドロップダウンで年度を切り替える。
5. **期待値**: ダッシュボードおよび応募一覧の表示内容が、選択した年度のデータに切り替わること。

---

## 4. 改訂履歴
- 2026-06-22: ござ市管理機能テスト仕様書 新規作成（初版）
