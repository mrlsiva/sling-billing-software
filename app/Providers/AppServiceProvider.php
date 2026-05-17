<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Mail;
use App\Models\Version;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $versionString = '0.0';

        // Only attempt DB query if the versions table exists
        if (Schema::hasTable('versions')) {
            $version = Version::where('status', 1)->first();
            $versionString = $version ? $version->version . '_' . $version->updated_at : $versionString;
        }

        View::share('version', $versionString);

        if (config('mail.mailers.smtp.transport') === 'smtp') {
            Mail::extend('smtp', function (array $config) {
                $scheme = ($config['scheme'] ?? null) === 'smtps' ? 'smtps' : 'smtp';
                $dsn = sprintf(
                    '%s://%s:%s@%s:%d?verify_peer=0',
                    $scheme,
                    urlencode($config['username'] ?? ''),
                    urlencode($config['password'] ?? ''),
                    $config['host'] ?? '127.0.0.1',
                    $config['port'] ?? 587
                );

                return \Symfony\Component\Mailer\Transport::fromDsn($dsn);
            });
        }
    }
}
