<?php
namespace Saher\ArtisanSchematics\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Saher\ArtisanSchematics\Analyzers\ModelAnalyzer;
use Symfony\Component\Finder\Finder;

class ExportCommand extends Command
{
    protected $signature = 'schematics:export {--paths=*}';
    protected $description = 'Generate model definitions for multiple languages from your Eloquent models and PHP enums.';

    public function handle(ModelAnalyzer $analyzer): int
    {
        $this->info('🚀 Artisan Schematics Initialized...');

        $sourcePaths = $this->option('paths');
        if (empty($sourcePaths)) {
            $sourcePaths = config('schematics.source_paths', [app_path('Models')]);
        }

        $schemas = [];

        $finder = (new Finder())->files()->in($sourcePaths)->name('*.php');
        $files = iterator_to_array($finder);

        // First pass: analyze all top-level classes/enums in the scanned files
        foreach ($files as $file) {
            $className = $this->getClassNameFromFile($file);
            if ($className && ($schema = $analyzer->analyze($className))) {
                $schemas[$schema->name] = $schema;
            }
        }

        // Recursively analyze and add any missing imports (enums/models) referenced by the main schemas
        $allClassNames = array_map(fn($file) => $this->getClassNameFromFile($file), $files);
        $allClassNames = array_filter($allClassNames);
        $added = true;
        // DEBUG: Output all class names found
        file_put_contents(__DIR__.'/../../debug_allClassNames.log', print_r($allClassNames, true));
        while ($added) {
            $added = false;
            foreach ($schemas as $schema) {
                foreach ($schema->imports as $importName) {
                    if (!isset($schemas[$importName])) {
                        // Try to find the class in allClassNames
                        $fqcn = null;
                        $filePath = null;
                        foreach ($allClassNames as $path => $candidate) {
                            // DEBUG: Log each importName and candidate
                            file_put_contents(__DIR__.'/../../debug_import_matching.log', "importName: $importName, candidate: $candidate\n", FILE_APPEND);
                            if (str_ends_with($candidate, "\\$importName")) {
                                $fqcn = $candidate;
                                $filePath = $path;
                                break;
                            }
                        }
                        if ($fqcn) {
                            file_put_contents(__DIR__.'/../../debug_import_found.log', "Matched importName: $importName to fqcn: $fqcn\n", FILE_APPEND);
                        }
                        // Ensure the file is loaded before analyzing (for enums)
                        if ($fqcn && !class_exists($fqcn) && !enum_exists($fqcn) && $filePath && file_exists($filePath)) {
                            require_once $filePath;
                        }
                        if ($fqcn && ($importSchema = $analyzer->analyze($fqcn))) {
                            $schemas[$importSchema->name] = $importSchema;
                            $added = true;
                        }
                    }
                }
            }
        }

        if (empty($schemas)) {
            $this->warn('No models or enums found to export in the specified paths.');
            return self::FAILURE;
        }

        $this->comment('Analyzed ' . count($schemas) . ' schemas. Starting generation...');
        $generators = config('schematics.generators', []);
        $generatedFiles = [];

        foreach ($generators as $lang => $config) {
            if ($config['enabled']) {
                $this->line("Running <fg=yellow>{$lang}</> generator...");

                File::ensureDirectoryExists($config['output_path']);
                /** @var \Saher\ArtisanSchematics\Contracts\GeneratorContract $generator */
                $generator = app($config['generator']);
                $generator->generate($schemas, $config['output_path']);
                $generatedFiles[$lang] = $config['output_path'];
            }
        }

        $this->newLine();
        $this->info('✅ Generation Complete!');
        $this->table(
            ['Language', 'Output Directory'],
            collect($generatedFiles)->map(fn ($path, $lang) => [ucfirst($lang), $path])->toArray()
        );

        return self::SUCCESS;
    }

    private function getClassNameFromFile(\SplFileInfo $file): ?string
    {
        $contents = file_get_contents($file->getRealPath());

        $namespace = '';
        if (preg_match('/^namespace\s+([^;]+);/m', $contents, $matches)) {
            $namespace = trim($matches[1]);
        }

        $classOrEnum = '';
        if (preg_match('/^(?:\s*)?(class|enum|interface)\s+([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/m', $contents, $matches)) {
            $classOrEnum = $matches[2];
        }

        if (empty($classOrEnum)) {
            return null;
        }

        return $namespace ? ($namespace . '\\' . $classOrEnum) : $classOrEnum;
    }
}