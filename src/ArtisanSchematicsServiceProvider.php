<?php
namespace Saher\ArtisanSchematics;

use Illuminate\Support\ServiceProvider;
use Saher\ArtisanSchematics\Commands\ExportCommand;

class ArtisanSchematicsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ExportCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/../config/schematics.php' => config_path('schematics.php'),
        ], 'schematics-config');
    }
}