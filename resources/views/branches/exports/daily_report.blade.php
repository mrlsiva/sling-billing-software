<table>
    <tr>
        <th colspan="2"><b>Daily Report Summary</b></th>
    </tr>

    <tr>
        <td>Total Sales</td>
        <td>{{ number_format($totalSales,2) }}</td>
    </tr>

</table>


<br><br>


<table>
    <thead>
        <tr>
            <th colspan="7"><b>Order Report</b></th>
        </tr>

        <tr>
            <th>S.No</th>
            <th>Branch</th>
            <th>Bill ID</th>
            <th>Amount</th>
            <th>Billed On</th>
            <th>Billed By</th>
            <th>Customer</th>
        </tr>
    </thead>

    <tbody>
        @foreach($orders as $order)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>
                @if($order->branch_id)
                {{ $order->branch->user_name }}
                @else
                {{ $order->shop->user_name }}
                @endif
            </td>
            <td>{{ $order->bill_id }}</td>
            <td>{{ $order->bill_amount }}</td>
            <td>{{ \Carbon\Carbon::parse($order->billed_on)->format('d M Y') }}</td>
            <td>{{ $order->billedBy->name }}</td>
            <td>{{ $order->customer->phone }} ({{ $order->customer->name }})</td>
        </tr>
        @endforeach
    </tbody>
</table>