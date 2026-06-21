<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meeting extends Model
{
    /**
     * このモデルに関連付けるテーブル。
     *
     * @var string
     */
    protected $table = 'comittee_meetings';

    /**
     * 複数代入可能な属性。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fiscal_year',
        'type',
        'name',
        'held_at',
        'location',
        'agenda',
        'minutes',
        'whiteboard_images',
    ];

    /**
     * キャストが必要な属性。
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'held_at' => 'datetime',
            'whiteboard_images' => 'array',
        ];
    }

    /**
     * この会議に参加する（した）ユーザー一覧を取得 (belongsToMany)
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'comittee_meeting_participants', 'meeting_id', 'user_id')
            ->withPivot('status', 'note')
            ->withTimestamps();
    }

    /**
     * 出欠登録レコードの一覧を取得 (hasMany)
     */
    public function participants(): HasMany
    {
        return $this->hasMany(MeetingParticipant::class, 'meeting_id');
    }
}
