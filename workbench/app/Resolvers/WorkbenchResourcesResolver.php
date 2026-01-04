<?php

namespace Workbench\App\Resolvers;

use Opscale\NovaMCP\Contracts\ResourcesResolver;

/**
 * Workbench Resources Resolver
 *
 * Example implementation of ResourcesResolver for the workbench environment.
 * This provides the list of MCP resources available in the workbench.
 */
class WorkbenchResourcesResolver implements ResourcesResolver
{
    /**
     * Resolve and return the array of MCP resource classes.
     *
     * @return array<int, class-string<\Laravel\Mcp\Server\Resource>>
     */
    public function resolve(): array
    {
        return [
            // Add your custom MCP resources here
            // Example: \Workbench\App\MCP\Resources\InventoryResource::class,
        ];
    }
}
