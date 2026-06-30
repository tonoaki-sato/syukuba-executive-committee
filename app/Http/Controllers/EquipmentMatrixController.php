<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentLoan;
use App\Models\EquipmentRentalSummary;
use Illuminate\Http\Request;

class EquipmentMatrixController extends Controller
{
    /**
     * 部門別コスト配分マトリクス画面を表示する。
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $fiscalYear = session('active_fiscal_year', 2026);

        // 1. レンタル機材および付帯諸経費を取得
        $equipments = Equipment::where('fiscal_year', $fiscalYear)
            ->where('ownership_type', 'rental')
            ->orderByRaw("CASE category 
                WHEN '什器・テント' THEN 1 
                WHEN '音響・電気' THEN 2 
                WHEN '保安・防災' THEN 3 
                WHEN '看板・装飾' THEN 4 
                WHEN '諸経費・サービス' THEN 5 
                ELSE 6 
            END")
            ->orderBy('id', 'asc')
            ->get();

        // 2. 部門リスト（割当先）を取得
        $departments = Department::where('fiscal_year', $fiscalYear)
            ->orderBy('id', 'asc')
            ->get();

        // 3. 貸出・割当データを取得し、[equipment_id-borrower_id] でグループ化
        $loans = EquipmentLoan::where('fiscal_year', $fiscalYear)
            ->where('borrower_type', 'staff')
            ->get()
            ->groupBy(function ($item) {
                return $item->equipment_id . '-' . $item->borrower_id;
            });

        // 4. レンタル全体集計（特別値引きや消費税率）を取得
        $summary = EquipmentRentalSummary::where('fiscal_year', $fiscalYear)->first() 
            ?? new EquipmentRentalSummary([
                'fiscal_year' => $fiscalYear,
                'special_discount' => 0,
                'tax_rate' => 10.00,
            ]);

        // 5. ござ市（出店者）への割当合計も集計（PDF内訳表の「※ござ市出店者用貸与品」に対応）
        $gozaichiLoans = EquipmentLoan::where('fiscal_year', $fiscalYear)
            ->where('borrower_type', 'gozaichi')
            ->get()
            ->groupBy('equipment_id');
        $canManage = auth()->user() ? auth()->user()->canManageEquipment($fiscalYear) : false;

        return view('equipment.matrix', compact(
            'fiscalYear',
            'equipments',
            'departments',
            'loans',
            'summary',
            'gozaichiLoans',
            'canManage'
        ));
    }
}
