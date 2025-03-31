<?php declare(strict_types=1);

namespace App\Providers;

use App\Models\PassportClient;
use App\Models\PassportToken;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

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
        URL::forceScheme((bool) config("emd.https", false) ? "https" : "http");

        RateLimiter::for(
            "api",
            static fn(Request $request) => Limit::perMinute(
                (int) config("emd.api.request_rate", 600)
            )->by($request->user()?->id ?: $request->ip())
        );

        if ((bool) config("emd.api.hash_secret")) {
            Passport::hashClientSecrets();
        }
        if ((bool) config("emd.api.password_grant")) {
            Passport::enablePasswordGrant();
        }
 
        Passport::useClientModel(PassportClient::class);
        Passport::useTokenModel(PassportToken::class);

        Passport::tokensExpireIn(
            now()->addDays((int) config("emd.api.acccess_tokens_expiry"))
        );
        Passport::refreshTokensExpireIn(
            now()->addDays((int) config("emd.api.refresh_tokens_expiry"))
        );
        Passport::personalAccessTokensExpireIn(
            now()->addDays((int) config("emd.api.personal_tokens_expiry"))
        );
 
        Passport::tokensCan([
            'send-emails' => 'Send emails',
            'upload-files' => 'Upload files',
        ]);
    }
}
