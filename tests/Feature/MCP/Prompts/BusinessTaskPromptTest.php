<?php

declare(strict_types=1);

use Opscale\NovaMCP\MCP\PlatformServer;
use Opscale\NovaMCP\MCP\Prompts\BusinessTaskPrompt;

it('returns the assembled ACTIVATORS prompt with the task injected', function () {
    PlatformServer::prompt(BusinessTaskPrompt::class, ['task' => 'Send invoice'])
        ->assertOk()
        ->assertSee([
            'Business Task Completion',
            'Send invoice',
            'EXECUTE NOW',
        ]);
});
