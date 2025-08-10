<?php
namespace Saher\ArtisanSchematics\Generators;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Saher\ArtisanSchematics\Contracts\GeneratorContract;
use Saher\ArtisanSchematics\DTOs\SchemaDefinition;

class DartGenerator implements GeneratorContract
{
    public function generate(array $schemas, string $outputPath): void
    {
        foreach ($schemas as $schema) {
            $fileName = Str::snake($schema->name) . '.dart';
            $content = match ($schema->type) {
                'model' => $this->renderModel($schema),
                'enum' => $this->renderEnum($schema),
            };
            File::put("{$outputPath}/{$fileName}", $content);
        }
    }

    private function renderModel(SchemaDefinition $schema): string
    {
        $fileName = Str::snake($schema->name) . '.dart';
        
        $imports = collect($schema->imports)
            ->map(fn (string $import) => "import '" . Str::snake($import) . ".dart';")
            ->prepend("import 'package:json_annotation/json_annotation.dart';")
            ->implode("\n");

        $properties = collect($schema->properties)
            ->map(function ($prop) {
                $dartType = $this->mapGenericTypeToDart($prop->genericType);
                $nullable = $prop->isNullable ? '?' : '';
                $name = Str::camel($prop->name);
                return "  final {$dartType}{$nullable} {$name};";
            })
            ->implode("\n");

        $constructorParams = collect($schema->properties)
            ->map(fn ($prop) => "    required this." . Str::camel($prop->name) . ",")
            ->implode("\n");

        return "{$imports}\n\npart '{$fileName}.g.dart';\n\n@JsonSerializable()\nclass {$schema->name} {\n{$properties}\n\n  {$schema->name}({\n{$constructorParams}\n  });\n\n  factory {$schema->name}.fromJson(Map<String, dynamic> json) => _\$_{$schema->name}FromJson(json);\n  Map<String, dynamic> toJson() => _\$_{$schema->name}ToJson(this);\n}\n";
    }

    private function renderEnum(SchemaDefinition $schema): string
    {
        $cases = collect($schema->properties)
            ->map(function ($case) {
                $value = is_string($case->value) ? "'{$case->value}'" : $case->value;
                return "  @JsonValue({$value})\n  " . Str::camel($case->name) . ",";
            })->implode("\n");

        return "import 'package:json_annotation/json_annotation.dart';\n\nenum {$schema->name} {\n{$cases}\n}\n";
    }

    private function mapGenericTypeToDart(string $type): string
    {
        if (Str::startsWith($type, 'array<')) {
            $innerType = Str::between($type, '<', '>');
            return "List<{$this->mapGenericTypeToDart($innerType)}>";
        }
        
        return match ($type) {
            'number' => 'double', // Use double for flexibility with decimals
            'boolean' => 'bool',
            'date', 'string' => 'String',
            'object', 'any' => 'dynamic',
            'array' => 'List<dynamic>',
            default => $type,
        };
    }
}