<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    use HasFactory;

    /**
     * モデルに関連付けるテーブル。
     *
     * @var string
     */
    protected $table = 'comittee_equipments';

    /**
     * 複数代入可能な属性。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fiscal_year',
        'ownership_type',
        'name',
        'specifications',
        'quantity',
        'unit',
        'unit_price',
        'category',
        'image_path',
        'description',
    ];

    /**
     * 追加のカスタム属性（JSONシリアライズ等で自動追加させたい場合）。
     */
    protected $appends = ['total_amount'];

    /**
     * 金額（数量 × 単価）を取得するアクセサ。
     */
    public function getTotalAmountAttribute(): int
    {
        return $this->quantity * ($this->unit_price ?? 0);
    }

    /**
     * 拠点別在庫とのリレーション。
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(EquipmentStock::class, 'equipment_id');
    }

    /**
     * 貸出・割当履歴とのリレーション。
     */
    public function loans(): HasMany
    {
        return $this->hasMany(EquipmentLoan::class, 'equipment_id');
    }

    /**
     * 破損・補充メンテナンス履歴とのリレーション。
     */
    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(EquipmentMaintenanceLog::class, 'equipment_id');
    }

    /**
     * 物理的な在庫管理対象の備品のみを抽出するスコープ。
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePhysicalItems($query)
    {
        return $query->where('category', '!=', '諸経費・サービス');
    }
}
