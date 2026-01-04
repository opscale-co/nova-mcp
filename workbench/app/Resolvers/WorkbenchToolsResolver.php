<?php

namespace Workbench\App\Resolvers;

use Opscale\NovaMCP\Contracts\ToolsResolver;
use Workbench\App\Services\Actions\ResetPassword;
use Workbench\App\Services\Actions\SendWelcomeEmail;

/**
 * Workbench Tools Resolver
 *
 * Example implementation of ToolsResolver for the workbench environment.
 * This provides the list of business logic tools available in the workbench.
 */
class WorkbenchToolsResolver implements ToolsResolver
{
    /**
     * Resolve and return the array of supported MCP tool classes.
     *
     * @return array<int, class-string<\Opscale\Actions\Action>>
     */
    public function resolve(): array
    {
        return [
            ResetPassword::class,
            SendWelcomeEmail::class,
        ];
    }
}
