<?php

namespace App\Providers;

use App\Models\Platform;
use App\Models\Site;
use App\Services\Platform\DetectionService;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Application;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use App\Services\Platform\EmailTemplatingService;

/**
 * Class AppServiceProvider
 * @package App\Providers
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        VerifyEmail::toMailUsing(function ($notification) {
            /** @var Platform $platform */
            $platform = current_platform();

            $system =  env('APP_NAME');

            $verifyUrl = URL::temporarySignedRoute(
                'verification.verify', Carbon::now()->addMinutes(60), ['id' => $notification->getKey()]
            );

            /** @var string $view */
            $view = EmailTemplatingService::getViewByPlatformAndCountry($platform,'verification_send',  null);

            /** @var string $subject */
            $subject = EmailTemplatingService::getSubjectByPlatformAndCountry($platform,'auth.verification',  null);

            return (new MailMessage)
                ->subject($subject)
                ->markdown($view, compact('verifyUrl', 'system'));
        });


    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('current_platform', function (Application $app) {
            /** @var DetectionService $instance */
            $instance = $app->make(DetectionService::class);

            /** @var Platform $platform */
            $platform = $instance->detectPlatform();

            return $platform;
        });

        $this->app->singleton('current_site', function (Application $app) {
            /** @var DetectionService $instance */
            $instance = $app->make(DetectionService::class);

            /** @var Platform $platform */
            $platform = current_platform();

            /** @var Site $site */
            $site = $instance->detectSite($platform);

            return $site;
        });
    }
}
