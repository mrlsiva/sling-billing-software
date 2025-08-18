<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\SubCategory;
use App\Models\Category;
use App\Models\Product;
use App\Models\Metric;
use App\Models\Stock;
use App\Traits\common;
use App\Traits\Log;
use App\Models\Tax;
use DB;

class productController extends Controller
{
    use Log, common;

    public function index(Request $request)
    {
        $products = Product::where('user_id',Auth::user()->id)->paginate(30);
        return view('users.products.index',compact('products'));
    }

    public function create(Request $request)
    {
        $categories = Category::where([['user_id',Auth::user()->id],['is_active',1]])->get();
        $taxes = Tax::where('is_active',1)->get();
        $metrics = Metric::where('is_active',1)->get();

        return view('users.products.create',compact('categories','taxes','metrics'));
    }

    public function get_sub_category(Request $request)
    {
        return $sub_categories = SubCategory::where([['user_id',Auth::user()->id],['category_id',$request->id],['is_active',1]])->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'nullable|mimes:jpg,jpeg,png,gif|max:2048', // up to 2MB
            'category' => 'required',
            'sub_category' => 'required',
            'name' => 'required|string|max:50',
            'code' => 'required|string|max:50',
            'price' => 'required|numeric|min:1',
            'tax' => 'required',
            'metric' => 'required',
            'discount_type' => 'nullable|required_with:discount',
            'discount' => 'nullable|required_with:discount_type|numeric|min:0',
            'quantity' => 'numeric|min:0',
        ], 
        [
            'image.mimes' => 'Image must be a JPG, JPEG, PNG, or GIF file.',
            'image.max' => 'Image size must not exceed 2MB.',

            'category.required' => 'Category is required.',
            'sub_category.required' => 'Sub Category is required.',
            'name.required' => 'Product Name is required.',
            'code.required' => 'Product Code is required.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a number.',
            'price.min' => 'Price should be greater than 1.',
            'tax.required' => 'Tax is required.',
            'metric.required' => 'Metric is required.',
            'discount_type.required_with' => 'Discount type is required when discount is provided.',
            'discount.required_with' => 'Discount value is required when discount type is provided.',
            'discount.numeric' => 'Discount must be a number.',
            'discount.min' => 'Discount cannot be negative.',
            'quantity.numeric' => 'Quantity must be a number.',
            'quantity.min' => 'Quantity cannot be negative.',
        ]);


        DB::beginTransaction();

        $tax = Tax::where('id',$request->tax)->first();
        $price = $request->price; // base price

        $taxAmount = 0; // default, in case tax = 0

        if ($tax && preg_match('/(\d+)%/', $tax->name, $matches)) {
            $taxRate = (int) $matches[1]; // extract number part e.g. "18" from "GST 18%"
            $taxAmount = $price * $taxRate / 100;
        }

        $product = Product::create([ 
            'user_id' => Auth::user()->id,
            'category_id' => $request->category,
            'sub_category_id' => $request->sub_category,
            'name' => Str::ucfirst($request->name),
            'description' => $request->description,
            'code' => $request->code,
            'hsn_code' => $request->hsn_code,
            'price' => $request->price,
            'tax_amount' => $taxAmount,
            'tax_id' => $request->tax,
            'metric_id' => $request->metric,
            'discount_type' => $request->discount_type,
            'discount' => $request->discount,
            'quantity' => $request->quantity,
            'is_active' => 1,
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = config('path.root') . '/' . request()->route('company') . '/' . config('path.product');

            // Save the file
            $filePath = $file->storeAs($path, $filename, 'public');

            // Save to user
            $product->image = $filePath; // This is relative to storage/app/public
            $product->save();
        }

        $stock = Stock::create([ 
            'shop_id' => Auth::user()->id,
            'category_id' => $request->category,
            'sub_category_id' => $request->sub_category,
            'product_id' => $product->id,
            'quantity' => $request->quantity,
            'is_active' => 1,
        ]);

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Stock Added','App/Models/Stock','stocks',$stock->id,'Insert',null,$request,'Success','Stock Added for this product');

        DB::commit();

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Product Create','App/Models/Product','products',$product->id,'Insert',null,$request,'Success','Product Created Successfully');

        return redirect()->back()->with('toast_success', 'Product created successfully.');
        
    }

    public function edit(Request $request,$company,$id)
    {
        $categories = Category::where([['user_id',Auth::user()->id],['is_active',1]])->get();
        $product = Product::find($id);
        $taxes = Tax::where('is_active',1)->get();
        $metrics = Metric::where('is_active',1)->get();
        return view('users.products.edit',compact('product','categories','taxes','metrics'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'image' => 'nullable|mimes:jpg,jpeg,png,gif|max:2048', // up to 2MB
            'category_id' => 'required',
            'sub_category' => 'required',
            'name' => 'required|string|max:50',
            'code' => 'required|string|max:50',
            'price' => 'required|numeric|min:1',
            'tax' => 'required',
            'metric' => 'required',
            'discount_type' => 'nullable|required_with:discount',
            'discount' => 'nullable|required_with:discount_type|numeric|min:0',
            'quantity' => 'nullable|numeric|min:0',
        ], 
        [
            'image.mimes' => 'Image must be a JPG, JPEG, PNG, or GIF file.',
            'image.max' => 'Image size must not exceed 2MB.',

            'category_id.required' => 'Category is required.',
            'sub_category.required' => 'Sub Category is required.',
            'name.required' => 'Product Name is required.',
            'code.required' => 'Product Code is required.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a number.',
            'price.min' => 'Price should be greater than 1.',
            'tax.required' => 'Tax is required.',
            'metric.required' => 'Metric is required.',
            'discount_type.required_with' => 'Discount type is required when discount is provided.',
            'discount.required_with' => 'Discount value is required when discount type is provided.',
            'discount.numeric' => 'Discount must be a number.',
            'discount.min' => 'Discount cannot be negative.',
            'quantity.numeric' => 'Quantity must be a number.',
            'quantity.min' => 'Quantity cannot be negative.',
        ]);


        DB::beginTransaction();

        $product = Product::find($request->id);

        $tax = Tax::where('id',$request->tax)->first();

        $price = $request->price; // base price

        $taxAmount = 0; // default, in case tax = 0

        if ($tax && preg_match('/(\d+)%/', $tax->name, $matches)) {
            $taxRate = (int) $matches[1]; // extract number part e.g. "18" from "GST 18%"
            $taxAmount = $price * $taxRate / 100;
        }

        $product->update([ 
            'category_id' => $request->category_id,
            'sub_category_id' => $request->sub_category,
            'name' => Str::ucfirst($request->name),
            'description' => $request->description,
            'code' => $request->code,
            'hsn_code' => $request->hsn_code,
            'price' => $request->price,
            'tax_amount' => $taxAmount,
            'tax_id' => $request->tax,
            'metric_id' => $request->metric,
            'discount_type' => $request->discount_type,
            'discount' => $request->discount,
            'quantity' => $request->quantity,
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = config('path.root') . '/' . request()->route('company') . '/' . config('path.product');

            // Save the file
            $filePath = $file->storeAs($path, $filename, 'public');

            // Save to user
            $product->image = $filePath; // This is relative to storage/app/public
            $product->save();
        }

        DB::commit();

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Product Update','App/Models/Product','products',$product->id,'Update',null,$request,'Success','Product Updated Successfully');

        return redirect()->back()->with('toast_success', 'Product updated successfully.');
    }

    public function status(Request $request)
    {
        $product = Product::find($request->id);

        if ($product) {
            $product->is_active = $product->is_active == 1 ? 0 : 1;
            $product->save();
        }

        $product = Product::find($request->id);

        $statusText = $product->is_active == 1 ? 'Product changed to active state' : 'Product changed to in-active state';

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Product Status Update','App/Models/Product','products',$request->id,'Update',null,null,'Success',$statusText);

        return redirect()->back()->with('toast_success', $statusText);
    }

    public function view(Request $request)
    {
        return view('users.products.view');
    }
}
