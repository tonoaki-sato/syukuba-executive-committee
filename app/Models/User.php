<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'name_kana',
    'email',
    'password',
    'profession',
    'affiliation',
    'skills',
    'roles',
    'referrer_id',
    'referrer_text',
    'line_display_name',
    'status',
    'approved_by',
    'approved_at'
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * このモデルに関連付けるテーブル。
     *
     * @var string
     */
    protected $table = 'comittee_users';

    /**
     * キャストが必要な属性。
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'skills' => 'array',
            'roles' => 'array',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * 紹介者の取得 (自己参照 belongsTo)
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * このユーザーが紹介した会員リスト (自己参照 hasMany)
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referrer_id');
    }

    /**
     * 承認した管理者の取得 (自己参照 belongsTo)
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * この管理者が承認したユーザーリスト (自己参照 hasMany)
     */
    public function approvedUsers(): HasMany
    {
        return $this->hasMany(User::class, 'approved_by');
    }

    /**
     * 登録されているパスキー
     */
    public function webAuthnKeys(): HasMany
    {
        return $this->hasMany(WebAuthnKey::class, 'user_id');
    }

    /**
     * パスキー登録セッション
     */
    public function passkeySessions(): HasMany
    {
        return $this->hasMany(PasskeySession::class, 'user_id');
    }

    /**
     * 年度ごとの所属・役割情報を取得 (hasMany)
     */
    public function userYears(): HasMany
    {
        return $this->hasMany(UserYear::class, 'user_id');
    }

    /**
     * 指定年度（デフォルトは現在のセッション年度）の所属レコードを取得
     */
    public function yearRecord($year = null)
    {
        $year = $year ?: session('active_fiscal_year', date('Y'));
        return $this->userYears()->where('fiscal_year', $year)->first();
    }

    /**
     * 出欠登録された会議リスト
     */
    public function meetings(): BelongsToMany
    {
        return $this->belongsToMany(Meeting::class, 'comittee_meeting_participants', 'user_id', 'meeting_id')
            ->withPivot('status', 'note')
            ->withTimestamps();
    }

    /**
     * ロール・会員属性の判定メソッド（年度別を優先、なければアカウントのデフォルトを参照）
     */
    public function hasRole(string $role, $year = null): bool
    {
        $record = $this->yearRecord($year);
        if ($record && is_array($record->roles)) {
            return in_array($role, $record->roles);
        }
        return is_array($this->roles) && in_array($role, $this->roles);
    }

    /**
     * システム管理者であるか判定
     */
    public function isSystemAdmin(): bool
    {
        return $this->status === 'active' && ($this->hasRole('admin') || (is_array($this->roles) && in_array('admin', $this->roles)));
    }

    /**
     * 幹事であるか判定
     */
    public function isKanji($year = null): bool
    {
        return $this->status === 'active' && $this->hasRole('kanji', $year);
    }

    /**
     * 一般会員であるか判定
     */
    public function isGeneralMember($year = null): bool
    {
        return $this->status === 'active' && $this->hasRole('general', $year);
    }
}
