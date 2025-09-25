@extends('layouts.master')

@section('title')
<title>{{ config('app.name')}} | Finance</title>
@endsection

@section('body')
    <div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
						<p class="card-title mb-0">General Settings</p>
					</div>
				</div>				
			</div>
		</div>
	</div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Payment Method Settings</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group mb-3">
                                    <p class="fw-medium mb-2">Cash</p>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="customersOnline" checked="">
                                        <label class="form-check-label" for="customersOnline">Yes</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group mb-3">
                                    <p class="fw-medium mb-2">Credit</p>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="customersActivity" checked="">
                                        <label class="form-check-label" for="customersActivity">Yes</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group mb-3">
                                    <p class="fw-medium mb-2">UPI</p>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="customerSearches" checked="">
                                        <label class="form-check-label" for="customerSearches">Yes</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <p class="fw-medium mb-2">Card</p>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="guestCheckout">
                                        <label class="form-check-label" for="guestCheckout">Yes</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <p class="fw-medium mb-2">Finance</p>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="loginDisplayPrice">
                                        <label class="form-check-label" for="loginDisplayPrice">Yes</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <p class="fw-medium mb-2">Finance</p>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="loginDisplayPrice">
                                        <label class="form-check-label" for="loginDisplayPrice">Yes</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <p class="fw-medium mb-2">Finance</p>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="loginDisplayPrice">
                                        <label class="form-check-label" for="loginDisplayPrice">Yes</label>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Categories Count</h4>
                </div>
                <div class="card-body">
                    <div class="form-group mb-3">
                            <!-- <p class="fw-medium mb-2"></p> -->
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="categoryProductCount" checked="">
                                <label class="form-check-label" for="categoryProductCount">Default</label>
                            </div>
                    </div>
                    <div class="form-group">
                            <form>
                                <div class="">
                                    <label for="items-par-page" class="form-label">Default Items Per Page</label>
                                    <input type="number" id="items-par-page" class="form-control" placeholder="000">
                                </div>
                            </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Sub Categories Page</h4>
                </div>
                <div class="card-body">
                    <div class="form-group mb-3">
                            <!-- <p class="fw-medium mb-2"></p> -->
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="categoryProductCount" checked="">
                                <label class="form-check-label" for="categoryProductCount">Default</label>
                            </div>
                    </div>
                    <div class="form-group">
                            <form>
                                <div class="">
                                    <label for="items-par-page" class="form-label">Default Items Per Page</label>
                                    <input type="number" id="items-par-page" class="form-control" placeholder="000">
                                </div>
                            </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Product Page</h4>
                </div>
                <div class="card-body">
                    <div class="form-group mb-3">
                            <!-- <p class="fw-medium mb-2"></p> -->
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="categoryProductCount" checked="">
                                <label class="form-check-label" for="categoryProductCount">Default</label>
                            </div>
                    </div>
                    <div class="form-group">
                            <form>
                                <div class="">
                                    <label for="items-par-page" class="form-label">Default Items Per Page</label>
                                    <input type="number" id="items-par-page" class="form-control" placeholder="000">
                                </div>
                            </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Reviews Settings</h4>
                </div>
                <div class="card-body">
                    <div class="form-group mb-3">
                            <p class="fw-medium mb-2">Allow Reviews</p>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="allowReviews" checked="">
                                <label class="form-check-label" for="allowReviews">Yes</label>
                            </div>
                    </div>
                    <div class="form-group">
                            <p class="fw-medium mb-2">Allow Guest Reviews</p>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="gaustReviews">
                                <label class="form-check-label" for="gaustReviews">Yes</label>
                            </div>
                    </div>
                </div>
            </div>
        </div>            
</div>
@endsection