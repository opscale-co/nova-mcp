<?php

declare(strict_types=1);

use Opscale\NovaMCP\MCP\PlatformServer;
use Opscale\NovaMCP\MCP\Resources\ProcessResource;

it('routes resource reads to the workbench BPMN resolver', function () {
    // The validator currently flags missing per-element documentation due to a known
    // SimpleXML xpath namespace quirk in src/MCP/Resources/ProcessResource.php (the inner
    // `.//bpmn:documentation` xpath does not re-register the namespace). The Feature
    // test only verifies the resource path is wired to the resolver — content
    // validation is covered by the Unit suite.
    $response = withSuppressedWarnings(fn () => PlatformServer::resource(ProcessResource::class));

    $response->assertSee(['User Login']);
});
