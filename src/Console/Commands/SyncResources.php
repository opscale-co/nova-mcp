<?php

namespace Opscale\NovaMCP\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

final class SyncResources extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nova-mcp:sync-resources
                            {--filter=* : Filter resources by namespace or class name}
                            {--exclude=* : Exclude specific resources}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Nova resources to nova-mcp config file';

    final public function __construct(
        private readonly Application $application
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    final public function handle(): int
    {
        $this->info('Discovering Nova resources...');

        // Use Nova::serving to ensure all resources are loaded
        Nova::serving(function (ServingNova $servingNova): void {
            $this->processResources();
        });

        // Trigger ServingNova event to load resources
        $request = Request::create('/');
        ServingNova::dispatch($this->application, $request);

        return Command::SUCCESS;
    }

    /**
     * Process and sync resources to config
     */
    private function processResources(): void
    {
        $resources = Nova::$resources;
        /** @var array<int, string> $filters */
        $filters = array_filter((array) $this->option('filter'));
        /** @var array<int, string> $excludes */
        $excludes = array_filter((array) $this->option('exclude'));

        if ($resources === []) {
            $this->warn('No Nova resources found.');

            return;
        }

        $this->info('Found ' . count($resources) . ' Nova resources.');

        // Filter resources if needed
        $filteredResources = $this->filterResources($resources, $filters, $excludes);

        if ($filteredResources === []) {
            $this->warn('No resources match the filter criteria.');

            return;
        }

        // Update config file
        $this->updateConfigFile($filteredResources);

        $this->info('Successfully synced ' . count($filteredResources) . ' resources to config/nova-mcp.php');

        // Display the resources
        $this->table(
            ['Resource', 'URI Key', 'Model'],
            array_map(function (string $resource): array {
                return [
                    $resource::label(),
                    $resource::uriKey(),
                    $resource::$model,
                ];
            }, $filteredResources)
        );
    }

    /**
     * Filter resources based on provided options
     *
     * @param  array<int, class-string>  $resources
     * @param  array<int, string>  $filters
     * @param  array<int, string>  $excludes
     * @return array<int, class-string>
     */
    private function filterResources(array $resources, array $filters, array $excludes): array
    {
        $filtered = $resources;

        // Apply include filters
        if ($filters !== []) {
            $filtered = array_filter($filtered, function (string $resource) use ($filters): bool {
                foreach ($filters as $filter) {
                    if (str_contains($resource, $filter)) {
                        return true;
                    }
                }

                return false;
            });
        }

        // Apply exclude filters
        if ($excludes !== []) {
            $filtered = array_filter($filtered, function (string $resource) use ($excludes): bool {
                foreach ($excludes as $exclude) {
                    if (str_contains($resource, $exclude)) {
                        return false;
                    }
                }

                return true;
            });
        }

        return array_values($filtered);
    }

    /**
     * Update the config file with discovered resources
     *
     * @param  array<int, class-string>  $resources
     */
    private function updateConfigFile(array $resources): void
    {
        $configPath = config_path('nova-mcp.php');

        if (! File::exists($configPath)) {
            $this->error('Config file not found at: ' . $configPath);
            $this->info('Please publish the config file first: php artisan vendor:publish --tag=nova-mcp-config');

            return;
        }

        // Read existing config file content
        $content = File::get($configPath);

        // Build the resources array string
        $resourcesArray = $this->buildResourcesArray($resources);

        // Replace the resources array in the content
        $pattern = '/[\'"]resources[\'"]\s*=>\s*\[[^\]]*\]/s';
        $replacement = "'resources' => [\n{$resourcesArray}    ]";

        $newContent = preg_replace($pattern, $replacement, $content);

        if ($newContent === null) {
            $this->error('Failed to update the config file. Pattern not found.');

            return;
        }

        // Write back to file
        File::put($configPath, $newContent);

        // Clear config cache to reflect changes
        $this->call('config:clear');
    }

    /**
     * Build the resources array string
     *
     * @param  array<int, class-string>  $resources
     */
    private function buildResourcesArray(array $resources): string
    {
        $resourcesContent = '';
        foreach ($resources as $resource) {
            $resourcesContent .= "        \\{$resource}::class,\n";
        }

        return $resourcesContent;
    }
}
