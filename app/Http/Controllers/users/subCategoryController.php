<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\SubCategory;
use App\Models\Category;
use App\Traits\Log;
use DB;

class subCategoryController extends Controller
{
    use Log; 

    public function index(Request $request)
    {
        $categories = Category::where([['user_id',Auth::user()->id],['is_active',1]])->get();
        $sub_categories = SubCategory::where('user_id',Auth::user()->id)->when(request('name'), function ($query) {
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
            'sub_category' => 'required',
            'image' => 'nullable|mimes:jpg,jpeg,png,gif|max:2048', // Allow jpg, jpeg, png up to 2MB
        ], 
        [
            'category.required' => 'Category is required.',
            'sub_category.required' => 'Sub Category is required.',
            'image.mimes' => 'Logo must be a JPG, JPEG or PNG file.',
            'image.max' => 'Logo size must not exceed 2MB.',
        ]);

        DB::beginTransaction();

        $sub_category = SubCategory::create([ 
            'user_id' => Auth::user()->id,
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
        $this->addToLog($this->unique(),Auth::user()->id,'Sub Category Create','App/Models/SubCategory','sub_categories',$sub_category->id,'Insert',null,$request,'Success','Sub Category Created Successfully');

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
            'image' => 'nullable|mimes:jpg,jpeg,png,gif|max:2048', // Allow jpg, jpeg, png up to 2MB
            'category_id' => 'required',
            'sub_category_name' => 'required',
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

        return redirect()->back()->with('toast_success', "Sub Category Status Changed");
    }
}
