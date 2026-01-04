<?php

namespace Workbench\App\Resolvers;

use Opscale\NovaMCP\Contracts\ResourcesResolver;
use Workbench\App\Nova\User;

/**
 * Workbench Resources Resolver
 *
 * Example implementation of ResourcesResolver for the workbench environment.
 * This provides the list of Nova resources available for CRUD operations.
 */
class WorkbenchResourcesResolver implements ResourcesResolver
{
    /**
     * Resolve and return the array of Nova resource classes.
     *
     * @return array<int, class-string<\Laravel\Nova\Resource>>
     */
    public function resolve(): array
    {
        return [
            User::class,
        ];
    }
}
