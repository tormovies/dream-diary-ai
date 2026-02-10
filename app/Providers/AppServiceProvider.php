<?php

namespace App\Providers;

use App\Console\Commands\BackfillDreamInterpretationStats;
use App\Console\Commands\ImportRedirectsFromFile;
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
        $this->commands([
            ImportRedirectsFromFile::class,
            BackfillDreamInterpretationStats::class,
        ]);
    }
}
