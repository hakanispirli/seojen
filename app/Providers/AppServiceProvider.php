<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SeoAnalyzerService;
use App\Services\SeoAnalyzer\HttpClientService;
use App\Services\SeoAnalyzer\MetaAnalyzerService;
use App\Services\SeoAnalyzer\HeadingAnalyzerService;
use App\Services\SeoAnalyzer\ImageAnalyzerService;
use App\Services\SeoAnalyzer\PerformanceAnalyzerService;
use App\Services\SeoAnalyzer\UrlAnalyzerService;
use App\Services\SeoAnalyzer\TechnicalSeoAnalyzerService;
use App\Services\SeoAnalyzer\LinkAnalyzerService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // HttpClientService singleton olarak kaydedilir
        $this->app->singleton(HttpClientService::class, function ($app) {
            return new HttpClientService();
        });

        // Analyzer servisleri
        $this->app->bind(MetaAnalyzerService::class, function ($app) {
            return new MetaAnalyzerService();
        });

        $this->app->bind(HeadingAnalyzerService::class, function ($app) {
            return new HeadingAnalyzerService();
        });

        $this->app->bind(ImageAnalyzerService::class, function ($app) {
            return new ImageAnalyzerService();
        });

        $this->app->bind(PerformanceAnalyzerService::class, function ($app) {
            return new PerformanceAnalyzerService();
        });

        $this->app->bind(UrlAnalyzerService::class, function ($app) {
            return new UrlAnalyzerService($app->make(HttpClientService::class));
        });

        $this->app->bind(TechnicalSeoAnalyzerService::class, function ($app) {
            return new TechnicalSeoAnalyzerService($app->make(HttpClientService::class));
        });

        $this->app->bind(LinkAnalyzerService::class, function ($app) {
            return new LinkAnalyzerService($app->make(HttpClientService::class));
        });

        // Ana SEO Analyzer servisi
        $this->app->bind(SeoAnalyzerService::class, function ($app) {
            return new SeoAnalyzerService(
                $app->make(HttpClientService::class),
                $app->make(MetaAnalyzerService::class),
                $app->make(HeadingAnalyzerService::class),
                $app->make(ImageAnalyzerService::class),
                $app->make(PerformanceAnalyzerService::class),
                $app->make(UrlAnalyzerService::class),
                $app->make(TechnicalSeoAnalyzerService::class),
                $app->make(LinkAnalyzerService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
