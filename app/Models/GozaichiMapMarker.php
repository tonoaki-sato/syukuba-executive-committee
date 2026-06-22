<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GozaichiMapMarker extends Model
{
    protected $table = 'comittee_map_markers';

    protected $fillable = [
        'fiscal_year',
        'marker_type',
        'sub_type',
        'x_position',
        'y_position',
        'name',
        'description',
        'application_id',
    ];

    protected $casts = [
        'fiscal_year' => 'integer',
        'x_position' => 'double',
        'y_position' => 'double',
        'application_id' => 'integer',
    ];

    /**
     * 紐づく応募データを取得
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(GozaichiApplication::class, 'application_id');
    }
}
