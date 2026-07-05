<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentMember extends Model
{
    use HasFactory;

    /**
     * モデルに関連付けるテーブル。
     *
     * @var string
     */
    protected $table = 'comittee_department_members';

    /**
     * 複数代入可能な属性。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'department_id',
        'user_id',
        'custom_name',
        'role_name',
        'is_leader',
        'sort_order',
    ];

    /**
     * キャストが必要な属性。
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_leader' => 'boolean',
    ];

    /**
     * 所属部門を取得 (belongsTo)
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * 割り当てられた会員ユーザーを取得 (belongsTo)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 表示名を取得するアクセサ。会員なら名前、非会員なら登録されたカスタム名を返します。
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->user ? $this->user->name : ($this->custom_name ?? '');
    }
}
