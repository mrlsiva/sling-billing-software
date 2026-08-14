<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\BillSetup;
use App\Models\UserDetail;
use App\Models\OrderDetail;
use App\Models\SubCategory;
use App\Models\Product;
use App\Models\Category;
use App\Models\OrderPaymentDetail;
use App\Models\QueueStock;
use App\Models\OrderHistory;
use App\Models\ShopPayment;
use App\Models\StockVariation;
use App\Models\Payment;
use App\Models\Order;
use App\Traits\Log;
use Carbon\Carbon;
use App\Models\Stock;
use DB;

class billController extends Controller
{
    use Log, Notifications;

    public function index(Request $request,$company,$branch = null)
    {
        if($branch == 0)
        {
            $bills = BillSetup::where([['shop_id',Auth::user()->owner_id],['branch_id',null]])->orderBy('id','desc')->paginate(10);
        }
        else
        {
            $bills = BillSetup::where([['branch_id',$branch],['shop_id',Auth::user()->owner_id]])->orderBy('id','desc')->paginate(10);
        }

        $branches = User::where([['parent_id',Auth::user()->owner_id],['is_active',1],['is_lock',0],['is_delete',0]])->get();
        
        return view('users.settings.bill',compact('branches','branch','bills'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required',
            'bill'      => ['required','string','max:255',
                Rule::unique('bill_setups', 'bill_number')->where(fn ($query) => 
                    $query->where('branch_id', $request->branch_id == 0 ? null : $request->branch_id)->where('shop_id', Auth::user()->owner_id)
                ),
            ],
        ]);

        DB::beginTransaction();

        // deactivate all previous bills for this branch
        BillSetup::where([['shop_id',Auth::user()->owner_id],['branch_id',$request->branch_id == 0 ? null : $request->branch_id]])->update(['is_active' => 0]);

        // create new active bill
        $bill = BillSetup::create([
            'shop_id'   => Auth::user()->owner_id,
            'branch_id' => $request->branch_id == 0 ? null : $request->branch_id,
            'bill_number' => $request->bill,
            'setup_on'    => now(),
            'is_active'   => 1,
        ]);

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Bill number setup','App/Models/BillSetup','bill_setups',$bill->id,'Insert',null,$request,'Success','Bill number set successfully');

        if($request->branch_id != 0)
        {
            $branch = User::where('id',$request->branch_id)->first();

            //Notifiction
            $this->notification(Auth::user()->owner_id, null,'App/Models/BillSetup', $bill->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Bill number "'.$request->bill.'" set successfully for branch '. $branch->name,null, null,11);
        }
        else
        {
            $ho = User::where('id',Auth::user()->owner_id)->first();

            //Notifiction
            $this->notification(Auth::user()->owner_id, null,'App/Models/BillSetup', $bill->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Bill number "'.$request->bill.'" set successfully for HO '. $ho->name,null, null,11);
        }

        DB::commit();

        return redirect()->back()->with('toast_success', 'Bill setup saved successfully!');

    }

    public function set_bank_status(Request $request)
    {
        $user = UserDetail::where('user_id',$request->id)->first();

        if (!$user) {
            return redirect()->back()->with('toast_error', 'User not found.');
        }

        // Toggle bank detail visibility
        $user->show_bank_detail = $user->show_bank_detail == 1 ? 0 : 1;
        $user->save();

        $statusText = $user->show_bank_detail == 1 
            ? 'Bank detail display enabled in bill'
            : 'Bank detail display disabled in bill';

        // Log the action
        $this->addToLog($this->unique(),Auth::user()->id,'Bank Detail Visibility Update','App/Models/UserDetail','user_details',$user->id,'Update',null,null,'Success',$statusText);

        $user = User::where('id',$request->id)->first();

        if($user->role_id == 2)
        {
            $this->notification(Auth::user()->owner_id,null,'App/Models/UserDetail',$user->id,null,json_encode($request->all()),now(),Auth::user()->id,$statusText.' for HO '.$user->user_name,null,null,11);
        }
        else if($user->role_id == 3)
        {
            $this->notification(Auth::user()->owner_id,null,'App/Models/UserDetail',$user->id,null,json_encode($request->all()),now(),Auth::user()->id,$statusText.' for Branch '.$user->user_name,null,null,11);
        }

        return redirect()->back()->with('toast_success', $statusText);
    }

    public function bill(Request $request,$company,$branch = null)
    {
        $branches = User::where([['parent_id',Auth::user()->owner_id],['is_active',1],['is_lock',0],['is_delete',0]])->get();

        $query = Order::where('shop_id', Auth::user()->owner_id);

        // Apply branch condition
        $query->when($branch != 0, function ($q) use ($branch) {
            $q->where('branch_id', $branch);
        }, function ($q) {
            $q->where('branch_id', null);
        });

        // Search filter
        $query->when(request('order'), function ($q) {
            $search = request('order');
            $q->where('bill_id', 'like', "%{$search}%");
        });

        // Final result
        $orders = $query->withCount('histories')->orderBy('id', 'desc')->paginate(10);

        return view('users.settings.edit_bill',compact('orders','branches'));
    }

    public function edit(Request $request,$company,$id)
    {

        $order = Order::where('id', $id)->first();
        $order_details = OrderDetail::where('order_id',$id)->get();
        $order_payment_details = OrderPaymentDetail::where('order_id',$id)->get();
        $categories = Stock::where([['shop_id',$order->shop_id],['branch_id',$order->branch_id],['quantity','>',0],['is_active',1]])->select('category_id')->get();
        $categories = Category::whereIn('id',$categories)->get();
        $shop_payment_ids = ShopPayment::where([['shop_id', $order->shop_id],['is_active', 1]])->pluck('payment_id')->toArray();
        $payments = Payment::whereIn('id',$shop_payment_ids)->get();

        return view('users.settings.bill_edit',compact('order','order_details','order_payment_details','categories','payments'));
    }

    public function get_sub_category(Request $request)
    {

        $sub_categories = Stock::where([['shop_id',$request->shop_id],['branch_id',$request->branch_id],['category_id',$request->category_id],['quantity','>',0],['is_active',1]])->select('sub_category_id')->get();
        $sub_categories = SubCategory::whereIn('id',$sub_categories)->get();

        return $sub_categories; 
    }

    public function get_product(Request $request)
    {

        $products = Stock::where([['shop_id',$request->shop_id],['branch_id',$request->branch_id],['category_id',$request->category_id],['sub_category_id',$request->sub_category_id],['quantity','>',0],['is_active',1]])->select('product_id')->get();
        $products = Product::whereIn('id',$products)->get();

        return $products; 
    }

    public function get_product_detail(Request $request)
    {

        $stock = Stock::where([
            'shop_id' => $request->shop_id,
            'branch_id' => $request->branch_id,
            'product_id' => $request->product,
            'is_active' => 1,
        ])->first();

        $queueStock = QueueStock::where([
            'product_id' => $request->product,
            'from' => $request->branch_id,
            'status' => 0,
        ])->sum('quantity');

        $availableStock = $stock->quantity ?? 0;
        $freeStock = max(0, $availableStock - $queueStock);

        return response()->json([
            'id' => $stock->product_id,
            'price' => $stock->product->discounted_price,
            'tax' => $stock->product->tax->name,
            'stock' => $availableStock,
            'queue' => $queueStock,
            'free' => $freeStock,
        ]);
    }

    public function history(Request $request,$company,$id)
    {
        $order = Order::with(['customer'])->findOrFail($id);

        $histories = OrderHistory::where('order_id', $id)
            ->with('editor')
            ->orderBy('edited_on', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $currentSnapshot = [
            'order'           => $order->toArray(),
            'order_details'   => OrderDetail::where('order_id', $order->id)->get()->toArray(),
            'payment_details' => OrderPaymentDetail::where('order_id', $order->id)->get()->toArray(),
        ];

        $paymentNames = Payment::pluck('name', 'id');

        $timeline = [];
        $snapshotCount = $histories->count();

        foreach ($histories as $index => $history) {

            $after = $histories->get($index + 1);

            $afterSnapshot = $after ? [
                'order'           => $after->order,
                'order_details'   => $after->order_details,
                'payment_details' => $after->payment_details,
            ] : $currentSnapshot;

            $timeline[] = [
                'edited_by'       => optional($history->editor)->user_name ?? '—',
                'edited_on'       => $history->edited_on,
                'remarks'         => $history->remarks,
                'is_latest'       => $index === $snapshotCount - 1,
                'order_changes'   => $this->diffOrderFields($history->order, $afterSnapshot['order']),
                'item_changes'    => $this->diffOrderDetails($history->order_details, $afterSnapshot['order_details']),
                'payment_changes' => $this->diffPaymentDetails($history->payment_details, $afterSnapshot['payment_details'], $paymentNames),
            ];
        }

        // Most recent edit first
        $timeline = array_reverse($timeline);

        return view('users.settings.bill_history', compact('order', 'timeline'));
    }

    private function diffOrderFields(array $before, array $after): array
    {
        $fields = [
            'bill_amount'            => 'Bill Amount',
            'order_discount'         => 'Order Discount',
            'total_product_discount' => 'Product Discount',
            'billed_on'              => 'Billed On',
        ];

        $changes = [];

        foreach ($fields as $key => $label) {
            $oldVal = $before[$key] ?? null;
            $newVal = $after[$key] ?? null;

            if ((string) $oldVal !== (string) $newVal) {
                $changes[] = ['label' => $label, 'old' => $oldVal, 'new' => $newVal];
            }
        }

        return $changes;
    }

    private function diffOrderDetails(array $before, array $after): array
    {
        $beforeMap = collect($before)->keyBy('product_id');
        $afterMap  = collect($after)->keyBy('product_id');

        $productIds = $beforeMap->keys()->merge($afterMap->keys())->unique();

        $changes = [];

        foreach ($productIds as $productId) {

            $oldItem = $beforeMap->get($productId);
            $newItem = $afterMap->get($productId);

            if ($oldItem && !$newItem) {
                $changes[] = [
                    'status'    => 'removed',
                    'name'      => $oldItem['name'],
                    'old_qty'   => $oldItem['quantity'], 'new_qty'   => null,
                    'old_price' => $oldItem['price'],    'new_price' => null,
                ];
            } elseif (!$oldItem && $newItem) {
                $changes[] = [
                    'status'    => 'added',
                    'name'      => $newItem['name'],
                    'old_qty'   => null, 'new_qty'   => $newItem['quantity'],
                    'old_price' => null, 'new_price' => $newItem['price'],
                ];
            } elseif ((string) $oldItem['quantity'] !== (string) $newItem['quantity']
                   || (string) $oldItem['price'] !== (string) $newItem['price']) {
                $changes[] = [
                    'status'    => 'changed',
                    'name'      => $newItem['name'],
                    'old_qty'   => $oldItem['quantity'], 'new_qty'   => $newItem['quantity'],
                    'old_price' => $oldItem['price'],    'new_price' => $newItem['price'],
                ];
            }
        }

        return $changes;
    }

    private function diffPaymentDetails(array $before, array $after, $paymentNames): array
    {
        $beforeMap = collect($before)->keyBy('payment_id');
        $afterMap  = collect($after)->keyBy('payment_id');

        $paymentIds = $beforeMap->keys()->merge($afterMap->keys())->unique();

        $changes = [];

        foreach ($paymentIds as $paymentId) {

            $oldItem = $beforeMap->get($paymentId);
            $newItem = $afterMap->get($paymentId);
            $label   = $paymentNames[$paymentId] ?? 'Payment';

            if ($oldItem && !$newItem) {
                $changes[] = ['status' => 'removed', 'name' => $label, 'old_amount' => $oldItem['amount'], 'new_amount' => null];
            } elseif (!$oldItem && $newItem) {
                $changes[] = ['status' => 'added', 'name' => $label, 'old_amount' => null, 'new_amount' => $newItem['amount']];
            } elseif ((string) $oldItem['amount'] !== (string) $newItem['amount']) {
                $changes[] = ['status' => 'changed', 'name' => $label, 'old_amount' => $oldItem['amount'], 'new_amount' => $newItem['amount']];
            }
        }

        return $changes;
    }

    public function update(Request $request,$company,$id)
    {
        $order = Order::with(['details', 'payments'])->findOrFail($id);

         $request->validate([
            'bill_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique('orders', 'bill_id')
                    ->where(function ($query) use ($order) {
                        return $query->where('shop_id', $order->shop_id)
                                     ->where('branch_id', $order->branch_id);
                    })
                    ->ignore($order->id), // ignore current order
            ],
        ]);

        //return $request;
        DB::beginTransaction();

        try {
            

            //Save History

            OrderHistory::create([
                'order_id'        => $order->id,
                'edited_by'       => Auth::id(),
                'edited_on'       => now(),
                'order'           => $order->toArray(),
                'order_details'   => $order->details->toArray(),
                'payment_details' => $order->payments->toArray(),
                'remarks'         => $request->remarks,
            ]);

            $order->update(['bill_id'=> $request->bill_id]);

            foreach ($order->details as $detail) {

                $stock = Stock::where([
                    'shop_id'    => $order->shop_id,
                    'branch_id'  => $order->branch_id,
                    'product_id' => $detail->product_id,
                    'is_active'  => 1,
                ])->first();

                if ($stock) {
                    $stock->increment('quantity', $detail->quantity);

                    $variation = StockVariation::where('stock_id',$stock->id)->increment('quantity', $detail->quantity);
                }
            }

            //Delete Old Details

            OrderDetail::where('order_id', $order->id)->delete();

            //Save New Details

            $grandTotal = 0;

            foreach ($request->product_id as $key => $productId) {

                $qty   = $request->qty[$key];
                $price = $request->price[$key];

                $stock = Stock::where([
                    'shop_id'    => $order->shop_id,
                    'branch_id'  => $order->branch_id,
                    'product_id' => $productId,
                    'is_active'  => 1,
                ])->firstOrFail();

                if ($stock->quantity < $qty) {
                    throw new \Exception("Insufficient stock.");
                }

                $stock->decrement('quantity', $qty);

                $variation = StockVariation::where('stock_id',$stock->id)->decrement('quantity', $qty);

                $product = $stock->product;

                $lineTotal = $qty * $price;

                OrderDetail::create([
                    'order_id'       => $order->id,
                    'product_id'     => $productId,
                    'name'           => $product->name,
                    'quantity'       => $qty,
                    'price'          => $price,
                    'tax_amount'     => $product->tax_amount,
                    'tax_percent'    => $product->tax->name,
                    'discount_type'  => $product->discount_type,
                    'discount'       => $product->discount,
                    'selling_price'  => $price,
                    'imei'           => null,
                    'size_id'        => null,
                    'colour_id'      => null,
                ]);

                $grandTotal += $lineTotal;
            }

            //Update Order

            $order->update([
                'bill_amount' => $grandTotal,
                'billed_on'   => Carbon::parse(
                    $request->billed_date . ' ' . $request->billed_time
                ),
            ]);

            //Update Payments

            OrderPaymentDetail::where('order_id', $order->id)->delete();

            foreach ($request->payment_id as $i => $payment) {

                if($request->amount[$i] > 0)
                {

                    OrderPaymentDetail::create([
                        'order_id'   => $order->id,
                        'payment_id' => $payment,
                        'amount'     => $request->amount[$i],
                        'number'     => $request->number[$i] ?? null,
                        'card'       => $request->card[$i] ?? null,
                        'finance_id' => $request->finance_id[$i] ?? null,
                    ]);
                }
            }

            DB::commit();

            return redirect()->back()->with('toast_success', 'Bill updated successfully.');
        }

        catch (\Exception $e) {

            DB::rollBack();

            return redirect()->back()->with('toast_error', $e->getMessage());
        }
        

    }


}


