<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Traits\ResponseHelper;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use App\Models\Finance;
use App\Traits\Log;
use DB;

class financeController extends Controller
{
    use Log, Notifications, ResponseHelper;

    public function list(Request $request)
    {
        if(Auth::user()->role_id == 2)
        {
            $finances = Finance::where('shop_id',Auth::user()->id)->when(request('finance'), function ($query) 
            {
                $query->where('name', 'like', '%' . request('finance') . '%');
            })->orderBy('id','desc')->paginate(10);

            return $this->successResponse($finances, 200, 'Successfully returned all finances');
        }
    }

    public function store(Request $request)
    {
        if(Auth::user()->role_id == 2)
        {
            $rules = [
                'name' => ['required','string',
                    Rule::unique('finances')->where(function ($query) {
                        return $query->where('shop_id', Auth::user()->owner_id);
                    }),
                ],
            ];

            $messages = [
                'name.required' => 'Finance is required.',
                'name.unique' => 'This Finance already exists for your account.',
            ];

            $validator=Validator::make($request->all(),$rules,$messages);

            if ($validator->fails()) {
                return $this->validationFailed($validator->errors(),"The given data was invalid.");
            }

            DB::beginTransaction();

            $finance = Finance::create([ 
                'shop_id' => Auth::user()->owner_id,
                'name' => $request->name,
                'is_active' => 1,
            ]);

            DB::commit();

            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Finance Create','App/Models/Finance','finances',$finance->id,'Insert',null,json_encode($request->all()),'Success','Finance Created Successfully');

            //Notifiction
            $this->notification(Auth::user()->owner_id, null,'App/Models/Finance', $finance->id, null, json_encode($request->all()), now(), Auth::user()->id, $request->name.' finance created successfully',null, null,9);

            return $this->successResponse($finance, 200, 'Finance created successfully');
        }
    }

    public function view(Request $request,$finance)
    {
        if(Auth::user()->role_id == 2)
        {
            $finance = Finance::where([['id',$finance],['shop_id',Auth::user()->owner_id]])->first();

            return $this->successResponse($finance, 200, 'Finance returned successfully');

        }
    }

    public function status(Request $request,$finance)
    {
        if(Auth::user()->role_id == 2)
        {
            $finance = Finance::where([['id',$finance],['shop_id',Auth::user()->owner_id]])->first();

            if ($finance) {

                $finance->is_active = $finance->is_active == 1 ? 0 : 1;
                $finance->save();
            

                $statusText = $finance->is_active == 1 ? 'Finance changed to active state' : 'Finance changed to in-active state';

                //Log
                $this->addToLog($this->unique(),Auth::user()->id,'Finance Status Update','App/Models/Finance','finances',$finance->id,'Update',null,null,'Success',$finance->name.' '.$statusText);

                //Notifiction
                $this->notification(Auth::user()->owner_id, null,'App/Models/Finance', $finance->id, null, json_encode($request->all()), now(), Auth::user()->id, $finance->name.' '.$statusText,null, null,9);

                return $this->successResponse("Success", 200, $statusText);
            }

        }
    }

    public function update(Request $request)
    {
        
        if(Auth::user()->role_id == 2)
        {
            $rules = [
                'name' => ['required','string',
                    Rule::unique('finances')->ignore($request->finance_id)->where(function ($query) {
                        return $query->where('shop_id', Auth::user()->owner_id);
                    }),
                ],
            ];

            $messages = [
                'name.required' => 'Finance is required.',
                'name.unique' => 'This Finance already exists for your account.',
            ];

            $validator=Validator::make($request->all(),$rules,$messages);

            if ($validator->fails()) {
                return $this->validationFailed($validator->errors(),"The given data was invalid.");
            }

            DB::beginTransaction();

            $finance = Finance::where([['id',$request->finance_id],['shop_id',Auth::user()->owner_id]])->first();

            $finance->update([ 
                'name' => $request->name
            ]);

            DB::commit();

            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Finance Update','App/Models/Finance','finances',$finance->id,'Update',null,$request,'Success','Finance Updated Successfully');

            //Notifiction
            $this->notification(Auth::user()->owner_id, null,'App/Models/Finance', $finance->id, null, json_encode($request->all()), now(), Auth::user()->id, $request->finance.' finance updated successfully',null, null,9);

            return $this->successResponse($finance, 200, 'Finance updated successfully');

        }
    }


    
}
