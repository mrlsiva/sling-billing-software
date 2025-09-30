<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use App\Models\Tax;
use App\Traits\Log;
use DB;

class taxController extends Controller
{
    use Log, Notifications;

    public function index(Request $request)
    {
        $taxes = Tax::where('shop_id',Auth::user()->id)->when(request('tax'), function ($query) 
        {
            $query->where('name', 'like', '%' . request('tax') . '%');
        })->orderBy('id','desc')->paginate(10);

        return view('users.settings.tax',compact('taxes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required','numeric',
                Rule::unique('taxes')->where(function ($query) {
                    return $query->where('shop_id', Auth::id());
                }),
            ],
        ], 
        [
            'name.required' => 'Tax is required.',
            'name.numeric' => 'Tax must be numeric.',
            'name.unique' => 'This Tax already exists for your account.',
        ]);

        DB::beginTransaction();

        $tax = Tax::create([ 
            'shop_id' => Auth::user()->id,
            'name' => $request->name,
            'is_active' => 1,
        ]);

        DB::commit();

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Tax Create','App/Models/Tax','taxes',$tax->id,'Insert',null,json_encode($request->all()),'Success','Tax Created Successfully');

        //Notifiction
        $this->notification(Auth::user()->owner_id, null,'App/Models/Tax', $tax->id, null, json_encode($request->all()), now(), Auth::user()->id, $request->name.'% tax created successfully',null, null);

        return redirect()->back()->with('toast_success', 'Tax created successfully.');
    }

    public function status(Request $request)
    {
        $tax = Tax::find($request->id);

        if ($tax) {
            $tax->is_active = $tax->is_active == 1 ? 0 : 1;
            $tax->save();
        }

        $tax = Tax::find($request->id);

        $statusText = $tax->is_active == 1 ? 'Tax changed to active state' : 'Tax changed to in-active state';

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Tax Status Update','App/Models/Tax','taxes',$request->id,'Update',null,null,'Success',$statusText);

        //Notifiction
        $this->notification(Auth::user()->owner_id, null,'App/Models/Tax', $request->id, null, json_encode($request->all()), now(), Auth::user()->id, $tax->name.'% '.$statusText,null, null);

        return redirect()->back()->with('toast_success', "Tax Status Changed");
    }

    public function edit(Request $request)
    {
        $tax = Tax::find($request->id);

        if ($tax) {
            return response()->json([
                'name' => $tax->name
            ]);
        }

        return response()->json(['error' => 'Tax not found'], 404);
    }

    public function update(Request $request)
    {
        $request->validate([
            'tax' => ['required','numeric',
                Rule::unique('taxes','name')->where(fn ($query) => $query->where('shop_id', Auth::id()))->ignore($request->tax_id),
            ],
        ],
        [
            'tax.required' => 'Tax is required.',
            'tax.numeric' => 'Tax must be numeric.',
            'tax.unique' => 'This tax already exists for your account.',
        ]);

        DB::beginTransaction();

        $tax = Tax::find($request->tax_id);

        $tax->update([ 
            'name' => $request->tax
        ]);

        DB::commit();

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Tax Update','App/Models/Tax','taxes',$tax->id,'Update',null,$request,'Success','Tax Updated Successfully');

        //Notifiction
        $this->notification(Auth::user()->owner_id, null,'App/Models/Tax', $tax->id, null, json_encode($request->all()), now(), Auth::user()->id, $request->tax.'% tax updated successfully',null, null);

        return redirect()->back()->with('toast_success', 'Tax updated successfully.');
    }
}
