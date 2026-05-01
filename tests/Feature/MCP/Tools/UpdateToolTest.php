<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Opscale\NovaMCP\MCP\PlatformServer;
use Opscale\NovaMCP\MCP\Tools\UpdateTool;
use Workbench\App\Models\User;

uses(RefreshDatabase::class);

it('updates an existing user', function () {
    $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);

    PlatformServer::tool(UpdateTool::class, [
        'resource' => 'users',
        'id' => (string) $user->id,
        'payload' => [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'password' => 'super-secret-pw',
        ],
    ])
        ->assertOk()
        ->assertSee('successfully updated');

    expect($user->fresh()->name)->toBe('New Name');
});

it('reports an error when the id does not exist', function () {
    PlatformServer::tool(UpdateTool::class, [
        'resource' => 'users',
        'id' => '999999',
        'payload' => ['name' => 'Ghost'],
    ])->assertHasErrors(["could not be found in 'users'"]);
});

it('reports validation errors on invalid payload', function () {
    $user = User::factory()->create();

    PlatformServer::tool(UpdateTool::class, [
        'resource' => 'users',
        'id' => (string) $user->id,
        'payload' => ['email' => 'not-an-email'],
    ])->assertHasErrors(['changes you made are invalid']);
});
