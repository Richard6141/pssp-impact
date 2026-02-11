<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        RateLimiter::for('auth-login', function (Request $request) {
            $login = (string) $request->input('login', 'guest');
            return Limit::perMinute(5)->by(strtolower($login) . '|' . $request->ip());
        });

        RateLimiter::for('auth-register', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('password-email', function (Request $request) {
            $email = (string) $request->input('email', 'guest');
            return Limit::perMinute(3)->by(strtolower($email) . '|' . $request->ip());
        });

        RateLimiter::for('password-reset', function (Request $request) {
            $email = (string) $request->input('email', 'guest');
            return Limit::perMinute(5)->by(strtolower($email) . '|' . $request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            $key = (string) ($request->session()->get('2fa:user:id') ?: $request->user()?->getAuthIdentifier() ?: $request->ip());
            return Limit::perMinute(6)->by($key . '|' . $request->ip());
        });

        RateLimiter::for('mail-send', function (Request $request) {
            $key = (string) ($request->user()?->getAuthIdentifier() ?: $request->ip());
            return Limit::perMinute(10)->by($key);
        });

        RateLimiter::for('search-suggest', function (Request $request) {
            $key = (string) ($request->user()?->getAuthIdentifier() ?: $request->ip());
            return Limit::perMinute(90)->by($key);
        });

        RateLimiter::for('api-read', function (Request $request) {
            $key = (string) ($request->user()?->getAuthIdentifier() ?: $request->ip());
            return Limit::perMinute(120)->by($key);
        });
    }
}
