<?php

namespace Opscale\NovaMCP;

use Laravel\Mcp\Facades\Mcp;
use Opscale\NovaMCP\MCP\PlatformServer;
use Opscale\NovaPackageTools\NovaPackageServiceProvider;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;

class ToolServiceProvider extends NovaPackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('nova-mcp')
            ->hasInstallCommand(function (InstallCommand $installCommand): void {
                $installCommand
                    ->askToStarRepoOnGitHub('opscale-co/nova-mcp');
            });
    }

    final public function packageBooted(): void
    {
        parent::packageBooted();
        $this->registerMCPRoutes();
    }

    final protected function registerMCPRoutes(): void
    {
        Mcp::local('nova-mcp', PlatformServer::class);
    }
}
