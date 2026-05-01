<?php

declare(strict_types=1);

use Opscale\NovaMCP\Tests\TestCase;

uses(TestCase::class)->in('Unit', 'Feature');

/**
 * Run a callback with PHP warnings suppressed.
 *
 * Some legacy code paths in src/ emit PHP warnings (e.g. inner SimpleXML xpath
 * calls without re-registered namespaces). With phpunit.xml's failOnWarning=true,
 * those warnings turn green tests red. Wrap only the call site that triggers them.
 */
function withSuppressedWarnings(Closure $callback): mixed
{
    set_error_handler(static fn () => true, E_WARNING | E_NOTICE);

    try {
        return $callback();
    } finally {
        restore_error_handler();
    }
}

function validBpmn(): string
{
    return <<<'BPMN'
<?xml version="1.0" encoding="UTF-8"?>
<definitions xmlns="http://www.omg.org/spec/BPMN/20100524/MODEL"
             id="Definitions_1"
             targetNamespace="http://example.com/bpmn">
  <process id="p1" name="Sample">
    <documentation>Sample documented process for tests</documentation>
    <startEvent id="s1" name="Start">
      <documentation>Start event documentation</documentation>
    </startEvent>
    <task id="t1" name="Do work">
      <documentation>Task documentation</documentation>
    </task>
    <exclusiveGateway id="g1" name="Choice">
      <documentation>Gateway documentation</documentation>
    </exclusiveGateway>
    <endEvent id="e1" name="End">
      <documentation>End event documentation</documentation>
    </endEvent>
  </process>
</definitions>
BPMN;
}
