<?php

declare(strict_types=1);

use Opscale\NovaMCP\MCP\PlatformServer;
use Opscale\NovaMCP\MCP\Resources\DomainResource;

it('exposes the workbench DBML as a resource', function () {
    PlatformServer::resource(DomainResource::class)
        ->assertOk()
        ->assertSee(['Project nova_mcp_workbench', 'Table users']);
});
