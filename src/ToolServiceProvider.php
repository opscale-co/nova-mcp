<?php

namespace Opscale\NovaMCP;

use Laravel\Mcp\Facades\Mcp;
use Opscale\NovaMCP\Console\Commands\SyncResources;
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
            ->hasConfigFile('nova-mcp')
            ->hasCommand(SyncResources::class)
            ->hasInstallCommand(function (InstallCommand $installCommand): void {
                $installCommand
                    ->publishConfigFile()
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
