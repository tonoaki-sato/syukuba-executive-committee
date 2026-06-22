<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GozaichiFeeSetting extends Model
{
    protected $table = 'comittee_gozaichi_fee_settings';

    protected $fillable = [
        'event_id',
        'fee_key',
        'fee_value',
    ];

    protected $casts = [
        'event_id' => 'integer',
        'fee_value' => 'integer',
    ];

    /**
     * 所属イベントを取得
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(GozaichiEvent::class, 'event_id');
    }
}
