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

    public function api_update(Request $request)
    {
        $version = Version::where('status', 1)->first();

        if (!$version) {
            return response()->json(['success' => false, 'message' => 'No active version found.'], 404);
        }

        $version->update([
            'updated_at' => Carbon::now()
        ]);

        return response()->json(['success' => true, 'message' => 'Version updated successfully.', 'data' => $version]);
    }
}
