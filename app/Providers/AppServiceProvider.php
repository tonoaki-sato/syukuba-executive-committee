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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::define('view-financials', function (\App\Models\User $user, ?int $year = null) {
            $year = $year ?: session('active_fiscal_year', date('Y'));
            return $user->canManageEquipment($year);
        });
    }
}
