@extends('layouts.master')

@section('title')
	<title>{{ config('app.name')}} | Product Create</title>
@endsection

@section('style')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<style>
    .select2-container .select2-selection--single { height: 38px; border: 1px solid #ced4da; border-radius: 4px; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px; color: #495057; padding-left: 10px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
</style>
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function () {
    $('#category').select2({ width: '100%', placeholder: 'Select' });
    $('#sub_category').select2({ width: '100%', placeholder: 'Select' });
    $('#tax').select2({ width: '100%', placeholder: 'Select' });
    $('#metric').select2({ width: '100%', placeholder: 'Select' });
    $('#discount_type').select2({ width: '100%', placeholder: 'Select' });
});
</script>
@endsection