<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use Carbon\Carbon;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Laravel\Passport\Http\Controllers\AuthorizationController;
use Laravel\Passport\Http\Controllers\ClientController;
use Laravel\Passport\Http\Controllers\ScopeController;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Passport rotalarını manuel olarak tanımlama
        Route::group(['prefix' => 'oauth'], function () {
            Route::post('/token', [AccessTokenController::class, 'issueToken']);
            Route::post('/authorize', [AuthorizationController::class, 'authorize']);
            Route::post('/clients', [ClientController::class, 'store']);
            Route::post('/scopes', [ScopeController::class, 'store']);
            Route::delete('/tokens/{token_id}', [AccessTokenController::class, 'destroy']);
            Route::delete('/clients/{client_id}', [ClientController::class, 'destroy']);
            Route::delete('/scopes/{scope_id}', [ScopeController::class, 'destroy']);
        });

        Passport::tokensExpireIn(Carbon::now()->addDays(15));
        Passport::refreshTokensExpireIn(Carbon::now()->addDays(30));
    }
}
