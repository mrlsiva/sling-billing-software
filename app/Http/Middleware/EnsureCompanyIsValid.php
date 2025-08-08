<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class EnsureCompanyIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $slug_name = request()->segment(1);

        $user = User::where([['role_id','!=',1],['slug_name',$slug_name]])->first();
        if(!$user)
        {
            abort(404, 'Invalid Company.');
        }

        return $next($request);
    }
}
