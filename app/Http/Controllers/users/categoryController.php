<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
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
        $categories = Category::with(['sub_categories'])->orderBy('id','desc')->paginate(30);
        return view('users.categories.index',compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:50',
        ], 
        [
            'category.required' => 'Name is required.',
        ]);

        DB::beginTransaction();

        $category = Category::create([ 
            'name' => Str::ucfirst($request->category),
            'is_active' => 1,
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = config('path.root') . '/' . config('path.HO.head_office') . '/' . request()->route('company') . '/' . config('path.HO.category');

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
            'category_name' => 'required|string|max:50',
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
            $path = config('path.root') . '/' . config('path.HO.head_office') . '/' . request()->route('company') . '/' . config('path.HO.category');

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
}
