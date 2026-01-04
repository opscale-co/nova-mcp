<?php

namespace Opscale\NovaMCP\Contracts;

interface ResourcesResolver
{
    /**
     * Resolve and return the array of Nova resource classes.
     *
     * This method should return an array of fully qualified class names
     * for Laravel Nova resources that should be exposed through CRUD tools.
     *
     * @return array<int, class-string<\Laravel\Nova\Resource>>
     *
     * @example
     * return [
     *     \App\Nova\User::class,
     *     \App\Nova\Post::class,
     *     \App\Nova\Order::class,
     * ];
     */
    public function resolve(): array;
}
