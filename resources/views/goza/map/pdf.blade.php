@php
    $mapVersionFile = public_path('images/map_base_version.txt');
    $mapVersion = file_exists($mapVersionFile) ? trim(file_get_contents($mapVersionFile)) : time();
@endphp
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>保土ケ谷宿場まつり ござ市・設備配置図 ({{ $event->fiscal_year }}年度)</title>
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
            color: #000;
        }
        .page-container {
            width: 800px;
            margin: 0 auto;
            position: relative;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            background-color: #fff;
        }
        .header {
            text-align: center;
            padding: 20px 0 10px 0;
            border-bottom: 2px solid #000;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 22px;
            margin: 0 0 5px 0;
        }
        .header p {
            font-size: 12px;
            color: #555;
            margin: 0;
        }
        
        /* マップ描画用のエリア */
        .map-container {
            position: relative;
            width: 800px;
            height: 1130px;
            background-image: url('{{ asset('images/map_base.png') }}?v={{ $mapVersion }}');
            background-size: 100% 100%;
            background-repeat: no-repeat;
        }

        
        /* マップ上のピン */
        .map-pin {
            position: absolute;
            width: 24px;
            height: 24px;
            margin-left: -12px;
            margin-top: -12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            border-radius: 50%;
            box-shadow: 0 1px 3px rgba(0,0,0,0.3);
            z-index: 10;
        }
        
        .pin-gozaichi { border: 1.5px solid #fff; }
        .pin-gozaichi.sub-B { background-color: #dc3545; color: #fff; }
        .pin-gozaichi.sub-A { background-color: #198754; color: #fff; }
        .pin-gozaichi.sub-general { background-color: #212529; color: #fff; }
        
        .pin-facility { background-color: #fff; border: 1.5px solid #fd7e14; color: #fd7e14; }
        .pin-water { background-color: #e0f2fe; border: 1.5px solid #0284c7; color: #0284c7; }
        .pin-event { background-color: #fffbeb; border: 1.5px solid #d97706; color: #d97706; }
        .pin-claim { background-color: #fef2f2; border: 1.5px solid #dc2626; color: #dc2626; font-weight: bold; }

        /* 給水カバー円 (印刷用) */
        .water-circle {
            position: absolute;
            border: 1.5px dashed #0284c7;
            border-radius: 50%;
            background-color: rgba(2, 132, 199, 0.03);
            pointer-events: none;
            z-index: 2;
            transform: translate(-50%, -50%);
        }

        /* 凡例 */
        .legend-box {
            position: absolute;
            bottom: 20px;
            left: 20px;
            border: 1px solid #000;
            background-color: #fff;
            padding: 10px;
            font-size: 10px;
            z-index: 20;
            width: 250px;
        }
        .legend-title {
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
            margin-bottom: 5px;
        }
        .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 3px;
        }
        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
            display: inline-block;
        }
        
        .print-btn-container {
            text-align: center;
            margin: 20px 0;
        }
        .print-btn {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 25px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        @media print {
            .print-btn-container {
                display: none;
            }
            .page-container {
                box-shadow: none;
                margin: 0;
                width: 100%;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>

<div class="print-btn-container">
    <button class="print-btn" onclick="window.print()">この配置図を印刷 / PDF保存</button>
</div>

<div class="page-container">
    <div class="header">
        <h1>保土ケ谷宿場まつり ござ市・設備配置図</h1>
        <p>対象年度: {{ $event->fiscal_year }}年度 ｜ 保土ケ谷宿場まつり実行委員会</p>
    </div>

    <div class="map-container">
        <!-- SVGベースマップ (indexと同様) -->
        <svg width="800" height="1130" viewBox="0 0 800 1130" xmlns="http://www.w3.org/2000/svg" style="position: absolute; top:0; left:0; width:100%; height:100%; pointer-events: none; z-index: 1;">
            <!-- スナップガイド線 (JSで吸い付き判定を行う基準線、デバッグ用に点線で薄く描画) -->
            <line x1="240" y1="0" x2="240" y2="1130" stroke="#8c1d30" stroke-width="1" stroke-dasharray="2,2" opacity="0.3"/>
            <line x1="284" y1="0" x2="284" y2="1130" stroke="#8c1d30" stroke-width="1" stroke-dasharray="2,2" opacity="0.3"/>
            <line x1="602" y1="0" x2="602" y2="1130" stroke="#8c1d30" stroke-width="1" stroke-dasharray="2,2" opacity="0.3"/>
            <line x1="646" y1="0" x2="646" y2="1130" stroke="#8c1d30" stroke-width="1" stroke-dasharray="2,2" opacity="0.3"/>
        </svg>

        <!-- 保健所の給水カバー円 (印刷プレビュー用) -->
        @foreach($markers->where('marker_type', 'water') as $wm)
            <div class="water-circle" style="left: {{ $wm->x_position }}%; top: {{ $wm->y_position }}%; width: 240px; height: 240px;"></div>
        @endforeach

        <!-- マーカーの描画 -->
        @foreach($markers as $m)
            @php
                $icon = '📍';
                if ($m->marker_type === 'gozaichi') $icon = $m->sub_type === 'B' ? '🔥' : ($m->sub_type === 'A' ? '🥗' : '🛍️');
                else if ($m->marker_type === 'facility') {
                    if ($m->sub_type === 'trash') $icon = '🗑️';
                    else if ($m->sub_type === 'speaker') $icon = '🔊';
                    else if ($m->sub_type === 'toilet') $icon = '🚾';
                    else if ($m->sub_type === 'cone') $icon = '🚧';
                }
                else if ($m->marker_type === 'water') $icon = '🚰';
                else if ($m->marker_type === 'claim') $icon = '⚠️';
            @endphp
            <div class="map-pin pin-{{ $m->marker_type }} sub-{{ $m->sub_type }}" style="left: {{ $m->x_position }}%; top: {{ $m->y_position }}%;" title="{{ $m->name }}">
                {!! $icon !!}
            </div>
        @endforeach

        <!-- 凡例 -->
        <div class="legend-box">
            <div class="legend-title">凡例</div>
            <div class="legend-item"><span class="legend-color" style="background-color: #dc3545;"></span>火器使用飲食 (赤)</div>
            <div class="legend-item"><span class="legend-color" style="background-color: #198754;"></span>火器なし食品 (緑)</div>
            <div class="legend-item"><span class="legend-color" style="background-color: #212529;"></span>一般物販 (黒)</div>
            <div class="legend-item"><span class="legend-color" style="background-color: #fff; border: 1.5px solid #fd7e14; height: 9px; width: 9px;"></span>付帯設備 (スピーカー・ゴミ箱等)</div>
            <div class="legend-item"><span class="legend-color" style="background-color: #e0f2fe; border: 1.5px solid #0284c7; height: 9px; width: 9px;"></span>給水設備 (カバー円: 半径20m)</div>
            <div class="legend-item"><span class="legend-color" style="background-color: #fef2f2; border: 1.5px solid #dc2626; height: 9px; width: 9px;"></span>過去クレーム地点 (⚠️)</div>
        </div>
    </div>
</div>

</body>
</html>
