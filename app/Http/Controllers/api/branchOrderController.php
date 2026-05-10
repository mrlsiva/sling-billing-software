<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Traits\ResponseHelper;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use App\Models\OrderPaymentDetail;
use App\Models\ProductImeiNumber;
use App\Models\RefundDetail;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Staff;
use App\Models\Stock;
use App\Models\Order;
use App\Traits\Log;
use Carbon\Carbon;
use DB;

class branchOrderController extends Controller
{
    use Log, Notifications, ResponseHelper;

    public function index(Request $request)
    {
        $orders = Order::where('branch_id', Auth::user()->id)
            ->when($request->order, function ($q) use ($request) {
                $search = $request->order;
                $q->where(function ($q2) use ($search) {
                    $q2->where('bill_id', 'like', "%{$search}%")
                       ->orWhereHas('customer', fn($q3) =>
                           $q3->where('name', 'like', "%{$search}%")
                              ->orWhere('phone', 'like', "%{$search}%")
                              ->orWhere('gst', 'like', "%{$search}%")
                       );
                });
            })
            ->with(['customer', 'billedBy'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return $this->successResponse($orders, 200, 'Orders retrieved successfully.');
    }

    // GET /api/branch/orders/{id}/refund_data  — data needed to show refund form
    public function refund_data(Request $request, $id)
    {
        $order                 = Order::find($id);
        $order_details         = OrderDetail::where('order_id', $id)->get();
        $order_payment_details = OrderPaymentDetail::where('order_id', $id)->get();
        $payments              = Payment::where('is_active', 1)->get();
        $staffs                = Staff::where([['branch_id', Auth::user()->id], ['is_active', 1]])->get();

        if (!$order) {
            return $this->errorResponse([], 404, 'Order not found.');
        }

        return $this->successResponse(
            compact('order', 'order_details', 'order_payment_details', 'payments', 'staffs'),
            200,
            'Refund data retrieved successfully.'
        );
    }

    public function refunded(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id'     => 'required|exists:orders,id',
            'refunded_by'  => 'required',
            'amount'       => 'required|numeric|min:0',
            'reason'       => 'nullable|string',
            'payment'      => 'required|exists:payments,id',
            'orders_details' => 'required|array',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed($validator->errors(), 'Validation failed.');
        }

        DB::beginTransaction();

        $refund = Refund::create([
            'order_id'      => $request->order_id,
            'refunded_by'   => $request->refunded_by,
            'refund_amount' => $request->amount,
            'refund_on'     => Carbon::now(),
            'reason'        => $request->reason,
            'payment_id'    => $request->payment,
            'payment_info'  => $request->detail,
        ]);

        foreach ($request->orders_details as $orderDetailId) {
            $qty = $request->quantity[$orderDetailId] ?? null;

            if ($qty !== null && $qty > 0) {
                $detail        = OrderDetail::find($orderDetailId);
                $selectedImeis = $request->imeis[$detail->id] ?? [];
                $qty           = $request->quantity[$detail->id];

                RefundDetail::create([
                    'refund_id'   => $refund->id,
                    'product_id'  => $detail->product_id,
                    'name'        => $detail->name,
                    'quantity'    => $qty,
                    'price'       => $detail->price,
                    'tax_amount'  => $detail->tax_amount,
                    'tax_percent' => $detail->tax_percent,
                    'imei'        => implode(',', $selectedImeis),
                    'size_id'     => $detail->size_id,
                    'colour_id'   => $detail->colour_id,
                ]);

                $stock = Stock::where([
                    ['shop_id', Auth::user()->parent_id],
                    ['branch_id', Auth::user()->id],
                    ['product_id', $detail->product_id],
                ])->first();

                $existingImeis = !empty($stock->imei) ? explode(',', $stock->imei) : [];

                $stock->update([
                    'quantity' => $stock->quantity + $qty,
                    'imei'     => implode(',', array_merge($existingImeis, $selectedImeis)),
                ]);

                if (!empty($selectedImeis)) {
                    ProductImeiNumber::whereIn('name', $selectedImeis)
                        ->where('product_id', $detail->product_id)
                        ->update(['is_sold' => 0]);
                }
            }
        }

        Order::where('id', $request->order_id)->update(['is_refunded' => 1]);

        DB::commit();

        $this->addToLog($this->unique(), Auth::id(), 'Refund', 'App/Models/Refund', 'refunds', $refund->id, 'Insert', null, null, 'Success', 'Refund done Successfully');
        $this->notification(Auth::user()->parent_id, null, 'App/Models/Refund', $refund->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Branch ' . Auth::user()->name . ' refunded order ' . $refund->order->bill_id . ' to customer ' . $refund->order->customer->name, null, null, 14);

        return $this->successResponse(['refund_id' => $refund->id], 200, 'Refund processed successfully.');
    }
}