<?php

namespace App\Http\Controllers;

use App\Models\GozaichiEvent;
use App\Models\GozaichiApplication;
use Illuminate\Http\Request;

class GozaichiPaymentController extends Controller
{
    protected function getOrCreateActiveEvent()
    {
        $activeYear = session('active_fiscal_year', date('Y'));
        $event = GozaichiEvent::where('fiscal_year', $activeYear)->first();
        if (!$event) {
            $event = GozaichiEvent::create([
                'fiscal_year' => $activeYear,
                'recruitment_status' => 'closed',
                'is_active' => true,
            ]);
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
            foreach ($defaults as $key => $val) {
                $event->feeSettings()->create([
                    'fee_key' => $key,
                    'fee_value' => $val,
                ]);
            }
        }
        return $event;
    }

    public function index(Request $request)
    {
        $event = $this->getOrCreateActiveEvent();
        $query = $event->applications()->where('status', 'accepted');
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('shop_name', 'like', "%{$search}%")
                  ->orWhere('exhibitor_name', 'like', "%{$search}%")
                  ->orWhere('spot_code', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('is_paid')) {
            $query->where('is_paid', $request->is_paid === '1');
        }

        $applications = $query->orderBy('spot_code')->get();
        return view('goza.payments.index', compact('event', 'applications'));
    }

    public function receive(Request $request, $id)
    {
        $app = GozaichiApplication::findOrFail($id);
        
        $request->validate([
            'equipment_fee_override' => ['nullable', 'integer', 'min:0'],
            'permit_issued' => ['nullable', 'boolean'],
        ]);

        if ($request->has('equipment_fee_override')) {
            $val = $request->equipment_fee_override;
            $app->equipment_fee_override = $val !== '' && $val !== null ? (int)$val : null;
        }

        $app->permit_issued = $request->boolean('permit_issued');
        $app->is_paid = true;
        $app->payment_received_at = now();
        $app->calculateFees();
        $app->save();

        return redirect()->route('goza.payments.index')->with('status', "屋号:「{$app->shop_name}」の料金受領処理を完了しました。");
    }

    public function receipt($id)
    {
        $app = GozaichiApplication::findOrFail($id);
        if (!$app->is_paid) {
            return redirect()->route('goza.payments.index')->with('error', '未入金の出店者は領収書を発行できません。');
        }
        return view('goza.payments.receipt', compact('app'));
    }

    public function permit($id)
    {
        $app = GozaichiApplication::findOrFail($id);
        if (!$app->is_paid) {
            return redirect()->route('goza.payments.index')->with('error', '未入金の出店者は出店許可証を発行できません。');
        }
        return view('goza.payments.permit', compact('app'));
    }
}
