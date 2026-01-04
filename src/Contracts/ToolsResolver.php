<?php

namespace Opscale\NovaMCP\Contracts;

interface ToolsResolver
{
    /**
     * Resolve and return the array of supported action classes.
     *
     * This method should return an array of fully qualified class names
     * for opscale-co/actions that represent business logic tasks (not CRUD).
     * These actions will be exposed as MCP tools for business task execution.
     *
     * @return array<int, class-string<\Opscale\Actions\Action>>
     *
     * @example
     * return [
     *     \App\Actions\ApproveOrder::class,
     *     \App\Actions\SendInvoice::class,
     *     \App\Actions\ProcessPayment::class,
     * ];
     */
    public function resolve(): array;
}
