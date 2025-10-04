<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Orders Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .logo { max-height: 60px; }
        .summary { margin-bottom: 20px; text-align: center; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: center; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('storage/' . $user->logo) }}" class="logo" alt="Logo"><br>
        <h2>Orders Report</h2>
    </div>

    <div class="summary">
        <strong>Total Orders:</strong> {{ $totalOrders }} &nbsp;&nbsp;
        <strong>Total Sales:</strong> ₹{{ number_format($totalSales, 2) }}
    </div>

    <table>
        <thead>
            <tr>
                <th>S.No</th>
                <th>Branch</th>
                <th>Bill ID</th>
                <th>Amount (₹)</th>
                <th>Billed On</th>
                <th>Billed By</th>
                <th>Customer</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $i => $order)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $order->branch->user_name ?? '-' }}</td>
                <td>{{ $order->bill_id }}</td>
                <td>{{ $order->bill_amount }}</td>
                <td>{{ \Carbon\Carbon::parse($order->billed_on)->format('d M Y') }}</td>
                <td>{{ $order->billedBy->name }}</td>
                <td>{{ $order->customer->phone }} ({{ $order->customer->name }})</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
