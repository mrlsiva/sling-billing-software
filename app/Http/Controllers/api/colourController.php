<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Traits\ResponseHelper;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use App\Models\Colour;
use App\Traits\Log;
use DB;

class colourController extends Controller
{
    use Log, Notifications, ResponseHelper;

    public function list(Request $request)
    {
        $colours = Colour::where('shop_id', Auth::user()->owner_id)
            ->when($request->colour, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->colour . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return $this->successResponse($colours, 200, 'Colours retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string',
                Rule::unique('colours')->where(function ($query) {
                    return $query->where('shop_id', Auth::user()->owner_id);
                }),
            ],
        ], [
            'name.required' => 'Colour is required.',
            'name.unique'   => 'This colour already exists for your account.',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed($validator->errors(), 'Validation failed.');
        }

        DB::beginTransaction();

        $colour = Colour::create([
            'shop_id'   => Auth::user()->owner_id,
            'name'      => $request->name,
            'is_active' => 1,
        ]);

        DB::commit();

        $this->addToLog($this->unique(), Auth::user()->id, 'Colour Create', 'App/Models/Colour', 'colours', $colour->id, 'Insert', null, json_encode($request->all()), 'Success', 'Colour Created Successfully');
        $this->notification(Auth::user()->owner_id, null, 'App/Models/Colour', $colour->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Colour "' . $request->name . '" created successfully', null, null, 16);

        return $this->successResponse($colour, 200, 'Colour created successfully.');
    }

    public function view(Request $request, $colour)
    {
        $colour = Colour::where('id', $colour)->where('shop_id', Auth::user()->owner_id)->first();

        if (!$colour) {
            return $this->errorResponse([], 404, 'Colour not found.');
        }

        return $this->successResponse($colour, 200, 'Colour retrieved successfully.');
    }

    public function status(Request $request, $colour)
    {
        $colour = Colour::where('id', $colour)->where('shop_id', Auth::user()->owner_id)->first();

        if (!$colour) {
            return $this->errorResponse([], 404, 'Colour not found.');
        }

        $colour->is_active = $colour->is_active == 1 ? 0 : 1;
        $colour->save();

        $statusText = $colour->is_active == 1 ? 'Colour changed to active state' : 'Colour changed to in-active state';

        $this->addToLog($this->unique(), Auth::user()->id, 'Colour Status Update', 'App/Models/Colour', 'colours', $colour->id, 'Update', null, null, 'Success', $statusText);
        $this->notification(Auth::user()->owner_id, null, 'App/Models/Colour', $colour->id, null, json_encode($request->all()), now(), Auth::user()->id, $colour->name . ' ' . $statusText, null, null, 16);

        return $this->successResponse($colour, 200, $statusText);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'colour_id' => 'required|exists:colours,id',
            'colour'    => ['required', 'string',
                Rule::unique('colours', 'name')
                    ->where(fn($q) => $q->where('shop_id', Auth::user()->owner_id))
                    ->ignore($request->colour_id),
            ],
        ], [
            'colour.required' => 'Colour is required.',
            'colour.unique'   => 'This colour already exists for your account.',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed($validator->errors(), 'Validation failed.');
        }

        DB::beginTransaction();

        $colour   = Colour::find($request->colour_id);
        $old_name = $colour->name;
        $colour->update(['name' => $request->colour]);

        DB::commit();

        $this->addToLog($this->unique(), Auth::user()->id, 'Colour Update', 'App/Models/Colour', 'colours', $colour->id, 'Update', null, $request, 'Success', 'Colour Updated Successfully');
        $this->notification(Auth::user()->owner_id, null, 'App/Models/Colour', $colour->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Colour "' . $old_name . '" updated to "' . $request->colour . '" successfully', null, null, 16);

        return $this->successResponse($colour, 200, 'Colour updated successfully.');
    }
}