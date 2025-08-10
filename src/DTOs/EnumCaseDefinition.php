<?php
namespace Saher\ArtisanSchematics\DTOs;

/**
 * A language-agnostic representation of an Enum case.
 */
final readonly class EnumCaseDefinition
{
    public function __construct(
        public string $name,
        public string|int $value,
    ) {}
}