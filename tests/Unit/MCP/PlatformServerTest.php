<?php

declare(strict_types=1);

use Laravel\Mcp\Server\Contracts\Transport;
use Laravel\Mcp\Server\Tool as McpTool;
use Opscale\NovaMCP\Contracts\PromptsResolver;
use Opscale\NovaMCP\Contracts\ResourcesResolver;
use Opscale\NovaMCP\Contracts\ToolsResolver;
use Opscale\NovaMCP\MCP\PlatformServer;
use Opscale\NovaMCP\MCP\Prompts\BusinessTaskPrompt;
use Opscale\NovaMCP\MCP\Resources\DomainResource;
use Opscale\NovaMCP\MCP\Resources\ProcessResource;
use Opscale\NovaMCP\MCP\Tools\CreateTool;
use Opscale\NovaMCP\MCP\Tools\DeleteTool;
use Opscale\NovaMCP\MCP\Tools\ReadTool;
use Opscale\NovaMCP\MCP\Tools\UpdateTool;

beforeEach(function () {
    $this->transport = Mockery::mock(Transport::class);
});

it('registers the four core CRUD tools', function () {
    $server = new PlatformServer($this->transport);

    $tools = (new ReflectionClass($server))->getProperty('tools');
    $tools->setAccessible(true);

    expect($tools->getValue($server))->toBe([
        CreateTool::class,
        ReadTool::class,
        UpdateTool::class,
        DeleteTool::class,
    ]);
});

it('registers the two core resources', function () {
    $server = new PlatformServer($this->transport);

    $resources = (new ReflectionClass($server))->getProperty('resources');
    $resources->setAccessible(true);

    expect($resources->getValue($server))->toBe([
        DomainResource::class,
        ProcessResource::class,
    ]);
});

it('registers the BusinessTaskPrompt by default', function () {
    $server = new PlatformServer($this->transport);

    $prompts = (new ReflectionClass($server))->getProperty('prompts');
    $prompts->setAccessible(true);

    expect($prompts->getValue($server))->toBe([BusinessTaskPrompt::class]);
});

it('merges extra tools from a ToolsResolver', function () {
    $extraTool = new class extends McpTool
    {
        protected string $name = 'extra-tool';
    };

    $resolver = Mockery::mock(ToolsResolver::class);
    $resolver->shouldReceive('resolve')->once()->andReturn([$extraTool::class]);

    $server = new PlatformServer($this->transport, toolsResolver: $resolver);

    $tools = (new ReflectionClass($server))->getProperty('tools');
    $tools->setAccessible(true);

    expect($tools->getValue($server))->toContain($extraTool::class);
});

it('merges extra resources from a ResourcesResolver', function () {
    $resolver = Mockery::mock(ResourcesResolver::class);
    $resolver->shouldReceive('resolve')->once()->andReturn(['App\\Custom\\Resource']);

    $server = new PlatformServer($this->transport, resourcesResolver: $resolver);

    $resources = (new ReflectionClass($server))->getProperty('resources');
    $resources->setAccessible(true);

    expect($resources->getValue($server))->toContain('App\\Custom\\Resource');
});

it('merges extra prompts from a PromptsResolver', function () {
    $resolver = Mockery::mock(PromptsResolver::class);
    $resolver->shouldReceive('resolve')->once()->andReturn(['App\\Custom\\Prompt']);

    $server = new PlatformServer($this->transport, promptsResolver: $resolver);

    $prompts = (new ReflectionClass($server))->getProperty('prompts');
    $prompts->setAccessible(true);

    expect($prompts->getValue($server))->toContain('App\\Custom\\Prompt');
});

it('does not require any resolver to be provided', function () {
    $server = new PlatformServer($this->transport);

    expect($server)->toBeInstanceOf(PlatformServer::class);
});
