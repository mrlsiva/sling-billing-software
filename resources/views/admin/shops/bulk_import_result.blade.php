@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Shop Import Result</title>
@endsection

@section('body')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <p class="card-title mb-0">Shop Import Result</p>
        <a href="{{ route('admin.shop.index') }}" class="btn btn-sm btn-outline-secondary">Back to Shops</a>
    </div>
    <div class="card-body">
        @if(empty($created) && empty($skipped))
            <p class="text-muted mb-0">This page only shows results right after an import. Refreshing clears it — go to Shops and import again if you need to see it.</p>
        @else
            @if(!empty($created))
                <div class="alert alert-warning">
                    <strong>Save these passwords now.</strong> They are shown only this once and are not stored anywhere — write them down or share them before leaving this page.
                </div>
                <h6>Created ({{ count($created) }})</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Name</th><th>Slug Name</th><th>User Name</th><th>Phone</th><th>Password</th></tr></thead>
                        <tbody>
                            @foreach($created as $row)
                                <tr>
                                    <td>{{ $row['name'] }}</td>
                                    <td>{{ $row['slug_name'] }}</td>
                                    <td>{{ $row['user_name'] }}</td>
                                    <td>{{ $row['phone'] }}</td>
                                    <td><code>{{ $row['password'] }}</code></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if(!empty($skipped))
                <h6>Skipped ({{ count($skipped) }})</h6>
                <ul>
                    @foreach($skipped as $line)
                        <li class="text-danger">{{ $line }}</li>
                    @endforeach
                </ul>
            @endif
        @endif
    </div>
</div>
@endsection
