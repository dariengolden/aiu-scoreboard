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
        // Uses a lock file so this only runs once per deployment.
        if (app()->runningInConsole()) {
            return;
        }

        $lockFile = storage_path('framework/migrated.lock');

        if (file_exists($lockFile)) {
            return;
        }

        try {
            Artisan::call('migrate', ['--force' => true]);

            if (Schema::hasTable('teams') && \App\Models\Team::count() === 0) {
                Artisan::call('db:seed', ['--force' => true]);
            }

            file_put_contents($lockFile, now()->toISOString());
        } catch (\Throwable $e) {
            logger()->warning('Auto-migrate/seed failed: '.$e->getMessage());
        }
    }
}
