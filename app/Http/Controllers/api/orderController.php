<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\OrderPaymentDetail;
use App\Traits\ResponseHelper;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use App\Models\OrderDetail;
use App\Models\Order;
use App\Traits\Log;
use DB;

class orderController extends Controller
{
    use Log, Notifications, ResponseHelper;

    public function order(Request $request)
    {
        if(Auth::user()->role_id == 2)
        {
            $branch = request('branch');

            if($branch == 0)
            {
                $orders = Order::where('shop_id',Auth::user()->owner_id)
                ->when(request('order'), function ($query) {
                    $search = request('order');
                    $query->where(function ($q) use ($search) {
                        // search by bill id
                        $q->where('bill_id', 'like', "%{$search}%")
                        // branch name / username
                        ->orWhereHas('branch', function ($q1) use ($search) {
                            $q1->where('name', 'like', "%{$search}%")
                            ->orWhere('user_name', 'like', "%{$search}%");
                        })
                        // customer name / phone
                        ->orWhereHas('customer', function ($q2) use ($search) {
                            $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('gst', 'like', "%{$search}%");;
                        });
                    });
                })->orderBy('id','desc')->paginate(10);

            }
            else
            {
                $orders = Order::where([['branch_id',$branch],['shop_id',Auth::user()->owner_id]])
                ->when(request('order'), function ($query) {
                    $search = request('order');
                    $query->where(function ($q) use ($search) {
                        // search by bill id
                        $q->where('bill_id', 'like', "%{$search}%")
                          // branch name / username
                          ->orWhereHas('branch', function ($q1) use ($search) {
                              $q1->where('name', 'like', "%{$search}%")
                                 ->orWhere('user_name', 'like', "%{$search}%");
                          })
                          // customer name / phone
                          ->orWhereHas('customer', function ($q2) use ($search) {
                              $q2->where('name', 'like', "%{$search}%")
                                 ->orWhere('phone', 'like', "%{$search}%")
                                 ->orWhere('gst', 'like', "%{$search}%");
                          });
                    });
                })->orderBy('id','desc')->paginate(10);
            }
        }

        if(Auth::user()->role_id == 3)
        {
            $orders = Order::where('branch_id',Auth::user()->id)
            ->when(request('order'), function ($query) {
                $search = request('order');
                $query->where(function ($q) use ($search) {
                    // Bill No
                    $q->where('bill_id', 'like', "%{$search}%")
                      // Customer Name / Phone
                      ->orWhereHas('customer', function ($q2) use ($search) {
                          $q2->where('name', 'like', "%{$search}%")
                             ->orWhere('phone', 'like', "%{$search}%")
                             ->orWhere('gst', 'like', "%{$search}%");
                      });
                });
            })->orderBy('id','desc')->paginate(10);
        }

        return $this->successResponse($orders, 200, 'Successfully returned all orders.');
    }

    public function view(Request $request, $order)
    {
        $order = Order::with('shop','branch','customer','billedBy')->where('id',$order)->first();
        $order_details = OrderDetail::where('order_id',$order->id)->get();
        $order_payment_details = OrderPaymentDetail::with('payment','finance')->where('order_id',$order->id)->get();

        $data = [

            'order'                 => $order,
            'order_details'         => $order_details,
            'order_payment_details' => $order_payment_details,

        ];

        return $this->successResponse($data, 200, 'Successfully returned requested order detail.');

    }
}
