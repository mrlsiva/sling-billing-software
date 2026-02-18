@extends('layouts.master')

@section('title')
	<title>{{ config('app.name')}} | GST Bill Create</title>
@endsection

@section('body')
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
            <form class="row" action="{{route('branch.gst_bill.store', ['company' => request()->route('company')])}}" method="post" enctype="multipart/form-data" id="productCreate">
                @csrf
                <div class="card">
                    <div class="card-header pb-0">
                        <h4 class="card-title">New GST Bill</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="order_id" class="form-label text-muted">Order ID</label>
                                    <span class="text-danger">*</span>
                                    <input type="text" name="order_id" id="order_id" value="{{old('order_id')}}" class="form-control" placeholder="Order ID" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="reference_no" class="form-label text-muted">Reference Number</label>
                                    <span class="text-danger">*</span>
                                    <input type="text" name="reference_no" id="reference_no" value="{{old('reference_no')}}" class="form-control" placeholder="Reference Number" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="date_time" class="form-label text-muted">Date and Time</label>
                                    <span class="text-danger">*</span>
                                    <input type="datetime-local" name="date_time" id="date_time" value="{{old('date_time')}}" class="form-control" placeholder="Date and Time" required>
                                </div>
                                
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="issued_by" class="form-label">Issued By</label>
                                    <span class="text-danger">*</span>
                                    <input type="text" name="issued_by" id="issued_by" value="{{old('issued_by')}}" class="form-control" placeholder="Issued By" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="sold_by" class="form-label">Sold By</label>
                                    <span class="text-danger">*</span>
                                    <input type="text" name="sold_by" id="sold_by" value="{{old('sold_by')}}" class="form-control" placeholder="Sold By" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="customer_name" class="form-label">Customer Name</label>
                                    <span class="text-danger">*</span>
                                    <input type="text" name="customer_name" id="customer_name" value="{{old('customer_name')}}" class="form-control" placeholder="Customer Name" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="customer_phone" class="form-label">Customer Phone</label>
                                    <span class="text-danger">*</span>
                                    <input type="text" name="customer_phone" id="customer_phone" value="{{old('customer_phone')}}" class="form-control" placeholder="Customer Phone" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="customer_address" class="form-label">Customer address</label>
                                    <span class="text-danger">*</span>
                                    <input type="text" name="customer_address" id="customer_address" value="{{old('customer_address')}}" class="form-control" placeholder="Customer address" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="choices-single-groups" class="form-label text-muted">Category</label>
                                    <span class="text-danger">*</span>
                                    <select class="form-control" data-choices name="category" id="category">
                                        <option value=""> Select </option>
                                        @foreach($categories as $category)
                                            <option value="{{$category->id}}">{{$category->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="choices-single-groups" class="form-label text-muted">Sub Category</label>
                                    <span class="text-danger">*</span>
                                    <select class="form-control"  name="sub_category" id="sub_category">
                                        <option value=""> Select </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="choices-single-groups" class="form-label text-muted">Product</label>
                                    <span class="text-danger">*</span>
                                    <select class="form-control"  name="product" id="product">
                                        <option value=""> Select </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="imie" class="form-label">IMIE</label>
                                    <input type="text" name="imie" id="imie" value="{{old('imie')}}" class="form-control" placeholder="IMIE">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="item_code" class="form-label">Item Code</label>
                                    <span class="text-danger">*</span>
                                    <input type="text" name="item_code" id="item_code" value="{{old('item_code')}}" class="form-control" placeholder="Item Code" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Quantity</label>
                                    <span class="text-danger">*</span>
                                    <input type="text" name="quantity" id="quantity" value="{{old('quantity')}}" class="form-control" placeholder="Quantity" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="gross" class="form-label">Gross</label>
                                    <span class="text-danger">*</span>
                                    <input type="text" name="gross" id="gross" value="{{old('gross')}}" class="form-control" placeholder="Gross" required>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
                <div class="p-3 mb-3 rounded">
                    <div class="row justify-content-end g-2">
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100"><i class="ri-save-line"></i> Submit</button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{route('branch.gst_bill.index', ['company' => request()->route('company')])}}" class="btn btn-outline-secondary w-100"><i class="ri-close-circle-line"></i> Cancel</a>
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

<script>
	jQuery(document).ready(function () {
	    jQuery('select[name="category"]').on('change', function () {
	        var category = jQuery(this).val();
	        if (category) {
	            jQuery.ajax({
	                url: 'get_sub_category',
	                type: 'GET',
	                dataType: 'json',
	                data: { id: category },
	                success: function (data) {
	                    console.log(data);

	                    jQuery('select[name="sub_category"]').empty();
	                    $('select[name="sub_category"]').append('<option value="">' + "Select" + '</option>');
	                    jQuery.each(data, function (key, value) {
	                        console.log(value.name)
	                        $('select[name="sub_category"]').append('<option value="' + value.id + '">' + value.name + '</option>');
	                    });

	                }
	            });
	        }
	    });
	});

	jQuery(document).ready(function ()
	{
		jQuery('select[name="sub_category"]').on('change',function(){
			var sub_category = jQuery(this).val();
			var category = jQuery("#category").val();
			if(sub_category)
			{
				jQuery.ajax({
					url : 'get_product',
					type: 'GET',
					dataType: 'json',
					data: { sub_category: sub_category, category: category },
					success:function(data)
					{
						console.log(data);

						jQuery('select[name="product"]').empty();
						$('select[name="product"]').append('<option value="">'+ "Select" +'</option>');
						jQuery.each(data, function(key,value){
							console.log(value.name)
							$('select[name="product"]').append('<option value="'+ value.id +'">'+ value.name +'</option>');
						});					
						
					}
				});
			}
		});
	});

</script>
@endsection