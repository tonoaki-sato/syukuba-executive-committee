<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentStock extends Model
{
    use HasFactory;

    /**
     * モデルに関連付けるテーブル。
     *
     * @var string
     */
    protected $table = 'comittee_equipment_stocks';

    /**
     * 複数代入可能な属性。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'equipment_id',
        'storage_location_id',
        'quantity',
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
}
