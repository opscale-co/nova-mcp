<?php

namespace Opscale\NovaMCP\MCP;

use Laravel\Mcp\Server;
use Opscale\NovaMCP\MCP\Prompts\BusinessTaskPrompt;
use Opscale\NovaMCP\MCP\Resources\DomainResource;
use Opscale\NovaMCP\MCP\Resources\ProcessResource;
use Opscale\NovaMCP\MCP\Tools\CreateTool;
use Opscale\NovaMCP\MCP\Tools\DeleteTool;
use Opscale\NovaMCP\MCP\Tools\ReadTool;
use Opscale\NovaMCP\MCP\Tools\UpdateTool;

class PlatformServer extends Server
{
    /**
     * The MCP server's name.
     */
    protected string $name = 'Platform Server';

    /**
     * The MCP server's version.
     */
    protected string $version = '1.0.0';

    /**
     * The MCP server's instructions for the LLM.
     */
    protected string $instructions = <<<'INSTRUCTIONS'
This server provides access to your platform capabilities, mirroring what you can do in the web application.

You can perform two types of tasks:
1. Managing Records: Add, view, update, and remove information (employees, clients, products, orders, etc.)
2. Business Tasks: Perform your actual work tasks (approve, send, process, complete)

Available Resources:
- domain://dbml - Business domain model with all entities and relationships
- process://bpmn - Business processes and workflows in BPMN 2.0 format

Available Prompts:
- business-tasks - Guide to performing business tasks

Use the tools to manage records and execute business tasks just like you would in the web application.
INSTRUCTIONS;

    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [
        CreateTool::class,
        ReadTool::class,
        UpdateTool::class,
        DeleteTool::class,
    ];

    /**
     * The resources registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Resource>>
     */
    protected array $resources = [
        DomainResource::class,
        ProcessResource::class,
    ];

    /**
     * The prompts registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Prompt>>
     */
    protected array $prompts = [
        BusinessTaskPrompt::class,
    ];
}
