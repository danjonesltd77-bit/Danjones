<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        $this->configureDefaults();

        Number::macro('crypto', function ($amount, $decimals = 8) {
            return rtrim(rtrim(number_format((float) $amount, $decimals, '.', ','), '0'), '.');
        });

        // Implicitly grant "super-admin" role all permissions
        // This handles task 3 (bypassing permissions checks for Super Admin using a Gate interceptor)
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });

        // Register SendGrid API Mail Transport
        \Illuminate\Support\Facades\Mail::extend('sendgrid', function (array $config) {
            return new \Symfony\Component\Mailer\Bridge\Sendgrid\Transport\SendgridApiTransport(
                env('SENDGRID_API_KEY'),
                \Symfony\Component\HttpClient\HttpClient::create()
            );
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(
            fn (): ?Password => app()->isProduction()
                ? Password::min(12)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
                : null,
        );
    }
}
