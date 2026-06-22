<?php

namespace App\Http\Controllers;

use App\Models\GozaichiEvent;
use App\Models\GozaichiApplication;
use Illuminate\Http\Request;

class GozaichiApplicationController extends Controller
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
        $applications = $event->applications()->orderBy('created_at', 'desc')->get();
        return view('goza.applications.index', compact('event', 'applications'));
    }

    public function create()
    {
        $event = $this->getOrCreateActiveEvent();
        return view('goza.applications.create', compact('event'));
    }

    protected function validateRequest(Request $request)
    {
        $request->validate([
            'shop_name' => ['required', 'string', 'max:100'],
            'exhibitor_name' => ['required', 'string', 'max:50'],
            'is_member' => ['required', 'boolean'],
            'introducer_name' => ['nullable', 'string', 'max:100'],
            'introducer_contact' => ['nullable', 'string', 'max:100'],
            'section_count' => ['required', 'integer', 'min:1', 'max:3'],
            'first_section_type' => ['required', 'in:general,A,B'],
            'subsequent_section_type' => ['required_if:section_count,2,3', 'nullable', 'in:general,A,B'],
            'has_fire' => ['required', 'boolean'],
            'fire_equipment' => ['required_if:has_fire,1', 'nullable', 'string', 'max:100'],
            'fire_equipment_count' => ['required_if:has_fire,1', 'nullable', 'integer', 'min:1'],
            'fire_fuel' => ['required_if:has_fire,1', 'nullable', 'string', 'max:100'],
            'has_food' => ['required', 'boolean'],
            'has_food_pledge' => ['required_if:has_food,1', 'boolean'],
            'tent' => ['nullable', 'integer', 'min:0'],
            'weight' => ['nullable', 'integer', 'min:0'],
            'desk' => ['nullable', 'integer', 'min:0'],
            'chair' => ['nullable', 'integer', 'min:0'],
            'trash_bag_45' => ['nullable', 'integer', 'min:0'],
            'trash_bag_70' => ['nullable', 'integer', 'min:0'],
        ]);

        // 発電機使用不可のカスタムバリデーション
        if ($request->has_fire) {
            if (
                mb_strpos($request->fire_equipment, '発電機') !== false ||
                mb_strpos($request->fire_fuel, '発電機') !== false
            ) {
                return back()->withErrors(['fire_equipment' => '発電機は使用できません。'])->withInput();
            }
        }
        return null;
    }

    public function store(Request $request)
    {
        $event = $this->getOrCreateActiveEvent();
        $errorResponse = $this->validateRequest($request);
        if ($errorResponse) return $errorResponse;

        $rentals = [
            'tent' => (int)($request->tent ?? 0),
            'weight' => (int)($request->weight ?? 0),
            'desk' => (int)($request->desk ?? 0),
            'chair' => (int)($request->chair ?? 0),
            'trash_bag_45' => (int)($request->trash_bag_45 ?? 0),
            'trash_bag_70' => (int)($request->trash_bag_70 ?? 0),
        ];

        $app = new GozaichiApplication($request->except(['tent', 'weight', 'desk', 'chair', 'trash_bag_45', 'trash_bag_70']));
        $app->event_id = $event->id;
        $app->rentals = $rentals;
        $app->status = 'submitted'; // 代理登録時は最初から応募済状態とする
        $app->calculateFees();
        $app->save();

        return redirect()->route('goza.applications.index')->with('status', '出店応募を新規登録しました。');
    }

    public function show($id)
    {
        $app = GozaichiApplication::findOrFail($id);
        return view('goza.applications.show', compact('app'));
    }

    public function edit($id)
    {
        $app = GozaichiApplication::findOrFail($id);
        $event = $app->event;
        return view('goza.applications.edit', compact('app', 'event'));
    }

    public function update(Request $request, $id)
    {
        $app = GozaichiApplication::findOrFail($id);
        $errorResponse = $this->validateRequest($request);
        if ($errorResponse) return $errorResponse;

        $rentals = [
            'tent' => (int)($request->tent ?? 0),
            'weight' => (int)($request->weight ?? 0),
            'desk' => (int)($request->desk ?? 0),
            'chair' => (int)($request->chair ?? 0),
            'trash_bag_45' => (int)($request->trash_bag_45 ?? 0),
            'trash_bag_70' => (int)($request->trash_bag_70 ?? 0),
        ];

        $app->fill($request->except(['tent', 'weight', 'desk', 'chair', 'trash_bag_45', 'trash_bag_70']));
        $app->rentals = $rentals;
        $app->calculateFees();
        $app->save();

        return redirect()->route('goza.applications.show', $app->id)->with('status', '応募情報を更新しました。');
    }

    public function updateStatus(Request $request, $id)
    {
        $app = GozaichiApplication::findOrFail($id);
        $request->validate([
            'status' => ['required', 'in:draft,submitted,accepted,rejected'],
        ]);

        $app->status = $request->status;
        $app->save();

        return back()->with('status', '応募ステータスを更新しました。');
    }
}
