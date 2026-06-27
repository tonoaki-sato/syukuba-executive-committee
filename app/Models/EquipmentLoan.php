<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentLoan extends Model
{
    use HasFactory;

    /**
     * モデルに関連付けるテーブル。
     *
     * @var string
     */
    protected $table = 'comittee_equipment_loans';

    /**
     * 複数代入可能な属性。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fiscal_year',
        'equipment_id',
        'borrower_type',
        'borrower_id',
        'quantity_requested',
        'quantity_loaned',
        'quantity_returned',
        'loaned_at',
        'returned_at',
        'status',
        'notes',
    ];

    /**
     * キャスト属性。
     */
    protected $casts = [
        'loaned_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    /**
     * 備品マスタとのリレーション。
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    /**
     * 指定年度のスコープ。
     */
    public function scopeForFiscalYear(Builder $query, ?int $year = null): Builder
    {
        $year = $year ?: session('active_fiscal_year', date('Y'));
        return $query->where('fiscal_year', $year);
    }
}
