<?php

namespace App\Http\Controllers\branches;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\UserDetail;
use App\Traits\Log;
use Carbon\Carbon;
use DB;

class settingController extends Controller
{
    use Log;

    public function index(Request $request)
    {
        return view('branches.settings.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'bill_type' => 'required|in:1,2', // only allow 1 or 2
        ]);

        $user = Auth::user();

        DB::beginTransaction();

        UserDetail::where('user_id',$user->id)->update(['bill_type' => $request->bill_type]);
        $user_detail = UserDetail::where('user_id',$user->id)->first();

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Bill Type Update','App/Models/UserDetail','user_details',$user_detail->id,'Update',null,$request,'Success','Bill Type Updated Successfully');

        DB::commit();

        return redirect()->back()->with('toast_success', 'Bill Type Updated Successfully!');
    }
}
