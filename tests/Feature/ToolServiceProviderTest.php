<?php

declare(strict_types=1);

use Laravel\Mcp\Facades\Mcp;

it('registers the local nova-mcp server with the laravel/mcp facade', function () {
    $servers = Mcp::servers();

    expect($servers)->toHaveKey('nova-mcp');
    expect(Mcp::getLocalServer('nova-mcp'))->toBeCallable();
});
