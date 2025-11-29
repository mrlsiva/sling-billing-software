<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use App\Models\Size;
use App\Traits\Log;
use DB;


class sizeController extends Controller
{
    use Log, Notifications;

    public function index(Request $request)
    {
        $sizes = Size::where('shop_id',Auth::user()->owner_id)->when(request('size'), function ($query) 
        {
            $query->where('name', 'like', '%' . request('size') . '%');
        })->orderBy('id','desc')->paginate(10);

        return view('users.settings.size',compact('sizes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required','string',
                Rule::unique('sizes')->where(function ($query) {
                    return $query->where('shop_id', Auth::user()->owner_id);
                }),
            ],
        ], 
        [
            'name.required' => 'Size is required.',
            'name.unique' => 'This Size already exists for your account.',
        ]);

        DB::beginTransaction();

        $size = Size::create([ 
            'shop_id' => Auth::user()->owner_id,
            'name' => $request->name,
            'is_active' => 1,
        ]);

        DB::commit();

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Size Create','App/Models/Size','sizes',$size->id,'Insert',null, json_encode($request->all()),'Success','Size Created Successfully');

        //Notifiction
        $this->notification(Auth::user()->owner_id, null,'App/Models/Size', $size->id, null, json_encode($request->all()), now(), Auth::user()->id, $request->name.' size created successfully',null, null,16);

        return redirect()->back()->with('toast_success', 'Size created successfully.');
    }

    public function status(Request $request)
    {
        $size = Size::find($request->id);

        if ($size) {
            $size->is_active = $size->is_active == 1 ? 0 : 1;
            $size->save();
        }

        $size = Size::find($request->id);

        $statusText = $size->is_active == 1 ? 'Size changed to active state' : 'Size changed to in-active state';

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Size Status Update','App/Models/Size','sizes',$request->id,'Update',null,null,'Success',$statusText);

        //Notifiction
        $this->notification(Auth::user()->owner_id, null,'App/Models/Size', $request->id, null, json_encode($request->all()), now(), Auth::user()->id, $size->name.' '.$statusText,null, null,16);

        return redirect()->back()->with('toast_success', "Size Status Changed");
    }

    public function edit(Request $request)
    {
        $size = Size::find($request->id);

        if ($size) {
            return response()->json([
                'name' => $size->name
            ]);
        }

        return response()->json(['error' => 'Size not found'], 404);
    }

    public function update(Request $request)
    {
        $request->validate([
            'size' => ['required','string',
                Rule::unique('sizes','name')->where(fn ($query) => $query->where('shop_id', Auth::user()->owner_id))->ignore($request->size_id),
            ],
        ],
        [
            'size.required' => 'Size is required.',
            'size.unique' => 'This size already exists for your account.',
        ]);

        DB::beginTransaction();

        $size = Size::find($request->size_id);

        $size->update([ 
            'name' => $request->size
        ]);

        DB::commit();

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Size Update','App/Models/Size','sizes',$size->id,'Update',null,$request,'Success','Size Updated Successfully');

        //Notifiction
        $this->notification(Auth::user()->owner_id, null,'App/Models/Size', $size->id, null, json_encode($request->all()), now(), Auth::user()->id, $request->size.' size updated successfully',null, null,16);

        return redirect()->back()->with('toast_success', 'Size updated successfully.');
    }
    
}
