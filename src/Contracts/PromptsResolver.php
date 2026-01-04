<?php

namespace Opscale\NovaMCP\Contracts;

interface PromptsResolver
{
    /**
     * Resolve and return the array of MCP prompt classes.
     *
     * This method should return an array of fully qualified class names
     * for MCP prompts that should be exposed through the platform server.
     *
     * @return array<int, class-string<\Laravel\Mcp\Server\Prompt>>
     *
     * @example
     * return [
     *     \App\MCP\Prompts\OnboardingPrompt::class,
     *     \App\MCP\Prompts\ReportGeneratorPrompt::class,
     * ];
     */
    public function resolve(): array;
}
