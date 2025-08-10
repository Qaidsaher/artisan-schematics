<?php
namespace Saher\ArtisanSchematics\Generators;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Saher\ArtisanSchematics\Contracts\GeneratorContract;
use Saher\ArtisanSchematics\DTOs\SchemaDefinition;

class SwiftGenerator implements GeneratorContract
{
    public function generate(array $schemas, string $outputPath): void
    {
        foreach ($schemas as $schema) {
            $fileName = Str::studly($schema->name) . '.swift';
            $content = match ($schema->type) {
                'model' => $this->renderModel($schema),
                'enum' => $this->renderEnum($schema),
            };
            File::put("{$outputPath}/{$fileName}", $content);
        }
    }

    private function renderModel(SchemaDefinition $schema): string
    {
        $imports = collect($schema->imports)
            ->map(fn (string $import) => "import " . Str::studly($import))
            ->implode("\n");

        $properties = collect($schema->properties)
            ->map(function ($prop) {
                $swiftType = $this->mapGenericTypeToSwift($prop->genericType);
                $optional = $prop->isNullable ? '?' : '';
                $name = Str::camel($prop->name);
                return "    var {$name}: {$swiftType}{$optional}";
            })
            ->implode("\n");

        return "import Foundation\n{$imports}\n\nstruct {$schema->name}: Codable {\n{$properties}\n}";
    }

    private function renderEnum(SchemaDefinition $schema): string
    {
        $cases = collect($schema->properties)
            ->map(function ($case) {
                $value = is_string($case->value) ? "= \"{$case->value}\"" : "= {$case->value}";
                return "    case " . Str::camel($case->name) . " {$value}";
            })
            ->implode("\n");

        return "import Foundation\n\nenum {$schema->name}: String, Codable {\n{$cases}\n}";
    }

    private function mapGenericTypeToSwift(string $type): string
    {
        if (Str::startsWith($type, 'array<')) {
            $innerType = Str::between($type, '<', '>');
            return "[{$this->mapGenericTypeToSwift($innerType)}]";
        }
        return match ($type) {
            'number' => 'Double',
            'boolean' => 'Bool',
            'date', 'string' => 'String',
            'object', 'any' => 'Any',
            'array' => '[Any]',
            default => $type,
        };
    }
}
