<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductExport;
use App\Imports\ProductImport;
use Illuminate\Validation\Rule;
use App\Traits\Notifications;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\BulkUploadLog;
use App\Models\SubCategory;
use App\Models\Category;
use App\Models\Product;
use App\Models\Metric;
use App\Models\Stock;
use App\Models\Colour;
use App\Models\Size;
use App\Traits\common;
use App\Traits\Log;
use App\Models\Tax;
use DB;

class productController extends Controller
{
    use Log, common, Notifications;

    public function index(Request $request)
    {
        $products = Product::where('user_id',Auth::user()->owner_id)->when(request('product'), function ($query) {
            $search = request('product');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('hsn_code', 'like', "%{$search}%");
            });
        })->orderBy('id','desc')->paginate(10);
        return view('users.products.index',compact('products'));
    }

    public function create(Request $request)
    {
        $categories = Category::where([['user_id',Auth::user()->owner_id],['is_active',1]])->get();
        $taxes = Tax::where([['shop_id',Auth::user()->owner_id],['is_active',1]])->get();
        $metrics = Metric::where([['shop_id',Auth::user()->id],['is_active',1]])->get();
        $sizes = Size::where('shop_id', Auth::user()->owner_id)->where('is_active', 1)->get();
        $colours = Colour::where('shop_id', Auth::user()->owner_id)->where('is_active', 1)->get();

        return view('users.products.create',compact('categories','taxes','metrics','sizes','colours'));
    }

    public function get_sub_category(Request $request)
    {
        return $sub_categories = SubCategory::where([['user_id',Auth::user()->owner_id],['category_id',$request->id],['is_active',1]])->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'nullable|mimes:jpg,jpeg,png,gif,webp|max:2048', // up to 2MB
            'category' => 'required',
            'sub_category' => 'required',
            'name' => ['required','string','max:50',
                Rule::unique('products')->where(function ($query) use ($request) {
                    return $query->where('user_id', Auth::user()->owner_id)
                                 ->where('category_id', $request->category)
                                 ->where('sub_category_id', $request->sub_category);
                }),
            ],
            'code' => ['required','string','max:50',
                Rule::unique('products')->where(function ($query) use ($request) {
                    return $query->where('user_id', Auth::user()->owner_id);
                }),
            ],
            'hsn_code' => 'nullable|string|max:50',
            'price' => 'required|numeric|min:1',
            'tax' => 'required',
            'metric' => 'required',
            'discount_type' => 'nullable|required_with:discount',
            'discount' => 'nullable|required_with:discount_type|numeric|min:0',
            'quantity' => 'numeric|min:0',

             // ⭐ Conditional validation
            'sizes'   => 'required_if:is_size_differentiation_available,1|array',
            'sizes.*' => 'required_if:is_size_differentiation_available,1',

            'colours'   => 'required_if:is_colour_differentiation_available,1|array',
            'colours.*' => 'required_if:is_colour_differentiation_available,1',
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

            // ⭐ Custom error messages
            'sizes.required_if' => 'Please select at least one size.',
            'colours.required_if' => 'Please select at least one colour.',
        ]);
        return 'gh';

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
            'is_size_differentiation_available' => $request->has('is_size_differentiation_available') ? 1 : 0,
            'is_colour_differentiation_available' => $request->has('is_colour_differentiation_available') ? 1 : 0,
            'is_active' => 1,
        ]);

        $product->size_id = $request->sizes ? implode(',', $request->sizes) : null;
        $product->colour_id = $request->colours ? implode(',', $request->colours) : null;
        $product->save();

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
            'shop_id' => Auth::user()->owner_id,
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

        return redirect()->back()->with('toast_success', 'Product created successfully.');
        
    }

    public function edit(Request $request,$company,$id)
    {
        $categories = Category::where([['user_id',Auth::user()->owner_id],['is_active',1]])->get();
        $product = Product::find($id);
        $taxes = Tax::where([['shop_id',Auth::user()->id],['is_active',1]])->get();
        $metrics = Metric::where([['shop_id',Auth::user()->id],['is_active',1]])->get();
        return view('users.products.edit',compact('product','categories','taxes','metrics'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'image' => 'nullable|mimes:jpg,jpeg,png,gif,webp|max:2048', // up to 2MB
            'category_id' => 'required',
            'sub_category' => 'required',
            'name' => ['required','string','max:50',
                Rule::unique('products')->where(function ($query) use ($request) {
                    return $query->where('user_id', Auth::user()->owner_id)
                                 ->where('category_id', $request->category_id)
                                 ->where('sub_category_id', $request->sub_category);
                })->ignore($request->id), // ignore current product on update
            ],
            'code' => ['required','string','max:50',
                Rule::unique('products')->where(function ($query) {
                    return $query->where('user_id', Auth::user()->owner_id);
                })->ignore($request->id), // <-- ignore current record
            ],
            'hsn_code' => 'nullable|string|max:50',
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

        $stock = Stock::where([['shop_id', Auth::user()->owner_id],['category_id', $product->category_id],['sub_category_id', $product->sub_category_id],['product_id', $request->id]])->first();

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
            'is_size_differentiation_available' => $request->has('is_size_differentiation_available') ? 1 : 0,
            'is_colour_differentiation_available' => $request->has('is_colour_differentiation_available') ? 1 : 0,
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
            'category_id' => $request->category_id,
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

        //Notifiction
        $this->notification(Auth::user()->owner_id, null,'App/Models/Product', $request->id, null, json_encode($request->all()), now(), Auth::user()->id, $statusText,null, null,5);

        return redirect()->back()->with('toast_success', $statusText);
    }

    public function view(Request $request)
    {
        return view('users.products.view');
    }

    public function bulk_upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx|max:10000',
        ]);

        // Generate unique run_id
        do {
            $run_id = rand(100000, 999999);
        } while (BulkUploadLog::where('run_id', $run_id)->exists());

        // Pass run_id to import
        $import = new ProductImport($run_id); // make sure ProductImport accepts run_id
        Excel::import($import, $request->file('file'));

        // Collect skipped rows
        $skipped = [];
        if ($import->failures()->isNotEmpty()) {
            foreach ($import->failures() as $failure) {
                $skipped[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }
        }

        // Counts
        $totalRecords      = $import->getRowCount(); // implement getRowCount() in ProductImport
        $errorRecords      = count($skipped);
        $successfulRecords = max(0, $totalRecords - $errorRecords);

        // Directory for this run
        $directory = "bulk_uploads/products/{$run_id}";

        // Ensure directory exists on public disk
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        // Save uploaded Excel file
        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $excelPath    = $uploadedFile->storeAs($directory, $originalName, 'public');

        // Build log content
        $logContent  = "======================" . PHP_EOL;
        $logContent .= "Bulk Product Upload Report" . PHP_EOL;
        $logContent .= "Uploaded On: " . now() . PHP_EOL;
        $logContent .= "Run ID: {$run_id}" . PHP_EOL;
        $logContent .= "Uploaded File: {$originalName}" . PHP_EOL;
        $logContent .= "Total Records: {$totalRecords}" . PHP_EOL;
        $logContent .= "Successful Records: {$successfulRecords}" . PHP_EOL;
        $logContent .= "Error Records: {$errorRecords}" . PHP_EOL;

        if ($errorRecords > 0) {
            $logContent .= "Error Details:" . PHP_EOL;
            foreach ($skipped as $error) {
                $logContent .= "- {$error}" . PHP_EOL;
            }
        }

        $logContent .= "======================" . PHP_EOL . PHP_EOL;

        // Save log file
        $logFile = "{$directory}/log.txt";
        Storage::disk('public')->put($logFile, $logContent);

        // Save record in BulkUploadLog table
        $bulk_upload = BulkUploadLog::create([
            'user_id'            => auth()->id(),
            'run_id'             => $run_id,
            'run_on'             => now(),
            'module'             => 'Product',
            'total_record'       => $totalRecords,
            'successfull_record' => $successfulRecords,
            'error_record'       => $errorRecords,
            'excel'              => $excelPath,
            'log'                => $logFile,
        ]);

        //Notifiction
        $this->notification(Auth::user()->owner_id, null,'App/Models/BulkUploadLog', $bulk_upload->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Bulk upload done for product',null, $logFile,5);

        // Return response
        if ($errorRecords > 0) {
            return redirect()->back()->with('error_alert', 'Some rows were skipped: ' . implode(' | ', $skipped));
        }

        return redirect()->back()->with('toast_success', 'Bulk products uploaded successfully.');
    }


    public function download(Request $request)
    {
        $products = Product::with(['category', 'sub_category', 'metric', 'tax'])
            ->where('user_id', Auth::user()->owner_id)
            ->when($request->product, function ($query) use ($request) {
                $search = $request->product;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('hsn_code', 'like', "%{$search}%");
                });
            })
            ->orderBy('id', 'desc')
            ->get();

        return Excel::download(new ProductExport($products), 'Products.xlsx'); // ✅ pass $products
    }
}
