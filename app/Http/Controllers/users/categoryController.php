<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CategoriesExport;
use App\Imports\CategoryImport;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\BulkUploadLog;
use App\Models\Category;
use App\Traits\Notifications;
use App\Traits\Log;
use DB;

class categoryController extends Controller
{
    use Log, Notifications;

    public function index(Request $request)
    {
        $categories = Category::with(['sub_categories'])->where('user_id',Auth::user()->id)->when(request('name'), function ($query) {
                $query->where('name', 'like', '%' . request('name') . '%');
        })->orderBy('id','desc')->paginate(10);
        return view('users.categories.index',compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'nullable|mimes:jpg,jpeg,png,gif,webp|max:2048', // up to 2MB
            'category' => ['required','string','max:50',
                Rule::unique('categories', 'name')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                }),
            ],
        ], 
        [
            'category.required' => 'Name is required.',
        ]);

        DB::beginTransaction();

        $category = Category::create([ 
            'user_id' => Auth::user()->id,
            'name' => Str::ucfirst($request->category),
            'is_active' => 1,
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = config('path.root') . '/' . request()->route('company') . '/' . config('path.category');

            // Save the file
            $filePath = $file->storeAs($path, $filename, 'public');

            // Save to user
            $category->image = $filePath; // This is relative to storage/app/public
            $category->save();
        }

        DB::commit();

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Category Create','App/Models/Category','categories',$category->id,'Insert',null,json_encode($request->all()),'Success','Category Created Successfully');

        //Notifiction
        $this->notification(Auth::user()->owner_id, null,'App/Models/Category', $category->id, null, json_encode($request->all()), now(), Auth::user()->id, Str::ucfirst($request->category). ' category created successfully',null, null);

        return redirect()->back()->with('toast_success', 'Category created successfully.');
    }

    public function edit(Request $request)
    {
        $category = Category::find($request->id);

        if ($category) {
            return response()->json([
                'category_name' => $category->name
            ]);
        }

        return response()->json(['error' => 'Category not found'], 404);
    }

    public function update(Request $request)
    {
        $request->validate([
            'image' => 'nullable|mimes:jpg,jpeg,png,gif,webp|max:2048', // Allow jpg, jpeg, png up to 2MB
            'category_name' => ['required','string','max:50',
                Rule::unique('categories', 'name')->where(fn($query) => $query->where('user_id', Auth::id()))->ignore($request->category_id),
            ],

            'category_id' => 'required',
        ], 
        [
            'category_name.required' => 'Name is required.',
            'image.mimes' => 'Logo must be a JPG, JPEG or PNG file.',
            'image.max' => 'Logo size must not exceed 2MB.',
        ]);

        DB::beginTransaction();

        $category = Category::find($request->category_id);

        $category->update([ 
            'name' => Str::ucfirst($request->category_name)
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = config('path.root') . '/' . request()->route('company') . '/' . config('path.category');

            // Save the file
            $filePath = $file->storeAs($path, $filename, 'public');

            // Save to user
            $category->image = $filePath; // This is relative to storage/app/public
            $category->save();
        }

        DB::commit();

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Category Update','App/Models/Category','categories',$category->id,'Update',null,json_encode($request->all()),'Success','Category Updated Successfully');

        //Notifiction
        $this->notification(Auth::user()->owner_id, null,'App/Models/Category', $category->id, null, json_encode($request->all()), now(), Auth::user()->id, Str::ucfirst($request->category_name).' category updated successfully',null, null);

        return redirect()->back()->with('toast_success', 'Category updated successfully.');
    }

    public function status(Request $request)
    {
        $category = Category::find($request->id);

        if ($category) {
            $category->is_active = $category->is_active == 1 ? 0 : 1;
            $category->save();
        }

        $category = Category::find($request->id);

        $statusText = $category->is_active == 1 ? 'Category changed to active state' : 'Category changed to in-active state';

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Category Status Update','App/Models/Category','categories',$request->id,'Update',null,null,'Success',$category->name.' '.$statusText);

        //Notifiction
        $this->notification(Auth::user()->owner_id, null,'App/Models/Category', $category->id, null, json_encode($request->all()), now(), Auth::user()->id, $category->name.' '.$statusText,null, null);

        return redirect()->back()->with('toast_success', "Category Status Changed");
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

        $import = new CategoryImport($run_id);
        Excel::import($import, $request->file('file'));

        $skipped = [];
        if ($import->failures()->isNotEmpty()) {
            foreach ($import->failures() as $failure) {
                $skipped[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }
        }

        

        // Counts
        $totalRecords      = $import->getRowCount(); // make sure your import counts ALL rows
        $errorRecords      = count($skipped);
        $successfulRecords = max(0, $totalRecords - $errorRecords); // prevent negative

        // Base directory for this run
        $directory = "bulk_uploads/categories/{$run_id}";

        // Ensure directory exists (on public disk)
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        // 1️⃣ Save uploaded Excel file (public disk)
        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $excelPath    = $uploadedFile->storeAs($directory, $originalName, 'public');

        // 2️⃣ Build log content
        $logContent  = "======================" . PHP_EOL;
        $logContent .= "Bulk Upload Report" . PHP_EOL;
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

        // 3️⃣ Save log file (public disk)
        $logFile = "{$directory}/log.txt";
        Storage::disk('public')->put($logFile, $logContent);

        // 4️⃣ Save into BulkUploadLog table
        $bulk_upload = BulkUploadLog::create([
            'user_id'            => auth()->id(),
            'run_id'             => $run_id,
            'run_on'             => now(),
            'module'             => 'Category',
            'total_record'       => $totalRecords,
            'successfull_record' => $successfulRecords,
            'error_record'       => $errorRecords,
            'excel'              => $excelPath,   // stored in storage/app/public
            'log'                => $logFile,     // stored in storage/app/public
        ]);

        //Notifiction
        $this->notification(Auth::user()->owner_id, null,'App/Models/BulkUploadLog', $bulk_upload->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Bulk upload done for category',null, $logFile);

        if ($errorRecords > 0) {
            return redirect()->back()->with('error_alert', 'Some rows were skipped: ' . implode(' | ', $skipped));
        }

        return redirect()->back()->with('toast_success', 'Bulk categories uploaded successfully.');
    }




    public function download(Request $request)
    {
        $categories = Category::with('sub_categories')
            ->where('user_id', Auth::user()->id)
            ->when($request->name, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->name . '%');
            })
            ->orderBy('id','desc')
            ->get();

        return Excel::download(new CategoriesExport($categories), 'Categories.xlsx');
    }
}
