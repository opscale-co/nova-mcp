<?php

declare(strict_types=1);

use Opscale\NovaMCP\Contracts\ProcessResolver;
use Opscale\NovaMCP\MCP\Resources\ProcessResource;

it('returns the BPMN payload as text when valid', function () {
    $resolver = Mockery::mock(ProcessResolver::class);
    $resolver->shouldReceive('resolve')->andReturn(validBpmn());

    $resource = new ProcessResource($resolver);
    $response = $resource->handle();

    $payload = (string) $response->content();
    expect($payload)->toContain('<process id="p1"');
});

it('reports an empty BPMN payload', function () {
    $resolver = Mockery::mock(ProcessResolver::class);
    $resolver->shouldReceive('resolve')->andReturn('');

    $resource = new ProcessResource($resolver);
    $response = $resource->handle();

    expect($response->isError())->toBeTrue();
    expect((string) $response->content())->toContain('BPMN XML is empty');
});

it('reports invalid XML', function () {
    $resolver = Mockery::mock(ProcessResolver::class);
    $resolver->shouldReceive('resolve')->andReturn('<not-xml>this is not closed correctly and is also too short');

    $resource = new ProcessResource($resolver);
    $response = $resource->handle();

    expect($response->isError())->toBeTrue();
});

it('reports missing BPMN namespace', function () {
    $resolver = Mockery::mock(ProcessResolver::class);
    $resolver->shouldReceive('resolve')->andReturn(<<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<definitions xmlns="http://example.com/not-bpmn" id="d">
  <process id="p" name="Sample">
    <documentation>doc</documentation>
  </process>
</definitions>
XML);

    $resource = new ProcessResource($resolver);
    $response = $resource->handle();

    expect($response->isError())->toBeTrue();
    expect((string) $response->content())->toContain('missing required BPMN 2.0 namespace');
});

it('reports tasks/events/gateways without documentation', function () {
    $resolver = Mockery::mock(ProcessResolver::class);
    $resolver->shouldReceive('resolve')->andReturn(<<<'BPMN'
<?xml version="1.0" encoding="UTF-8"?>
<definitions xmlns="http://www.omg.org/spec/BPMN/20100524/MODEL" id="d">
  <process id="p" name="Sample">
    <documentation>Process doc</documentation>
    <startEvent id="s" name="Start"/>
    <task id="t" name="Do work"/>
    <exclusiveGateway id="g" name="Choice"/>
    <endEvent id="e" name="End"/>
  </process>
</definitions>
BPMN);

    $resource = new ProcessResource($resolver);
    $response = withSuppressedWarnings(fn () => $resource->handle());

    expect($response->isError())->toBeTrue();
    $content = (string) $response->content();
    expect($content)->toContain('Tasks missing documentation');
    expect($content)->toContain('Events missing documentation');
    expect($content)->toContain('Gateways missing documentation');
});

it('returns an error when the resolver throws', function () {
    $resolver = Mockery::mock(ProcessResolver::class);
    $resolver->shouldReceive('resolve')->andThrow(new RuntimeException('boom'));

    $resource = new ProcessResource($resolver);
    $response = $resource->handle();

    expect($response->isError())->toBeTrue();
    expect((string) $response->content())->toContain('Error resolving BPMN');
});
