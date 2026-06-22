<?php

namespace App\Http\Controllers;

use App\Models\GozaichiEvent;
use App\Models\GozaichiApplication;
use Illuminate\Http\Request;

class GozaichiSpotController extends Controller
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

    public function index()
    {
        $event = $this->getOrCreateActiveEvent();
        // 当選済みの応募を取得
        $applications = $event->applications()->where('status', 'accepted')->get();
        return view('goza.spots.index', compact('event', 'applications'));
    }

    public function update(Request $request, $id)
    {
        $app = GozaichiApplication::findOrFail($id);
        
        $request->validate([
            'spot_code' => ['nullable', 'string', 'max:20'],
        ]);

        $spotCode = $request->spot_code;

        // 重複チェック
        if ($spotCode) {
            $duplicate = GozaichiApplication::where('event_id', $app->event_id)
                ->where('id', '!=', $app->id)
                ->where('spot_code', $spotCode)
                ->first();

            if ($duplicate) {
                return back()->withErrors(['spot_code' => "区画コード「{$spotCode}」はすでに「{$duplicate->shop_name}」に割り当てられています。"])->withInput();
            }
        }

        $app->spot_code = $spotCode;
        $app->save();

        // 警告の検知（フラッシュメッセージ用）
        $warning = null;
        if ($spotCode && ($app->first_section_type === 'B' || $app->subsequent_section_type === 'B')) {
            // 火器使用飲食(B)の場合に3方幕テントが必要という警告
            $warning = "【警告】屋号:「{$app->shop_name}」は火器使用飲食（B区画）として配置されました。調理を伴うため、3方幕テントの準備が必要です。";
        }

        if ($warning) {
            return redirect()->route('goza.spots.index')->with('status', '出店場所を更新しました。')->with('warning', $warning);
        }

        return redirect()->route('goza.spots.index')->with('status', '出店場所を更新しました。');
    }
}
