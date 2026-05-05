<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1f36; }
    .header { background: #1a1f36; color: #fff; padding: 16px 20px; margin-bottom: 16px; }
    .header h1 { font-size: 16px; font-weight: bold; margin-bottom: 2px; }
    .header p  { font-size: 10px; color: #a0aec0; }
    .date-range { font-size: 10px; color: #555; margin-bottom: 14px; padding: 6px 10px; background: #f4f6fa; border-left: 3px solid #2d3561; }
    .section-title { font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: .5px; color: #2d3561; background: #f4f6fa; padding: 6px 10px; margin-bottom: 6px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #2d3561; color: #fff; padding: 7px 10px; text-align: left; font-size: 10px; }
    td { padding: 6px 10px; border-bottom: 1px solid #e9ecef; font-size: 10px; }
    tr:last-child td { border-bottom: none; }
    .text-right { text-align: right; }
    .total-row td { font-weight: bold; background: #f8f9fa; border-top: 2px solid #2d3561; }
    .footer { margin-top: 20px; font-size: 9px; color: #aaa; text-align: center; border-top: 1px solid #e9ecef; padding-top: 8px; }
</style>
</head>
<body>

<div class="header">
    <h1>Branch Dashboard Report — {{ Auth::user()->user_name }}</h1>
    <p>Branch Dashboard &mdash; Generated on {{ now()->format('d M Y, h:i A') }}</p>
</div>

<div class="date-range">
    Period: <strong>{{ $from_date->format('d M Y') }}</strong> to <strong>{{ $to_date->format('d M Y') }}</strong>
</div>

<div class="section-title">Summary</div>
<table>
    <thead>
        <tr>
            <th>Metric</th>
            <th class="text-right">Value</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Orders ({{ $from_date->format('d M Y') }} – {{ $to_date->format('d M Y') }})</td>
            <td class="text-right">{{ $date_orders }}</td>
        </tr>
        <tr>
            <td>Sales ({{ $from_date->format('d M Y') }} – {{ $to_date->format('d M Y') }})</td>
            <td class="text-right">&#8377; {{ number_format($date_order_amount, 2) }}</td>
        </tr>
        <tr>
            <td>Total Orders (All time)</td>
            <td class="text-right">{{ $total_orders }}</td>
        </tr>
        <tr class="total-row">
            <td>Total Sales (All time)</td>
            <td class="text-right">&#8377; {{ number_format($total_order_amount, 2) }}</td>
        </tr>
    </tbody>
</table>

<div class="footer">{{ config('app.name') }} &mdash; Branch Dashboard Report</div>

</body>
</html>
