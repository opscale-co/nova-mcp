<?php

declare(strict_types=1);

namespace Opscale\NovaMCP\Tests;

use Inertia\ServiceProvider;
use Laravel\Dusk\Browser;
use Laravel\Dusk\DuskServiceProvider;
use Laravel\Fortify\FortifyServiceProvider;
use Laravel\Mcp\Server\McpServiceProvider;
use Laravel\Nova\NovaCoreServiceProvider;
use Laravel\Nova\NovaServiceProvider;
use Lorisleiva\Actions\ActionServiceProvider;
use Opscale\NovaMCP\ToolServiceProvider;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\Dusk\TestCase as BaseTestCase;
use Override;
use Workbench\App\Providers\WorkbenchServiceProvider;

abstract class DuskTestCase extends BaseTestCase
{
    use WithWorkbench;

    protected static $baseServePort = 8089;

    #[Override]
    protected function getPackageProviders($app): array
    {
        return array_merge(parent::getPackageProviders($app), [
            ServiceProvider::class,
            NovaCoreServiceProvider::class,
            NovaServiceProvider::class,
            FortifyServiceProvider::class,
            DuskServiceProvider::class,
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

        $app['config']->set('app.key', 'base64:J/BOQS8DkmztF/8XJkS9PqUd+/oeRPtlT0Lyo43QZ64=');
    }

    final protected function loginToNova(Browser $browser): Browser
    {
        $browser->visit('/nova');

        if ($browser->element('input[name="email"]')) {
            $browser->type('email', 'admin@laravel.com')
                ->type('password', 'password')
                ->press('Log In')
                ->waitForText('Get Started');
        }

        return $browser;
    }
}
