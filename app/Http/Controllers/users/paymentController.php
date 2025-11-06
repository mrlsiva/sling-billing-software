<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use App\Models\ShopPayment;
use App\Models\Payment;
use App\Traits\Log;
use DB;

class paymentController extends Controller
{
    use Log, Notifications;

    public function index(Request $request)
    {
       $payments = Payment::where('is_active',1)->get();
       $shop_payments = ShopPayment::where('shop_id',Auth::user()->owner_id)->get();
       //$shop_payment_ids = ShopPayment::where('shop_id', Auth::id())->pluck('payment_id')->toArray();
       return view('users.settings.payment',compact('payments','shop_payments'));
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
        $this->addToLog($this->unique(),Auth::user()->id,'Payment method Update','App/Models/ShopPayment','products',$request->id,'Update',null,null,'Success',$statusText);

        //Notifiction
        $this->notification(Auth::user()->owner_id, null,'App/Models/ShopPayment', $request->id, null, json_encode($request->all()), now(), Auth::user()->id, $shop_payment->payment->name.' '.$statusText,null, null,10);

        return redirect()->back()->with('toast_success', $statusText);
    }
}
