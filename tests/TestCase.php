<?php

declare(strict_types=1);

namespace Opscale\NovaMCP\Tests;

use Opscale\NovaMCP\ToolServiceProvider;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Override;

abstract class TestCase extends BaseTestCase
{
    use WithWorkbench;

    #[Override]
    protected function getPackageProviders($app): array
    {
        return array_merge(parent::getPackageProviders($app), [
            \Inertia\ServiceProvider::class,
            \Laravel\Nova\NovaCoreServiceProvider::class,
            \Laravel\Nova\NovaServiceProvider::class,
            \Laravel\Fortify\FortifyServiceProvider::class,
            \Laravel\Mcp\Server\McpServiceProvider::class,
            \Lorisleiva\Actions\ActionServiceProvider::class,
            \Opscale\Actions\ToolServiceProvider::class,
            \Workbench\App\Providers\WorkbenchServiceProvider::class,
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
