<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
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
        // Auto-migrate and seed in production (e.g. Railway).
        // Runs migrations if needed, then seeds if the teams table is empty.
        if (app()->runningInConsole()) {
            return;
        }

        try {
            // Run any pending migrations
            Artisan::call('migrate', ['--force' => true]);

            // Seed only if the database is empty (teams table has no rows)
            if (Schema::hasTable('teams') && \App\Models\Team::count() === 0) {
                Artisan::call('db:seed', ['--force' => true]);
            }
        } catch (\Throwable $e) {
            // Log but don't crash the app if DB isn't ready yet
            logger()->warning('Auto-migrate/seed failed: '.$e->getMessage());
        }
    }
}
