<?php

namespace Workbench\App\Resolvers;

use Opscale\NovaMCP\Contracts\ModelsResolver;
use Workbench\App\Nova\User;

/**
 * Workbench Models Resolver
 *
 * Example implementation of ModelsResolver for the workbench environment.
 * This provides the list of Nova resources available for CRUD operations.
 */
class WorkbenchModelsResolver implements ModelsResolver
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
