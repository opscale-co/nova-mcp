<?php

declare(strict_types=1);

use Opscale\NovaMCP\Tests\TestCase;

it('boots the testbench application', function () {
    expect($this->app)->not->toBeNull();
    expect($this->app->environment())->toBe('testing');
});

it('uses the package TestCase', function () {
    expect($this)->toBeInstanceOf(TestCase::class);
});
