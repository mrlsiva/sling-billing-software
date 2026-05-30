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
use App\Models\BillSetup;
use App\Models\UserDetail;
use App\Models\User;
use App\Traits\Log;
use DB;

class billController extends Controller
{
    use Log, Notifications, ResponseHelper;

    public function list(Request $request,$branch)
    {
        if(Auth::user()->role_id == 2)
        {
            if($branch == 0)
            {
                $bills = BillSetup::where([['shop_id',Auth::user()->owner_id],['branch_id',null]])->orderBy('id','desc')->paginate(10);
            }
            else
            {
                $bills = BillSetup::where([['branch_id',$branch],['shop_id',Auth::user()->owner_id]])->orderBy('id','desc')->paginate(10);
            }

            return $this->successResponse($bills, 200, 'Successfully returned all bill setup list');
        }
    }

    public function store(Request $request)
    {
        if(Auth::user()->role_id == 2)
        {
            $rules = [
                'branch_id' => 'required',
                'bill'      => ['required','string','max:255',
                    Rule::unique('bill_setups', 'bill_number')->where(fn ($query) => 
                        $query->where('branch_id', $request->branch_id == 0 ? null : $request->branch_id)->where('shop_id', Auth::user()->owner_id)
                    ),
                ],
            ];

            $messages = [
                'branch_id.required' => 'Branch is required.',
                'bill.required' => 'Bill number is required.',
            ];

            $validator=Validator::make($request->all(),$rules,$messages);

            if ($validator->fails()) {
                return $this->validationFailed($validator->errors(),"The given data was invalid.");
            }

            DB::beginTransaction();

            // deactivate all previous bills for this branch
            BillSetup::where([['shop_id',Auth::user()->owner_id],['branch_id',$request->branch_id == 0 ? null : $request->branch_id]])->update(['is_active' => 0]);

            // create new active bill
            $bill = BillSetup::create([
                'shop_id'   => Auth::user()->owner_id,
                'branch_id' => $request->branch_id == 0 ? null : $request->branch_id,
                'bill_number' => $request->bill,
                'setup_on'    => now(),
                'is_active'   => 1,
            ]);

            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Bill number setup','App/Models/BillSetup','bill_setups',$bill->id,'Insert',null,$request,'Success','Bill number set successfully');

            if($request->branch_id != 0)
            {
                $branch = User::where('id',$request->branch_id)->first();

                //Notifiction
                $this->notification(Auth::user()->owner_id, null,'App/Models/BillSetup', $bill->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Bill number set successfully for branch '. $branch->name,null, null,11);
            }
            else
            {
                $ho = User::where('id',Auth::user()->owner_id)->first();

                //Notifiction
                $this->notification(Auth::user()->owner_id, null,'App/Models/BillSetup', $bill->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Bill number set successfully for HO '. $ho->name,null, null,11);
            }

            DB::commit();

            return $this->successResponse($bill, 200, 'Bill setup done successfully');
        }
    }

    public function set_bank_status(Request $request)
    {
        if (Auth::user()->role_id !== 2) {
            return $this->errorResponse([], 403, 'Only HO accounts can update this setting.');
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ], [
            'user_id.required' => 'User ID is required.',
            'user_id.exists'   => 'User not found.',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed($validator->errors(), 'Validation failed.');
        }

        $detail = UserDetail::where('user_id', $request->user_id)->first();

        if (!$detail) {
            return $this->errorResponse([], 404, 'User detail not found.');
        }

        $detail->show_bank_detail = $detail->show_bank_detail == 1 ? 0 : 1;
        $detail->save();

        $statusText = $detail->show_bank_detail == 1
            ? 'Bank detail display enabled in bill'
            : 'Bank detail display disabled in bill';

        $this->addToLog($this->unique(), Auth::id(), 'Bank Detail Visibility Update', 'App/Models/UserDetail', 'user_details', $detail->id, 'Update', null, $request, 'Success', $statusText);

        $targetUser = User::find($request->user_id);
        $label = $targetUser->role_id == 2 ? 'HO ' : 'Branch ';
        $this->notification(Auth::user()->owner_id, null, 'App/Models/UserDetail', $detail->id, null, json_encode($request->all()), now(), Auth::id(), $statusText . ' for ' . $label . $targetUser->user_name, null, null, 11);

        return $this->successResponse([
            'user_id'          => $request->user_id,
            'show_bank_detail' => (bool) $detail->show_bank_detail,
        ], 200, $statusText);
    }
}
