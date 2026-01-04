<?php

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;
use Opscale\NovaMCP\Contracts\DomainResolver;
use Opscale\NovaMCP\Contracts\ModelsResolver;
use Opscale\NovaMCP\Contracts\ProcessResolver;
use Opscale\NovaMCP\Contracts\PromptsResolver;
use Opscale\NovaMCP\Contracts\ResourcesResolver;
use Opscale\NovaMCP\Contracts\ToolsResolver;
use Workbench\App\Resolvers\WorkbenchDomainResolver;
use Workbench\App\Resolvers\WorkbenchModelsResolver;
use Workbench\App\Resolvers\WorkbenchProcessResolver;
use Workbench\App\Resolvers\WorkbenchPromptsResolver;
use Workbench\App\Resolvers\WorkbenchResourcesResolver;
use Workbench\App\Resolvers\WorkbenchToolsResolver;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(DomainResolver::class, WorkbenchDomainResolver::class);
        $this->app->singleton(ProcessResolver::class, WorkbenchProcessResolver::class);
        $this->app->singleton(ModelsResolver::class, WorkbenchModelsResolver::class);
        $this->app->singleton(PromptsResolver::class, WorkbenchPromptsResolver::class);
        $this->app->singleton(ResourcesResolver::class, WorkbenchResourcesResolver::class);
        $this->app->singleton(ToolsResolver::class, WorkbenchToolsResolver::class);
    }
}
