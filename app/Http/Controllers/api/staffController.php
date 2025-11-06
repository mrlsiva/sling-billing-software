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
use App\Models\Staff;
use App\Traits\Log;
use DB;

class staffController extends Controller
{
    use Log, Notifications, ResponseHelper;

    public function list(Request $request)
    {
        if(Auth::user()->role_id == 2)
        {
            $staffs = Staff::where([['shop_id', Auth::user()->owner_id],['branch_id',null]])
            ->when($request->name, function ($query, $name) {
                $query->where('name', 'like', "%{$name}%");
            })->paginate(10);

            return $this->successResponse($staffs, 200, 'Successfully returned all staffs');

        }

        if(Auth::user()->role_id == 3)
        {
            $staffs = Staff::where('branch_id', Auth::user()->id)
            ->when($request->name, function ($query, $name) {
                $query->where('name', 'like', "%{$name}%");
            })->paginate(10);

            return $this->successResponse($staffs, 200, 'Successfully returned all staffs');
        }
    }

    public function store(Request $request)
    {
        $rules = [

            'name' => ['required',
                Rule::unique('staffs')->where(function ($query) use ($request) {
                    return $query->where('shop_id', Auth::user()->owner_id);
                }),
            ],
            'phone' => 'nullable|numeric|digits:10',
        ];

        $messages = [
            'name.required' => 'Name is required.',
        ];

        $validator=Validator::make($request->all(),$rules,$messages);

        if ($validator->fails()) {
            return $this->validationFailed($validator->errors(),"The given data was invalid.");
        }


        if(Auth::user()->role_id == 2)
        {
            DB::beginTransaction();

            $staff = Staff::create([ 
                'shop_id' => Auth::user()->owner_id,
                'name' => $request->name,
                'phone' => $request->phone,
                'role' => $request->role,
                'is_active' => 1,
            ]);

            DB::commit();

            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Staff Create','App/Models/Staff','staffs',$staff->id,'Insert',null,$request,'Success','Staff Created Successfully');

            //Notifiction
            $this->notification(Auth::user()->id, null,'App/Models/Staff', $staff->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Shop '.Auth::user()->name. ' created new staff '.Str::ucfirst($request->name),null, null,13);

            return $this->successResponse($staff, 200, 'Successfully created staff');
        }

        if(Auth::user()->role_id == 3)
        {
            DB::beginTransaction();

            $staff = Staff::create([ 
                'branch_id' => Auth::user()->id,
                'name' => $request->name,
                'phone' => $request->phone,
                'role' => $request->role,
                'is_active' => 1,
            ]);

            DB::commit();

            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Staff Create','App/Models/Staff','staffs',$staff->id,'Insert',null,$request,'Success','Staff Created Successfully');

            //Notifiction
            $this->notification(Auth::user()->parent_id, null,'App/Models/Staff', $staff->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Branch '.Auth::user()->name. ' created new staff '.Str::ucfirst($request->name),null, null,13);

            return $this->successResponse($staff, 200, 'Successfully created staff');
        }
    }

    public function view(Request $request, $staff)
    {
        if(Auth::user()->role_id == 2)
        {
            $staff = Staff::where([['shop_id',Auth::user()->owner_id],['id',$staff]])->first();
        }

        if(Auth::user()->role_id == 3)
        {
            $staff = Staff::where([['branch_id',Auth::user()->id],['id',$staff]])->first();
        }

        return $this->successResponse($staff, 200, 'Successfully returned the requested staff');
    }

    public function status(Request $request, $staff)
    {
        if(Auth::user()->role_id == 2)
        {
            $staff = Staff::where([['shop_id',Auth::user()->owner_id],['id',$staff]])->first();

            if ($staff) {
                $staff->is_active = $staff->is_active == 1 ? 0 : 1;
                $staff->save();
                

                $staff = Staff::where('id',$staff->id)->first();

                $statusText = $staff->is_active == 1 ? 'Staff changed to active state' : 'Staff changed to in-active state';

                //Log
                $this->addToLog($this->unique(),Auth::user()->id,'Staff Status Update','App/Models/Staff','staffs',$staff->id,'Update',null,null,'Success',$statusText);

                //Notifiction
                $this->notification(Auth::user()->id, null,'App/Models/Staff', $staff->id, null, $staff->id, now(), Auth::user()->id, $staff->name .' '.$statusText .' in HO '.Auth::user()->name,null, null,13);
            }
        }

        if(Auth::user()->role_id == 3)
        {
            $staff = Staff::where([['branch_id',Auth::user()->id],['id',$staff]])->first();

            if ($staff) {

                $staff->is_active = $staff->is_active == 1 ? 0 : 1;
                $staff->save();
            

                $staff = Staff::where('id',$staff->id)->first();

                $statusText = $staff->is_active == 1 ? 'Staff changed to active state' : 'Staff changed to in-active state';

                //Log
                $this->addToLog($this->unique(),Auth::user()->id,'Staff Status Update','App/Models/Staff','staffs',$staff->id,'Update',null,null,'Success',$statusText);

                //Notifiction
                $this->notification(Auth::user()->parent_id, null,'App/Models/Staff', $staff->id, null, $staff->id, now(), Auth::user()->id, $staff->name .' '.$statusText .' in branch '.Auth::user()->name,null, null,13);
            }

        }

        return $this->successResponse("Success", 200, $statusText);

    }

    public function update(Request $request)
    {
        $rules = [

            'name' => ['required',
                Rule::unique('staffs')->ignore($request->staff_id)->where(function ($query) use ($request) {
                    return $query->where('shop_id', Auth::user()->owner_id);
                }),
            ],
            'phone' => 'nullable|numeric|digits:10',
        ];

        $messages = [
            'name.required' => 'Name is required.',
        ];

        $validator=Validator::make($request->all(),$rules,$messages);

        if ($validator->fails()) {
            return $this->validationFailed($validator->errors(),"The given data was invalid.");
        }

       
        DB::beginTransaction();

        $staff = Staff::find($request->staff_id);

        $staff->update([ 
            'name' => $request->name,
            'phone' => $request->phone,
            'role' => $request->role,
        ]);

        DB::commit();

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Staff Update','App/Models/Staff','staffs',$staff->id,'Update',null,json_encode($request->all()),'Success','Staff Updated Successfully');

        if(Auth::user()->role_id == 3)
        {
            //Notification
            $this->notification(Auth::user()->parent_id, null,'App/Models/Staff', $staff->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Branch '.Auth::user()->name. ' updated staff '.Str::ucfirst($request->name),null, null,13);
        }

        if(Auth::user()->role_id == 2)
        {
            //Notification
            $this->notification(Auth::user()->id, null,'App/Models/Staff', $staff->id, null, json_encode($request->all()), now(), Auth::user()->id, 'HO '.Auth::user()->name. ' updated staff '.Str::ucfirst($request->name),null, null,13);
        }

        return $this->successResponse($staff, 200, 'Successfully updated staff');
    }
}
