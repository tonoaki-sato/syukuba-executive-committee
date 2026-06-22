<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>出店許可証 - {{ $app->shop_name }}</title>
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
            margin: 0;
            padding: 20px;
            color: #000;
            background-color: #fff;
        }
        .permit-container {
            max-width: 600px;
            margin: 0 auto;
            border: 4px double #000;
            padding: 40px;
            text-align: center;
            position: relative;
        }
        .header {
            font-size: 16px;
            margin-bottom: 20px;
            letter-spacing: 0.1em;
        }
        .title {
            font-size: 32px;
            font-weight: bold;
            margin: 0 0 30px 0;
            letter-spacing: 0.2em;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .spot-section {
            margin: 30px 0;
        }
        .spot-label {
            font-size: 16px;
            color: #555;
            margin-bottom: 5px;
        }
        .spot-code {
            font-size: 48px;
            font-weight: bold;
            border: 2px solid #000;
            display: inline-block;
            padding: 10px 30px;
            background-color: #f0f0f0;
            letter-spacing: 0.05em;
        }
        .shop-name {
            font-size: 24px;
            font-weight: bold;
            margin: 20px 0;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            display: inline-block;
            min-width: 80%;
        }
        .exhibitor-name {
            font-size: 18px;
            margin-bottom: 30px;
        }
        .footer-info {
            margin-top: 40px;
            font-size: 14px;
            line-height: 1.6;
            text-align: left;
            border-top: 1px solid #000;
            padding-top: 20px;
        }
        .issuer {
            text-align: right;
            font-weight: bold;
            margin-top: 15px;
        }
        .print-btn-container {
            text-align: center;
            margin-top: 20px;
        }
        .print-btn {
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 10px 25px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        .print-btn:hover {
            background-color: #218838;
        }
        @media print {
            .print-btn-container {
                display: none;
            }
            body {
                padding: 0;
            }
            .permit-container {
                border: 4px double #000;
                padding: 30px;
            }
        }
    </style>
</head>
<body>

<div class="permit-container">
    <div class="header">{{ $app->event->fiscal_year }}年度 保土ケ谷宿場まつり</div>
    <h1 class="title">出店許可証</h1>

    <div class="shop-name">
        {{ $app->shop_name }}
    </div>
    <div class="exhibitor-name">
        代表者: {{ $app->exhibitor_name }} 様
    </div>

    <div class="spot-section">
        <div class="spot-label">【 出店区画コード 】</div>
        <div class="spot-code">
            {{ $app->spot_code ?? '未定' }}
        </div>
    </div>

    <div class="footer-info">
        <div><strong>出店規定:</strong></div>
        <ul style="margin: 5px 0 0 0; padding-left: 20px; font-size: 12px;">
            <li>本許可証は、まつり開催期間中、ブース内の見やすい場所に必ず掲示してください。</li>
            <li>ゴミは決められた分別ルールに従い、各自指定の集積場所に処理してください。</li>
            <li>火気を使用する出店者は、消火器の設置および安全管理を徹底してください。</li>
        </ul>
        <div class="issuer">
            保土ケ谷宿場まつり実行委員会
        </div>
    </div>
</div>

<div class="print-btn-container">
    <button class="print-btn" onclick="window.print()">印刷する</button>
</div>

</body>
</html>
