@extends('layouts.master')

@section('title')
	<title>{{ config('app.name')}} | Admin Login</title>
@endsection

@section('body')
    <div class="row">
        <div class="col-xl-12 col-md-12">
            <div class="row">
                @foreach($shops as $shop)
                    <div class="col-md-3 col-md-3">
                        <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <p class="mb-3 card-title">{{$shop->name}}</p>
                                            <h4 class="fw-bold d-flex align-items-center gap-2 mb-0">30 Orders</h4>
                                        </div>
                                        <div>
                                            <a href="{{route('admin.shop.view', ['id' => $shop->id])}}"> <i class="ri-arrow-right-circle-line fs-32 text-muted"></i></a>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

