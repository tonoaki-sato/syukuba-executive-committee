<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class DocumentController extends Controller
{
    /**
     * 公開可能な文書のリスト
     */
    private $allowedDocuments = [
        'organization_chart' => [
            'name' => '2026年度 実行委員会組織図（案）',
            'description' => '2026年度保土ケ谷宿場まつり実行委員会の各部会および役割分担を示した組織図です。',
            'path' => 'docs/2026_保土ケ谷宿場まつり実行委員会_組織図_案.pdf',
            'mime' => 'application/pdf',
            'category' => '組織・体制',
            'icon' => '📁'
        ],
        'patrol_security_plan' => [
            'name' => '安全管理・警備巡回計画書',
            'description' => 'まつり当日の警備・防災体制および緊急時の連絡エスカレーションフローを定義した公式文書です。',
            'path' => 'docs/patrol/patrol_security_plan.pdf',
            'mime' => 'application/pdf',
            'category' => '安全・防災',
            'icon' => '🚨'
        ],
    ];

    /**
     * 文書一覧を表示
     */
    public function index()
    {
        return view('documents.index', [
            'documents' => $this->allowedDocuments
        ]);
    }

    /**
     * 指定された文書を安全に表示（ダウンロード・インライン表示）
     */
    public function show($key)
    {
        if (!array_key_exists($key, $this->allowedDocuments)) {
            abort(404);
        }

        $doc = $this->allowedDocuments[$key];
        $filePath = base_path($doc['path']);

        if (!File::exists($filePath)) {
            abort(404);
        }

        return response()->file($filePath, [
            'Content-Type' => $doc['mime'],
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"'
        ]);
    }
}
