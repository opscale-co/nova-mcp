<?php

declare(strict_types=1);

namespace Opscale\NovaMCP\Tests;

use Laravel\Dusk\Browser;
use Opscale\NovaMCP\ToolServiceProvider;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\Dusk\TestCase as BaseTestCase;
use Override;

abstract class DuskTestCase extends BaseTestCase
{
    use WithWorkbench;

    protected static $baseServePort = 8089;

    #[Override]
    protected function getPackageProviders($app): array
    {
        return array_merge(parent::getPackageProviders($app), [
            \Inertia\ServiceProvider::class,
            \Laravel\Nova\NovaCoreServiceProvider::class,
            \Laravel\Nova\NovaServiceProvider::class,
            \Laravel\Fortify\FortifyServiceProvider::class,
            \Laravel\Dusk\DuskServiceProvider::class,
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
