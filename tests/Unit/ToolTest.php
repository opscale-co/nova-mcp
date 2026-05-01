<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;
use Opscale\NovaMCP\Tool;

it('builds a Nova menu section pointing at nova-mcp', function () {
    $tool = new Tool;

    $menu = $tool->menu(Request::create('/nova', 'GET'));

    expect($menu)->toBeInstanceOf(MenuSection::class);
    expect($menu->jsonSerialize()['path'])->toContain('nova-mcp');
});
