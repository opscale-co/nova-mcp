<?php

declare(strict_types=1);

use Opscale\NovaMCP\Contracts\DomainResolver;
use Opscale\NovaMCP\MCP\Resources\DomainResource;

it('resolves a valid DBML payload as a text response', function () {
    $resolver = Mockery::mock(DomainResolver::class);
    $resolver->shouldReceive('resolve')->andReturn(<<<'DBML'
Project sample {
  database_type: 'PostgreSQL'
  Note: 'Sample project for tests with enough length to satisfy validators.'
}

Table users {
  id integer [pk, increment, note: 'Primary key']
  email varchar [not null, note: 'Login email']
  Note: 'User accounts'
}
DBML);

    $resource = new DomainResource($resolver);
    $response = $resource->handle();

    $payload = (string) $response->content();
    expect($payload)->toContain('Project sample');
    expect($payload)->toContain('Table users');
});

it('returns an error response when the DBML is empty', function () {
    $resolver = Mockery::mock(DomainResolver::class);
    $resolver->shouldReceive('resolve')->andReturn('   ');

    $resource = new DomainResource($resolver);
    $response = $resource->handle();

    expect($response->isError())->toBeTrue();
    expect((string) $response->content())->toContain('DBML is empty');
});

it('flags missing project, table and column notes', function () {
    $resolver = Mockery::mock(DomainResolver::class);
    $resolver->shouldReceive('resolve')->andReturn(<<<'DBML'
Project sample {
  database_type: 'PostgreSQL'
}

Table users {
  id integer [pk]
  email varchar [not null]
}
DBML);

    $resource = new DomainResource($resolver);
    $response = $resource->handle();

    expect($response->isError())->toBeTrue();
    $content = (string) $response->content();
    expect($content)->toContain('missing project-level notes');
    expect($content)->toContain('Tables missing notes');
    expect($content)->toContain('Columns missing notes');
});

it('flags incomplete DBML payloads', function () {
    $resolver = Mockery::mock(DomainResolver::class);
    $resolver->shouldReceive('resolve')->andReturn('Project x {}');

    $resource = new DomainResource($resolver);
    $response = $resource->handle();

    expect($response->isError())->toBeTrue();
    expect((string) $response->content())->toContain('less than 50 characters');
});

it('returns an error when the resolver throws', function () {
    $resolver = Mockery::mock(DomainResolver::class);
    $resolver->shouldReceive('resolve')->andThrow(new RuntimeException('boom'));

    $resource = new DomainResource($resolver);
    $response = $resource->handle();

    expect($response->isError())->toBeTrue();
    $content = (string) $response->content();
    expect($content)->toContain('Error resolving DBML');
    expect($content)->toContain('boom');
});
