<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserYear extends Model
{
    /**
     * このモデルに関連付けるテーブル。
     *
     * @var string
     */
    protected $table = 'comittee_user_years';

    /**
     * 複数代入可能な属性。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'fiscal_year',
        'roles',
        'status',
    ];

    /**
     * キャストが必要な属性。
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'roles' => 'array',
        ];
    }

    /**
     * このレコードに関連するユーザーを取得 (belongsTo)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
