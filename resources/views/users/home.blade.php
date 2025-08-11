<div class="card">
   
<a href="{{ route('login', ['company' => request()->route('company')]) }}" >
<button type="button" class="btn btn-primary w-100"><i class="ri-save-line"></i> Login</button></a>
                <div class="card-header">
                    <h4 class="card-title mb-0">Shop Info</h4>
                </div>
                <div class="card-body">
                    <div class="pb-3 border-bottom">
                        <h5 class="text-dark fs-12 text-uppercase fw-bold">Shop Name :</h5>
                        <p class="fw-medium mb-0">{{$user->name}}</p>
                    </div>
                    <div class="py-3 border-bottom">
                        <h5 class="text-dark fs-12 text-uppercase fw-bold">Slug Name :</h5>
                        <p class="fw-medium mb-0">{{$user->slug_name}}</p>
                    </div>
                    <div class="py-3 border-bottom">
                        <h5 class="text-dark fs-12 text-uppercase fw-bold">User Name :</h5>
                        <p class="fw-medium mb-0">{{$user->user_name}}</p>
                    </div>
                    <div class="py-3 border-bottom">
                            <h5 class="text-dark fs-12 text-uppercase fw-bold">Phone Number :</h5>
                            <p class="fw-medium mb-0">{{$user->phone}} @if($user->alt_phone != null) | {{$user->alt_phone}} @endif</p>
                    </div>
                    <div class="py-3 border-bottom">
                            <h5 class="text-dark fs-12 text-uppercase fw-bold">Address :</h5>
                            <p class="fw-medium mb-0">@if($user->user_detail->address != null) {{$user->user_detail->address}} @else - @endif</p>
                    </div>
                    <div class="py-3 border-bottom">
                            <h5 class="text-dark fs-12 text-uppercase fw-bold">Email :</h5>
                            <p class="fw-medium mb-0">@if($user->email != null) {{$user->email}} @else - @endif</p>
                    </div>
                    <div class="py-3 border-bottom">
                            <h5 class="text-dark fs-12 text-uppercase fw-bold">Company GSTin :</h5>
                            <p class="fw-medium mb-0">@if($user->user_detail->gst != null) {{$user->user_detail->gst}} @else - @endif</p>
                    </div>
                    <div class="pt-3">
                            <h5 class="text-dark fs-12 text-uppercase fw-bold">Primary Color :</h5>
                            <p class="fw-medium mb-0">@if($user->user_detail->primary_colour != null) {{$user->user_detail->primary_colour}} @else - @endif</p>
                    </div>
                    <div class="pt-3">
                            <h5 class="text-dark fs-12 text-uppercase fw-bold">Secondary Color :</h5>
                            <p class="fw-medium mb-0">@if($user->user_detail->secondary_colour != null) {{$user->user_detail->secondary_colour}} @else - @endif</p>
                    </div>

                    <div class="" id="bank-details">
                        <p class="fw-medium mb-0">@if($user->bank_detail->name != null) {{$user->bank_detail->name}} @else - @endif</p>
                        <p class="fw-medium mb-0">@if($user->bank_detail->holder_name != null) {{$user->bank_detail->holder_name}} @else - @endif</p>
                        <p class="fw-medium mb-0">@if($user->bank_detail->branch != null) {{$user->bank_detail->branch}} @else - @endif</p>
                        <p class="fw-medium mb-0">@if($user->bank_detail->account_no != null) {{$user->bank_detail->account_no}} @else - @endif</p>
                        <p class="fw-medium mb-0">@if($user->bank_detail->ifsc_code != null) {{$user->bank_detail->ifsc_code}} @else - @endif</p>
                    </div>
                    
                </div>
            </div>
