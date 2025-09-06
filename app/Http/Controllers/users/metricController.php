<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\Metric;
use App\Traits\Log;
use DB;

class metricController extends Controller
{
    use Log;
    
    public function index(Request $request)
    {
        $metrics = Metric::where('shop_id',Auth::user()->id)->when(request('metric'), function ($query) 
        {
            $query->where('name', 'like', '%' . request('metric') . '%');
        })->orderBy('id','desc')->paginate(10);

        return view('users.settings.metric',compact('metrics'));
    }

     public function store(Request $request)
    {
        $request->validate([
            'name' => ['required','string',
                Rule::unique('metrics')->where(function ($query) {
                    return $query->where('shop_id', Auth::id());
                }),
            ],
        ], 
        [
            'name.required' => 'Metric is required.',
            'name.unique' => 'This Metric already exists for your account.',
        ]);

        DB::beginTransaction();

        $metric = Metric::create([ 
            'shop_id' => Auth::user()->id,
            'name' => $request->name,
            'is_active' => 1,
        ]);

        DB::commit();

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Metric Create','App/Models/Metric','metrics',$metric->id,'Insert',null,$request,'Success','Metric Created Successfully');

        return redirect()->back()->with('toast_success', 'Metric created successfully.');
    }

    public function status(Request $request)
    {
        $metric = Metric::find($request->id);

        if ($metric) {
            $metric->is_active = $metric->is_active == 1 ? 0 : 1;
            $metric->save();
        }

        $metric = Metric::find($request->id);

        $statusText = $metric->is_active == 1 ? 'Metric changed to active state' : 'Metric changed to in-active state';

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Metric Status Update','App/Models/Metric','metrics',$request->id,'Update',null,null,'Success',$statusText);

        return redirect()->back()->with('toast_success', "Metric Status Changed");
    }

    public function edit(Request $request)
    {
        $metric = Metric::find($request->id);

        if ($metric) {
            return response()->json([
                'name' => $metric->name
            ]);
        }

        return response()->json(['error' => 'Metric not found'], 404);
    }

    public function update(Request $request)
    {
        $request->validate([
            'metric' => ['required','string',
                Rule::unique('metrics','name')->where(fn ($query) => $query->where('shop_id', Auth::id()))->ignore($request->metric_id),
            ],
        ],
        [
            'metric.required' => 'Metric is required.',
            'metric.unique' => 'This metric already exists for your account.',
        ]);

        DB::beginTransaction();

        $metric = Metric::find($request->metric_id);

        $metric->update([ 
            'name' => $request->metric
        ]);

        DB::commit();

        //Log
        $this->addToLog($this->unique(),Auth::user()->id,'Metric Update','App/Models/Metric','metrics',$metric->id,'Update',null,$request,'Success','Metric Updated Successfully');

        return redirect()->back()->with('toast_success', 'Metric updated successfully.');
    }
}
