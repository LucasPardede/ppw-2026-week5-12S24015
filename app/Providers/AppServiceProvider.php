<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Services\Auth\CisAuthenticatorInterface::class,
            function () {
                $mode = config('perpustakaan.cis_auth_mode', 'mock');
                return $mode === 'api'
                    ? new \App\Services\Auth\ApiCisAuthenticator()
                    : new \App\Services\Auth\MockCisAuthenticator();
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Penggunaan pagination berbasis Bootstrap 5
        Paginator::useBootstrapFive();

        // Audit Kinerja Kueri SQL untuk pembuktian mitigasi N+1 Query Problem
        DB::listen(function ($query) {
            Log::info(sprintf(
                "[SQL AUDIT] (%.2f ms) %s", $query->time, $query->sql
            ));
        });
    }
}
