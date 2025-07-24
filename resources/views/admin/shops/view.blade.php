@extends('layouts.master')

@section('title')
	<title>{{ config('app.name')}} | Shop</title>
@endsection

@section('body')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                            <p class="card-title mb-0">Vasantham</p>
                    </div>
                    <div class="d-flex gap-3">
                        <!-- <a href="{{route('admin.shop.edit')}}" class="btn btn-sm btn-primary"><i class="ri-edit-line align-middle fs-20"></i> Edit</a> -->
                        <a href="{{route('admin.shop.edit')}}" class="link-dark"><i class="ri-edit-line align-middle fs-20"></i></a>
                        <!-- <div class="dropdown">
                            <a href="#" class="dropdown-toggle btn btn-sm btn-outline-light rounded" data-bs-toggle="dropdown" aria-expanded="false">
                                Reports
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#!" class="dropdown-item">Export</a>
                                <a href="#!" class="dropdown-item">Import</a>
                            </div>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
	<div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Restaurant Settings</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                            <div class="col-md-7">
                                <div class="mb-3">
                                    <p class="fw-medium mb-2">Upload Restaurant Logo</p>
                                    <div class="profile-photo-edit w-50 auth-logo border bg-light-subtle p-2 rounded">
                                        <input id="profile-img-file-input" type="file" class="profile-img-file-input">
                                        <label for="profile-img-file-input" class="profile-photo-edit px-4 py-2"><img src="{{asset('assets/images/sling-logo.png')}}" class="logo-dark me-1" alt="user-profile-image" height="24"> <img src="assets/images/logo-white.png" class="logo-light me-1" alt="user-profile-image" height="24"></label>
                                    </div>

                                </div>
                            </div>
                            <div class="col-md-6">
                                <form>
                                    <div class="mb-3">
                                        <label for="restaurant-name" class="form-label">Restaurant Name</label>
                                        <input type="text" id="restaurant-name" class="form-control" placeholder="Enter name" value="Admin">
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <form>
                                    <div class="mb-3">
                                        <label for="owner-name" class="form-label">Restaurant Owner Full Name</label>
                                        <input type="text" id="owner-name" class="form-control" placeholder="Full name" value="Randy P. Ralph">
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="schedule-number" class="form-label">Owner Phone number</label>
                                    <input type="text" id="schedule-number" name="schedule-number" class="form-control" placeholder="Number" value="+ 312-494-3321">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <form>
                                    <div class="mb-3">
                                        <label for="schedule-email" class="form-label">Owner Email</label>
                                        <input type="email" id="schedule-email" name="schedule-email" class="form-control" placeholder="Email" value="randypralph@jourrapide.com">
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="address" class="form-label">Full Address</label>
                                    <textarea class="form-control bg-light-subtle" id="address" rows="3" placeholder="Type address">4822 West Drive Chicago, IL 60610</textarea>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <form>
                                    <div class="mb-3">
                                        <label for="your-zipcode" class="form-label">Zip-Code</label>
                                        <input type="number" id="your-zipcode" class="form-control" placeholder="zip-code" value="60610">
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-4">
                                <form>
                                    <div class="mb-3">
                                        <label for="choices-city" class="form-label">City</label>
                                        <select class="form-select" id="choices-city" data-choices data-choices-groups data-placeholder="Select City" name="choices-city">
                                                <option value="">Choose a city</option>
                                                <optgroup label="UK">
                                                    <option value="London">London</option>
                                                    <option value="Manchester">Manchester</option>
                                                    <option value="Liverpool">Liverpool</option>
                                                </optgroup>
                                                <optgroup label="FR">
                                                    <option value="Paris">Paris</option>
                                                    <option value="Lyon">Lyon</option>
                                                    <option value="Marseille">Marseille</option>
                                                </optgroup>
                                                <optgroup label="DE" disabled>
                                                    <option value="Hamburg">Hamburg</option>
                                                    <option value="Munich">Munich</option>
                                                    <option value="Berlin">Berlin</option>
                                                </optgroup>
                                                <optgroup label="US">
                                                    <option value="New York" selected>New York</option>
                                                    <option value="Washington" disabled>
                                                        Washington
                                                    </option>
                                                    <option value="Michigan">Michigan</option>
                                                </optgroup>
                                                <optgroup label="SP">
                                                    <option value="Madrid">Madrid</option>
                                                    <option value="Barcelona">Barcelona</option>
                                                    <option value="Malaga">Malaga</option>
                                                </optgroup>
                                                <optgroup label="CA">
                                                    <option value="Montreal">Montreal</option>
                                                    <option value="Toronto">Toronto</option>
                                                    <option value="Vancouver">Vancouver</option>
                                                </optgroup>
                                        </select>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-4">
                                <form>
                                    <label for="choices-country" class="form-label">Country</label>
                                    <select class="form-control" id="choices-country" data-choices data-choices-groups data-placeholder="Select Country" name="choices-country">
                                        <option value="">Choose a country</option>
                                        <optgroup label="">
                                                <option value="">United Kingdom</option>
                                                <option value="Fran">France</option>
                                                <option value="Netherlands">Netherlands</option>
                                                <option value="U.S.A" selected>U.S.A</option>
                                                <option value="Denmark">Denmark</option>
                                                <option value="Canada">Canada</option>
                                                <option value="Australia">Australia</option>
                                                <option value="India">India</option>
                                                <option value="Germany">Germany</option>
                                                <option value="Spain">Spain</option>
                                                <option value="United Arab Emirates">United Arab Emirates</option>
                                        </optgroup>
                                    </select>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <div class="">
                                    <label for="from-time" class="form-label">Restaurant Opening Time</label>
                                    <input type="text" id="preloading-timepicker" class="form-control" placeholder="Pick a time">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="">
                                    <label for="to-time" class="form-label">Restaurant Close Time</label>
                                    <input type="text" id="preloading-timepicker2" class="form-control" placeholder="Pick a time">
                                </div>
                            </div>                                             
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Personal Info</h4>
                </div>
                <div class="card-body">
                    <div class="pb-3 border-bottom">
                            <h5 class="text-dark fs-12 text-uppercase fw-bold">About Me :</h5>
                            <p class="fw-medium mb-0">Hi, Gaston Lapierre I'm 36 and I work as a Digital Designer for the “debater” Agency in Ontario, Canada</p>
                    </div>
                    <div class="py-3 border-bottom">
                            <h5 class="text-dark fs-12 text-uppercase fw-bold">Birth Date : </h5>
                            <p class="fw-medium mb-0">December 17, 1985</p>
                    </div>
                    <div class="py-3 border-bottom">
                            <h5 class="text-dark fs-12 text-uppercase fw-bold">Phone Number :</h5>
                            <p class="fw-medium mb-0">+1-989-232435234</p>
                    </div>
                    <div class="py-3 border-bottom">
                            <h5 class="text-dark fs-12 text-uppercase fw-bold">Gender :</h5>
                            <p class="fw-medium mb-0">Male</p>
                    </div>
                    <div class="py-3 border-bottom">
                            <h5 class="text-dark fs-12 text-uppercase fw-bold">Country :</h5>
                            <p class="fw-medium mb-0">2182 Arron Smith Drive Honolulu, USA</p>
                    </div>
                    <div class="py-3 border-bottom">
                            <h5 class="text-dark fs-12 text-uppercase fw-bold">Occupation :</h5>
                            <p class="fw-medium mb-0">Web Designer</p>
                    </div>
                    <div class="pt-3">
                            <h5 class="text-dark fs-12 text-uppercase fw-bold">Joined :</h5>
                            <p class="fw-medium mb-0">December 20, 2001</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection