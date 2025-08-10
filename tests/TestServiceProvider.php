<?php

namespace Saher\ArtisanSchematics\Tests;

use Illuminate\Support\ServiceProvider;

class TestServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // This is the magic. We get Composer's autoloader and tell it
        // to register our test Fixtures namespace. Now, the sandboxed
        // Laravel application will know how to find Post, User, and PostStatus.
        $loader = require __DIR__.'/../vendor/autoload.php';
        $loader->addPsr4('Saher\\ArtisanSchematics\\Tests\\Fixtures\\', __DIR__.'/Fixtures');
    }
}