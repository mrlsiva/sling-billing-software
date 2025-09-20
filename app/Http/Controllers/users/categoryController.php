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
use App\Models\Category;
use App\Traits\Log;
use DB;

class categoryController extends Controller
{
    use Log;

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
            'image' => 'nullable|mimes:jpg,jpeg,png,gif|max:2048', // up to 2MB
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
        $this->addToLog($this->unique(),Auth::user()->id,'Category Create','App/Models/Category','categories',$category->id,'Insert',null,$request,'Success','Category Created Successfully');

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
            'image' => 'nullable|mimes:jpg,jpeg,png,gif|max:2048', // Allow jpg, jpeg, png up to 2MB
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
        $this->addToLog($this->unique(),Auth::user()->id,'Category Update','App/Models/Category','categories',$category->id,'Update',null,$request,'Success','Category Updated Successfully');

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
        $this->addToLog($this->unique(),Auth::user()->id,'Category Status Update','App/Models/Category','categories',$request->id,'Update',null,null,'Success',$statusText);

        return redirect()->back()->with('toast_success', "Category Status Changed");
    }

    public function bulk_upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx|max:10000', // Allow larger files
        ]);

        $import = new CategoryImport();
        Excel::import($import, $request->file('file'));

        $skipped = [];
        if ($import->failures()->isNotEmpty()) {
            foreach ($import->failures() as $failure) {
                $skipped[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }
        }

        if (count($skipped) > 0) {

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
