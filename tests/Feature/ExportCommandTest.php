<?php

use Illuminate\Support\Facades\File;
use Saher\ArtisanSchematics\Analyzers\ModelAnalyzer;
use Saher\ArtisanSchematics\DTOs\SchemaDefinition;
use Saher\ArtisanSchematics\Tests\Fixtures\Models\Post;

// NO MORE `require_once` CALLS. The TestServiceProvider handles this correctly.

test('model analyzer correctly creates schema definition', function () {
    $analyzer = new ModelAnalyzer();
    $schema = $analyzer->analyze(Post::class);

    expect($schema)->toBeInstanceOf(SchemaDefinition::class);
    expect($schema->name)->toBe('Post');
    expect($schema->imports)->toContain('User', 'PostStatus');
});

test('export command generates all required files successfully', function () {
    $tsPath = __DIR__.'/../output/ts';
    $dartPath = __DIR__.'/../output/dart';
    $kotlinPath = __DIR__.'/../output/kotlin';
    $swiftPath = __DIR__.'/../output/swift';

    // Cleanup before test
    File::deleteDirectory($tsPath);
    File::deleteDirectory($dartPath);
    File::deleteDirectory($kotlinPath);
    File::deleteDirectory($swiftPath);

    config()->set('schematics.generators', [
        'typescript' => [
            'enabled'     => true,
            'generator'   => \Saher\ArtisanSchematics\Generators\TypeScriptGenerator::class,
            'output_path' => $tsPath,
        ],
        'dart' => [
            'enabled'     => true,
            'generator'   => \Saher\ArtisanSchematics\Generators\DartGenerator::class,
            'output_path' => $dartPath,
        ],
        'kotlin' => [
            'enabled'     => true,
            'generator'   => \Saher\ArtisanSchematics\Generators\KotlinGenerator::class,
            'output_path' => $kotlinPath,
        ],
        'swift' => [
            'enabled'     => true,
            'generator'   => \Saher\ArtisanSchematics\Generators\SwiftGenerator::class,
            'output_path' => $swiftPath,
        ],
    ]);

    // Act: Run the command
    $this->artisan('schematics:export', [
        '--paths' => [
            __DIR__.'/../Fixtures/Models',
            __DIR__.'/../Fixtures/Enums',
        ]
    ])->assertSuccessful();


    // TypeScript
    expect(File::exists($tsPath.'/Post.ts'))->toBeTrue();
    expect(File::exists($tsPath.'/User.ts'))->toBeTrue();
    expect(File::exists($tsPath.'/PostStatus.ts'))->toBeTrue();
    expect(File::exists($tsPath.'/Comment.ts'))->toBeTrue();
    expect(File::exists($tsPath.'/Tag.ts'))->toBeTrue();
    expect(File::exists($tsPath.'/Country.ts'))->toBeTrue();
    expect(File::exists($tsPath.'/Address.ts'))->toBeTrue();

    // Dart
    expect(File::exists($dartPath.'/post.dart'))->toBeTrue();
    expect(File::exists($dartPath.'/user.dart'))->toBeTrue();
    expect(File::exists($dartPath.'/post_status.dart'))->toBeTrue();
    expect(File::exists($dartPath.'/comment.dart'))->toBeTrue();
    expect(File::exists($dartPath.'/tag.dart'))->toBeTrue();
    expect(File::exists($dartPath.'/country.dart'))->toBeTrue();
    expect(File::exists($dartPath.'/address.dart'))->toBeTrue();

    // Kotlin
    expect(File::exists($kotlinPath.'/Post.kt'))->toBeTrue();
    expect(File::exists($kotlinPath.'/User.kt'))->toBeTrue();
    expect(File::exists($kotlinPath.'/PostStatus.kt'))->toBeTrue();
    expect(File::exists($kotlinPath.'/Comment.kt'))->toBeTrue();
    expect(File::exists($kotlinPath.'/Tag.kt'))->toBeTrue();
    expect(File::exists($kotlinPath.'/Country.kt'))->toBeTrue();
    expect(File::exists($kotlinPath.'/Address.kt'))->toBeTrue();

    // Swift
    expect(File::exists($swiftPath.'/Post.swift'))->toBeTrue();
    expect(File::exists($swiftPath.'/User.swift'))->toBeTrue();
    expect(File::exists($swiftPath.'/PostStatus.swift'))->toBeTrue();
    expect(File::exists($swiftPath.'/Comment.swift'))->toBeTrue();
    expect(File::exists($swiftPath.'/Tag.swift'))->toBeTrue();
    expect(File::exists($swiftPath.'/Country.swift'))->toBeTrue();
    expect(File::exists($swiftPath.'/Address.swift'))->toBeTrue();

    // Cleanup after test
    File::deleteDirectory($tsPath);
    File::deleteDirectory($dartPath);
    File::deleteDirectory($kotlinPath);
    File::deleteDirectory($swiftPath);
});