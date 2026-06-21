<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WebAuthnKey;
use App\Models\PasskeySession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use lbuchs\WebAuthn\WebAuthn;

class WebAuthnController extends Controller
{
    protected WebAuthn $webAuthn;

    public function __construct()
    {
        // 本システムのドメイン (www.syukuba.home) に基づき WebAuthn を初期化
        $this->webAuthn = new WebAuthn(
            '保土ケ谷宿場まつり実行委員会 実務管理システム', // アプリ名
            'www.syukuba.home'                            // ドメイン名 (ID)
        );
    }

    /**
     * ログイン用のチャレンジ（Conditional UI対応）を生成
     */
    public function getLoginChallenge(Request $request)
    {
        try {
            $email = $request->input('email');
            $allowedCredentialIds = [];

            // メールアドレスが入力されている場合は、登録済みのパスキーに制限する
            if ($email) {
                $user = User::where('email', $email)->first();
                if ($user) {
                    $allowedCredentialIds = WebAuthnKey::where('user_id', $user->id)
                        ->pluck('credential_id')
                        ->map(fn($id) => $this->base64UrlDecode($id))
                        ->toArray();
                }
            }

            // WebAuthnのログイン引数（GET引数）を生成
            // Conditional UIの場合、$allowedCredentialIds は空の配列となり、任意の登録済みキーを受け入れます。
            $args = $this->webAuthn->getGetArgs(
                $allowedCredentialIds,
                60, // タイムアウト（秒）
                true, // ユーザー検証を要求 (PIN/生体認証)
                true  // ユーザーの存在確認を要求
            );

            // チャレンジ値をセッションに保存（検証時に使用）
            $challenge = $this->webAuthn->getChallenge();
            session(['webauthn_login_challenge' => $challenge]);

            return response()->json($args);
        } catch (\Exception $e) {
            Log::error('WebAuthn Login Challenge Error: ' . $e->getMessage());
            return response()->json(['error' => 'チャレンジの生成に失敗しました。'], 500);
        }
    }

    /**
     * パスキー認証によるログイン検証
     */
    public function postLoginVerify(Request $request)
    {
        try {
            $challenge = session('webauthn_login_challenge');
            if (!$challenge) {
                return response()->json(['error' => 'セッション有効期限切れ、または不正なチャレンジです。'], 400);
            }

            // クライアントからの認証情報を取得
            $credentialId = $request->input('id'); // Base64URL
            $clientDataJSON = $this->base64UrlDecode($request->input('clientDataJSON'));
            $authenticatorData = $this->base64UrlDecode($request->input('authenticatorData'));
            $signature = $this->base64UrlDecode($request->input('signature'));

            // データベースから対応する公開鍵を取得
            $webAuthnKey = WebAuthnKey::where('credential_id', $credentialId)->first();
            if (!$webAuthnKey) {
                return response()->json(['error' => '登録されていないパスキーです。'], 400);
            }

            $user = $webAuthnKey->user;
            if (!$user) {
                return response()->json(['error' => 'ユーザーが見つかりません。'], 404);
            }

            // パスキーの検証処理を実行
            $publicKeyPEM = $webAuthnKey->public_key;
            $prevCounter = $webAuthnKey->counter;

            $success = $this->webAuthn->processGet(
                $clientDataJSON,
                $authenticatorData,
                $signature,
                $this->base64UrlDecode($credentialId),
                $publicKeyPEM,
                $challenge,
                $prevCounter,
                true // ユーザー検証の要求
            );

            if (!$success) {
                return response()->json(['error' => '生体認証またはPINの検証に失敗しました。'], 401);
            }

            // 認証カウンターの更新（クローン検知用）
            // processGet成功時に内部状態のカウンターが更新されるため取得
            $webAuthnKey->counter = $this->webAuthn->getSignatureCounter();
            $webAuthnKey->last_used_at = now();
            $webAuthnKey->save();

            // 仮会員ステータスのチェック（一切の機能を使用できないロックアウト）
            if ($user->status === 'temporary') {
                // セッション上はログインさせないか、またはログイン後にリダイレクトをかけるが、
                // 非同期認証APIとしては403ステータスと承認待ちメッセージを返却
                return response()->json([
                    'status' => 'temporary',
                    'message' => 'アカウントはシステム管理者の承認待ちです。承認されるまでログインできません。'
                ], 403);
            }

            if (in_array($user->status, ['suspended', 'expelled', 'rejected'])) {
                return response()->json(['error' => 'このアカウントは現在ご利用いただけません。'], 403);
            }

            // ログイン実行
            Auth::login($user);
            session()->forget('webauthn_login_challenge');

            return response()->json([
                'status' => 'success',
                'redirect' => route('dashboard')
            ]);
        } catch (\Exception $e) {
            Log::error('WebAuthn Login Verify Error: ' . $e->getMessage());
            return response()->json(['error' => 'ログイン認証中にエラーが発生しました。: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 新規パスキー登録用のチャレンジを生成
     */
    public function getRegisterChallenge(Request $request)
    {
        try {
            // パスキー登録セッションのトークンが送信されているか確認
            $token = $request->input('token');
            $user = null;

            if ($token) {
                // ワンタイムURLからアクセスしたユーザー
                $passkeySession = PasskeySession::where('token', $token)->first();
                if (!$passkeySession || $passkeySession->isExpired()) {
                    return response()->json(['error' => '登録期限が切れているか、無効なURLです。'], 400);
                }
                $user = $passkeySession->user;
            } else {
                // ログイン中の管理者自身が登録する場合
                $user = Auth::user();
            }

            if (!$user) {
                return response()->json(['error' => 'ユーザーが見つかりません。登録セッションが無効です。'], 404);
            }

            // ユーザーIDはバイナリに変換（ここではメールアドレスのハッシュや単なる文字列等）
            $userIdBinary = strval($user->id);

            // WebAuthnの登録引数を生成
            $args = $this->webAuthn->getCreateArgs(
                $userIdBinary,
                $user->email,
                $user->name,
                60, // タイムアウト（秒）
                true, // ユーザー検証の要求
                false // アテステーション(デバイス証明)は不問
            );

            // チャレンジ値をセッションに保存
            $challenge = $this->webAuthn->getChallenge();
            session(['webauthn_register_challenge' => $challenge]);
            if ($token) {
                session(['webauthn_register_token' => $token]);
            }

            return response()->json($args);
        } catch (\Exception $e) {
            Log::error('WebAuthn Register Challenge Error: ' . $e->getMessage());
            return response()->json(['error' => '登録用チャレンジの生成に失敗しました。'], 500);
        }
    }

    /**
     * パスキー登録データの検証と保存
     */
    public function postRegisterVerify(Request $request)
    {
        try {
            $challenge = session('webauthn_register_challenge');
            if (!$challenge) {
                return response()->json(['error' => 'セッション有効期限切れ、または不正なチャレンジです。'], 400);
            }

            // トークンまたはログイン中ユーザーの確認
            $token = session('webauthn_register_token');
            $user = null;

            if ($token) {
                $passkeySession = PasskeySession::where('token', $token)->first();
                if (!$passkeySession || $passkeySession->isExpired()) {
                    return response()->json(['error' => '無効または有効期限切れの登録セッションです。'], 400);
                }
                $user = $passkeySession->user;
            } else {
                // ログイン中の管理者が自分のキーを追加登録する場合
                $user = Auth::user();
            }

            if (!$user) {
                return response()->json(['error' => 'ユーザーの特定に失敗しました。'], 404);
            }

            // クライアントからの登録レスポンスを取得
            $clientDataJSON = $this->base64UrlDecode($request->input('clientDataJSON'));
            $attestationObject = $this->base64UrlDecode($request->input('attestationObject'));
            $deviceName = $request->input('device_name', '新しいパスキーデバイス');

            // WebAuthnの検証処理
            $data = $this->webAuthn->processRegister(
                $clientDataJSON,
                $attestationObject,
                $challenge,
                false, // ルート証明書の検証はスキップ
                true,  // ユーザー検証の要求
                true   // ユーザー存在確認の要求
            );

            if (!$data) {
                return response()->json(['error' => '登録データの検証に失敗しました。'], 400);
            }

            // 公開鍵とCredential IDをBase64URLエンコードして保存
            $credentialId = $this->base64UrlEncode($data->credentialId);
            $publicKeyPEM = $data->credentialPublicKey;
            $aaguid = $data->aaguid;

            // 既に同一のCredential IDが登録されているかチェック
            if (WebAuthnKey::where('credential_id', $credentialId)->exists()) {
                return response()->json(['error' => 'このデバイスは既に登録されています。'], 400);
            }

            // DBに保存
            WebAuthnKey::create([
                'user_id' => $user->id,
                'credential_id' => $credentialId,
                'public_key' => $publicKeyPEM,
                'device_name' => $deviceName,
                'aaguid' => $aaguid,
                'counter' => $data->signatureCounter,
            ]);

            // ワンタイムセッションだった場合は削除
            if ($token) {
                PasskeySession::where('token', $token)->delete();
                session()->forget('webauthn_register_token');
            }

            session()->forget('webauthn_register_challenge');

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('WebAuthn Register Verify Error: ' . $e->getMessage());
            return response()->json(['error' => 'パスキー登録中にエラーが発生しました。: ' . $e->getMessage()], 500);
        }
    }

    /**
     * パスキー登録有無の非同期判定API（ID手動入力後の挙動用）
     */
    public function checkUserPasskeys(Request $request)
    {
        $email = $request->input('email');
        if (!$email) {
            return response()->json(['has_passkey' => false]);
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['has_passkey' => false]);
        }

        $hasPasskey = WebAuthnKey::where('user_id', $user->id)->exists();

        return response()->json([
            'has_passkey' => $hasPasskey,
            'status' => $user->status // 仮会員かどうかのステータスも返す
        ]);
    }

    // --- Base64URL 変換ヘルパーメソッド ---

    private function base64UrlDecode(string $data): string
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
