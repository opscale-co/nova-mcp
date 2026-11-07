<?php

declare(strict_types=1);

namespace Workbench\App\Resolvers;

use Laravel\Mcp\Server\Prompt;
use Opscale\NovaMCP\Contracts\PromptsResolver;

/**
 * Workbench Prompts Resolver
 *
 * Example implementation of PromptsResolver for the workbench environment.
 * This provides the list of MCP prompts available in the workbench.
 */
class WorkbenchPromptsResolver implements PromptsResolver
{
    /**
     * Resolve and return the array of MCP prompt classes.
     *
     * @return array<int, class-string<Prompt>>
     */
    public function resolve(): array
    {
        return [
            // Add your custom MCP prompts here
            // Example: \Workbench\App\MCP\Prompts\OnboardingPrompt::class,
        ];
    }
}
