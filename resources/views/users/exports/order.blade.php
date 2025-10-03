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
