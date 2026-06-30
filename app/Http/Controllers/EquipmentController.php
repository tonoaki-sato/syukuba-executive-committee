<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentLoan;
use App\Models\EquipmentMaintenanceLog;
use App\Models\EquipmentRentalSummary;
use App\Models\EquipmentStock;
use App\Models\StorageLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EquipmentController extends Controller
{
    /**
     * 備品ダッシュボード・一覧表示
     */
    public function index()
    {
        $year = session('active_fiscal_year', date('Y'));

        // 年度での絞り込みを適用
        $equipments = Equipment::where('fiscal_year', $year)->orderBy('id', 'asc')->get();
        $locations = StorageLocation::orderBy('id', 'asc')->get();
        
        // 拠点別在庫（物理備品のみにスコープ）
        $stocks = EquipmentStock::whereHas('equipment', function ($query) use ($year) {
                $query->where('fiscal_year', $year)->physicalItems();
            })
            ->with(['equipment', 'location'])
            ->get();
        
        $loans = EquipmentLoan::with(['equipment'])
            ->forFiscalYear($year)
            ->orderBy('created_at', 'desc')
            ->get();

        $maintenanceLogs = EquipmentMaintenanceLog::with(['equipment', 'location'])
            ->forFiscalYear($year)
            ->orderBy('recorded_at', 'desc')
            ->get();

        $canManage = Auth::user()->canManageEquipment($year);

        // レンタル備品全体の集計（見積額、値引き、消費税、税込請求総額）の算出
        $rentalSubtotal = 0;
        $rentalDiscount = 0;
        $rentalTax = 0;
        $rentalGrandTotal = 0;

        if ($canManage) {
            $rentalSubtotal = Equipment::where('fiscal_year', $year)
                ->where('ownership_type', 'rental')
                ->get()
                ->sum('total_amount');

            $summary = EquipmentRentalSummary::where('fiscal_year', $year)->first();
            if ($summary) {
                $rentalDiscount = $summary->special_discount;
                $taxable = max(0, $rentalSubtotal - $rentalDiscount);
                $rentalTax = floor($taxable * ($summary->tax_rate / 100));
                $rentalGrandTotal = $taxable + $rentalTax;
            } else {
                $taxable = $rentalSubtotal;
                $rentalTax = floor($taxable * 0.10);
                $rentalGrandTotal = $taxable + $rentalTax;
            }
        }

        // 一般ユーザー向け：単価・金額情報を完全に秘匿化する
        if (!$canManage) {
            $equipments->each(function ($eq) {
                $eq->makeHidden(['unit_price', 'total_amount']);
                $eq->unit_price = null;
            });
            $stocks->each(function ($st) {
                if ($st->equipment) {
                    $st->equipment->makeHidden(['unit_price', 'total_amount']);
                    $st->equipment->unit_price = null;
                }
            });
            $loans->each(function ($ln) {
                if ($ln->equipment) {
                    $ln->equipment->makeHidden(['unit_price', 'total_amount']);
                    $ln->equipment->unit_price = null;
                }
            });
            $maintenanceLogs->each(function ($mt) {
                if ($mt->equipment) {
                    $mt->equipment->makeHidden(['unit_price', 'total_amount']);
                    $mt->equipment->unit_price = null;
                }
            });
        }

        return view('equipment.index', compact(
            'equipments',
            'locations',
            'stocks',
            'loans',
            'maintenanceLogs',
            'canManage',
            'rentalSubtotal',
            'rentalDiscount',
            'rentalTax',
            'rentalGrandTotal',
            'year'
        ));
    }

    /**
     * 備品マスタ新規登録
     */
    public function storeMaster(Request $request)
    {
        $validated = $request->validate([
            'ownership_type' => 'required|in:owned,rental',
            'name' => 'required|string|max:100',
            'specifications' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string|max:20',
            'unit_price' => 'nullable|integer|min:0',
            'category' => 'required|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('equipments', 'public');
            $validated['image_path'] = $path;
        }

        unset($validated['image']);

        $validated['fiscal_year'] = session('active_fiscal_year', date('Y'));

        Equipment::create($validated);

        return redirect()->route('equipment.index')->with('status', '備品を登録しました。');
    }

    /**
     * 備品マスタ編集・更新
     */
    public function updateMaster(Request $request, $id)
    {
        $equipment = Equipment::findOrFail($id);

        $validated = $request->validate([
            'ownership_type' => 'required|in:owned,rental',
            'name' => 'required|string|max:100',
            'specifications' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string|max:20',
            'unit_price' => 'nullable|integer|min:0',
            'category' => 'required|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            // 古い画像の削除
            if ($equipment->image_path) {
                Storage::disk('public')->delete($equipment->image_path);
            }
            $path = $request->file('image')->store('equipments', 'public');
            $validated['image_path'] = $path;
        }

        unset($validated['image']);

        $equipment->update($validated);

        return redirect()->route('equipment.index')->with('status', '備品情報を更新しました。');
    }

    /**
     * 備品マスタ削除
     */
    public function destroyMaster($id)
    {
        $equipment = Equipment::findOrFail($id);

        // 関連する画像の物理削除
        if ($equipment->image_path) {
            Storage::disk('public')->delete($equipment->image_path);
        }

        $equipment->delete();

        return redirect()->route('equipment.index')->with('status', '備品を削除しました。');
    }

    /**
     * 保管場所新規登録
     */
    public function storeLocation(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'contact_person' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        StorageLocation::create($validated);

        return redirect()->route('equipment.index')->with('status', '保管場所を追加しました。');
    }

    /**
     * 拠点別在庫の手動調整
     */
    public function adjustStock(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => 'required|exists:comittee_equipments,id',
            'storage_location_id' => 'required|exists:comittee_storage_locations,id',
            'quantity' => 'required|integer|min:0',
        ]);

        EquipmentStock::updateOrCreate(
            [
                'equipment_id' => $validated['equipment_id'],
                'storage_location_id' => $validated['storage_location_id']
            ],
            [
                'quantity' => $validated['quantity']
            ]
        );

        return redirect()->route('equipment.index')->with('status', '在庫数量を調整しました。');
    }

    /**
     * 貸出・割当の登録
     */
    public function storeLoan(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => 'required|exists:comittee_equipments,id',
            'borrower_type' => 'required|in:gozaichi,staff',
            'borrower_id' => 'required|integer',
            'quantity_requested' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $year = session('active_fiscal_year', date('Y'));
        $validated['fiscal_year'] = $year;
        $validated['status'] = 'pending';

        EquipmentLoan::create($validated);

        return redirect()->route('equipment.index')->with('status', '貸出割当を登録しました。');
    }

    /**
     * 貸出ステータスの更新
     */
    public function updateLoanStatus(Request $request, $id)
    {
        $loan = EquipmentLoan::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,loaned,returned,partial,lost',
            'quantity_loaned' => 'required|integer|min:0',
            'quantity_returned' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validated['status'] === 'loaned' && !$loan->loaned_at) {
            $loan->loaned_at = now();
        }
        if ($validated['status'] === 'returned' && !$loan->returned_at) {
            $loan->returned_at = now();
        }

        $loan->update($validated);

        return redirect()->route('equipment.index')->with('status', '貸出ステータスを更新しました。');
    }

    /**
     * 破損・補充ログの登録
     */
    public function storeMaintenance(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => 'required|exists:comittee_equipments,id',
            'storage_location_id' => 'nullable|exists:comittee_storage_locations,id',
            'log_type' => 'required|in:repair,discard,lost,replenish',
            'quantity' => 'required|integer|min:1',
            'description' => 'required|string',
        ]);

        $year = session('active_fiscal_year', date('Y'));
        $validated['fiscal_year'] = $year;
        $validated['recorded_at'] = now();

        DB::transaction(function () use ($validated) {
            EquipmentMaintenanceLog::create($validated);

            // 廃棄 (discard) または 紛失 (lost) の場合、実在庫および総保有数をマイナスする
            if (in_array($validated['log_type'], ['discard', 'lost'])) {
                if ($validated['storage_location_id']) {
                    $stock = EquipmentStock::where('equipment_id', $validated['equipment_id'])
                        ->where('storage_location_id', $validated['storage_location_id'])
                        ->first();
                    if ($stock) {
                        $stock->quantity = max(0, $stock->quantity - $validated['quantity']);
                        $stock->save();
                    }
                }

                $equipment = Equipment::find($validated['equipment_id']);
                if ($equipment) {
                    $equipment->quantity = max(0, $equipment->quantity - $validated['quantity']);
                    $equipment->save();
                }
            }

            // 新規購入補充 (replenish) の場合、総保有数および実在庫をプラスする
            if ($validated['log_type'] === 'replenish') {
                if ($validated['storage_location_id']) {
                    $stock = EquipmentStock::firstOrNew([
                        'equipment_id' => $validated['equipment_id'],
                        'storage_location_id' => $validated['storage_location_id']
                    ]);
                    $stock->quantity += $validated['quantity'];
                    $stock->save();
                }

                $equipment = Equipment::find($validated['equipment_id']);
                if ($equipment) {
                    $equipment->quantity += $validated['quantity'];
                    $equipment->save();
                }
            }
        });

        return redirect()->route('equipment.index')->with('status', 'メンテナンス・破損補充ログを記録しました。');
    }

    /**
     * 前年度から備品および保管場所データをコピー引き継ぎ
     */
    public function copyFromPreviousYear()
    {
        $currentYear = session('active_fiscal_year', date('Y'));
        $previousYear = $currentYear - 1;

        // 本年度の備品マスタがすでに存在しているかチェック
        $exists = Equipment::where('fiscal_year', $currentYear)->exists();
        if ($exists) {
            return redirect()->route('equipment.index')->with('status', '本年度の備品データはすでに存在します。');
        }

        // 前年度の備品データを取得
        $prevEquipments = Equipment::where('fiscal_year', $previousYear)->get();

        if ($prevEquipments->isEmpty()) {
            return redirect()->route('equipment.index')->with('status', '前年度の備品データが見つかりませんでした。');
        }

        DB::transaction(function () use ($prevEquipments, $currentYear) {
            foreach ($prevEquipments as $prevEq) {
                // 備品を本年度用にコピー
                $newEq = $prevEq->replicate();
                $newEq->fiscal_year = $currentYear;
                $newEq->save();

                // 拠点別在庫もコピー
                $prevStocks = EquipmentStock::where('equipment_id', $prevEq->id)->get();
                foreach ($prevStocks as $prevSt) {
                    $newSt = $prevSt->replicate();
                    $newSt->equipment_id = $newEq->id;
                    $newSt->save();
                }
            }

            // 部門（Departments）もコピーする
            $prevDepts = \App\Models\Department::where('fiscal_year', $previousYear)->get();
            foreach ($prevDepts as $prevDept) {
                $newDept = $prevDept->replicate();
                $newDept->fiscal_year = $currentYear;
                $newDept->save();
            }

            // レンタル全体集計もコピーする
            $prevSummary = \App\Models\EquipmentRentalSummary::where('fiscal_year', $previousYear)->first();
            if ($prevSummary) {
                $newSummary = $prevSummary->replicate();
                $newSummary->fiscal_year = $currentYear;
                $newSummary->save();
            }
        });

        Log::info("{$previousYear} 年度から {$currentYear} 年度への備品データ移行処理が実行されました。");

        return redirect()->route('equipment.index')->with('status', "{$previousYear} 年度から本年度（{$currentYear} 年度）へ備品データを引き継ぎました。");
    }

    /**
     * レンタル全体集計（特別値引き・税率）の更新
     */
    public function updateRentalSummary(Request $request)
    {
        $year = session('active_fiscal_year', date('Y'));

        $validated = $request->validate([
            'special_discount' => 'required|integer|min:0',
            'tax_rate' => 'required|numeric|between:0,99.99',
            'notes' => 'nullable|string',
        ]);

        \App\Models\EquipmentRentalSummary::updateOrCreate(
            ['fiscal_year' => $year],
            $validated
        );

        return redirect()->route('equipment.index')->with('status', 'レンタル費用集計設定を更新しました。');
    }
}
