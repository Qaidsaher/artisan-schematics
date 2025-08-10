<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Source Paths
    |--------------------------------------------------------------------------
    |
    | An array of paths where the package will scan for PHP Models and Enums
    | to be analyzed and transformed. You can add any directory that
    | contains classes you want to export.
    |
    */
    'source_paths' => [
        app_path('Models'),
        app_path('Enums'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Language Generators
    |--------------------------------------------------------------------------
    |
    | This is the core configuration for defining which languages to generate
    | schematics for. Each entry represents a target language.
    |
    | - 'enabled': Set to `true` to activate this generator.
    | - 'generator': The fully qualified class name of the generator.
    | - 'output_path': The absolute path where generated files will be stored.
    |
    */
    'generators' => [

        'typescript' => [
            'enabled' => true,
            'generator' => \Saher\ArtisanSchematics\Generators\TypeScriptGenerator::class,
            'output_path' => resource_path('ts/schemas'),
        ],

        'dart' => [
            'enabled' => true,
            'generator' => \Saher\ArtisanSchematics\Generators\DartGenerator::class,
            'output_path' => base_path('tests/output/dart'),
        ],

        'kotlin' => [
            'enabled' => true,
            'generator' => \Saher\ArtisanSchematics\Generators\KotlinGenerator::class,
            'output_path' => base_path('tests/output/kotlin'),
        ],

        'swift' => [
            'enabled' => true,
            'generator' => \Saher\ArtisanSchematics\Generators\SwiftGenerator::class,
            'output_path' => base_path('tests/output/swift'),
        ],

    ],
];