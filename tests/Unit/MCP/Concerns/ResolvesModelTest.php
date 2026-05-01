<?php

declare(strict_types=1);

use Opscale\NovaMCP\Contracts\ModelsResolver;
use Opscale\NovaMCP\MCP\Concerns\ResolvesModel;
use Workbench\App\Models\User;
use Workbench\App\Nova\User as UserResource;

beforeEach(function () {
    $this->subject = new class
    {
        use ResolvesModel {
            resolveModelClass as public;
            getAvailableResources as public;
            getModelFromResource as public;
        }
    };
});

it('resolves the model class from a Nova URI key', function () {
    $resolver = Mockery::mock(ModelsResolver::class);
    $resolver->shouldReceive('resolve')->andReturn([UserResource::class]);
    $this->app->instance(ModelsResolver::class, $resolver);

    expect($this->subject->resolveModelClass(UserResource::uriKey()))->toBe(User::class);
});

it('returns null for an unknown URI key', function () {
    $resolver = Mockery::mock(ModelsResolver::class);
    $resolver->shouldReceive('resolve')->andReturn([UserResource::class]);
    $this->app->instance(ModelsResolver::class, $resolver);

    expect($this->subject->resolveModelClass('does-not-exist'))->toBeNull();
});

it('skips classes that do not exist', function () {
    $resolver = Mockery::mock(ModelsResolver::class);
    $resolver->shouldReceive('resolve')->andReturn(['App\\NotARealClass', UserResource::class]);
    $this->app->instance(ModelsResolver::class, $resolver);

    expect($this->subject->resolveModelClass(UserResource::uriKey()))->toBe(User::class);
});

it('lists available resource URIs', function () {
    $resolver = Mockery::mock(ModelsResolver::class);
    $resolver->shouldReceive('resolve')->andReturn([UserResource::class]);
    $this->app->instance(ModelsResolver::class, $resolver);

    expect($this->subject->getAvailableResources())->toBe([UserResource::uriKey()]);
});
