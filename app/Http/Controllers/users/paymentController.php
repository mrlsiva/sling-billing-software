<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\ShopPayment;
use App\Models\Payment;
use App\Traits\Log;
use DB;

class paymentController extends Controller
{
    use Log;

    public function index(Request $request)
    {
       $payments = Payment::where('is_active',1)->get();
       $shop_payments = ShopPayment::where('shop_id',Auth::user()->id)->get();
       //$shop_payment_ids = ShopPayment::where('shop_id', Auth::id())->pluck('payment_id')->toArray();
       return view('users.settings.payment',compact('payments','shop_payments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'payments' => 'required',
        ], 
        [
            'payments.required' => 'Payment is required.',
        ]);

        DB::beginTransaction();

        ShopPayment::where('shop_id',Auth::user()->id)->delete();

        foreach($request->payments as $payment)
        {
            $shop_payment = ShopPayment::create([ 
                'shop_id' => Auth::user()->id,
                'payment_id' => $payment,
            ]);
        }

        DB::commit();

        return redirect()->back()->with('toast_success', "Payment method added successfully");

    }

    public function update(Request $request)
    {
        $shop_payment = ShopPayment::find($request->id);

        if ($shop_payment) {
            $shop_payment->is_active = $shop_payment->is_active == 1 ? 0 : 1;
            $shop_payment->save();
        }

        $shop_payment = ShopPayment::find($request->id);

        $statusText = $shop_payment->is_active == 1 ? 'Payment method changed to active state' : 'Payment method changed to in-active state';

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Payment method Update','App/Models/Product','products',$request->id,'Update',null,null,'Success',$statusText);

        return redirect()->back()->with('toast_success', $statusText);
    }
}
