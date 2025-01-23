<?php declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

/**
 * App service provider class
 *
 * @package  App
 * @category Providers
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
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
        if ((bool) env("FORCE_HTTPS", false)) {
            URL::forceScheme("https");
        }

        RateLimiter::for("api", function (Request $request) {
            $limit = (int) env("API_REQUEST_PER_MINUTE", 600);
            return Limit::perMinute($limit)->by(
                $request->user()?->id ?: $request->ip()
            );
        });
    }
}
