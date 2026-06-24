<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserYear;
use App\Models\GozaichiEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class GozaichiMapUploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $kanji;
    protected User $general;
    protected GozaichiEvent $event;
    protected string $originalMapPath;
    protected string $tempMapPath;

    protected function setUp(): void
    {
        parent::setUp();

        // テストユーザー作成
        $this->admin = User::create([
            'name' => '管理者',
            'name_kana' => 'かんりしゃ',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'profession' => '行政',
            'line_display_name' => 'admin_line',
            'roles' => ['admin'],
            'status' => 'active',
        ]);

        $this->kanji = User::create([
            'name' => '幹事',
            'name_kana' => 'かんじ',
            'email' => 'kanji@example.com',
            'password' => bcrypt('password123'),
            'profession' => '自営業',
            'line_display_name' => 'kanji_line',
            'roles' => ['kanji'],
            'status' => 'active',
        ]);

        $this->general = User::create([
            'name' => '一般',
            'name_kana' => 'いっぱん',
            'email' => 'general@example.com',
            'password' => bcrypt('password123'),
            'profession' => '会社員',
            'line_display_name' => 'general_line',
            'roles' => ['general'],
            'status' => 'active',
        ]);

        $this->event = GozaichiEvent::create([
            'fiscal_year' => 2026,
            'recruitment_status' => 'open',
            'is_active' => true,
        ]);

        // 共通のセッション年度設定
        session(['active_fiscal_year' => 2026]);

        // テスト用のmap_base.pngを一時退避/生成
        $this->originalMapPath = public_path('images/map_base.png');
        if (file_exists($this->originalMapPath)) {
            $this->tempMapPath = public_path('images/map_base_temp_backup.png');
            copy($this->originalMapPath, $this->tempMapPath);
        } else {
            // ディレクトリがなければ作成し、ダミー画像を配置
            @mkdir(public_path('images'), 0755, true);
            file_put_contents($this->originalMapPath, 'dummy_png_content');
            $this->tempMapPath = null;
        }
    }

    protected function tearDown(): void
    {
        // テスト終了後のクリーンアップと復元
        if ($this->tempMapPath && file_exists($this->tempMapPath)) {
            if (file_exists($this->originalMapPath)) {
                unlink($this->originalMapPath);
            }
            rename($this->tempMapPath, $this->originalMapPath);
        } elseif (!$this->tempMapPath && file_exists($this->originalMapPath)) {
            unlink($this->originalMapPath);
        }

        $backupPath = public_path('images/map_base_backup.png');
        if (file_exists($backupPath)) {
            unlink($backupPath);
        }

        $versionPath = public_path('images/map_base_version.txt');
        if (file_exists($versionPath)) {
            unlink($versionPath);
        }

        parent::tearDown();
    }

    /**
     * 未ログインゲストのアクセス制限 (ログイン画面へのリダイレクトを確認)
     */
    public function test_guest_cannot_upload_base_map(): void
    {
        $response = $this->post(route('admin.map.uploadBase'), [
            'map_pdf' => UploadedFile::fake()->create('map.pdf', 100, 'application/pdf')
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    /**
     * 一般会員のアクセス制限
     */
    public function test_general_user_cannot_upload_base_map(): void
    {
        $response = $this->actingAs($this->general)->post(route('admin.map.uploadBase'), [
            'map_pdf' => UploadedFile::fake()->create('map.pdf', 100, 'application/pdf')
        ], [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(403);
    }

    /**
     * 幹事会員のアクセス制限
     */
    public function test_kanji_user_cannot_upload_base_map(): void
    {
        $response = $this->actingAs($this->kanji)->post(route('admin.map.uploadBase'), [
            'map_pdf' => UploadedFile::fake()->create('map.pdf', 100, 'application/pdf')
        ], [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(403);
    }

    /**
     * バリデーションエラー検証（PDF以外の拡張子を送信したとき）
     */
    public function test_validation_rejects_non_pdf_file(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.map.uploadBase'), [
            'map_pdf' => UploadedFile::fake()->create('map.png', 100, 'image/png')
        ], [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['map_pdf']);
    }



    /**
     * バリデーションエラー検証（10MBを超えるファイルを送信したとき）
     */
    public function test_validation_rejects_large_file(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.map.uploadBase'), [
            'map_pdf' => UploadedFile::fake()->create('map.pdf', 12000, 'application/pdf') // 12MB
        ], [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['map_pdf']);
    }

    /**
     * 正常系アップロードと変換処理
     */
    public function test_admin_can_upload_valid_pdf(): void
    {
        $sourcePdf = '/opt/project/syukuba-executive-committee/docs/大久保さん送付0923-2.pdf';
        
        $tempPdf = tempnam(sys_get_temp_dir(), 'test_map_');
        copy($sourcePdf, $tempPdf);
        
        $uploadFile = new UploadedFile($tempPdf, 'map.pdf', 'application/pdf', null, true);

        if (file_exists($this->originalMapPath)) {
            unlink($this->originalMapPath);
        }

        $response = $this->actingAs($this->admin)->post(route('admin.map.uploadBase'), [
            'map_pdf' => $uploadFile
        ], [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['success' => true]);
        $response->assertJsonStructure(['success', 'version']);

        $this->assertFileExists($this->originalMapPath);
        $this->assertGreaterThan(0, filesize($this->originalMapPath));

        $versionPath = public_path('images/map_base_version.txt');
        $this->assertFileExists($versionPath);
    }

    /**
     * 変換失敗時のロールバック検証
     */
    public function test_rollback_on_failed_conversion(): void
    {
        file_put_contents($this->originalMapPath, 'original_pre_upload_content');

        $corruptedPdf = UploadedFile::fake()->create('corrupted.pdf', 10, 'application/pdf');

        $response = $this->actingAs($this->admin)->post(route('admin.map.uploadBase'), [
            'map_pdf' => $corruptedPdf
        ], [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(500);
        $response->assertJsonFragment(['success' => false]);

        $this->assertFileExists($this->originalMapPath);
        $this->assertEquals('original_pre_upload_content', file_get_contents($this->originalMapPath));

        $backupPath = public_path('images/map_base_backup.png');
        $this->assertFileDoesNotExist($backupPath);
    }
}
