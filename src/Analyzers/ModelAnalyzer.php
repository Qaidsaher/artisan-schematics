<?php
namespace Saher\ArtisanSchematics\Analyzers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use Saher\ArtisanSchematics\DTOs\EnumCaseDefinition;
use Saher\ArtisanSchematics\DTOs\PropertyDefinition;
use Saher\ArtisanSchematics\DTOs\SchemaDefinition;

class ModelAnalyzer
{
    public function analyze(string $className): ?SchemaDefinition
    {
        $logFile = __DIR__ . '/../../debug_analyzer.log';
        file_put_contents($logFile, "analyze called: $className\n", FILE_APPEND);
        if (!class_exists($className) && !enum_exists($className)) {
            file_put_contents($logFile, "  -> does not exist\n", FILE_APPEND);
            return null;
        }

        $reflection = new ReflectionClass($className);

        if ($reflection->isEnum()) {
            file_put_contents($logFile, "  -> is enum\n", FILE_APPEND);
            $result = $this->analyzeEnum($reflection);
            file_put_contents($logFile, "  -> analyzeEnum result: " . ($result ? 'ok' : 'null') . "\n", FILE_APPEND);
            return $result;
        }

        if ($reflection->isSubclassOf(Model::class) && !$reflection->isAbstract()) {
            file_put_contents($logFile, "  -> is model\n", FILE_APPEND);
            $result = $this->analyzeModel($reflection);
            file_put_contents($logFile, "  -> analyzeModel result: " . ($result ? 'ok' : 'null') . "\n", FILE_APPEND);
            return $result;
        }

        file_put_contents($logFile, "  -> not enum or model\n", FILE_APPEND);
        return null;
    }

    private function analyzeEnum(ReflectionClass $reflection): ?SchemaDefinition
    {
        $cases = [];
        // PHP 8.1+ native enums
        if (method_exists($reflection, 'isEnum') && $reflection->isEnum()) {
            // Use ReflectionEnum if available
            if (class_exists('ReflectionEnum')) {
                $enumReflection = new \ReflectionEnum($reflection->getName());
                foreach ($enumReflection->getCases() as $case) {
                    // $case->getValue() returns the enum instance, not the value
                    $enumInstance = $case->getValue();
                    $caseName = $case->getName();
                    $caseValue = method_exists($enumInstance, 'value') ? $enumInstance->value : $caseName;
                    $cases[] = new EnumCaseDefinition($caseName, $caseValue);
                }
            } else {
                // Fallback: try to use enum class itself (works for backed enums)
                if (method_exists($reflection->getName(), 'cases')) {
                    foreach (call_user_func([$reflection->getName(), 'cases']) as $case) {
                        $cases[] = new EnumCaseDefinition($case->name, property_exists($case, 'value') ? $case->value : $case->name);
                    }
                }
            }
        } else {
            // Classic PHP class constants (simulate enum)
            foreach ($reflection->getConstants() as $name => $value) {
                $cases[] = new EnumCaseDefinition($name, $value);
            }
        }
        if (empty($cases)) {
            return null;
        }
        return new SchemaDefinition($reflection->getShortName(), 'enum', $cases, []);
    }

    private function analyzeModel(ReflectionClass $reflection): SchemaDefinition
    {
        $model = $reflection->newInstanceWithoutConstructor();
        $properties = [];
        $imports = [];

        $properties['id'] = new PropertyDefinition('id', 'number', false);

        foreach ($model->getCasts() as $property => $cast) {
            $genericType = $this->mapCastToGenericType($cast);
            if (enum_exists($cast)) {
                $enumName = class_basename($cast);
                $imports[] = $enumName;
                $properties[$property] = new PropertyDefinition($property, $enumName, true, true);
            } else {
                $properties[$property] = new PropertyDefinition($property, $genericType, true);
            }
        }

        if ($model->usesTimestamps()) {
            $properties['created_at'] = new PropertyDefinition('created_at', 'date', true);
            $properties['updated_at'] = new PropertyDefinition('updated_at', 'date', true);
        }

        foreach ($model->getAppends() as $appended) {
            $properties[$appended] = new PropertyDefinition($appended, 'any', true);
        }
        
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getNumberOfParameters() > 0 || !($returnType = $method->getReturnType()) || str_starts_with($method->getName(), 'get')) {
                continue;
            }

            if (is_a($returnType->getName(), Relation::class, true)) {
                try {
                    $relation = $method->invoke($model);
                    $relatedModelName = class_basename($relation->getRelated());
                    $imports[] = $relatedModelName;
                    
                    $isToMany = Str::contains(class_basename($relation), ['Many', 'MorphToMany', 'HasManyThrough']);
                    $genericType = $isToMany ? "array<{$relatedModelName}>" : $relatedModelName;
                    
                    // Use the relation name as the key to ensure uniqueness
                    $properties[$method->getName()] = new PropertyDefinition($method->getName(), $genericType, true, true);
                } catch (\Throwable) {}
            }
        }

        return new SchemaDefinition(
            $reflection->getShortName(),
            'model',
            // CORRECTED: Using array_values on an associative array is the correct way
            // to get a simple list of its values, preserving uniqueness by key.
            array_values($properties),
            array_values(array_unique($imports))
        );
    }

    private function mapCastToGenericType(string $cast): string
    {
        return match (Str::before($cast, ':')) {
            'int', 'integer', 'timestamp' => 'number',
            'real', 'float', 'double', 'decimal' => 'number',
            'bool', 'boolean' => 'boolean',
            'string' => 'string',
            'date', 'datetime' => 'date',
            'array', 'json', 'collection' => 'array',
            'object' => 'any',
            default => 'any',
        };
    }
}