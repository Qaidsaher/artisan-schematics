<?php

namespace Saher\ArtisanSchematics\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Saher\ArtisanSchematics\ArtisanSchematicsServiceProvider;

class TestCase extends OrchestraTestCase
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            ArtisanSchematicsServiceProvider::class,
            TestServiceProvider::class, // <-- We add our test provider here.
        ];
    }
}