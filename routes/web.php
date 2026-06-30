<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\WebAuthnController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MeetingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- ゲスト（未ログイン）ルート ---
Route::middleware('guest')->group(function () {
    // ログイン
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'postLogin']);

    // 新規登録申請（仮登録）
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'postRegister']);

    // パスキーログイン用API (非同期)
    Route::post('/webauthn/login/challenge', [WebAuthnController::class, 'getLoginChallenge']);
    Route::post('/webauthn/login/verify', [WebAuthnController::class, 'postLoginVerify']);
    Route::post('/webauthn/login/check', [WebAuthnController::class, 'checkUserPasskeys']);
});

// 承認待ち画面（仮会員ロックアウト画面）
Route::get('/register/pending', [AuthController::class, 'showPending'])->name('register.pending');

// --- ログイン中 ＆ 正式承認済（一般会員・幹事・管理者共通）ルート ---
Route::middleware(['auth', 'approved'])->group(function () {
    // ポータル・ログアウト・マイページ
    Route::get('/dashboard', [AuthController::class, 'showDashboard'])->name('dashboard');
    Route::get('/safety', [\App\Http\Controllers\SafetyController::class, 'index'])->name('safety.index');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/mypage', [AuthController::class, 'showMyPage'])->name('mypage');
    Route::get('/mypage/edit', [AuthController::class, 'editMyPage'])->name('mypage.edit');
    Route::put('/mypage', [AuthController::class, 'updateMyPage'])->name('mypage.update');
    Route::post('/mypage/password', [AuthController::class, 'postPassword'])->name('mypage.password');
    Route::get('/users/{user}', [AuthController::class, 'showUserDetail'])->name('users.show');

    // 開催年度の切り替え（グローバルコンテキスト）
    Route::post('/fiscal-year/change', [MeetingController::class, 'changeFiscalYear'])->name('fiscal-year.change');

    // 会議スケジュールと出欠回答
    Route::get('/meetings', [MeetingController::class, 'index'])->name('meetings.index');
    Route::get('/meetings/show/{meeting}', [MeetingController::class, 'show'])->name('meetings.show');
    Route::post('/meetings/{meeting}/attendance', [MeetingController::class, 'updateAttendance'])->name('meetings.attendance');

    // 管理者から発行されたワンタイムURLによるパスキーの登録画面
    Route::get('/passkey/register/{token}', function ($token) {
        return view('auth.passkey_register', compact('token'));
    })->name('passkey.register');

    // ログイン状態での追加パスキー登録用チャレンジAPI (非同期)
    Route::post('/webauthn/register/challenge', [WebAuthnController::class, 'getRegisterChallenge']);
    Route::post('/webauthn/register/verify', [WebAuthnController::class, 'postRegisterVerify']);
});

// --- 幹事 ＆ 管理者ルート（会議作成・議事録） ---
Route::middleware(['auth', 'approved'])->group(function () {
    // 幹事・管理者向け会議作成・議事録登録
    Route::get('/meetings/create', [MeetingController::class, 'create'])->name('meetings.create');
    Route::post('/meetings', [MeetingController::class, 'store'])->name('meetings.store');
    Route::get('/meetings/{meeting}/minutes', [MeetingController::class, 'showMinutesForm'])->name('meetings.minutes');
    Route::post('/meetings/{meeting}/minutes', [MeetingController::class, 'updateMinutes']);
});

// --- システム管理者（adminロール）限定ルート ---
Route::middleware(['auth', 'approved', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // 仮会員承認フロー
    Route::get('/users/pending', [AdminController::class, 'pendingUsers'])->name('users.pending');
    Route::post('/users/{user}/approve', [AdminController::class, 'approveUser'])->name('users.approve');
    Route::post('/users/{user}/reject', [AdminController::class, 'rejectUser'])->name('users.reject');

    // 正式会員・パスキー管理
    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::post('/users/{user}/passkey-session', [AdminController::class, 'createPasskeySession'])->name('users.passkey-session');
    Route::post('/users/{user}/password', [AdminController::class, 'updateUserPassword'])->name('users.password-update');
    Route::delete('/users/{user}/passkey/{key}', [AdminController::class, 'deletePasskey'])->name('users.passkey-delete');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
    
    // 新年度への移行・引き継ぎ
    Route::get('/users/transition', [AdminController::class, 'showTransitionForm'])->name('users.transition');
    Route::post('/users/transition', [AdminController::class, 'executeTransition'])->name('users.transition-execute');

    // ベースマップPDFのアップロード・画像変換
    Route::post('/map/upload-base', [\App\Http\Controllers\GozaichiMapController::class, 'uploadBaseMap'])->name('map.uploadBase');
});


// --- ござ市地図閲覧・出力ルート（一般会員・幹事・管理者共通） ---
Route::middleware(['auth', 'approved'])->prefix('goza/map')->name('goza.map.')->group(function () {
    Route::get('/', [\App\Http\Controllers\GozaichiMapController::class, 'index'])->name('index');
    Route::get('/markers', [\App\Http\Controllers\GozaichiMapController::class, 'getMarkers'])->name('markers');
    Route::get('/pdf', [\App\Http\Controllers\GozaichiMapController::class, 'exportPdf'])->name('pdf');

    // 地図編集API (幹事・管理者のみ)
    Route::middleware('gozaichi')->group(function () {
        Route::post('/markers', [\App\Http\Controllers\GozaichiMapController::class, 'storeMarker'])->name('storeMarker');
        Route::put('/markers/{id}', [\App\Http\Controllers\GozaichiMapController::class, 'updateMarker'])->name('updateMarker');
        Route::delete('/markers/{id}', [\App\Http\Controllers\GozaichiMapController::class, 'deleteMarker'])->name('deleteMarker');
    });
});

// --- ござ市管理ルート（幹事・管理者共通） ---
Route::middleware(['auth', 'approved', 'gozaichi'])->prefix('goza')->name('goza.')->group(function () {
    // ダッシュボード
    Route::get('/', [\App\Http\Controllers\GozaichiController::class, 'index'])->name('index');

    // 出店応募管理
    Route::get('/applications', [\App\Http\Controllers\GozaichiApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/create', [\App\Http\Controllers\GozaichiApplicationController::class, 'create'])->name('applications.create');
    Route::post('/applications', [\App\Http\Controllers\GozaichiApplicationController::class, 'store'])->name('applications.store');
    Route::get('/applications/{id}', [\App\Http\Controllers\GozaichiApplicationController::class, 'show'])->name('applications.show');
    Route::get('/applications/{id}/edit', [\App\Http\Controllers\GozaichiApplicationController::class, 'edit'])->name('applications.edit');
    Route::put('/applications/{id}', [\App\Http\Controllers\GozaichiApplicationController::class, 'update'])->name('applications.update');
    Route::put('/applications/{id}/status', [\App\Http\Controllers\GozaichiApplicationController::class, 'updateStatus'])->name('applications.updateStatus');

    // 出店場所配置
    Route::get('/spots', [\App\Http\Controllers\GozaichiSpotController::class, 'index'])->name('spots.index');
    Route::put('/spots/{id}', [\App\Http\Controllers\GozaichiSpotController::class, 'update'])->name('spots.update');

    // 当日集金・領収書・許可証
    Route::get('/payments', [\App\Http\Controllers\GozaichiPaymentController::class, 'index'])->name('payments.index');
    Route::put('/payments/{id}/receive', [\App\Http\Controllers\GozaichiPaymentController::class, 'receive'])->name('payments.receive');
    Route::get('/payments/{id}/receipt', [\App\Http\Controllers\GozaichiPaymentController::class, 'receipt'])->name('payments.receipt');
    Route::get('/payments/{id}/permit', [\App\Http\Controllers\GozaichiPaymentController::class, 'permit'])->name('payments.permit');

    // 募集設定・イベント管理
    Route::get('/settings', [\App\Http\Controllers\GozaichiSettingController::class, 'index'])->name('settings.index')->middleware('admin');
    Route::put('/settings', [\App\Http\Controllers\GozaichiSettingController::class, 'update'])->name('settings.update')->middleware('admin');
});

// --- 備品管理ルート（一般会員・幹事・管理者共通：閲覧のみ） ---
Route::middleware(['auth', 'approved'])->prefix('equipment')->name('equipment.')->group(function () {
    Route::get('/', [\App\Http\Controllers\EquipmentController::class, 'index'])->name('index');
    Route::get('/matrix', [\App\Http\Controllers\EquipmentMatrixController::class, 'index'])->name('matrix');

    // 登録・編集・削除などのCUD操作は備品管理者・幹事・管理者のみ
    Route::middleware(['equipment.manage'])->group(function () {
        Route::post('/master/store', [\App\Http\Controllers\EquipmentController::class, 'storeMaster'])->name('master.store');
        Route::put('/master/update/{id}', [\App\Http\Controllers\EquipmentController::class, 'updateMaster'])->name('master.update');
        Route::delete('/master/delete/{id}', [\App\Http\Controllers\EquipmentController::class, 'destroyMaster'])->name('master.destroy');
        Route::post('/location/store', [\App\Http\Controllers\EquipmentController::class, 'storeLocation'])->name('location.store');
        Route::post('/stock/adjust', [\App\Http\Controllers\EquipmentController::class, 'adjustStock'])->name('stock.adjust');
        Route::post('/loan/store', [\App\Http\Controllers\EquipmentController::class, 'storeLoan'])->name('loan.store');
        Route::put('/loan/update/{id}', [\App\Http\Controllers\EquipmentController::class, 'updateLoanStatus'])->name('loan.update');
        Route::post('/maintenance/store', [\App\Http\Controllers\EquipmentController::class, 'storeMaintenance'])->name('maintenance.store');
        Route::post('/copy-year', [\App\Http\Controllers\EquipmentController::class, 'copyFromPreviousYear'])->name('copy-year');
        Route::put('/rental-summary', [\App\Http\Controllers\EquipmentController::class, 'updateRentalSummary'])->name('rental-summary.update');
    });
});

// パスキーのトラブルシューティングページ
Route::get('/passkey/troubleshooting', [WebAuthnController::class, 'showTroubleshooting'])->name('passkey.troubleshooting');

// 初期リダイレクト
Route::get('/', function () {
    return redirect()->route('login');
});


