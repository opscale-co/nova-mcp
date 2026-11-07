<?php

declare(strict_types=1);

namespace Opscale\NovaMCP\Tests;

use Inertia\ServiceProvider;
use Laravel\Fortify\FortifyServiceProvider;
use Laravel\Mcp\Server\McpServiceProvider;
use Laravel\Nova\NovaCoreServiceProvider;
use Laravel\Nova\NovaServiceProvider;
use Lorisleiva\Actions\ActionServiceProvider;
use Opscale\NovaMCP\ToolServiceProvider;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Override;
use Workbench\App\Providers\WorkbenchServiceProvider;

abstract class TestCase extends BaseTestCase
{
    use WithWorkbench;

    #[Override]
    protected function getPackageProviders($app): array
    {
        return array_merge(parent::getPackageProviders($app), [
            ServiceProvider::class,
            NovaCoreServiceProvider::class,
            NovaServiceProvider::class,
            FortifyServiceProvider::class,
            McpServiceProvider::class,
            ActionServiceProvider::class,
            \Opscale\Actions\ToolServiceProvider::class,
            WorkbenchServiceProvider::class,
            \Workbench\App\Providers\NovaServiceProvider::class,
            ToolServiceProvider::class,
        ]);
    }

    #[Override]
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    #[Override]
    protected function defineDatabaseMigrations(): void
    {
        parent::defineDatabaseMigrations();
        $this->loadLaravelMigrations();
    }
}
