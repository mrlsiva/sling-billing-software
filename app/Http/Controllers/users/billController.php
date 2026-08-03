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
        $orders = $query->orderBy('id', 'desc')->paginate(10);

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
            'price' => $stock->product->discounted_price,
            'tax' => $stock->product->tax->name,
            'stock' => $availableStock,
            'queue' => $queueStock,
            'free' => $freeStock,
        ]); 
    }

    public function update(Request $request,$company,$id)
    {
        //return $request;
        DB::beginTransaction();

        

            $order = Order::with(['details', 'payments'])->findOrFail($id);

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
                    'price'          => $request->amount[$key],
                    'tax_amount'     => 0,
                    'tax_percent'    => $product->tax->name,
                    'discount_type'  => $product->discount_type,
                    'discount'       => $product->discount,
                    'selling_price'  => $request->amount[$key],
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

            return redirect()->back()->with('success', 'Bill updated successfully.');
        

    }


}


