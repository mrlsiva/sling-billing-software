<?php

namespace App\Http\Controllers\branches;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductHistory;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use App\Models\SubCategory;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use App\Traits\Log;
use DB;

class stockController extends Controller
{
    use Log, Notifications;

    public function index(Request $request)
    {
        $stocks = Stock::where('branch_id', Auth::user()->id)
        ->when(request('product'), function ($query) {
            $search = request('product');
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($q1) use ($search) {
                    $q1->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('product.category', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('product.sub_category', function ($q3) use ($search) {
                    $q3->where('name', 'like', "%{$search}%");
                });
            });
        })->orderBy('category_id')->orderBy('sub_category_id')->orderBy('product_id')->paginate(10);

        return view('branches.products.index',compact('stocks'));
    }

    public function qrcode(Request $request,$company,Product $product)
    {
        //$product = Product::where('id',$id)->first();
        return view('branches.products.qrcode',compact('product'));

    }

    public function barcode(Request $request,$company,$id)
    {
        $product = Product::where('id',$id)->first();
        return view('branches.products.barcode',compact('product'));
    }

    public function transfer(Request $request)
    {

        $categories = Category::where([['user_id',Auth::user()->parent_id],['is_active',1]])->get();
        $branches = User::where([['parent_id',Auth::user()->parent_id],['id','!=',Auth::user()->id],['is_active',1],['is_lock',0],['is_delete',0]])->get();

        $transfers = ProductHistory::where(function ($q) {
            $q->where('from', Auth::user()->id)
              ->orWhere('to', Auth::user()->id);
        })
        ->when(request()->filled('product'), function ($query) {
            $product = request('product');
            $query->whereHas('product', function ($q) use ($product) {
                $q->where('name', 'like', "%{$product}%");
            });
        })
        ->when(request()->filled('branch'), function ($query) {
            $branch = request('branch');
            $query->where(function ($q) use ($branch) {
                $q->where('to', $branch)->orWhere('from', $branch);
            });
        })
        ->with(['product.metric', 'transfer_from', 'transfer_to'])
        ->orderBy('transfer_on', 'desc')
        ->paginate(10);

        return view('branches.products.transfer',compact('categories','branches','transfers'));
    }

    public function get_sub_category(Request $request)
    {
        return $sub_categories = SubCategory::where([['user_id',Auth::user()->parent_id],['category_id',$request->id],['is_active',1]])->get();
    }


    public function get_product(Request $request)
    {
        return $products = Product::where([['user_id',Auth::user()->parent_id],['category_id',$request->category],['sub_category_id',$request->sub_category],['is_active',1]])->get();
    }

    public function get_product_detail(Request $request)
    {
        return $product = Stock::with('product.metric')->where([['shop_id', Auth::user()->parent_id],['branch_id', Auth::user()->id],['product_id', $request->product]])->first();

        //return $product = Product::with('metric')->where('id',$request->product)->first();
    }

    public function store(Request $request)
    {
        $request->validate([
            'category'      => 'required',
            'sub_category'  => 'required',
            'product'       => 'required',
            'quantity'      => 'required|numeric|min:0',
        ], [
            'category.required'     => 'Category is required.',
            'sub_category.required' => 'Sub Category is required.',
            'product.required'      => 'Product is required.',
            'quantity.required'     => 'Quantity is required.',
            'quantity.numeric'      => 'Quantity must be a number.',
            'quantity.min'          => 'Quantity cannot be negative.',
        ]);

        if($request->transfer_to == 1)
        {
            $request->validate([
                'branch'  => 'required',
            ], 
            [
                'branch.required' => 'Branch is required.',
            ]);
        }

        $product = Product::findOrFail($request->product);

        if ($product->quantity == 0) 
        {
            return redirect()->back()->with('toast_error', 'You cant transfer a product with 0 quantity.');
        }

        if ($product->quantity < $request->quantity) 
        {
            return redirect()->back()->with('toast_error', 'Quantity can’t be greater than stock.');
        }

        DB::beginTransaction();

        if($request->transfer_to == 2)
        {
            // Update or create branch stock
            $hoStock = Stock::where([['shop_id', Auth::user()->parent_id],['branch_id', null],['product_id', $request->product]])->first();

            if ($hoStock) 
            {
                $hoStock->update([
                    'shop_id'        => Auth::user()->parent_id,
                    'branch_id'      => null,
                    'category_id'    => $request->category,
                    'sub_category_id'=> $request->sub_category,
                    'product_id'     => $request->product,
                    'quantity'       => $hoStock->quantity + $request->quantity,
                    'is_active'      => 1,
                ]);


                //Log
                $this->addToLog($this->unique(),Auth::user()->id,'Stock Updated','App/Models/Stock','stocks',$hoStock->id,'Update',null,$request,'Success','Stock Updated for this product');
            } 

            // Deduct from main shop stock
            $mainStock = Stock::where([['shop_id', Auth::user()->parent_id],['branch_id', Auth::user()->id],['product_id', $request->product]])->first();

            if ($mainStock) 
            {
                $mainStock->update([
                    'quantity' => $mainStock->quantity - $request->quantity
                ]);
            }

            // Update product table stock
            Product::where('id', $request->product)->update(['quantity' => $product->quantity + $request->quantity]);

            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Quantity Updated','App/Models/Poduct','products',$product->id,'Update',null,$request,'Success','Quantity Updated for this product');

            $transfer = ProductHistory::create([
                'from'           => Auth::user()->id,
                'to'             => Auth::user()->parent_id,
                'category_id'    => $request->category,
                'sub_category_id'=> $request->sub_category,
                'product_id'     => $request->product,
                'quantity'       => $request->quantity,
                'transfer_on'    => now(),
                'transfer_by'    => Auth::user()->id,
            ]);

            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Product Transfer','App/Models/ProductHistory','product_histories',$transfer->id,'Create',null,$request,'Success','Product Transfered Successfully');

            //Notification
            $this->notification(Auth::user()->owner_id, null,'App/Models/ProductHistory', $transfer->id, null, json_encode($request->all()), now(), Auth::user()->id, $transfer->product->name.' has been successfully transfered to HO '.$transfer->transfer_to->name,null, null);
        }
        else
        {
        
            // Update or create branch stock
            $branchStock = Stock::where([['shop_id', Auth::user()->parent_id],['branch_id', $request->branch],['product_id', $request->product]])->first();

            if ($branchStock) 
            {
                $branchStock->update([
                    'shop_id'        => Auth::user()->parent_id,
                    'branch_id'      => $request->branch,
                    'category_id'    => $request->category,
                    'sub_category_id'=> $request->sub_category,
                    'product_id'     => $request->product,
                    'quantity'       => $branchStock->quantity + $request->quantity,
                    'is_active'      => 1,
                ]);

                //Log
                $this->addToLog($this->unique(),Auth::user()->id,'Stock Updated','App/Models/Stock','stocks',$branchStock->id,'Update',null,$request,'Success','Stock Updated for this product');
            } 
            else 
            {
                $branchStock = Stock::create([
                    'shop_id'        => Auth::user()->parent_id,
                    'branch_id'      => $request->branch,
                    'category_id'    => $request->category,
                    'sub_category_id'=> $request->sub_category,
                    'product_id'     => $request->product,
                    'quantity'       => $request->quantity,
                    'is_active'      => 1,
                ]);

                //Log
                $this->addToLog($this->unique(),Auth::user()->id,'Stock Added','App/Models/Stock','stocks',$branchStock->id,'Insert',null,$request,'Success','Stock Added for this product');
            }

            // Deduct from main shop stock
            $mainStock = Stock::where([['shop_id', Auth::user()->parent_id],['branch_id', Auth::user()->id],['product_id', $request->product]])->first();

            if ($mainStock) 
            {
                $mainStock->update([
                    'quantity' => $mainStock->quantity - $request->quantity
                ]);
            }

            $transfer = ProductHistory::create([
                'from'           => Auth::user()->id,
                'to'             => $request->branch,
                'category_id'    => $request->category,
                'sub_category_id'=> $request->sub_category,
                'product_id'     => $request->product,
                'quantity'       => $request->quantity,
                'transfer_on'    => now(),
                'transfer_by'    => Auth::user()->id,
            ]);

            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Product Transfer','App/Models/ProductHistory','product_histories',$transfer->id,'Create',null,$request,'Success','Product Transfered Successfully');

            //Notification
            $this->notification(Auth::user()->owner_id, null,'App/Models/ProductHistory', $transfer->id, null, json_encode($request->all()), now(), Auth::user()->id, $transfer->product->name.' has been successfully transfered to branch '.$transfer->transfer_to->name,null, null);

            //Notification
            $this->notification(null, $request->branch,'App/Models/ProductHistory', $transfer->id, null, json_encode($request->all()), now(), Auth::user()->id, $transfer->product->name.' has been successfully transfered to your branch '.$transfer->transfer_to->name,null, null);
        }

        DB::commit();

        return redirect()->back()->with('toast_success', 'Product transferred successfully.');
    }

}
