<?php

namespace App\Providers;

use App\Http\Middleware\ActivityLogger;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ── Locale from user preferences ─────────────────────────────────
        $this->app->singleton('locale_setter', function () {
            if (auth()->check() && auth()->user()->locale) {
                app()->setLocale(auth()->user()->locale);
            } elseif (session()->has('locale')) {
                app()->setLocale(session('locale'));
            }
        });

        // ── Rate-limiting for API and auth ───────────────────────────────
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });
    }
}
