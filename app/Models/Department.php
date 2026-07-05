<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    /**
     * モデルに関連付けるテーブル。
     *
     * @var string
     */
    protected $table = 'comittee_departments';

    /**
     * 複数代入可能な属性。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fiscal_year',
        'code',
        'name',
        'category',
        'parent_id',
        'sort_order',
    ];

    /**
     * 親部門を取得 (belongsTo)
     */
    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    /**
     * 子部門を取得 (hasMany)
     */
    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Department::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * 部門メンバーを取得 (hasMany)
     */
    public function members(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DepartmentMember::class, 'department_id')->orderBy('sort_order');
    }

    /**
     * この部門に割り当てられた貸出・割当履歴とのリレーション。
     */
    public function loans(): HasMany
    {
        return $this->hasMany(EquipmentLoan::class, 'borrower_id')->where('borrower_type', 'staff');
    }
}
