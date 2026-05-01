<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Opscale\NovaMCP\MCP\PlatformServer;
use Opscale\NovaMCP\MCP\Tools\DeleteTool;
use Workbench\App\Models\User;

uses(RefreshDatabase::class);

it('deletes an existing user', function () {
    $user = User::factory()->create();

    PlatformServer::tool(DeleteTool::class, [
        'resource' => 'users',
        'id' => (string) $user->id,
    ])
        ->assertOk()
        ->assertSee('successfully removed');

    expect(User::query()->find($user->id))->toBeNull();
});

it('reports an error when the id does not exist', function () {
    PlatformServer::tool(DeleteTool::class, [
        'resource' => 'users',
        'id' => '999999',
    ])->assertHasErrors(["could not be found in 'users'"]);
});

it('reports an error for an unknown resource', function () {
    PlatformServer::tool(DeleteTool::class, [
        'resource' => 'unicorns',
        'id' => '1',
    ])->assertHasErrors(["The collection 'unicorns'"]);
});
