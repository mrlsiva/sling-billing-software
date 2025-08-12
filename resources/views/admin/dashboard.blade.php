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
                                <div class="card-body pt-3 pb-2 px-4">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <p class="mb-3 card-title">{{$shop->name}}</p>
                                            <h4 class="fw-bold d-flex align-items-center gap-2 mb-0">3 <span class="fs-10 mt-2"> Branches</span></h4>
                                        </div>
                                        <div class="d-flex flex-column align-items-center justify-content-center gap-2">
                                            <div class="d-flex align-items-center gap-2 pt-2">
                                                <div class="box" style="background-color: #000;"></div>
                                                <div class="box" style="background-color: #000;"></div>
                                            </div>
                                            <a href="{{route('admin.shop.view', ['id' => $shop->id])}}"> <i class="ri-arrow-right-circle-line fs-32 text-muted"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-Footer border px-4 pt-2 d-flex justify-content-between align-items-center">
                                    <h4 class="fw-bold d-flex align-items-center gap-2">10 <span class="fs-10 mt-2"> Categories</span></h4>
                                    <h4 class="fw-bold d-flex align-items-center gap-2 ">29 <span class="fs-10 mt-2"> Products</span></h4>
                                </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

