<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureCompanyIsValid;
use App\Models\ErrorReport;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')->prefix('admin')->name('admin.')->group(base_path('routes/admin.php'));
            Route::middleware('web')->prefix('users')->name('users.')->group(base_path('routes/user.php'));
        },
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'is_company_valid' => EnsureCompanyIsValid::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
            
        $exceptions->renderable(function (Throwable $e, $request) {
            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            if ($statusCode == 500) 
            {
                try 
                {

                    ErrorReport::create([
                        'user_id' => Auth::check() ? Auth::id() : null,
                        'error' => $e->getMessage(),
                        'code' =>  $statusCode,
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                        'ip' => $request->ip(),
                        'agent' => $request->userAgent(),
                    ]);
                } 
                catch (\Exception $inner) {
                    Log::error('⚠️ Failed to log error to DB: ' . $inner->getMessage());
                }
            }
        });
    })->create();
