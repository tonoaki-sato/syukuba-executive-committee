<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>領収書 - {{ $app->shop_name }}</title>
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
            margin: 0;
            padding: 20px;
            color: #000;
            background-color: #fff;
        }
        .receipt-container {
            max-width: 700px;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 30px;
            position: relative;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .title {
            font-size: 28px;
            font-weight: bold;
            letter-spacing: 0.1em;
            margin: 0 0 10px 0;
            border-bottom: 2px double #000;
            display: inline-block;
            padding-bottom: 5px;
        }
        .date {
            text-align: right;
            font-size: 14px;
        }
        .address-block {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .recipient {
            font-size: 20px;
            font-weight: bold;
            border-bottom: 1px solid #000;
            width: 55%;
            padding-bottom: 5px;
            align-self: flex-end;
        }
        .issuer {
            width: 40%;
            font-size: 14px;
            line-height: 1.5;
            text-align: right;
        }
        .stamp-box {
            border: 1px solid #000;
            width: 60px;
            height: 60px;
            display: inline-block;
            text-align: center;
            line-height: 60px;
            font-size: 10px;
            color: #ccc;
            margin-top: 10px;
        }
        .amount-box {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            border: 2px solid #000;
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 30px;
            letter-spacing: 0.05em;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .details-table th, .details-table td {
            border: 1px solid #000;
            padding: 8px 12px;
            font-size: 14px;
        }
        .details-table th {
            background-color: #f2f2f2;
            text-align: left;
        }
        .details-table td.num {
            text-align: right;
        }
        .print-btn-container {
            text-align: center;
            margin-top: 20px;
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
        .print-btn:hover {
            background-color: #0056b3;
        }
        @media print {
            .print-btn-container {
                display: none;
            }
            body {
                padding: 0;
            }
            .receipt-container {
                border: none;
                padding: 10px;
            }
        }
    </style>
</head>
<body>

<div class="receipt-container">
    <div class="date">領収日: {{ $app->payment_received_at ? $app->payment_received_at->format('Y年m月d日') : date('Y年m月d日') }}</div>
    <div class="header">
        <h1 class="title">領　収　書</h1>
    </div>

    <div class="address-block">
        <div class="recipient">
            {{ $app->shop_name }} 様
        </div>
        <div class="issuer">
            <strong>保土ケ谷宿場まつり実行委員会</strong><br>
            〒240-0006<br>
            神奈川県横浜市保土ケ谷区<br>
            <div class="stamp-box">領収印</div>
        </div>
    </div>

    <div class="amount-box">
        金額 ￥{{ number_format($app->total_fee) }}-
    </div>

    <p style="font-size: 14px; margin-bottom: 15px;">但し、{{ $app->event->fiscal_year }}年度保土ケ谷宿場まつり ござ市出店料等として、上記金額を正に領収いたしました。</p>

    <table class="details-table">
        <thead>
            <tr>
                <th>項目・内訳</th>
                <th style="text-align: right; width: 150px;">金額</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>① 出店料（{{ $app->section_count }}区画分）</td>
                <td class="num">￥{{ number_format($app->exhibition_fee) }}</td>
            </tr>
            <tr>
                <td>② 備品貸出料（テント/机/椅子等）</td>
                <td class="num">￥{{ number_format($app->equipment_fee_override !== null ? $app->equipment_fee_override : $app->equipment_fee) }}</td>
            </tr>
            <tr>
                <td>③ ゴミ袋料</td>
                <td class="num">￥{{ number_format($app->trash_bag_fee) }}</td>
            </tr>
            <tr style="font-weight: bold; background-color: #f9f9f9;">
                <td>合計</td>
                <td class="num">￥{{ number_format($app->total_fee) }}</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="print-btn-container">
    <button class="print-btn" onclick="window.print()">印刷する</button>
</div>

</body>
</html>
