<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentMaintenanceLog extends Model
{
    use HasFactory;

    /**
     * モデルに関連付けるテーブル。
     *
     * @var string
     */
    protected $table = 'comittee_equipment_maintenance_logs';

    /**
     * 複数代入可能な属性。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fiscal_year',
        'equipment_id',
        'storage_location_id',
        'log_type',
        'quantity',
        'description',
        'recorded_at',
    ];

    /**
     * キャスト属性。
     */
    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    /**
     * 備品マスタとのリレーション。
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    /**
     * 保管場所とのリレーション。
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(StorageLocation::class, 'storage_location_id');
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
