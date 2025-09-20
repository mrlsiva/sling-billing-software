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
        
    }
}
