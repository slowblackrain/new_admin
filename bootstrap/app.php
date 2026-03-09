<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'seller.grade' => \App\Http\Middleware\CheckSellerGrade::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'payment/*',
        ]);
        
        // SAFE MODE: Rollback transactions on write methods when in local environment
        if (env('APP_ENV') === 'local') {
            $middleware->web(append: [
                \App\Http\Middleware\ForceTransactionRollback::class,
            ]);
        }

        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.login');
            }
            if ($request->is('seller') || $request->is('seller/*')) {
                return route('seller.login');
            }
            return route('member.login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
