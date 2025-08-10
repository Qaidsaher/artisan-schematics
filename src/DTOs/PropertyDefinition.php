<?php
namespace Saher\ArtisanSchematics\DTOs;

/**
 * A language-agnostic representation of a Model property.
 */
final readonly class PropertyDefinition
{
    /**
     * @param 'string'|'number'|'boolean'|'date'|'array'|'object'|string $genericType
     */
    public function __construct(
        public string $name,
        public string $genericType,
        public bool $isNullable,
        public bool $isCustomType = false,
    ) {}
}