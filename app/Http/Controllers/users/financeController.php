<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\Finance;
use App\Traits\Log;
use DB;

class financeController extends Controller
{
    use Log;

    public function index(Request $request)
    {
        $finances = Finance::where('shop_id',Auth::user()->id)->when(request('finance'), function ($query) 
        {
            $query->where('name', 'like', '%' . request('finance') . '%');
        })->orderBy('id','desc')->paginate(10);

        return view('users.settings.finance',compact('finances'));
    }

     public function store(Request $request)
    {
        $request->validate([
            'name' => ['required','string',
                Rule::unique('finances')->where(function ($query) {
                    return $query->where('shop_id', Auth::id());
                }),
            ],
        ], 
        [
            'name.required' => 'Finance is required.',
            'name.unique' => 'This Finance already exists for your account.',
        ]);

        DB::beginTransaction();

        $finance = Finance::create([ 
            'shop_id' => Auth::user()->id,
            'name' => $request->name,
            'is_active' => 1,
        ]);

        DB::commit();

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Finance Create','App/Models/Finance','finances',$finance->id,'Insert',null,$request,'Success','Finance Created Successfully');

        return redirect()->back()->with('toast_success', 'Finance created successfully.');
    }

    public function status(Request $request)
    {
        $finance = Finance::find($request->id);

        if ($finance) {
            $finance->is_active = $finance->is_active == 1 ? 0 : 1;
            $finance->save();
        }

        $finance = Finance::find($request->id);

        $statusText = $finance->is_active == 1 ? 'Finance changed to active state' : 'Finance changed to in-active state';

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Finance Status Update','App/Models/Finance','metrics',$request->id,'Update',null,null,'Success',$statusText);

        return redirect()->back()->with('toast_success', "Finance Status Changed");
    }

    public function edit(Request $request)
    {
        $finance = Finance::find($request->id);

        if ($finance) {
            return response()->json([
                'name' => $finance->name
            ]);
        }

        return response()->json(['error' => 'Finance not found'], 404);
    }

    public function update(Request $request)
    {
        $request->validate([
            'finance' => ['required','string',
                Rule::unique('finances','name')->where(fn ($query) => $query->where('shop_id', Auth::id()))->ignore($request->finance_id),
            ],
        ],
        [
            'finance.required' => 'Finance is required.',
            'finance.unique' => 'This finance already exists for your account.',
        ]);

        DB::beginTransaction();

        $finance = Finance::find($request->finance_id);

        $finance->update([ 
            'name' => $request->finance
        ]);

        DB::commit();

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Finance Update','App/Models/Finance','metrics',$finance->id,'Update',null,$request,'Success','Finance Updated Successfully');

        return redirect()->back()->with('toast_success', 'Finance updated successfully.');
    }
    
}
