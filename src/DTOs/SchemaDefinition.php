<?php
namespace Saher\ArtisanSchematics\DTOs;

/**
 * A language-agnostic representation of a Model or Enum.
 * This is the core Intermediate Representation (IR).
 */
final readonly class SchemaDefinition
{
    /**
     * @param 'model'|'enum' $type
     * @param list<PropertyDefinition|EnumCaseDefinition> $properties
     * @param list<string> $imports
     */
    public function __construct(
        public string $name,
        public string $type,
        public array $properties,
        public array $imports = [],
    ) {}
}