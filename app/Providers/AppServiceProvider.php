<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
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
                $transport = new EsmtpTransport(
                    $config['host'] ?? '127.0.0.1',
                    $config['port'] ?? 587,
                    ($config['scheme'] ?? null) === 'smtps',
                );

                if (!empty($config['username'])) {
                    $transport->setUsername($config['username']);
                }
                if (!empty($config['password'])) {
                    $transport->setPassword($config['password']);
                }
                if (!empty($config['timeout'])) {
                    $transport->setTimeout($config['timeout']);
                }

                $transport->setStreamOptions([
                    'ssl' => [
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                        'allow_self_signed' => true,
                    ],
                ]);

                return $transport;
            });
        }
    }
}
