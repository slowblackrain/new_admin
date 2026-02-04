<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

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
        Paginator::useBootstrap();

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

        // Popup Integration (Front Only)
        \Illuminate\Support\Facades\View::composer(['layouts.front', 'front.main.index', 'front.layouts.mobile_header', 'front.layouts.mobile_bottom_nav', 'front.goods.search'], function ($view) {
            $now = now();
            // Cart Link (Assuming Cart Model or Facade)
            $cartCount = 0;
            if (\Illuminate\Support\Facades\Auth::check()) {
                $cartCount = \App\Models\Cart::currentUser()->count();
            } else {
                // Check session/cookie cart if exists? 
                // Legacy logic usually depends. For now, Auth only or Guest cart via session id.
                // Cart::currentUser() should handle session_id check if implemented correctly.
                // Checking Cart model implementation... Assuming Cart::currentUser() works for guest too if built that way.
                // If not, just 0 for guests for safe fallback.
                 $cartCount = \App\Models\Cart::currentUser()->count();
            }
            $view->with('cartCount', $cartCount);


            $popups = \App\Models\DesignPopup::where('status', '!=', 'stop')
                ->where(function($q) use ($now) {
                    $q->where('status', 'show')
                      ->orWhere(function($sub) use ($now) {
                          $sub->where('status', 'period')
                              ->where('period_s', '<=', $now)
                              ->where('period_e', '>=', $now);
                      });
                })
                ->where('design_type', 'basic') // Assuming basic for now
                //->where('open', 1) // If open column controls visibility
                ->get();
            $view->with('popups', $popups);
        });
    }
}
