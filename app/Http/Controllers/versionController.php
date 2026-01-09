<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Version;
use Carbon\Carbon;

class versionController extends Controller
{
    public function update(Request $request)
    {
        $version = Version::where('status',1)->first();

        $version->update([
            'updated_at' => Carbon::now()
        ]);

        return "Success";
    }
}
