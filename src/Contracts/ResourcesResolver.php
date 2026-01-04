<?php

namespace Opscale\NovaMCP\Contracts;

interface ResourcesResolver
{
    /**
     * Resolve and return the array of MCP resource classes.
     *
     * This method should return an array of fully qualified class names
     * for MCP resources that should be exposed through the platform server.
     *
     * @return array<int, class-string<\Laravel\Mcp\Server\Resource>>
     *
     * @example
     * return [
     *     \App\MCP\Resources\InventoryResource::class,
     *     \App\MCP\Resources\ReportsResource::class,
     *     \App\MCP\Resources\ConfigResource::class,
     * ];
     */
    public function resolve(): array;
}
