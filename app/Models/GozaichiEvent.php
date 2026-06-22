<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GozaichiEvent extends Model
{
    protected $table = 'comittee_gozaichi_events';

    protected $fillable = [
        'fiscal_year',
        'recruitment_start_at',
        'recruitment_end_at',
        'recruitment_status',
        'is_active',
    ];

    protected $casts = [
        'fiscal_year' => 'integer',
        'recruitment_start_at' => 'datetime',
        'recruitment_end_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * このイベントに属する料金設定
     */
    public function feeSettings(): HasMany
    {
        return $this->hasMany(GozaichiFeeSetting::class, 'event_id');
    }

    /**
     * このイベントに属する出店応募
     */
    public function applications(): HasMany
    {
        return $this->hasMany(GozaichiApplication::class, 'event_id');
    }

    /**
     * 料金設定をキーと値のペアで取得
     */
    public function getFeesAttribute(): array
    {
        return $this->feeSettings->pluck('fee_value', 'fee_key')->toArray();
    }
}
