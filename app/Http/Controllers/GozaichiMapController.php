<?php

namespace App\Http\Controllers;

use App\Models\GozaichiEvent;
use App\Models\GozaichiApplication;
use App\Models\GozaichiMapMarker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class GozaichiMapController extends Controller
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

    protected function checkEditPermission()
    {
        $user = Auth::user();
        if (!$user || (!$user->isSystemAdmin() && !$user->isKanji())) {
            abort(403, 'この操作を行う権限がありません。');
        }
    }

    public function index()
    {
        $event = $this->getOrCreateActiveEvent();
        $user = Auth::user();
        $canEdit = $user->isSystemAdmin() || $user->isKanji();

        // 当選済みかつ未配置（mapMarker が存在しない）の応募データを取得
        $unplacedApplications = GozaichiApplication::where('event_id', $event->id)
            ->where('status', 'accepted')
            ->whereDoesntHave('mapMarker')
            ->get();

        return view('goza.map.index', compact('event', 'canEdit', 'unplacedApplications'));
    }

    public function getMarkers()
    {
        $event = $this->getOrCreateActiveEvent();
        // この年度のマーカーを、応募情報も含めて取得
        $markers = GozaichiMapMarker::where('fiscal_year', $event->fiscal_year)
            ->with('application')
            ->get();

        return response()->json($markers);
    }

    public function storeMarker(Request $request)
    {
        $this->checkEditPermission();
        $event = $this->getOrCreateActiveEvent();

        $request->validate([
            'marker_type' => ['required', 'in:gozaichi,facility,water,event,claim'],
            'sub_type' => ['nullable', 'string', 'max:50'],
            'x_position' => ['required', 'numeric', 'min:0', 'max:100'],
            'y_position' => ['required', 'numeric', 'min:0', 'max:100'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'application_id' => ['nullable', 'integer', 'exists:comittee_gozaichi_applications,id'],
        ]);

        $markerData = $request->all();
        $markerData['fiscal_year'] = $event->fiscal_year;

        // ござ市応募データの紐付け
        if ($request->marker_type === 'gozaichi' && $request->application_id) {
            $app = GozaichiApplication::findOrFail($request->application_id);
            GozaichiMapMarker::where('application_id', $app->id)->delete();
            
            $markerData['name'] = $app->shop_name;
            $markerData['sub_type'] = $app->first_section_type;
        }

        $marker = GozaichiMapMarker::create($markerData);

        return response()->json($marker, 201);
    }

    public function updateMarker(Request $request, $id)
    {
        $this->checkEditPermission();
        $marker = GozaichiMapMarker::findOrFail($id);

        $request->validate([
            'x_position' => ['required', 'numeric', 'min:0', 'max:100'],
            'y_position' => ['required', 'numeric', 'min:0', 'max:100'],
            'name' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);

        $marker->update($request->only(['x_position', 'y_position', 'name', 'description']));

        return response()->json($marker);
    }

    public function deleteMarker($id)
    {
        $this->checkEditPermission();
        $marker = GozaichiMapMarker::findOrFail($id);
        $marker->delete();

        return response()->json(['success' => true]);
    }

    protected function checkAdminPermission()
    {
        $user = Auth::user();
        if (!$user || !$user->isSystemAdmin()) {
            abort(403, 'この操作を行う権限がありません。');
        }
    }

    public function exportPdf()
    {
        $event = $this->getOrCreateActiveEvent();
        $markers = GozaichiMapMarker::where('fiscal_year', $event->fiscal_year)
            ->with('application')
            ->get();

        return view('goza.map.pdf', compact('event', 'markers'));
    }

    public function uploadBaseMap(Request $request)
    {
        $this->checkAdminPermission();

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'map_pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $pdfFile = $request->file('map_pdf');

        $basePath = public_path('images');
        $targetPng = $basePath . '/map_base.png';
        $backupPng = $basePath . '/map_base_backup.png';

        // 1. バックアップの作成
        if (file_exists($targetPng)) {
            copy($targetPng, $backupPng);
        }

        $tempPdfPath = $pdfFile->getRealPath();

        try {
            // 2. Python変換スクリプトの実行
            $result = Process::run([
                'python3',
                base_path('scripts/convert_pdf_to_base.py'),
                $tempPdfPath,
                $targetPng
            ]);

            if (!$result->successful() || !file_exists($targetPng) || filesize($targetPng) === 0) {
                throw new \Exception('PDFの画像変換に失敗しました: ' . $result->errorOutput());
            }

            // 成功時: バックアップの削除とバージョンタイムスタンプの書き込み
            if (file_exists($backupPng)) {
                unlink($backupPng);
            }

            $timestamp = time();
            file_put_contents($basePath . '/map_base_version.txt', $timestamp);

            return response()->json([
                'success' => true,
                'version' => $timestamp
            ]);

        } catch (\Exception $e) {
            // 失敗時: ロールバック
            if (file_exists($backupPng)) {
                rename($backupPng, $targetPng);
            }
            Log::error('ベースマップPDF差し替えエラー: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

