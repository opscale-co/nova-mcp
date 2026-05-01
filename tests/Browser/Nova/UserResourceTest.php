<?php

declare(strict_types=1);

namespace Opscale\NovaMCP\Tests\Browser\Nova;

use Laravel\Dusk\Browser;
use Opscale\NovaMCP\Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\Test;

final class UserResourceTest extends DuskTestCase
{
    #[Test]
    final public function admin_can_log_into_nova(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->assertSee('Get Started');
        });
    }

    #[Test]
    final public function admin_can_browse_the_user_resource_index(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/users')
                ->waitForText('Users')
                ->assertSee('Users');
        });
    }

    #[Test]
    final public function admin_can_open_the_create_user_form(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/users/new')
                ->waitForText('Create User')
                ->assertSee('Name')
                ->assertSee('Email');
        });
    }
}
