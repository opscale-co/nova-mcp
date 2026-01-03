<?php

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;
use Opscale\NovaMCP\Contracts\DomainResolver;
use Opscale\NovaMCP\Contracts\ProcessResolver;
use Opscale\NovaMCP\MCP\Resources\DomainResource;
use Opscale\NovaMCP\MCP\Resources\ProcessResource;
use Workbench\App\Resolvers\WorkbenchDomainResolver;
use Workbench\App\Resolvers\WorkbenchProcessResolver;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind the DomainResolver implementation
        $this->app->singleton(DomainResolver::class, WorkbenchDomainResolver::class);

        // Bind the ProcessResolver implementation
        $this->app->singleton(ProcessResolver::class, WorkbenchProcessResolver::class);

        // Bind DomainResource with the resolver
        $this->app->when(DomainResource::class)
            ->needs(DomainResolver::class)
            ->give(WorkbenchDomainResolver::class);

        // Bind ProcessResource with the resolver
        $this->app->when(ProcessResource::class)
            ->needs(ProcessResolver::class)
            ->give(WorkbenchProcessResolver::class);
    }
}
