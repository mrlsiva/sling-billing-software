<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Traits\ResponseHelper;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use App\Models\Metric;
use App\Traits\Log;
use DB;

class metricController extends Controller
{
    use Log, Notifications, ResponseHelper;

    public function list(Request $request)
    {
        if(Auth::user()->role_id == 2)
        {
            $metrics = Metric::where('shop_id',Auth::user()->owner_id)->when(request('metric'), function ($query) 
            {
                $query->where('name', 'like', '%' . request('metric') . '%');
            })->orderBy('id','desc')->paginate(10);

            return $this->successResponse($metrics, 200, 'Successfully returned all metrics');
        }
    }

    public function store(Request $request)
    {
        if(Auth::user()->role_id == 2)
        {
            $rules = [
                'name' => ['required','string',
                    Rule::unique('metrics')->where(function ($query) {
                        return $query->where('shop_id', Auth::user()->owner_id);
                    }),
                ],
            ];

            $messages = [
                'name.required' => 'Metric is required.',
                'name.unique' => 'This Metric already exists for your account.',
            ];

            $validator=Validator::make($request->all(),$rules,$messages);

            if ($validator->fails()) {
                return $this->validationFailed($validator->errors(),"The given data was invalid.");
            }

            DB::beginTransaction();

            $metric = Metric::create([ 
                'shop_id' => Auth::user()->owner_id,
                'name' => $request->name,
                'is_active' => 1,
            ]);

            DB::commit();

            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Metric Create','App/Models/Metric','metrics',$metric->id,'Insert',null, json_encode($request->all()),'Success','Metric Created Successfully');

            //Notifiction
            $this->notification(Auth::user()->owner_id, null,'App/Models/Metric', $metric->id, null, json_encode($request->all()), now(), Auth::user()->id, $request->name.' metric created successfully',null, null,4);

            return $this->successResponse($metric, 200, 'Metric created successfully');
        }
    }

    public function view(Request $request,$metric)
    {
        if(Auth::user()->role_id == 2)
        {
            $metric = Metric::where([['id',$metric],['shop_id',Auth::user()->owner_id]])->first();

            return $this->successResponse($metric, 200, 'Metric returned successfully');

        }
    }

    public function status(Request $request,$metric)
    {
        if(Auth::user()->role_id == 2)
        {
            $metric = Metric::where([['id',$metric],['shop_id',Auth::user()->owner_id]])->first();

            if ($metric) {

                $metric->is_active = $metric->is_active == 1 ? 0 : 1;
                $metric->save();
            

                $statusText = $metric->is_active == 1 ? 'Metric changed to active state' : 'Metric changed to in-active state';

                //Log
                $this->addToLog($this->unique(),Auth::user()->id,'Metric Status Update','App/Models/Metric','metrics',$metric->id,'Update',null,null,'Success',$metric->name.' '.$statusText);

                //Notifiction
                $this->notification(Auth::user()->owner_id, null,'App/Models/Metric', $metric->id, null, json_encode($request->all()), now(), Auth::user()->id, $metric->name.' '.$statusText,null, null,4);

                return $this->successResponse("Success", 200, $statusText);
            }

        }
    }

    public function update(Request $request)
    {
        
        if(Auth::user()->role_id == 2)
        {
            $rules = [
                'name' => ['required','string',
                    Rule::unique('metrics')->ignore($request->metric_id)->where(function ($query) {
                        return $query->where('shop_id', Auth::user()->owner_id);
                    }),
                ],
            ];

            $messages = [
                'name.required' => 'Metric is required.',
                'name.unique' => 'This Metric already exists for your account.',
            ];

            $validator=Validator::make($request->all(),$rules,$messages);

            if ($validator->fails()) {
                return $this->validationFailed($validator->errors(),"The given data was invalid.");
            }

            DB::beginTransaction();

            $metric = Metric::where([['id',$request->metric_id],['shop_id',Auth::user()->owner_id]])->first();

            $metric->update([ 
                'name' => $request->name
            ]);

            DB::commit();

            //Log
            $this->addToLog($this->unique(),Auth::user()->id,'Metric Update','App/Models/Metric','metrics',$metric->id,'Update',null,$request,'Success','Metric Updated Successfully');

            //Notifiction
            $this->notification(Auth::user()->owner_id, null,'App/Models/Metric', $metric->id, null, json_encode($request->all()), now(), Auth::user()->id, $request->metric.' metric updated successfully',null, null,4);

            return $this->successResponse($metric, 200, 'Metric updated successfully');

        }
    }


}
