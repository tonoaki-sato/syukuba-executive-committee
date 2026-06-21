<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebAuthnKey extends Model
{
    /**
     * このモデルに関連付けるテーブル。
     *
     * @var string
     */
    protected $table = 'comittee_webauthn_keys';

    /**
     * 複数代入可能な属性。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'credential_id',
        'public_key',
        'device_name',
        'aaguid',
        'counter',
        'last_used_at',
    ];

    /**
     * キャストが必要な属性。
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'counter' => 'integer',
            'last_used_at' => 'datetime',
        ];
    }

    /**
     * このパスキーを保有するユーザーを取得 (belongsTo)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
