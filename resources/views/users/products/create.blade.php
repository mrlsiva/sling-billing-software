@extends('layouts.master')

@section('title')
	<title>{{ config('app.name')}} | Product Create</title>
@endsection

@section('body')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="metrics-store-url" content="{{ route('setting.metric.store', ['company' => request()->route('company')]) }}">
    <meta name="taxes-store-url" content="{{ route('setting.tax.store', ['company' => request()->route('company')]) }}">
    <meta name="size-store-url" content="{{ route('setting.size.store', ['company' => request()->route('company')]) }}">
    <meta name="colour-store-url" content="{{ route('setting.colour.store', ['company' => request()->route('company')]) }}">

     <div class="row">
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Whoops!</strong> There were some problems with your input.<br><br>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @include('users.products.partial')
    </div>
@endsection

@section('script')
<!-- jQuery Validation Plugin -->
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>

<!-- Optional additional methods (if you need pattern, equalTo, etc.) -->
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/additional-methods.min.js"></script>
<script src="{{asset('assets/js/users/product.js?' . $version)}}"></script>
<script src="{{asset('assets/js/users/sub_category.js?' . $version)}}"></script>
<script src="{{asset('assets/js/users/category.js?' . $version)}}"></script>
<script src="{{asset('assets/js/users/tax.js?' . $version)}}"></script>
<script src="{{asset('assets/js/users/metric.js?' . $version)}}"></script>
<script src="{{asset('assets/js/users/size.js?' . $version)}}"></script>
<script src="{{asset('assets/js/users/colour.js?' . $version)}}"></script>
@endsection