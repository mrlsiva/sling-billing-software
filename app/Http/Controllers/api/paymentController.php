<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Traits\ResponseHelper;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ShopPayment;
use App\Traits\Log;
use DB;

class paymentController extends Controller
{
    use Log, Notifications, ResponseHelper;

    public function list(Request $request)
    {
        if(Auth::user()->role_id == 2)
        {
            $shop_payments = ShopPayment::where('shop_id',Auth::user()->owner_id)->get();

            return $this->successResponse($shop_payments, 200, 'Successfully returned all payments');
        }
    }

    public function update(Request $request, $payment)
    {
        if(Auth::user()->role_id == 2)
        {

            $shop_payment = ShopPayment::where([['shop_id',Auth::user()->owner_id],['id',$payment]])->first();

            if ($shop_payment) {
                $shop_payment->is_active = $shop_payment->is_active == 1 ? 0 : 1;
                $shop_payment->save();
            }

            $shop_payment = ShopPayment::where([['shop_id',Auth::user()->owner_id],['id',$payment]])->first();

            $statusText = $shop_payment->is_active == 1 ? 'Payment method changed to active state' : 'Payment method changed to in-active state';

            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Payment method Update','App/Models/ShopPayment','shop_payments',$payment,'Update',null,null,'Success',$statusText);

            //Notifiction
            $this->notification(Auth::user()->owner_id, null,'App/Models/ShopPayment', $request->id, null, $payment, now(), Auth::user()->id, $shop_payment->payment->name.' '.$statusText,null, null);

            return $this->successResponse("Success", 200, $statusText);
        }
    }
}
