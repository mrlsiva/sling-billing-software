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
use App\Models\Category;
use App\Models\SubCategory;
use App\Traits\Log;
use DB;

class subCategoryController extends Controller
{
    use Log, Notifications, ResponseHelper;

    public function list(Request $request)
    {
        if(Auth::user()->role_id == 2)
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

            foreach($sub_categories as $sub_category)
            {
                $sub_category->image = $sub_category->image
                ? asset('storage/' . $sub_category->image)
                : asset('no-image-icon.jpg');
            }

            return $this->successResponse($sub_categories, 200, 'Successfully returned all sub_categories');
        
        }
    }

    public function store(Request $request)
    {
        if(Auth::user()->role_id == 2)
        {

            $rules = [
                'category' => 'required',
                'sub_category' => ['required','string','max:50',
                    Rule::unique('sub_categories', 'name')->where(function ($query) use ($request) {
                        $userId = Auth::user()->owner_id;
                        return $query->where('user_id', $userId)->where('category_id', $request->category);
                    }),
                ],
                'image' => 'nullable|mimes:jpg,jpeg,png,gif,webp|max:2048',
            ];

            $messages = [
                'category.required' => 'Category is required.',
                'sub_category.required' => 'Sub Category is required.',
                'sub_category.unique' => 'You already have a sub category with this name.',
                'image.mimes' => 'Logo must be a JPG, JPEG, PNG, GIF or WEBP file.',
                'image.max' => 'Logo size must not exceed 2MB.',
            ];

            // Validate here before DB::beginTransaction()
            $validator=Validator::make($request->all(),$rules,$messages);

            if ($validator->fails()) {
                return $this->validationFailed($validator->errors(),"The given data was invalid.");
            }
            

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

            $sub_category->image = $sub_category->image
                ? asset('storage/' . $sub_category->image)
                : asset('no-image-icon.jpg');

            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Sub Category Create','App/Models/SubCategory','sub_categories',$sub_category->id,'Insert',null, json_encode($request->all()),'Success','Sub Category Created Successfully');

            //Notifiction
            $this->notification(Auth::user()->owner_id, null,'App/Models/SubCategory', $sub_category->id, null, json_encode($request->all()), now(), Auth::user()->id, Str::ucfirst($request->sub_category).' sub category created successfully',null, null);

            return $this->successResponse($sub_category, 200, 'Sub Category created successfully');
        }
    }

    public function view(Request $request, $sub_category)
    {
        if(Auth::user()->role_id == 2)
        {
            $sub_category = SubCategory::where([['id',$sub_category],['user_id',Auth::user()->owner_id]])->first();

            if ($sub_category) {

                $sub_category->image = $sub_category->image ? asset('storage/' . $sub_category->image) : asset('no-image-icon.jpg');
                return $this->successResponse($sub_category, 200, 'Sub Category returned successfully');
            }
        }
    }

    public function status(Request $request,SubCategory $sub_category)
    {
        if(Auth::user()->role_id == 2)
        {

            if ($sub_category) {
                $sub_category->is_active = $sub_category->is_active == 1 ? 0 : 1;
                $sub_category->save();
            }

            $statusText = $sub_category->is_active == 1 ? 'Sub Category changed to active state' : 'Sub Category changed to in-active state';

            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Sub Category Status Update','App/Models/SubCategory','sub_categories',$sub_category->id,'Update',null,null,'Success',$statusText);

            //Notifiction
            $this->notification(Auth::user()->owner_id, null,'App/Models/SubCategory', $sub_category->id, null, json_encode($request->all()), now(), Auth::user()->id, $sub_category->name.' '.$statusText,null, null);

            return $this->successResponse("Success", 200, $statusText);

        }

    } 

    public function update(Request $request)
    {
        
        if(Auth::user()->role_id == 2)
        {

            $rules = [
                'sub_category_id' => 'required',
                'sub_category_name' => ['required','string','max:50',
                    Rule::unique('sub_categories', 'name')->where(function ($query) use ($request) {
                        $userId = Auth::user()->owner_id;
                        return $query->where('user_id', $userId)->where('category_id', $request->category_id);
                    })->ignore($request->sub_category_id),
                ],
                'image' => 'nullable|mimes:jpg,jpeg,png,gif,webp|max:2048',
            ];

            $messages = [
                'sub_category_id.required'       => 'Category is required.',
                'sub_category_name.required'   => 'Sub Category is required.',
                'sub_category_name.unique'     => 'You already have a sub category with this name.',
                'image.mimes'             => 'Logo must be a JPG, JPEG, PNG, GIF, or WEBP file.',
                'image.max'               => 'Logo size must not exceed 2MB.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return $this->validationFailed($validator->errors(), "The given data was invalid.");
            }


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
            $this->notification(Auth::user()->owner_id, null,'App/Models/SubCategory', $sub_category->id, null, json_encode($request->all()), now(), Auth::user()->id, Str::ucfirst($request->sub_category).' sub category updated successfully',null, null);

            $sub_category->image = $sub_category->image
                ? asset('storage/' . $sub_category->image)
                : asset('no-image-icon.jpg');

            return $this->successResponse($sub_category, 200, 'Sub Category updated successfully');
        }
    }
}
