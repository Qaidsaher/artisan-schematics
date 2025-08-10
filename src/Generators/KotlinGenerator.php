<?php
namespace Saher\ArtisanSchematics\Generators;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Saher\ArtisanSchematics\Contracts\GeneratorContract;
use Saher\ArtisanSchematics\DTOs\SchemaDefinition;

class KotlinGenerator implements GeneratorContract
{
    public function generate(array $schemas, string $outputPath): void
    {
        foreach ($schemas as $schema) {
            $fileName = "{$schema->name}.kt";
            $content = match ($schema->type) {
                'model' => $this->renderModel($schema, $outputPath),
                'enum' => $this->renderEnum($schema, $outputPath),
            };
            File::put("{$outputPath}/{$fileName}", $content);
        }
    }

    private function renderModel(SchemaDefinition $schema, string $outputPath): string
    {
        $package = $this->resolvePackageName($outputPath);

        $properties = collect($schema->properties)
            ->map(function ($prop) {
                $kotlinType = $this->mapGenericTypeToKotlin($prop->genericType);
                $nullable = $prop->isNullable ? '?' : '';
                $name = Str::camel($prop->name);
                return "    @SerializedName(\"{$prop->name}\")\n    val {$name}: {$kotlinType}{$nullable},";
            })
            ->implode("\n");

        return "package {$package}\n\nimport com.google.gson.annotations.SerializedName\n\ndata class {$schema->name}(\n{$properties}\n)\n";
    }

    private function renderEnum(SchemaDefinition $schema, string $outputPath): string
    {
        $package = $this->resolvePackageName($outputPath);

        $cases = collect($schema->properties)
            ->map(function ($case) {
                $value = is_string($case->value) ? "\"{$case->value}\"" : $case->value;
                return "    @SerializedName({$value})\n    {$case->name},";
            })
            ->implode("\n");

        return "package {$package}\n\nimport com.google.gson.annotations.SerializedName\n\nenum class {$schema->name} {\n{$cases}\n}\n";
    }

    private function mapGenericTypeToKotlin(string $type): string
    {
        if (Str::startsWith($type, 'array<')) {
            $innerType = Str::between($type, '<', '>');
            return "List<{$this->mapGenericTypeToKotlin($innerType)}>";
        }

        return match ($type) {
            'number' => 'Double',
            'boolean' => 'Boolean',
            'date', 'string' => 'String',
            'object', 'any' => 'Any',
            'array' => 'List<Any>',
            default => $type,
        };
    }

    private function resolvePackageName(string $outputPath): string
    {
        if (Str::contains($outputPath, '/src/main/java/')) {
            return Str::of($outputPath)
                ->after('/src/main/java/')
                ->replace('/', '.')
                ->trim('.');
        }
        return 'com.example.schematics';
    }
}