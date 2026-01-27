<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind Search Intention Service
        $this->app->bind(\App\Contracts\SearchIntentionInterface::class, function ($app) {
            if (!empty(config('services.openai.api_key'))) {
                return new \App\Services\Search\OpenAiSearchIntentionService();
            }
            return new \App\Services\Search\MockSearchIntentionService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Auth::provider('seller_driver', function ($app, array $config) {
            return new \App\Providers\SellerUserProvider($app['hash'], $config['model']);
        });

        // Scope to layouts to avoid overhead/conflicts on partials or exports
        \Illuminate\Support\Facades\View::composer(['admin.layouts.*', 'front.layouts.*', 'layouts.*', 'welcome'], function ($view) {
            $globalCategories = \App\Models\Category::where('level', 2)
                ->where('hide_in_navigation', '0')
                ->orderBy('position', 'asc')
                ->get();
            $view->with('globalCategories', $globalCategories);
        });
    }
}
