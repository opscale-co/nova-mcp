<?php

namespace Opscale\NovaMCP\MCP\Resources;

use Exception;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;
use Opscale\NovaMCP\Contracts\ProcessResolver;

/**
 * Process Resource - Provides business processes for logic operations.
 *
 * This resource exposes business process definitions in BPMN 2.0 format, enabling
 * understanding of:
 * - The sequence of actions required to complete a business task
 * - Decision points and conditions that affect the workflow
 * - Which operations (CRUD or tools) are needed at each step
 *
 * The process definitions help determine whether a task requires data management
 * (CRUD operations) or business logic execution (tools), and in what order.
 */
class ProcessResource extends Resource
{
    /**
     * The resource URI
     */
    public string $uri = 'process://bpmn';

    /**
     * The resource name
     */
    public string $name = 'Business Processes';

    /**
     * The resource description
     */
    public string $description = 'Business processes for logic operations - describes sequences of actions to complete tasks and helps determine if CRUD operations or tools are needed';

    /**
     * The resource MIME type
     */
    public string $mimeType = 'application/xml';

    /**
     * Process resolver instance for providing BPMN XML
     */
    protected ProcessResolver $processResolver;

    /**
     * Constructor
     */
    public function __construct(ProcessResolver $processResolver)
    {
        $this->processResolver = $processResolver;
    }

    /**
     * Handle the resource request
     */
    public function handle(): Response
    {
        try {
            $bpmnXml = $this->processResolver->resolve();

            // Validate the BPMN XML
            $validationErrors = $this->validateBpmn($bpmnXml);

            if (! empty($validationErrors)) {
                return Response::error('BPMN validation failed: ' . implode('; ', $validationErrors));
            }

            return Response::text($bpmnXml);
        } catch (Exception $e) {
            return Response::error("Error resolving BPMN: {$e->getMessage()}");
        }
    }

    /**
     * Validate BPMN XML for completeness and required documentation
     *
     * @return array<int, string> Array of validation error messages
     */
    protected function validateBpmn(string $bpmnXml): array
    {
        $errors = [];

        // Check if XML is empty or too short
        if (empty(trim($bpmnXml))) {
            $errors[] = 'BPMN XML is empty';

            return $errors;
        }

        if (strlen($bpmnXml) < 100) {
            $errors[] = 'BPMN XML appears incomplete (less than 100 characters)';
        }

        // Validate XML structure
        if (! $this->isValidXml($bpmnXml)) {
            $errors[] = 'BPMN XML is not valid XML';

            return $errors;
        }

        // Validate BPMN 2.0 structure
        if (! $this->hasBpmnDefinitions($bpmnXml)) {
            $errors[] = 'BPMN XML is missing <definitions> root element';
        }

        if (! $this->hasBpmnNamespace($bpmnXml)) {
            $errors[] = 'BPMN XML is missing required BPMN 2.0 namespace';
        }

        // Validate process documentation
        $processesWithoutDocs = $this->getProcessesWithoutDocumentation($bpmnXml);
        if (! empty($processesWithoutDocs)) {
            $errors[] = 'Processes missing documentation: ' . implode(', ', $processesWithoutDocs);
        }

        // Validate task documentation
        $tasksWithoutDocs = $this->getTasksWithoutDocumentation($bpmnXml);
        if (! empty($tasksWithoutDocs)) {
            $count = count($tasksWithoutDocs);
            $sample = array_slice($tasksWithoutDocs, 0, 5);
            $errors[] = "Tasks missing documentation ({$count} total, showing first 5): " . implode(', ', $sample);
        }

        // Validate event documentation
        $eventsWithoutDocs = $this->getEventsWithoutDocumentation($bpmnXml);
        if (! empty($eventsWithoutDocs)) {
            $count = count($eventsWithoutDocs);
            $sample = array_slice($eventsWithoutDocs, 0, 5);
            $errors[] = "Events missing documentation ({$count} total, showing first 5): " . implode(', ', $sample);
        }

        // Validate gateway documentation
        $gatewaysWithoutDocs = $this->getGatewaysWithoutDocumentation($bpmnXml);
        if (! empty($gatewaysWithoutDocs)) {
            $count = count($gatewaysWithoutDocs);
            $sample = array_slice($gatewaysWithoutDocs, 0, 5);
            $errors[] = "Gateways missing documentation ({$count} total, showing first 5): " . implode(', ', $sample);
        }

        return $errors;
    }

    /**
     * Check if string is valid XML
     */
    protected function isValidXml(string $xml): bool
    {
        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($xml);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors(false);

        return $doc !== false && empty($errors);
    }

    /**
     * Check if BPMN has definitions root element
     */
    protected function hasBpmnDefinitions(string $bpmnXml): bool
    {
        return preg_match('/<definitions[^>]*>/', $bpmnXml) === 1;
    }

    /**
     * Check if BPMN has correct namespace
     */
    protected function hasBpmnNamespace(string $bpmnXml): bool
    {
        return preg_match('/xmlns="http:\/\/www\.omg\.org\/spec\/BPMN\/20100524\/MODEL"/', $bpmnXml) === 1
            || preg_match('/xmlns:bpmn="http:\/\/www\.omg\.org\/spec\/BPMN\/20100524\/MODEL"/', $bpmnXml) === 1;
    }

    /**
     * Get list of processes without documentation
     *
     * @return array<int, string>
     */
    protected function getProcessesWithoutDocumentation(string $bpmnXml): array
    {
        $processesWithoutDocs = [];

        try {
            $xml = simplexml_load_string($bpmnXml);
            if ($xml === false) {
                return [];
            }

            // Register namespaces
            $xml->registerXPathNamespace('bpmn', 'http://www.omg.org/spec/BPMN/20100524/MODEL');

            // Find all processes
            $processes = $xml->xpath('//bpmn:process | //process');

            if ($processes) {
                foreach ($processes as $process) {
                    $processId = (string) ($process['id'] ?? 'unknown');
                    $processName = (string) ($process['name'] ?? $processId);

                    // Check if process has documentation
                    $docs = $process->xpath('.//bpmn:documentation | .//documentation');
                    if (empty($docs) || empty(trim((string) $docs[0]))) {
                        $processesWithoutDocs[] = $processName;
                    }
                }
            }
        } catch (Exception $e) {
            // If parsing fails, return empty array
        }

        return $processesWithoutDocs;
    }

    /**
     * Get list of tasks without documentation
     *
     * @return array<int, string>
     */
    protected function getTasksWithoutDocumentation(string $bpmnXml): array
    {
        return $this->getElementsWithoutDocumentation($bpmnXml, 'task');
    }

    /**
     * Get list of events without documentation
     *
     * @return array<int, string>
     */
    protected function getEventsWithoutDocumentation(string $bpmnXml): array
    {
        $elements = [];
        $elements = array_merge($elements, $this->getElementsWithoutDocumentation($bpmnXml, 'startEvent'));
        $elements = array_merge($elements, $this->getElementsWithoutDocumentation($bpmnXml, 'endEvent'));
        $elements = array_merge($elements, $this->getElementsWithoutDocumentation($bpmnXml, 'intermediateThrowEvent'));
        $elements = array_merge($elements, $this->getElementsWithoutDocumentation($bpmnXml, 'intermediateCatchEvent'));

        return $elements;
    }

    /**
     * Get list of gateways without documentation
     *
     * @return array<int, string>
     */
    protected function getGatewaysWithoutDocumentation(string $bpmnXml): array
    {
        $elements = [];
        $elements = array_merge($elements, $this->getElementsWithoutDocumentation($bpmnXml, 'exclusiveGateway'));
        $elements = array_merge($elements, $this->getElementsWithoutDocumentation($bpmnXml, 'parallelGateway'));
        $elements = array_merge($elements, $this->getElementsWithoutDocumentation($bpmnXml, 'inclusiveGateway'));
        $elements = array_merge($elements, $this->getElementsWithoutDocumentation($bpmnXml, 'eventBasedGateway'));

        return $elements;
    }

    /**
     * Get list of specific BPMN elements without documentation
     *
     * @return array<int, string>
     */
    protected function getElementsWithoutDocumentation(string $bpmnXml, string $elementType): array
    {
        $elementsWithoutDocs = [];

        try {
            $xml = simplexml_load_string($bpmnXml);
            if ($xml === false) {
                return [];
            }

            // Register namespaces
            $xml->registerXPathNamespace('bpmn', 'http://www.omg.org/spec/BPMN/20100524/MODEL');

            // Find all elements of this type
            $elements = $xml->xpath("//bpmn:{$elementType} | //{$elementType}");

            if ($elements) {
                foreach ($elements as $element) {
                    $elementId = (string) ($element['id'] ?? 'unknown');
                    $elementName = (string) ($element['name'] ?? $elementId);

                    // Check if element has documentation
                    $docs = $element->xpath('.//bpmn:documentation | .//documentation');
                    if (empty($docs) || empty(trim((string) $docs[0]))) {
                        $elementsWithoutDocs[] = "{$elementType}:{$elementName}";
                    }
                }
            }
        } catch (Exception $e) {
            // If parsing fails, return empty array
        }

        return $elementsWithoutDocs;
    }
}
