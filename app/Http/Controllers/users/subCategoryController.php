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
        $categories = Category::where('is_active',1)->get();
        $sub_categories = SubCategory::orderBy('id','desc')->paginate(30);
        return view('users.sub_categories.index',compact('sub_categories','categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required',
            'sub_category' => 'required',
        ], 
        [
            'category.required' => 'Category is required.',
            'sub_category.required' => 'Sub Category is required.',
        ]);

        DB::beginTransaction();

        $sub_category = SubCategory::create([ 
            'category_id' => $request->category,
            'name' => Str::ucfirst($request->sub_category),
            'is_active' => 1,
        ]);

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
            'category_id' => 'required',
            'sub_category_name' => 'required',
        ], 
        [
            'category_id.required' => 'Category is required.',
            'sub_category_name.required' => 'Sub Category is required.',
        ]);

        DB::beginTransaction();

        $sub_category = SubCategory::find($request->sub_category_id);

        $sub_category->update([ 
            'category_id' => $request->category_id,
            'name' => Str::ucfirst($request->sub_category_name),
        ]);

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
