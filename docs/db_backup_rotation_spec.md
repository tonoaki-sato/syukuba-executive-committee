# データベース自動バックアップ・ローテーション機能 仕様書 (第3版)

## 1. 目的
システムデータの保護と障害復旧を目的として、データベース（MySQL）のダンプファイル（バックアップ）を定期的に自動取得し、過去2週間（14日間）のデータを世代管理する仕組みを導入します。

---

## 2. 設計仕様

### 2.1. データベース接続情報の取得
*   開発環境（`.env`）および本番環境（`.env.example` をコピーして設定される本番環境の `.env` やシステム環境変数）の接続設定は、Laravelの `config('database.connections.mysql')` を介して一元的に取得します。これにより、環境ごとのファイルパーサーを個別に作ることなく、Laravelの標準的な方法で安全に接続情報を取得できます。

### 2.2. バックアップの実行（Artisan コマンド）
*   バックアップ専用の Artisan コマンド `php artisan db:backup` を新規作成します。
*   コマンドの動作プロセス:
    1.  `config` から接続情報（Host, Port, Database, Username, Password）を取得。
    2.  `mysqldump` コマンドを実行し、SQLのダンプファイルを作成。
    3.  ディスク容量節約のため、ダンプファイルを `gzip` で圧縮。
    4.  保存先: `storage/app/backups/backup-YYYYMMDD-HHMMSS.sql.gz`
    5.  バックアップ作成完了後、**ローテーション処理（2.4節）**をトリガーします。

### 2.3. 実行スケジュール（1日3回）
*   Laravel のタスクスケジューラー（`routes/console.php`）にコマンドを登録し、1日3回定期実行します。
*   *実行スケジュール案*: 8時間ごと（毎日 0:00, 8:00, 16:00）
    ```php
    use Illuminate\Support\Facades\Schedule;
    
    // 1日3回、8時間おきに実行 (0:00, 8:00, 16:00)
    Schedule::command('db:backup')->cron('0 */8 * * *');
    ```

### 2.4. ローテーションルール（2週間保存）
*   バックアップ実行時、`storage/app/backups/` ディレクトリ内のファイルをスキャンします。
*   ファイルの最終更新日時（`Storage::lastModified`）が **14日（336時間）以上古いもの** を自動的に検出し、`Storage::delete()` を用いて削除します。

---

## 3. 提案する変更点

### 3.1. [NEW] [BackupDatabase.php](file:///opt/project/syukuba-executive-committee/app/Console/Commands/BackupDatabase.php)
データベースのダンプ作成、Gzip圧縮、および世代ローテーションを行うコマンドクラス。

### 3.2. [MODIFY] [console.php](file:///opt/project/syukuba-executive-committee/routes/console.php)
タスクスケジューラーに `db:backup` コマンドの定期実行登録を追記。

---

## 4. サーバー定期実行環境の設定方法

Laravelのタスクスケジューラー（`schedule:run`）を自動実行するため、サーバー環境に合わせて以下のいずれかの方法を設定します。

### 方式A. `crontab` を利用する場合
サーバー上の `crontab` に毎分実行のタスクスケジュールを登録します。

1.  サーバー上で設定を開きます:
    ```bash
    crontab -e
    ```
2.  以下の1行を追加します（Webサーバー実行ユーザーで実行することを推奨します）:
    ```bash
    * * * * * cd /opt/project/syukuba-executive-committee && php artisan schedule:run >> /dev/null 2>&1
    ```

> [!IMPORTANT]
> **さくらのレンタルサーバ（スタンダードプラン等の共用サーバー）における注意点**
> *   共用サーバー環境では root 権限がないため、**「方式B. systemd.timer」は利用できません**。必ず**「方式A. crontab」**を選択してください。
> *   さくらのサーバー環境では環境変数やPHPのパスが実行時に引き継がれないことがあるため、コマンド内のパスはすべて**フルパス（絶対パス）で指定する**必要があります。
> *   *設定例 (さくらのコントロールパネルまたはcrontab)*:
>     `* * * * * /usr/local/bin/php /home/[さくらのユーザーID]/[プロジェクトのフォルダ]/artisan schedule:run >> /dev/null 2>&1`
>     *(※ `/usr/local/bin/php` は動作させるPHPバージョンに応じて `/usr/local/bin/php-8.x` などに合わせる必要があります)*

---

### 方式B. `systemd.timer` を利用する場合
root 権限があり、より詳細なログ管理（journald）やリソース制限を行いたいVPSや専用サーバー環境等で推奨されます。

1.  **サービスファイルの作成**: 
    `/etc/systemd/system/laravel-scheduler.service` を以下の内容で作成します。
    ```ini
    [Unit]
    Description=Run Laravel Scheduler
    After=network.target

    [Service]
    Type=simple
    User=www-data
    Group=www-data
    WorkingDirectory=/opt/project/syukuba-executive-committee
    ExecStart=/usr/bin/php artisan schedule:run
    ```
    *(※User/Groupは動作環境に合わせて適宜変更してください)*

2.  **タイマーファイルの作成**:
    `/etc/systemd/system/laravel-scheduler.timer` を以下の内容で作成します。
    ```ini
    [Unit]
    Description=Run Laravel Scheduler every minute

    [Timer]
    OnBootSec=1min
    OnUnitActiveSec=1min

    [Install]
    WantedBy=timers.target
    ```

3.  **タイマーの起動と有効化**:
    ```bash
    sudo systemctl daemon-reload
    sudo systemctl enable --now laravel-scheduler.timer
    ```

4.  **実行ログの確認**:
    ```bash
    sudo journalctl -u laravel-scheduler.service -f
    ```

---

## 5. ユーザーレビューが必要な点

> [!IMPORTANT]
> **本番環境での `mysqldump` コマンドの利用可能性について**
> *   本機能はサーバー上の `mysqldump` および `gzip` コマンドに依存します。本番環境サーバーのシェルでこれらのコマンドが実行可能（パスが通っている）であることを前提とします。
> *   もし本番環境が `mysqldump` を利用できない環境（ホスト型PaaSや制限の厳しい共用サーバーなど）である場合は、PHPライブラリでダンプを行う方式に変更しますのでお知らせください。

---

## 6. 検証プラン

### 6.1. 手動検証
*   `php artisan db:backup` コマンドをコンソールから手動で実行し、`storage/app/backups/` 配下に圧縮された `.sql.gz` が正しく生成されること、およびその中身（解凍したSQL）が正しいことを確認します。
*   テスト用に過去日付のダミーファイルを配置し、コマンド実行時に14日以上前のファイルのみが正しく削除されるかローテーションの挙動を確認します。

### 6.2. 自動テスト (PHPUnit)
*   `tests/Feature/BackupDatabaseTest.php` を作成し、コマンド実行時のダンプファイル作成とローテーション処理がモック化されたストレージ上で正しく機能することを検証します。
