<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\BillSetup;
use App\Traits\Log;
use Carbon\Carbon;
use DB;

class billController extends Controller
{
    use Log, Notifications;

    public function index(Request $request,$company,$branch = null)
    {
        if($branch == null)
        {
            $branch = User::where([['parent_id',Auth::user()->id],['is_active',1],['is_lock',0],['is_delete',0]])->first();
            if($branch)
            {
                $branch = $branch->id;
            }
            else
            {
                $branch = null;
            }
        }

        $branches = User::where([['parent_id',Auth::user()->id],['is_active',1],['is_lock',0],['is_delete',0]])->get();
        $bills = BillSetup::where('branch_id',$branch)->orderBy('id','desc')->paginate(10);
        return view('users.settings.bill',compact('branches','branch','bills'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:users,id',
            'bill'      => ['required','string','max:255',
                Rule::unique('bill_setups', 'bill_number')->where(fn ($query) => 
                    $query->where('branch_id', $request->branch_id)->where('shop_id', Auth::user()->id)
                ),
            ],
        ]);

        DB::beginTransaction();

        // deactivate all previous bills for this branch
        BillSetup::where('branch_id', $request->branch_id)->update(['is_active' => 0]);

        // create new active bill
        $bill = BillSetup::create([
            'shop_id'   => Auth::user()->id,
            'branch_id'   => $request->branch_id,
            'bill_number' => $request->bill,
            'setup_on'    => now(),
            'is_active'   => 1,
        ]);

        $branch = User::where('id',$request->branch_id)->first();

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Bill number setup','App/Models/BillSetup','bill_setups',$bill->id,'Insert',null,$request,'Success','Bill number set successfully');

        //Notifiction
        $this->notification(Auth::user()->owner_id, null,'App/Models/BillSetup', $bill->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Bill number set successfully for branch '. $branch->name,null, null);

        DB::commit();

        return redirect()->back()->with('toast_success', 'Bill setup saved successfully!');

    }

}
