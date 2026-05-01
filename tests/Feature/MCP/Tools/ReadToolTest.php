<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Opscale\NovaMCP\MCP\PlatformServer;
use Opscale\NovaMCP\MCP\Tools\ReadTool;
use Workbench\App\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    User::factory()->create(['name' => 'Alice', 'email' => 'alice@example.com']);
    User::factory()->create(['name' => 'Bob', 'email' => 'bob@example.com']);
});

it('lists users', function () {
    PlatformServer::tool(ReadTool::class, [
        'resource' => 'users',
    ])
        ->assertOk()
        ->assertSee(['alice@example.com', 'bob@example.com']);
});

it('filters users by exact email', function () {
    PlatformServer::tool(ReadTool::class, [
        'resource' => 'users',
        'filter' => ['email' => 'alice@example.com'],
    ])
        ->assertOk()
        ->assertSee('alice@example.com')
        ->assertDontSee('bob@example.com');
});

it('reports an error for an unknown resource', function () {
    PlatformServer::tool(ReadTool::class, [
        'resource' => 'unicorns',
    ])->assertHasErrors(["The collection 'unicorns'"]);
});
