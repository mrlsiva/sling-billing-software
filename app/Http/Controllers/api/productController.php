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
use App\Models\Product;
use App\Models\Stock;
use App\Models\Tax;
use App\Traits\Log;
use DB;

class productController extends Controller
{
    use Log, Notifications, ResponseHelper;

    public function list(Request $request)
    {
        if(Auth::user()->role_id == 2)
        {
            $products = Product::with('category','sub_category','tax','metric')->where('user_id',Auth::user()->owner_id)->when(request('product'), function ($query) {
                $search = request('product');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('hsn_code', 'like', "%{$search}%");
                });
            })->orderBy('id','desc')->paginate(10);

            // Prepend full URL to images
            $products->getCollection()->transform(function ($product) {
                $product->image = $product->image ? asset('storage/' . $product->image) : null;

                if ($product->category && $product->category->image) {
                    $product->category->image = asset('storage/' . $product->category->image);
                }
                else
                {
                    $product->category->image = asset('assets/images/no-image-icon.svg');
                }

                if ($product->sub_category && $product->sub_category->image) {
                    $product->sub_category->image = asset('storage/' . $product->sub_category->image);
                }
                else
                {
                    $product->sub_category->image = asset('assets/images/no-image-icon.svg');
                }

                return $product;
            });



            return $this->successResponse($products, 200, 'Successfully returned all products');
        }
    }

    public function store(Request $request)
    {
        if(Auth::user()->role_id == 2)
        {

            $rules = [
                
                'image' => 'nullable|mimes:jpg,jpeg,png,gif,webp|max:2048', // up to 2MB
                'category' => 'required',
                'sub_category' => 'required',
                'name' => ['required','string','max:50',
                    Rule::unique('products')->where(function ($query) use ($request) {
                        return $query->where('user_id', Auth::user()->id)
                        ->where('category_id', $request->category)
                        ->where('sub_category_id', $request->sub_category);
                    }),
                ],
                'code' => ['required','string','max:50',
                    Rule::unique('products')->where(function ($query) use ($request) {
                        return $query->where('user_id', Auth::user()->id);
                    }),
                ],
                'hsn_code' => 'nullable|string|max:50',
                'price' => 'required|numeric|min:1',
                'tax' => 'required',
                'metric' => 'required',
                'discount_type' => 'nullable|required_with:discount',
                'discount' => 'nullable|required_with:discount_type|numeric|min:0',
                'quantity' => 'numeric|min:0',
            ];

            $messages = [
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
            ];

            $validator=Validator::make($request->all(),$rules,$messages);

            if ($validator->fails()) {
                return $this->validationFailed($validator->errors(),"The given data was invalid.");
            }

            DB::beginTransaction();
            $tax = Tax::where('id',$request->tax)->first();
            $price = $request->price; // base price

            $taxAmount = 0; // default, in case tax = 0

            // if ($tax && preg_match('/(\d+)%/', $tax->name, $matches)) {
            //     $taxRate = (int) $matches[1]; // extract number part e.g. "18" from "GST 18%"
            //     $taxAmount = $price * $taxRate / 100;
            // }

            if ($tax && $tax->name != 0)
            {            
                $taxRate   = (float) $tax->name;
                $taxAmount   = round($price / (1 + ($taxRate / 100)),2);
                //$taxAmount = $price * ((float) $tax->name / 100); 
                //$finalPrice = $price + $taxAmount; // if you want price including tax
            }

            //return $taxAmount;

            $product = Product::create([ 
                'user_id' => Auth::user()->owner_id,
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
            $this->addToLog($this->unique(),Auth::user()->id,'Stock Added','App/Models/Stock','stocks',$stock->id,'Insert',null,json_encode($request->all()),'Success','Stock Added for this product');

            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Product Create','App/Models/Product','products',$product->id,'Insert',null,json_encode($request->all()),'Success','Product Created Successfully');

            //Notifiction
            $this->notification(Auth::user()->owner_id, null,'App/Models/Product', $product->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Product Created Successfully',null, null,5);

            DB::commit();

            return $this->successResponse('Success', 200, 'Product created successfully');

        }
    }

    public function status(Request $request, $product)
    {
        if(Auth::user()->role_id == 2)
        {
            $product = Product::find($product);

            if ($product) {
                $product->is_active = $product->is_active == 1 ? 0 : 1;
                $product->save();
            }

            $product = Product::find($product->id);

            $statusText = $product->is_active == 1 ? 'Product changed to active state' : 'Product changed to in-active state';

            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Product Status Update','App/Models/Product','products',$product->id,'Update',null,null,'Success',$statusText);

            //Notifiction
            $this->notification(Auth::user()->owner_id, null,'App/Models/Product', $request->id, null, $product->id, now(), Auth::user()->id, $statusText,null, null,5);

            return $this->successResponse("Success", 200, $statusText);
        }
    }

    public function view(Request $request, $product)
    {
        if(Auth::user()->role_id == 2)
        {
             $product = Product::with('category','sub_category','tax','metric')->where('id',$product)->first();

             $product->image = $product->image ? asset('storage/' . $product->image) : null;

             if ($product->category->image) {
                $product->category->image = asset('storage/' . $product->category->image);
            }
            else
            {
                $product->category->image = asset('assets/images/no-image-icon.svg');
            }

            if ($product->sub_category->image) {
                $product->sub_category->image = asset('storage/' . $product->sub_category->image);
            }
            else
            {
                $product->sub_category->image = asset('assets/images/no-image-icon.svg');
            }

            return $this->successResponse($product, 200, 'Product returned successfully');

        }
    }

    public function update(Request $request)
    {
        if(Auth::user()->role_id == 2)
        {
            $rules = [
                
                'image' => 'nullable|mimes:jpg,jpeg,png,gif,webp|max:2048', // up to 2MB
                'category' => 'required',
                'sub_category' => 'required',
                'name' => ['required','string','max:50',
                    Rule::unique('products')->where(function ($query) use ($request) {
                        return $query->where('user_id', Auth::user()->id)
                                     ->where('category_id', $request->category_id)
                                     ->where('sub_category_id', $request->sub_category);
                    })->ignore($request->id), // ignore current product on update
                ],
                'code' => ['required','string','max:50',
                    Rule::unique('products')->where(function ($query) {
                        return $query->where('user_id', Auth::user()->id);
                    })->ignore($request->id), // <-- ignore current record
                ],
                'hsn_code' => 'nullable|string|max:50',
                'price' => 'required|numeric|min:1',
                'tax' => 'required',
                'metric' => 'required',
                'discount_type' => 'nullable|required_with:discount',
                'discount' => 'nullable|required_with:discount_type|numeric|min:0',
                'quantity' => 'nullable|numeric|min:0',

            ];

            $messages = [
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
            ];

            $validator=Validator::make($request->all(),$rules,$messages);

            if ($validator->fails()) {
                return $this->validationFailed($validator->errors(),"The given data was invalid.");
            }

            DB::beginTransaction();

            $product = Product::find($request->id);

            $stock = Stock::where([['shop_id', Auth::user()->id],['category_id', $product->category_id],['sub_category_id', $product->sub_category_id],['product_id', $request->id]])->first();

            $tax = Tax::where('id',$request->tax)->first();

            $price = $request->price; // base price

            $taxAmount = 0; // default, in case tax = 0

            if ($tax && preg_match('/(\d+)%/', $tax->name, $matches)) {
                $taxRate = (int) $matches[1]; // extract number part e.g. "18" from "GST 18%"
                $taxAmount = $price * $taxRate / 100;
            }

            $product->update([ 
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

            

            //$stock = Stock::where([['shop_id', Auth::user()->id],['category_id', $request->category_id],['sub_category_id', $request->sub_category],['product_id', $request->id]])->first();

            $stock->update([ 
                'category_id' => $request->category,
                'sub_category_id' => $request->sub_category,
                'quantity' => $request->quantity,
            ]);


            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Stock Updated','App/Models/Stock','stocks',$stock->id,'Update',null, json_encode($request->all()),'Success','Stock Updated for this product');

            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Product Update','App/Models/Product','products',$product->id,'Update',null, json_encode($request->all()),'Success','Product Updated Successfully');

            //Notifiction
            $this->notification(Auth::user()->owner_id, null,'App/Models/Product', $product->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Product Updated Successfully',null, null,5);

            DB::commit();

            return $this->successResponse($product, 200, 'Product updated successfully');
        }
    }
    
}
