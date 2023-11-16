<?php

namespace Log1x\DisableMediaPages;

use Log1x\DisableMediaPages\Console\MediaGenerateSlugsCommand;
use Illuminate\Support\ServiceProvider;

class DisableMediaPagesServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Log1x/DisableMediaPages', function () {
            return new DisableMediaPages();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->commands([
            MediaGenerateSlugsCommand::class,
        ]);

        $this->app->make('Log1x/DisableMediaPages');
    }
}
