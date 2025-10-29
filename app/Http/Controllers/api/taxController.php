<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Traits\ResponseHelper;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use App\Models\Tax;
use App\Traits\Log;
use DB;

class taxController extends Controller
{
    use Log, Notifications, ResponseHelper;

    public function list(Request $request)
    {
        if(Auth::user()->role_id == 2)
        {
            $taxes = Tax::where('shop_id',Auth::user()->owner_id)->when(request('tax'), function ($query) 
            {
                $query->where('name', 'like', '%' . request('tax') . '%');
            })->orderBy('id','desc')->paginate(10);

            return $this->successResponse($taxes, 200, 'Successfully returned all taxes');
        }
    }

    public function store(Request $request)
    {
        if(Auth::user()->role_id == 2)
        {
            $rules = [
                'name' => ['required','numeric',
                    Rule::unique('taxes')->where(function ($query) {
                        return $query->where('shop_id', Auth::user()->owner_id);
                    }),
                ],
            ];

            $messages = [
                'name.required' => 'Tax is required.',
                'name.numeric' => 'Tax must be numeric.',
                'name.unique' => 'This Tax already exists for your account.',
            ];

            $validator=Validator::make($request->all(),$rules,$messages);

            if ($validator->fails()) {
                return $this->validationFailed($validator->errors(),"The given data was invalid.");
            }

            DB::beginTransaction();

            $tax = Tax::create([ 
                'shop_id' => Auth::user()->owner_id,
                'name' => $request->name,
                'is_active' => 1,
            ]);

            DB::commit();

            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Tax Create','App/Models/Tax','taxes',$tax->id,'Insert',null,json_encode($request->all()),'Success','Tax Created Successfully');

            //Notifiction
            $this->notification(Auth::user()->owner_id, null,'App/Models/Tax', $tax->id, null, json_encode($request->all()), now(), Auth::user()->id, $request->name.'% tax created successfully',null, null);

            return $this->successResponse($tax, 200, 'Tax created successfully');

        }
    }

    public function view(Request $request, $tax)
    {
        if(Auth::user()->role_id == 2)
        {
            $tax = Tax::where([['id',$tax],['shop_id',Auth::user()->owner_id]])->first();
            return $this->successResponse($tax, 200, 'Tax returned successfully');

        }
    }

    public function status(Request $request,Tax $tax)
    {
        if(Auth::user()->role_id == 2)
        {

            if ($tax) {
                $tax->is_active = $tax->is_active == 1 ? 0 : 1;
                $tax->save();
            }

            $statusText = $tax->is_active == 1 ? 'Tax changed to active state' : 'Tax changed to in-active state';

            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Tax Status Update','App/Models/Tax','taxes',$tax->id,'Update',null,null,'Success',$tax->name.' '.$statusText);

            //Notifiction
            $this->notification(Auth::user()->owner_id, null,'App/Models/Tax', $tax->id, null, json_encode($request->all()), now(), Auth::user()->id, $tax->name.' '.$statusText,null, null);

            return $this->successResponse("Success", 200, $statusText);

        }

    }

    public function update(Request $request)
    {
        
        if(Auth::user()->role_id == 2)
        {
            $rules = [
                'name' => ['required','numeric',
                    Rule::unique('taxes')->ignore($request->tax_id)->where(function ($query) {
                        return $query->where('shop_id', Auth::user()->owner_id);
                    }),
                ],
            ];

            $messages = [
                'name.required' => 'Tax is required.',
                'name.numeric' => 'Tax must be numeric.',
                'name.unique' => 'This Tax already exists for your account.',
            ];

            $validator=Validator::make($request->all(),$rules,$messages);

            if ($validator->fails()) {
                return $this->validationFailed($validator->errors(),"The given data was invalid.");
            }

            DB::beginTransaction();

            $tax = Tax::find($request->tax_id);

            $tax->update([ 
                'name' => $request->name
            ]);

            DB::commit();

            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Tax Update','App/Models/Tax','taxes',$tax->id,'Update',null,$request,'Success','Tax Updated Successfully');

            //Notifiction
            $this->notification(Auth::user()->owner_id, null,'App/Models/Tax', $tax->id, null, json_encode($request->all()), now(), Auth::user()->id, $request->tax.'% tax updated successfully',null, null);

            return $this->successResponse($tax, 200, 'Tax updated successfully');
        }
    }
}
