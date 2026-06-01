<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Traits\ResponseHelper;
use App\Traits\Log;
use Illuminate\Http\Request;
use App\Models\BillingAddress;
use App\Models\Order;

class billingAddressController extends Controller
{
    use Log, ResponseHelper;

    public function view(Request $request, $order_id)
    {
        $order = Order::find($order_id);

        if (!$order) {
            return $this->errorResponse([], 404, 'Order not found.');
        }

        $billingAddress = BillingAddress::where('order_id', $order_id)->first();

        if (!$billingAddress) {
            return $this->errorResponse([], 404, 'Billing address not found for this order.');
        }

        return $this->successResponse($billingAddress, 200, 'Billing address retrieved successfully.');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'name'     => 'required|string|max:100',
            'phone'    => 'nullable|digits:10',
            'address'  => 'nullable|string|max:255',
            'city'     => 'nullable|string|max:100',
            'pincode'  => 'nullable|digits:6|regex:/^[1-9][0-9]{5}$/',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed($validator->errors(), 'Validation failed.');
        }

        $billingAddress = BillingAddress::where('order_id', $request->order_id)->first();

        if (!$billingAddress) {
            return $this->errorResponse([], 404, 'Billing address not found for this order.');
        }

        $old = $billingAddress->toArray();

        $billingAddress->update([
            'name'    => $request->name,
            'phone'   => $request->phone,
            'address' => $request->address,
            'city'    => $request->city,
            'pincode' => $request->pincode,
        ]);

        $this->addToLog($this->unique(), Auth::id(), 'Billing Address', 'App/Models/BillingAddress', 'billing_addresses', $billingAddress->id, 'Update', json_encode($old), $request, 'Success', 'Billing Address Updated Successfully');

        return $this->successResponse($billingAddress, 200, 'Billing address updated successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'name'     => 'required|string|max:100',
            'phone'    => 'nullable|digits:10',
            'address'  => 'nullable|string|max:255',
            'city'     => 'nullable|string|max:100',
            'pincode'  => 'nullable|digits:6|regex:/^[1-9][0-9]{5}$/',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed($validator->errors(), 'Validation failed.');
        }

        $exists = BillingAddress::where('order_id', $request->order_id)->exists();
        if ($exists) {
            return $this->errorResponse([], 422, 'Billing address already exists for this order. Use update instead.');
        }

        $order = Order::find($request->order_id);

        $billingAddress = BillingAddress::create([
            'user_id' => $order->shop_id,
            'order_id' => $request->order_id,
            'name'    => $request->name,
            'phone'   => $request->phone,
            'address' => $request->address,
            'city'    => $request->city,
            'pincode' => $request->pincode,
        ]);

        $this->addToLog($this->unique(), Auth::id(), 'Billing Address', 'App/Models/BillingAddress', 'billing_addresses', $billingAddress->id, 'Insert', null, $request, 'Success', 'Billing Address Created Successfully');

        return $this->successResponse($billingAddress, 200, 'Billing address created successfully.');
    }
}
