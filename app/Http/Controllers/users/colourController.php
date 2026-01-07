<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use App\Models\Colour;
use App\Traits\Log;
use DB;

class colourController extends Controller
{
    use Log, Notifications;

    public function index(Request $request)
    {
        $colours = Colour::where('shop_id',Auth::user()->owner_id)->when(request('colour'), function ($query) 
        {
            $query->where('name', 'like', '%' . request('colour') . '%');
        })->orderBy('id','desc')->paginate(10);

        return view('users.settings.colour',compact('colours'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required','string',
                Rule::unique('colours')->where(function ($query) {
                    return $query->where('shop_id', Auth::user()->owner_id);
                }),
            ],
        ], 
        [
            'name.required' => 'Colour is required.',
            'name.unique' => 'This Colour already exists for your account.',
        ]);

        DB::beginTransaction();

        $colour = Colour::create([ 
            'shop_id' => Auth::user()->owner_id,
            'name' => $request->name,
            'is_active' => 1,
        ]);

        DB::commit();

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Colour Create','App/Models/Colour','colours',$colour->id,'Insert',null, json_encode($request->all()),'Success','Colour Created Successfully');

        //Notifiction
        $this->notification(Auth::user()->owner_id, null,'App/Models/Colour', $colour->id, null, json_encode($request->all()), now(), Auth::user()->id, $request->name.' colour created successfully',null, null,16);

        return response()->json([
            'status'   => true,
            'message'  => 'Colour created successfully.',
            'redirect' => route('setting.colour.index', ['company' => request()->route('company')]),
            'data' => [
                'id' => $colour->id,
                'name' => $colour->name,
            ]
        ]);

        return redirect()->back()->with('toast_success', 'Colour created successfully.');
    }

    public function status(Request $request)
    {
        $colour = Colour::find($request->id);

        if ($colour) {
            $colour->is_active = $colour->is_active == 1 ? 0 : 1;
            $colour->save();
        }

        $colour = Colour::find($request->id);

        $statusText = $colour->is_active == 1 ? 'Colour changed to active state' : 'Colour changed to in-active state';

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Colour Status Update','App/Models/Colour','colours',$request->id,'Update',null,null,'Success',$statusText);

        //Notifiction
        $this->notification(Auth::user()->owner_id, null,'App/Models/Colour', $request->id, null, json_encode($request->all()), now(), Auth::user()->id, $colour->name.' '.$statusText,null, null,16);

        return redirect()->back()->with('toast_success', "Colour Status Changed");
    }

    public function edit(Request $request)
    {
        $colour = Colour::find($request->id);

        if ($colour) {
            return response()->json([
                'name' => $colour->name
            ]);
        }

        return response()->json(['error' => 'Colour not found'], 404);
    }

    public function update(Request $request)
    {
        $request->validate([
            'colour' => ['required','string',
                Rule::unique('colours','name')->where(fn ($query) => $query->where('shop_id', Auth::user()->owner_id))->ignore($request->colour_id),
            ],
        ],
        [
            'colour.required' => 'Colour is required.',
            'colour.unique' => 'This Colour already exists for your account.',
        ]);

        DB::beginTransaction();

        $colour = Colour::find($request->colour_id);

        $colour->update([ 
            'name' => $request->colour
        ]);

        DB::commit();

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Colour Update','App/Models/Colour','colours',$colour->id,'Update',null,$request,'Success','Colour Updated Successfully');

        //Notifiction
        $this->notification(Auth::user()->owner_id, null,'App/Models/Colour', $colour->id, null, json_encode($request->all()), now(), Auth::user()->id, $request->colour.' colour updated successfully',null, null,16);

        return redirect()->back()->with('toast_success', 'Colour updated successfully.');
    }
}
