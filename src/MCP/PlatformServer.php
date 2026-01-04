<?php

namespace Opscale\NovaMCP\MCP;

use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Contracts\Transport;
use Opscale\NovaMCP\Contracts\ToolsResolver;
use Opscale\NovaMCP\MCP\Prompts\BusinessTaskPrompt;
use Opscale\NovaMCP\MCP\Resources\DomainResource;
use Opscale\NovaMCP\MCP\Resources\ProcessResource;
use Opscale\NovaMCP\MCP\Tools\CreateTool;
use Opscale\NovaMCP\MCP\Tools\DeleteTool;
use Opscale\NovaMCP\MCP\Tools\ReadTool;
use Opscale\NovaMCP\MCP\Tools\UpdateTool;

/**
 * Platform Server
 *
 * MCP server that provides platform capabilities including
 * CRUD tools, dynamic resources, and business prompts.
 */
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

## Available Capabilities

### Tools
- **CRUD Tools**: create, read, update, delete - for managing records (users, products, orders, etc.)
- **Business Logic Tools**: reset-password, send-welcome-email - for executing business actions

### Resources (read these to understand the system)
- **process://bpmn** - Business process definitions in BPMN 2.0 format. Read this to understand the sequence of steps for any business task.
- **domain://dbml** - Domain schema defining all entities and their relationships. Read this to understand what data is needed.

### Prompts
- **Business Task Completion** - Use this prompt with a task description to execute a complete business workflow.

## How to Use

1. **To understand a business process**: Read the process://bpmn resource
2. **To understand data requirements**: Read the domain://dbml resource
3. **To manage data**: Use CRUD tools (create, read, update, delete)
4. **To execute business logic**: Use logic tools (reset-password, send-welcome-email)

When executing business tasks, always read the BPMN process first to understand the required sequence of operations.
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

    /**
     * Create a new Platform Server instance.
     */
    public function __construct(Transport $transport, ToolsResolver $toolsResolver)
    {
        parent::__construct($transport);

        $this->tools = array_merge($this->tools, $toolsResolver->resolve());
    }
}
