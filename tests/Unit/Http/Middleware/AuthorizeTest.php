<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Laravel\Nova\Nova;
use Opscale\NovaMCP\Http\Middleware\Authorize;
use Opscale\NovaMCP\Tool;

it('passes the request through when the Tool authorizes it', function () {
    Nova::tools([new Tool]);

    $middleware = new Authorize;
    $called = false;

    $response = $middleware->handle(Request::create('/nova-mcp', 'GET'), function ($req) use (&$called) {
        $called = true;

        return 'next-handler-result';
    });

    expect($called)->toBeTrue();
    expect($response)->toBe('next-handler-result');
});

it('matches only Tool instances', function () {
    $middleware = new Authorize;

    expect($middleware->matchesTool(new Tool))->toBeTrue();
    expect($middleware->matchesTool(new stdClass))->toBeFalse();
});
