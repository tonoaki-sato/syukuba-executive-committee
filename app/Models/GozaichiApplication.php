<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GozaichiApplication extends Model
{
    protected $table = 'comittee_gozaichi_applications';

    protected $fillable = [
        'event_id',
        'shop_name',
        'exhibitor_name',
        'is_member',
        'introducer_name',
        'introducer_contact',
        'status',
        'spot_code',
        'section_count',
        'first_section_type',
        'subsequent_section_type',
        'has_fire',
        'fire_equipment',
        'fire_equipment_count',
        'fire_fuel',
        'has_food',
        'has_food_pledge',
        'rentals',
        'exhibition_fee',
        'equipment_fee',
        'equipment_fee_override',
        'trash_bag_fee',
        'total_fee',
        'is_paid',
        'payment_received_at',
        'permit_issued',
    ];

    protected $casts = [
        'event_id' => 'integer',
        'is_member' => 'boolean',
        'section_count' => 'integer',
        'has_fire' => 'boolean',
        'fire_equipment_count' => 'integer',
        'has_food' => 'boolean',
        'has_food_pledge' => 'boolean',
        'rentals' => 'array',
        'exhibition_fee' => 'integer',
        'equipment_fee' => 'integer',
        'equipment_fee_override' => 'integer',
        'trash_bag_fee' => 'integer',
        'total_fee' => 'integer',
        'is_paid' => 'boolean',
        'payment_received_at' => 'datetime',
        'permit_issued' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(GozaichiEvent::class, 'event_id');
    }

    /**
     * 地図上の配置マーカー情報を取得 (hasOne)
     */
    public function mapMarker(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(GozaichiMapMarker::class, 'application_id');
    }

    /**
     * 料金の自動計算および保存
     */
    public function calculateFees(): void
    {
        $fees = $this->event ? $this->event->fees : [];

        // フォールバック用の単価定義
        $defaults = [
            'member_1st' => 2000,
            'member_general_2nd' => 3000,
            'member_A_2nd' => 4000,
            'member_B_2nd' => 5000,
            'general_1st' => 6000,
            'general_A_1st' => 8000,
            'general_B_1st' => 10000,
            'general_2nd' => 6000,
            'general_A_2nd' => 8000,
            'general_B_2nd' => 10000,
            'tent' => 4500,
            'weight' => 500,
            'desk' => 2500,
            'chair' => 500,
            'trash_45' => 500,
            'trash_70' => 700,
        ];

        $getFee = function ($key) use ($fees, $defaults) {
            return $fees[$key] ?? $defaults[$key];
        };

        // ①出店料の計算
        $exhibitionFee = 0;
        if ($this->is_member) {
            // 加盟店: 1区画目は一律2,000円
            $exhibitionFee += $getFee('member_1st');

            // 2区画目以降
            if ($this->section_count > 1 && $this->subsequent_section_type) {
                $subsequentFeeKey = 'member_' . $this->subsequent_section_type . '_2nd';
                $exhibitionFee += $getFee($subsequentFeeKey) * ($this->section_count - 1);
            }
        } else {
            // 一般: 1区画目
            $firstFeeKey = 'general_' . $this->first_section_type . '_1st';
            if ($this->first_section_type === 'general') {
                $firstFeeKey = 'general_1st';
            }
            $exhibitionFee += $getFee($firstFeeKey);

            // 2区画目以降
            if ($this->section_count > 1 && $this->subsequent_section_type) {
                $subsequentFeeKey = 'general_' . $this->subsequent_section_type . '_2nd';
                if ($this->subsequent_section_type === 'general') {
                    $subsequentFeeKey = 'general_2nd';
                }
                $exhibitionFee += $getFee($subsequentFeeKey) * ($this->section_count - 1);
            }
        }

        // ②備品貸出料の計算
        $rentals = $this->rentals ?: [];
        $tentCount = (int)($rentals['tent'] ?? 0);
        $weightCount = (int)($rentals['weight'] ?? 0);
        $deskCount = (int)($rentals['desk'] ?? 0);
        $chairCount = (int)($rentals['chair'] ?? 0);

        $equipmentFeeCalculated = ($tentCount * $getFee('tent'))
            + ($weightCount * $getFee('weight'))
            + ($deskCount * $getFee('desk'))
            + ($chairCount * $getFee('chair'));

        // ③ゴミ袋料の計算
        $trashBag45Count = (int)($rentals['trash_bag_45'] ?? 0);
        $trashBag70Count = (int)($rentals['trash_bag_70'] ?? 0);

        $trash45Fee = 0;
        if (!$this->is_member) {
            // 一般出店者には45Lが2枚無料枠
            $chargeable45 = max(0, $trashBag45Count - 2);
            $trash45Fee = $chargeable45 * $getFee('trash_45');
        } else {
            $trash45Fee = $trashBag45Count * $getFee('trash_45');
        }

        $trash70Fee = $trashBag70Count * $getFee('trash_70');
        $trashBagFee = $trash45Fee + $trash70Fee;

        // 金額のセット
        $this->exhibition_fee = $exhibitionFee;
        $this->equipment_fee = $equipmentFeeCalculated;
        $this->trash_bag_fee = $trashBagFee;

        $finalEquipmentFee = $this->equipment_fee_override !== null ? $this->equipment_fee_override : $equipmentFeeCalculated;
        $this->total_fee = $exhibitionFee + $finalEquipmentFee + $trashBagFee;
    }
}
