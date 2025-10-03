<table>
    <thead>
        <tr>
            <th>S.No</th>
            <th>Bill ID</th>
            <th>Amount (in ₹)</th>
            <th>Billed on</th>
            <th>Billed by</th>
            <th>Mode of payment</th>
            <th>Customer</th>
        </tr>
    </thead>
    <tbody>
        @foreach($orders as $index => $order)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $order->bill_id }}</td>
                <td>{{ $order->bill_amount }}</td>
                <td>{{ \Carbon\Carbon::parse($order->billed_on)->format('d M Y') }}</td>
                <td>{{ $order->billedBy->name }}</td>

                @php
                    $payment_ids = App\Models\OrderPaymentDetail::where('order_id', $order->id)->pluck('payment_id');
                    $payments = App\Models\Payment::whereIn('id', $payment_ids)->pluck('name')->toArray();
                @endphp

                <td>{{ implode(', ', $payments) }}</td>
                <td>{{ $order->customer->phone }} ({{ $order->customer->name }})</td>
            </tr>
        @endforeach
    </tbody>
</table>
