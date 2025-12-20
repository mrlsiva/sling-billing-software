@if($variations->count())
<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th>#</th>
            <th>Size</th>
            <th>Colour</th>
            <th>Quantity</th>
        </tr>
    </thead>
    <tbody>
        @foreach($variations as $key => $variation)
        <tr>
            <td>{{ $key + 1 }}</td>
            <td>{{ $variation->size->name ?? '-' }}</td>
            <td>{{ $variation->colour->name ?? '-' }}</td>
            <td>{{ $variation->quantity }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<p class="text-center text-muted">No variations found.</p>
@endif
