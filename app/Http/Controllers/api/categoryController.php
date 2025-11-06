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
use App\Traits\Log;
use DB;

class categoryController extends Controller
{
    use Log, Notifications, ResponseHelper;

    public function list(Request $request)
    {
        if(Auth::user()->role_id == 2)
        {
            $categories = Category::with(['sub_categories'])->where('user_id',Auth::user()->owner_id)->when(request('name'), function ($query) {
                $query->where('name', 'like', '%' . request('name') . '%');
            })->orderBy('id','desc')->paginate(10);

            foreach($categories as $category)
            {
                $category->image = $category->image
                ? asset('storage/' . $category->image)
                : asset('no-image-icon.svg');
            }

            return $this->successResponse($categories, 200, 'Successfully returned all categories');
        
        }
    }

    public function store(Request $request)
    {
        if(Auth::user()->role_id == 2)
        {

            $rules = [
                'image' => 'nullable|mimes:jpg,jpeg,png,gif,webp|max:2048', // up to 2MB
                'category' => ['required','string','max:50',
                    Rule::unique('categories', 'name')->where(function ($query) {
                        $userId = Auth::user()->owner_id;
                        return $query->where('user_id', $userId);
                    }),
                ],
            ];

            $messages = [
                'category.required' => 'Category name is required.',
                'category.unique'   => 'You already have a category with this name.',
                'image.mimes'       => 'Image must be a JPG, JPEG, PNG, GIF, or WEBP file.',
                'image.max'         => 'Image size must not exceed 2MB.',
            ];

            $validator=Validator::make($request->all(),$rules,$messages);

            if ($validator->fails()) {
                return $this->validationFailed($validator->errors(),"The given data was invalid.");
            }

            DB::beginTransaction();

            $category = Category::create([ 
                'user_id' => Auth::user()->owner_id,
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

            $category->image = $category->image
                ? asset('storage/' . $category->image)
                : asset('no-image-icon.svg');

            DB::commit();

            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Category Create','App/Models/Category','categories',$category->id,'Insert',null,json_encode($request->all()),'Success','Category Created Successfully');

            //Notifiction
            $this->notification(Auth::user()->owner_id, null,'App/Models/Category', $category->id, null, json_encode($request->all()), now(), Auth::user()->id, Str::ucfirst($request->category). ' category created successfully',null, null,1);

            return $this->successResponse($category, 200, 'Category created successfully');
        }
    }

    public function view(Request $request, $category)
    {
        if(Auth::user()->role_id == 2)
        {
            $category = Category::where([['id',$category],['user_id',Auth::user()->owner_id]])->first();

            if ($category) {

                $category->image = $category->image ? asset('storage/' . $category->image) : asset('no-image-icon.svg');
                return $this->successResponse($category, 200, 'Category returned successfully');
            }
        }
    }

    public function update(Request $request)
    {
        
        if(Auth::user()->role_id == 2)
        {
            $rules = [
                'image' => 'nullable|mimes:jpg,jpeg,png,gif,webp|max:2048', // up to 2MB
                'category_id' => 'required',
                'category_name' => ['required','string','max:50',
                    Rule::unique('categories', 'name')->ignore($request->category_id)->where(function ($query) {
                        $userId = Auth::user()->owner_id; // handles both owner and normal user
                        return $query->where('user_id', $userId);
                    }),
                ],
            ];

            $messages = [
                'category_name.required' => 'Category name is required.',
                'category_name.unique'   => 'You already have a category with this name.',
                'image.mimes'       => 'Image must be a JPG, JPEG, PNG, GIF, or WEBP file.',
                'image.max'         => 'Image size must not exceed 2MB.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return $this->validationFailed($validator->errors(), "The given data was invalid.");
            }

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
            $this->notification(Auth::user()->owner_id, null,'App/Models/Category', $category->id, null, json_encode($request->all()), now(), Auth::user()->id, Str::ucfirst($request->category_name).' category updated successfully',null, null,1);

            $category->image = $category->image ? asset('storage/' . $category->image) : asset('no-image-icon.svg');

            return $this->successResponse($category, 200, 'Category updated successfully');
        }

    }


    public function status(Request $request,Category $category)
    {
        if(Auth::user()->role_id == 2)
        {

            if ($category) {
                $category->is_active = $category->is_active == 1 ? 0 : 1;
                $category->save();
            }

            $statusText = $category->is_active == 1 ? 'Category changed to active state' : 'Category changed to in-active state';

            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Category Status Update','App/Models/Category','categories',$category->id,'Update',null,null,'Success',$category->name.' '.$statusText);

            //Notifiction
            $this->notification(Auth::user()->owner_id, null,'App/Models/Category', $category->id, null, json_encode($request->all()), now(), Auth::user()->id, $category->name.' '.$statusText,null, null,1);

            return $this->successResponse("Success", 200, $statusText);

        }

    }
}
