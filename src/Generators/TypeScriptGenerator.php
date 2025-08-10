<?php
namespace Saher\ArtisanSchematics\Generators;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Saher\ArtisanSchematics\Contracts\GeneratorContract;
use Saher\ArtisanSchematics\DTOs\SchemaDefinition;

class TypeScriptGenerator implements GeneratorContract
{
    public function generate(array $schemas, string $outputPath): void
    {
        // DEBUG: Log all schema names and types
        $log = [];
        foreach ($schemas as $schema) {
            $log[] = $schema->name . ':' . $schema->type;
        }
        file_put_contents(__DIR__.'/../../debug_ts_generator.log', implode("\n", $log));

        foreach ($schemas as $schema) {
            $content = match ($schema->type) {
                'model' => $this->renderModel($schema),
                'enum' => $this->renderEnum($schema),
            };

            File::put("{$outputPath}/{$schema->name}.ts", $content);
        }
    }

    private function renderModel(SchemaDefinition $schema): string
    {
        $imports = collect($schema->imports)
            ->map(fn (string $import) => "import type { {$import} } from './{$import}';")
            ->implode("\n");

        $properties = collect($schema->properties)
            ->map(function ($prop) {
                $tsType = $this->mapGenericTypeToTs($prop->genericType);
                $nullable = $prop->isNullable ? ' | null' : '';
                return "    {$prop->name}: {$tsType}{$nullable};";
            })
            ->implode("\n");

        return "{$imports}\n\nexport interface {$schema->name} {\n{$properties}\n}\n";
    }

    private function renderEnum(SchemaDefinition $schema): string
    {
        $cases = collect($schema->properties)
            ->map(function ($case) {
                $value = is_string($case->value) ? "\"{$case->value}\"" : $case->value;
                return "    {$case->name} = {$value},";
            })
            ->implode("\n");
        
        return "export enum {$schema->name} {\n{$cases}\n}\n";
    }

    private function mapGenericTypeToTs(string $type): string
    {
        if (Str::startsWith($type, 'array<')) {
            $innerType = Str::between($type, '<', '>');
            return "{$this->mapGenericTypeToTs($innerType)}[]";
        }

        return match ($type) {
            'number' => 'number',
            'boolean' => 'boolean',
            'date', 'string' => 'string',
            'object', 'array', 'any' => 'any',
            default => $type, // For custom types like other models/enums
        };
    }
}