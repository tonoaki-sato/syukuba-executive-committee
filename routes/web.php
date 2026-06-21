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
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/mypage', [AuthController::class, 'showMyPage'])->name('mypage');
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
    Route::post('/users/{user}/passkey-session', [AdminController::class, 'createPasskeySession'])->name('users.passkey-session');
    Route::delete('/users/{user}/passkey/{key}', [AdminController::class, 'deletePasskey'])->name('users.passkey-delete');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
    
    // 新年度への移行・引き継ぎ
    Route::get('/users/transition', [AdminController::class, 'showTransitionForm'])->name('users.transition');
    Route::post('/users/transition', [AdminController::class, 'executeTransition'])->name('users.transition-execute');
});

// 初期リダイレクト
Route::get('/', function () {
    return redirect()->route('login');
});
