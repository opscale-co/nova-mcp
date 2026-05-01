<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Opscale\NovaMCP\MCP\PlatformServer;
use Opscale\NovaMCP\MCP\Tools\CreateTool;
use Workbench\App\Models\User;

uses(RefreshDatabase::class);

it('creates a user via the create tool', function () {
    $response = PlatformServer::tool(CreateTool::class, [
        'resource' => 'users',
        'payload' => [
            'name' => 'Mary Sue',
            'email' => 'mary@example.com',
            'password' => 'super-secret-pw',
        ],
    ]);

    $response->assertOk()->assertSee('successfully added');

    expect(User::query()->where('email', 'mary@example.com')->exists())->toBeTrue();
});

it('reports an error for an unknown resource', function () {
    PlatformServer::tool(CreateTool::class, [
        'resource' => 'unicorns',
        'payload' => ['name' => 'x'],
    ])->assertHasErrors(["The collection 'unicorns'"]);
});

it('reports validation errors when the payload is incomplete', function () {
    PlatformServer::tool(CreateTool::class, [
        'resource' => 'users',
        'payload' => [
            'name' => 'No Email',
        ],
    ])->assertHasErrors(['required information is missing']);
});
