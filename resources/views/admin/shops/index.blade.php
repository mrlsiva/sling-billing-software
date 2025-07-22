@extends('layouts.master')

@section('title')
	<title>{{ config('app.name')}} | Shop</title>
@endsection

@section('body')
	<a href="{{route('admin.shop.create')}}">
		<button>Create</button>
	</a>
@endsection