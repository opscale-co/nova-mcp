<?php

declare(strict_types=1);

use Laravel\Mcp\Request;
use Opscale\NovaMCP\MCP\Prompts\BusinessTaskPrompt;

it('declares a required task argument', function () {
    $prompt = new BusinessTaskPrompt;
    $args = $prompt->arguments();

    expect($args)->toHaveCount(1);
    expect($args[0]->name)->toBe('task');
    expect($args[0]->required)->toBeTrue();
});

it('assembles all ACTIVATORS sections plus the EXECUTE NOW block', function () {
    $prompt = new BusinessTaskPrompt;
    $request = new Request(['task' => 'Send invoice to Acme Corp']);

    $output = $prompt->handle($request);

    expect($output)
        ->toContain('# Business Task Completion')
        ->toContain('## AUDIENCE')
        ->toContain('## CONTEXT')
        ->toContain('## TONE')
        ->toContain('## INTENT')
        ->toContain('## VERIFICATION')
        ->toContain('## APPEARANCE')
        ->toContain('## TASK')
        ->toContain('## OUTPUT')
        ->toContain('## REVIEW')
        ->toContain('## SAMPLES')
        ->toContain('## EXECUTE NOW')
        ->toContain('Send invoice to Acme Corp');
});

it('falls back to a placeholder when no task is provided', function () {
    $prompt = new BusinessTaskPrompt;
    $request = new Request([]);

    expect($prompt->handle($request))->toContain('No task provided');
});
