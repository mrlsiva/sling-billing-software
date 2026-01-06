@extends('layouts.master')

@section('title')
	<title>{{ config('app.name')}} | Product Create</title>
@endsection

@section('body')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="metrics-store-url" content="{{ route('setting.metric.store', ['company' => request()->route('company')]) }}">
    <meta name="taxes-store-url" content="{{ route('setting.tax.store', ['company' => request()->route('company')]) }}">
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
        <div class="col-xl-12 col-md-12">
            <form class="row" action="{{ route('product.store', ['company' => request()->route('company')]) }}" method="post" enctype="multipart/form-data" id="productCreate">
                @csrf
                <div class="card">
                    <div class="card-header pb-0">
                        <h4 class="card-title">Add New Product</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-12 col-md-12 mb-3">
                                <label for="name" class="form-label">Upload Product Image</label>
                                <div class="input-group">
                                    <input type="file" name="image" id="image" class="form-control" accept="image/*">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3 position-relative">
                                    <label class="form-label text-muted d-flex justify-content-between align-items-center">
                                        <span>
                                            Category <span class="text-danger">*</span>
                                        </span>

                                        <a data-bs-toggle="modal" data-bs-target="#categoryAdd" class="text-primary" title="Add Category">
                                            <i class="ri-add-circle-line fs-5">Category</i>
                                        </a>
                                    </label>

                                    <select class="form-control" name="category" id="category">
                                        <option value=""> Select </option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label text-muted d-flex justify-content-between align-items-center">
                                        <span>
                                            Sub Category <span class="text-danger">*</span>
                                        </span>

                                        <a data-bs-toggle="modal" data-bs-target="#subCategoryAdd" class="text-primary" title="Add Category">
                                            <i class="ri-add-circle-line fs-5">Sub Category</i>
                                        </a>
                                    </label>
                                    <select class="form-control"  name="sub_category" id="sub_category">
                                        <option value=""> Select </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Product Name</label>
                                    <span class="text-danger">*</span>
                                    <input type="text" name="name" id="name" value="{{old('name')}}" class="form-control" placeholder="Enter Product Name">
                                </div>
                                
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Product Code</label>
                                    <span class="text-danger">*</span>
                                    <input type="text" name="code" id="code" value="{{old('code')}}" class="form-control" placeholder="Enter Product Code">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="hsn_code" class="form-label">HSN Code</label>
                                    <input type="text" name="hsn_code" id="hsn_code" value="{{old('hsn_code')}}" class="form-control" placeholder="Enter HSN Code">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea name="description" id="description" class="form-control" placeholder="Enter Description" rows="4">{{ old('description') }}</textarea>
                                </div>
                            </div>

                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="Price" class="form-label">Selling Price (Including Tax)</label>
                                    <span class="text-danger">*</span>
                                    <input type="number" name="price" id="price" value="{{old('price')}}" class="form-control" placeholder="Enter Selling Price (Including Tax)" min="1">
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label text-muted d-flex justify-content-between align-items-center">
                                        <span>
                                            Tax <span class="text-danger">*</span>
                                        </span>

                                        <a data-bs-toggle="modal" data-bs-target="#taxAdd" class="text-primary" title="Add Tax">
                                            <i class="ri-add-circle-line fs-5">Tax</i>
                                        </a>
                                    </label>
                                    <select class="form-control"  name="tax" id="tax">
                                        <option value=""> Select </option>
                                        @foreach($taxes as $tax)
                                            <option value="{{$tax->id}}">{{$tax->name}}%</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label text-muted d-flex justify-content-between align-items-center">
                                        <span>
                                            Metric <span class="text-danger">*</span>
                                        </span>

                                        <a data-bs-toggle="modal" data-bs-target="#metricAdd" class="text-primary" title="Add Metric">
                                            <i class="ri-add-circle-line fs-5">Metric</i>
                                        </a>
                                    </label>
                                    <select class="form-control"  name="metric" id="metric">
                                        <option value=""> Select </option>
                                        @foreach($metrics as $metric)
                                            <option value="{{$metric->id}}">{{$metric->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="choices-single-groups" class="form-label text-muted">Discount Type</label>
                                    <select class="form-control"  name="discount_type" id="discount_type">
                                        <option value=""> Select </option>
                                        <option value="1"> Flat </option>
                                        <option value="2"> Percentage </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="Discount" class="form-label">Discount</label>
                                    <input type="number" name="discount" id="discount" value="{{old('discount')}}" class="form-control" placeholder="Enter Discount" min="1">
                                </div>
                            </div>

                            @php
                                $user_detail = \App\Models\UserDetail::where('user_id', Auth::user()->owner_id)->first();
                            @endphp

                            @if($user_detail->is_size_differentiation_available == 1)
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="form-check form-switch mt-5">
                                        <input class="form-check-input" type="checkbox"
                                               id="is_size_differentiation_available"
                                               name="is_size_differentiation_available"
                                               value="1">
                                        <label class="form-check-label" for="is_size_differentiation_available">Is Size Differentiation Available</label>
                                        <span class="text-danger">*</span>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if($user_detail->is_colour_differentiation_available == 1)
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="form-check form-switch mt-5">
                                        <input class="form-check-input" type="checkbox"
                                               id="is_colour_differentiation_available"
                                               name="is_colour_differentiation_available"
                                               value="1">
                                        <label class="form-check-label" for="is_colour_differentiation_available">Is Colour Differentiation Available</label>
                                        <span class="text-danger">*</span>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="col-md-12" id="sizes-section" style="display:none;">
                                <label class="mt-3"><strong>Select Sizes</strong></label>
                                <div class="row">

                                    @foreach($sizes as $size)
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input size-checkbox"
                                                       type="checkbox"
                                                       name="sizes[]"
                                                       value="{{ $size->id }}"
                                                       id="size_{{ $size->id }}">

                                                <label class="form-check-label" for="size_{{ $size->id }}">
                                                    {{ $size->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach

                                </div>
                            </div>

                            <div class="col-md-12" id="colours-section" style="display:none;">
                                <label class="mt-3"><strong>Select Colours</strong></label>
                                <div class="row">
                                    @foreach($colours as $colour)
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input colour-checkbox"
                                                       type="checkbox"
                                                       name="colours[]"
                                                       value="{{ $colour->id }}"
                                                       id="colour_{{ $colour->id }}">

                                                <label class="form-check-label" for="colour_{{ $colour->id }}">
                                                    {{ $colour->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>


                            <input type="hidden" name="quantity" id="quantity" value="0">

                        </div>
                    </div>
                </div>
                <div class="p-3 mb-3 rounded">
                    <div class="row justify-content-end g-2">
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100"><i class="ri-save-line"></i> Save Change</button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('product.index', ['company' => request()->route('company')]) }}" class="btn btn-outline-secondary w-100"><i class="ri-close-circle-line"></i> Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
<!-- jQuery Validation Plugin -->
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>

<!-- Optional additional methods (if you need pattern, equalTo, etc.) -->
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/additional-methods.min.js"></script>
<script src="{{asset('assets/js/users/product.js')}}"></script>
<script src="{{asset('assets/js/users/sub_category.js')}}"></script>
<script src="{{asset('assets/js/users/category.js')}}"></script>
<script src="{{asset('assets/js/users/tax.js')}}"></script>
<script src="{{asset('assets/js/users/metric.js')}}"></script>
@endsection