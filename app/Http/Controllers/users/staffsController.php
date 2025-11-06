<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Staff;
use App\Traits\Log;
use Carbon\Carbon;
use DB;

class staffsController extends Controller
{
    use Log, Notifications;

    public function index(Request $request)
    {
        $staffs = Staff::where([['shop_id', Auth::user()->owner_id],['branch_id',null]])
        ->when($request->name, function ($query, $name) {
            $query->where('name', 'like', "%{$name}%");
        })->paginate(10);
        return view('users.staffs.index',compact('staffs'));

    }

    public function store(Request $request)
    {
        $request->validate([

            'name' => ['required',
                Rule::unique('staffs')->where(function ($query) use ($request) {
                    return $query->where('shop_id', Auth::user()->owner_id);
                }),
            ],
            'phone' => 'nullable|numeric|digits:10',

        ], 
        [
            'name.required' => 'Name is required.',
        ]);

        DB::beginTransaction();

        $staff = Staff::create([ 
            'shop_id' => Auth::user()->id,
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

        return redirect()->back()->with('toast_success', 'Staff created successfully.');
    }

    public function status(Request $request)
    {
        $staff = Staff::find($request->id);

        if ($staff) {
            $staff->is_active = $staff->is_active == 1 ? 0 : 1;
            $staff->save();
        }

        $staff = Staff::find($request->id);

        $statusText = $staff->is_active == 1 ? 'Staff changed to active state' : 'Staff changed to in-active state';

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Staff Status Update','App/Models/Staff','staffs',$request->id,'Update',null,null,'Success',$statusText);

        //Notifiction
        $this->notification(Auth::user()->owner_id, null,'App/Models/Staff', $staff->id, null, json_encode($request->all()), now(), Auth::user()->id, $staff->name .' '.$statusText .' in HO '.Auth::user()->name,null, null,13);

        return redirect()->back()->with('toast_success', "Staff Status Changed");
    }

    public function update(Request $request)
    {

        $request->validate([
            'staff_name' => [
                'required',
                Rule::unique('staffs', 'name') // DB column is `name`
                    ->where(function ($query) use ($request) {
                        return $query->where('shop_id', Auth::user()->owner_id);
                    })
                    ->ignore($request->staff_id), // ignore current staff
            ],
            'staff_phone' => 'nullable|numeric|digits:10',
        ], 
        [
            'staff_name.required' => 'Name is required.',
        ]);

        DB::beginTransaction();

        $staff = Staff::find($request->staff_id);

        $staff->update([ 
            'name' => $request->staff_name,
            'phone' => $request->staff_phone,
            'role' => $request->staff_role,
        ]);

        DB::commit();

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Staff Update','App/Models/Staff','staffs',$staff->id,'Update',null,$request,'Success','Staff Updated Successfully');

        //Notifiction
        $this->notification(Auth::user()->id, null,'App/Models/Staff', $staff->id, null, json_encode($request->all()), now(), Auth::user()->id, 'HO '.Auth::user()->name. ' updated staff '.Str::ucfirst($request->name),null, null,13);

        return redirect()->back()->with('toast_success', 'Staff updated successfully.');

    }
}
