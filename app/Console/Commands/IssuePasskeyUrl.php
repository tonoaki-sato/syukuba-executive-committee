<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\WebAuthnKey;
use App\Models\PasskeySession;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class IssuePasskeyUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'passkey:issue-url {--email= : 対象ユーザーのメールアドレス}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '指定されたメールアドレスのユーザーに対してパスキー登録用の一時URLを発行します（既存のパスキーはクリアされます）。';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email');

        if (!$email) {
            $this->error('メールアドレスを指定してください。例: --email=user@example.com');
            return 1;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error('指定されたメールアドレスのユーザーが存在しません。先にシステムへユーザーを登録してください。');
            return 1;
        }

        // 既存パスキーのクリア
        WebAuthnKey::where('user_id', $user->id)->delete();

        // 古いセッションがあれば削除
        PasskeySession::where('user_id', $user->id)->delete();

        // 新しい登録用ワンタイムトークン（24時間有効）を発行
        $token = Str::random(64);
        PasskeySession::create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => now()->addHours(24),
        ]);

        $registerUrl = route('passkey.register', ['token' => $token]);

        $this->info('パスキー登録用URLの発行に成功しました（既存のパスキーはリセットされました）。');
        $this->info('以下のURLにブラウザでアクセスし、パスキーの登録を行ってください（有効期限: 24時間）。');
        $this->line('');
        $this->line($registerUrl);
        $this->line('');

        return 0;
    }
}
