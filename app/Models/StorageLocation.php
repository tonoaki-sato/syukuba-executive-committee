<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StorageLocation extends Model
{
    use HasFactory;

    /**
     * モデルに関連付けるテーブル。
     *
     * @var string
     */
    protected $table = 'comittee_storage_locations';

    /**
     * 複数代入可能な属性。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'contact_person',
        'notes',
    ];

    /**
     * 在庫とのリレーション。
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(EquipmentStock::class, 'storage_location_id');
    }
}
