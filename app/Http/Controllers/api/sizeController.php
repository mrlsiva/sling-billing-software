<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Traits\ResponseHelper;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use App\Models\Size;
use App\Traits\Log;
use DB;

class sizeController extends Controller
{
    use Log, Notifications, ResponseHelper;

    public function list(Request $request)
    {
        $sizes = Size::where('shop_id', Auth::user()->owner_id)
            ->when($request->size, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->size . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return $this->successResponse($sizes, 200, 'Sizes retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string',
                Rule::unique('sizes')->where(function ($query) {
                    return $query->where('shop_id', Auth::user()->owner_id);
                }),
            ],
        ], [
            'name.required' => 'Size is required.',
            'name.unique'   => 'This size already exists for your account.',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed($validator->errors(), 'Validation failed.');
        }

        DB::beginTransaction();

        $size = Size::create([
            'shop_id'   => Auth::user()->owner_id,
            'name'      => $request->name,
            'is_active' => 1,
        ]);

        DB::commit();

        $this->addToLog($this->unique(), Auth::user()->id, 'Size Create', 'App/Models/Size', 'sizes', $size->id, 'Insert', null, json_encode($request->all()), 'Success', 'Size Created Successfully');
        $this->notification(Auth::user()->owner_id, null, 'App/Models/Size', $size->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Size "' . $request->name . '" created successfully', null, null, 16);

        return $this->successResponse($size, 200, 'Size created successfully.');
    }

    public function view(Request $request, $size)
    {
        $size = Size::where('id', $size)->where('shop_id', Auth::user()->owner_id)->first();

        if (!$size) {
            return $this->errorResponse([], 404, 'Size not found.');
        }

        return $this->successResponse($size, 200, 'Size retrieved successfully.');
    }

    public function status(Request $request, $size)
    {
        $size = Size::where('id', $size)->where('shop_id', Auth::user()->owner_id)->first();

        if (!$size) {
            return $this->errorResponse([], 404, 'Size not found.');
        }

        $size->is_active = $size->is_active == 1 ? 0 : 1;
        $size->save();

        $statusText = $size->is_active == 1 ? 'Size changed to active state' : 'Size changed to in-active state';

        $this->addToLog($this->unique(), Auth::user()->id, 'Size Status Update', 'App/Models/Size', 'sizes', $size->id, 'Update', null, null, 'Success', $statusText);
        $this->notification(Auth::user()->owner_id, null, 'App/Models/Size', $size->id, null, json_encode($request->all()), now(), Auth::user()->id, $size->name . ' ' . $statusText, null, null, 16);

        return $this->successResponse($size, 200, $statusText);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'size_id' => 'required|exists:sizes,id',
            'size'    => ['required', 'string',
                Rule::unique('sizes', 'name')
                    ->where(fn($q) => $q->where('shop_id', Auth::user()->owner_id))
                    ->ignore($request->size_id),
            ],
        ], [
            'size.required' => 'Size is required.',
            'size.unique'   => 'This size already exists for your account.',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed($validator->errors(), 'Validation failed.');
        }

        DB::beginTransaction();

        $size = Size::find($request->size_id);
        $old_name = $size->name;
        $size->update(['name' => $request->size]);

        DB::commit();

        $this->addToLog($this->unique(), Auth::user()->id, 'Size Update', 'App/Models/Size', 'sizes', $size->id, 'Update', null, $request, 'Success', 'Size Updated Successfully');
        $this->notification(Auth::user()->owner_id, null, 'App/Models/Size', $size->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Size "' . $old_name . '" updated to "' . $request->size . '" successfully', null, null, 16);

        return $this->successResponse($size, 200, 'Size updated successfully.');
    }
}