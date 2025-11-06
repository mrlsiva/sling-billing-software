<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SubCategoryExport;
use App\Imports\SubCategoryImport;
use Illuminate\Validation\Rule;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use App\Models\BulkUploadLog;
use Illuminate\Support\Str;
use App\Models\SubCategory;
use App\Models\Category;
use App\Traits\Log;
use DB;

class subCategoryController extends Controller
{
    use Log, Notifications; 

    public function index(Request $request)
    {
        $categories = Category::where([['user_id',Auth::user()->owner_id],['is_active',1]])->get();
        $sub_categories = SubCategory::where('user_id',Auth::user()->owner_id)->when(request('name'), function ($query) {
            $search = request('name');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%") // match subcategory name
                  ->orWhereHas('category', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%"); // match category name
                  });
            });
        })->orderBy('id','desc')->paginate(10);
        return view('users.sub_categories.index',compact('sub_categories','categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required',
            'sub_category' => ['required','string','max:50',
                Rule::unique('sub_categories', 'name')->where(fn($q) => $q->where('user_id', Auth::user()->owner_id)->where('category_id', $request->category)),
            ],
            'image' => 'nullable|mimes:jpg,jpeg,png,gif,webp|max:2048', // Allow jpg, jpeg, png up to 2MB
        ], 
        [
            'category.required' => 'Category is required.',
            'sub_category.required' => 'Sub Category is required.',
            'sub_category.unique'   => 'You already have a sub category with this name.',
            'image.mimes' => 'Logo must be a JPG, JPEG or PNG file.',
            'image.max' => 'Logo size must not exceed 2MB.',
        ]);

        DB::beginTransaction();

        $sub_category = SubCategory::create([ 
            'user_id' => Auth::user()->owner_id,
            'category_id' => $request->category,
            'name' => Str::ucfirst($request->sub_category),
            'is_active' => 1,
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = config('path.root') . '/' . request()->route('company') . '/' . config('path.sub_category');

            // Save the file
            $filePath = $file->storeAs($path, $filename, 'public');

            // Save to user
            $sub_category->image = $filePath; // This is relative to storage/app/public
            $sub_category->save();
        }

        DB::commit();

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Sub Category Create','App/Models/SubCategory','sub_categories',$sub_category->id,'Insert',null, json_encode($request->all()),'Success','Sub Category Created Successfully');

        //Notifiction
        $this->notification(Auth::user()->owner_id, null,'App/Models/SubCategory', $sub_category->id, null, json_encode($request->all()), now(), Auth::user()->id, Str::ucfirst($request->sub_category).' sub category created successfully',null, null,2);

        return redirect()->back()->with('toast_success', 'Sub Category created successfully.');
    }

    public function edit(Request $request)
    {
        $sub_category = SubCategory::find($request->id);

        if ($sub_category) {
            return response()->json([
                'sub_category' => $sub_category
            ]);
        }

        return response()->json(['error' => 'Sub Category not found'], 404);
    }

    public function update(Request $request)
    {
        $request->validate([
            'image' => 'nullable|mimes:jpg,jpeg,png,gif,webp|max:2048', // Allow jpg, jpeg, png up to 2MB
            'category_id' => 'required',
            'sub_category_name' => ['required','string','max:50',
                Rule::unique('sub_categories', 'name')->where(fn($q) => $q->where('user_id', Auth::user()->owner_id)->where('category_id', $request->category_id))->ignore($request->sub_category_id),
            ], 
        ],
        [
            'category_id.required' => 'Category is required.',
            'sub_category_name.required' => 'Sub Category is required.',
            'image.mimes' => 'Logo must be a JPG, JPEG or PNG file.',
            'image.max' => 'Logo size must not exceed 2MB.',
        ]);

        DB::beginTransaction();

        $sub_category = SubCategory::find($request->sub_category_id);

        $sub_category->update([ 
            'category_id' => $request->category_id,
            'name' => Str::ucfirst($request->sub_category_name),
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = config('path.root') . '/' . request()->route('company') . '/' . config('path.sub_category');

            // Save the file
            $filePath = $file->storeAs($path, $filename, 'public');

            // Save to user
            $sub_category->image = $filePath; // This is relative to storage/app/public
            $sub_category->save();
        }

        DB::commit();

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Sub Category Update','App/Models/SubCategory','sub_categories',$sub_category->id,'Update',null,$request,'Success','Sub Category Updated Successfully');

        //Notifiction
        $this->notification(Auth::user()->owner_id, null,'App/Models/SubCategory', $sub_category->id, null, json_encode($request->all()), now(), Auth::user()->id, Str::ucfirst($request->sub_category).' sub category updated successfully',null, null,2);

        return redirect()->back()->with('toast_success', 'Sub Category Updated Successfully.');
    }

    public function status(Request $request)
    {
        $sub_category = SubCategory::find($request->id);

        if ($sub_category) {
            $sub_category->is_active = $sub_category->is_active == 1 ? 0 : 1;
            $sub_category->save();
        }

        $sub_category = SubCategory::find($request->id);

        $statusText = $sub_category->is_active == 1 ? 'Sub Category changed to active state' : 'Sub Category changed to in-active state';

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Sub Category Status Update','App/Models/SubCategory','sub_categories',$request->id,'Update',null,null,'Success',$statusText);

        //Notifiction
        $this->notification(Auth::user()->owner_id, null,'App/Models/SubCategory', $sub_category->id, null, json_encode($request->all()), now(), Auth::user()->id, $sub_category->name.' '.$statusText,null, null,2);

        return redirect()->back()->with('toast_success', "Sub Category Status Changed");
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
        $import = new SubCategoryImport($run_id);
        Excel::import($import, $request->file('file'));

        // Collect skipped rows
        $skipped = [];
        if ($import->failures()->isNotEmpty()) {
            foreach ($import->failures() as $failure) {
                $skipped[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }
        }

        // Counts
        $totalRecords      = $import->getRowCount(); // make sure import counts ALL rows
        $errorRecords      = count($skipped);
        $successfulRecords = max(0, $totalRecords - $errorRecords);

        // Directory for this run
        $directory = "bulk_uploads/sub_categories/{$run_id}";

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
        $logContent .= "Bulk SubCategory Upload Report" . PHP_EOL;
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
            'module'             => 'Sub Category',
            'total_record'       => $totalRecords,
            'successfull_record' => $successfulRecords,
            'error_record'       => $errorRecords,
            'excel'              => $excelPath,
            'log'                => $logFile,
        ]);

        //Notifiction
        $this->notification(Auth::user()->owner_id, null,'App/Models/BulkUploadLog', $bulk_upload->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Bulk upload done for sub category',null, $logFile,2);

        // Return response
        if ($errorRecords > 0) {
            return redirect()->back()->with('error_alert', 'Some rows were skipped: ' . implode(' | ', $skipped));
        }

        return redirect()->back()->with('toast_success', 'Bulk sub categories uploaded successfully.');
    }


    public function download(Request $request)
    {
        $sub_categories = SubCategory::with('category')
            ->where('user_id', Auth::user()->owner_id)
            ->when($request->name, function ($query) use ($request) {
                $search = $request->name;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhereHas('category', function ($q2) use ($search) {
                          $q2->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->orderBy('id', 'desc')
            ->get();

        return Excel::download(new SubCategoryExport($sub_categories), 'Sub Categories.xlsx');
    }

}
