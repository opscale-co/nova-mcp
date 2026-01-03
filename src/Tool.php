<?php

namespace Opscale\NovaMCP;

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool as NovaTool;

class Tool extends NovaTool
{
    public function boot()
    {
        Nova::script('nova-mcp', __DIR__ . '/../dist/js/tool.js');
        Nova::style('nova-mcp', __DIR__ . '/../dist/css/tool.css');
    }

    public function menu(Request $request)
    {
        return MenuSection::make('NovaMCP')
            ->path('nova-mcp')
            ->icon('server');
    }
}
