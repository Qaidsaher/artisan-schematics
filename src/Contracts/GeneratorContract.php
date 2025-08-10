<?php
namespace Saher\ArtisanSchematics\Contracts;

use Saher\ArtisanSchematics\DTOs\SchemaDefinition;

/**
 * Defines the contract that all language generators must implement.
 */
interface GeneratorContract
{
    /**
     * Generates language-specific files from the provided schemas.
     *
     * @param array<string, SchemaDefinition> $schemas An associative array of all schemas.
     * @param string $outputPath The absolute path to the output directory.
     * @return void
     */
    public function generate(array $schemas, string $outputPath): void;
}