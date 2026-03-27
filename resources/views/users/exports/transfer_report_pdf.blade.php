<table border="1" width="100%" cellspacing="0" cellpadding="5">
    <thead>
        <tr>
            <th>Date</th>
            <th>Type</th>
            <th>From</th>
            <th>To</th>
            <th>Category</th>
            <th>Sub</th>
            <th>Item</th>
            <th>Code</th>
            <th>Qty</th>
        </tr>
    </thead>
    <tbody>
        @foreach($datas as $data)
        <tr>
            <td>{{ \Carbon\Carbon::parse($data->transfer_on)->format('d M Y H:i') }}</td>
            <td>
			    @if($current_branch == 0)
			        {{ $data->to == Auth::user()->id ? 'Stock_In' : 'Stock_Out' }}
			    @else
			        {{ $data->to == $current_branch ? 'Stock_In' : 'Stock_Out' }}
			    @endif
			</td>
            <td>{{ $data->transfer_from->user_name ?? '' }}</td>
            <td>{{ $data->transfer_to->user_name ?? '' }}</td>
            <td>{{ $data->category->name ?? '' }}</td>
            <td>{{ $data->sub_category->name ?? '' }}</td>
            <td>{{ $data->product->name ?? '' }}</td>
            <td>{{ $data->product->code ?? '' }}</td>
            <td>{{ $data->quantity }}</td>
        </tr>
        @endforeach
    </tbody>
</table>