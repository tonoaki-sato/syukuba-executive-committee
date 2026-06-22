<?php

namespace App\Http\Controllers;

use App\Models\GozaichiEvent;
use App\Models\GozaichiFeeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GozaichiSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->isSystemAdmin()) {
                abort(403, '募集設定の変更はシステム管理者のみ可能です。');
            }
            return $next($request);
        });
    }

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

    public function index()
    {
        $event = $this->getOrCreateActiveEvent();
        $feeSettings = $event->feeSettings;
        return view('goza.settings.index', compact('event', 'feeSettings'));
    }

    public function update(Request $request)
    {
        $event = $this->getOrCreateActiveEvent();

        $request->validate([
            'recruitment_start_at' => ['nullable', 'date'],
            'recruitment_end_at' => ['nullable', 'date', 'after_or_equal:recruitment_start_at'],
            'recruitment_status' => ['required', 'in:closed,open'],
            'fees' => ['required', 'array'],
            'fees.*' => ['required', 'integer', 'min:0'],
        ]);

        $event->update([
            'recruitment_start_at' => $request->recruitment_start_at,
            'recruitment_end_at' => $request->recruitment_end_at,
            'recruitment_status' => $request->recruitment_status,
        ]);

        foreach ($request->fees as $key => $val) {
            $event->feeSettings()->updateOrCreate(
                ['fee_key' => $key],
                ['fee_value' => (int)$val]
            );
        }

        // すでに登録されている応募も料金再計算を行う
        foreach ($event->applications as $app) {
            $app->calculateFees();
            $app->save();
        }

        return redirect()->route('goza.settings.index')->with('status', '募集設定および料金マスタを更新しました。');
    }
}
