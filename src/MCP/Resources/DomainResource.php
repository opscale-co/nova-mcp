<?php

namespace Opscale\NovaMCP\MCP\Resources;

use Butschster\Dbml\DbmlParserFactory;
use Exception;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;
use Opscale\NovaMCP\Contracts\DomainResolver;

/**
 * Domain Resource - Provides DBML from DomainResolver with validation
 *
 * This resource uses a DomainResolver implementation to get the complete
 * DBML schema with business descriptions, and validates it to ensure
 * quality and completeness of the documentation.
 */
class DomainResource extends Resource
{
    /**
     * The resource URI
     */
    public string $uri = 'domain://dbml';

    /**
     * The resource name
     */
    public string $name = 'Domain DBML';

    /**
     * The resource description
     */
    public string $description = 'Entity Relationship Diagram of the application domain in DBML format';

    /**
     * The resource MIME type
     */
    public string $mimeType = 'text/plain';

    /**
     * Domain resolver instance for providing DBML
     */
    protected ?DomainResolver $domainResolver = null;

    /**
     * Constructor
     */
    public function __construct(?DomainResolver $domainResolver = null)
    {
        $this->domainResolver = $domainResolver;
    }

    /**
     * Handle the resource request
     */
    public function handle(): Response
    {
        if (! $this->domainResolver) {
            return Response::error('DomainResolver is required but not configured. Please bind an implementation of DomainResolver in your service provider.');
        }

        try {
            $dbml = $this->domainResolver->resolve();

            // Validate the DBML
            $validationErrors = $this->validateDbml($dbml);

            if (! empty($validationErrors)) {
                return Response::error('DBML validation failed: ' . implode('; ', $validationErrors));
            }

            return Response::text($dbml);
        } catch (Exception $e) {
            return Response::error("Error resolving DBML: {$e->getMessage()}");
        }
    }

    /**
     * Validate DBML for completeness and required notes
     *
     * @return array<int, string> Array of validation error messages
     */
    protected function validateDbml(string $dbml): array
    {
        $errors = [];

        // Check if DBML is empty or too short
        if (empty(trim($dbml))) {
            $errors[] = 'DBML is empty';

            return $errors;
        }

        if (strlen($dbml) < 50) {
            $errors[] = 'DBML appears incomplete (less than 50 characters)';
        }

        try {
            // Parse DBML using the parser
            $parser = DbmlParserFactory::create();
            $schema = $parser->parse($dbml);

            if (! $schema) {
                $errors[] = 'Failed to parse DBML';

                return $errors;
            }

            // Validate project notes
            $project = $schema->getProject();
            if (! $project || empty($project->getNote())) {
                $errors[] = 'DBML is missing project-level notes';
            }

            // Validate table notes
            $tablesWithoutNotes = [];
            foreach ($schema->getTables() as $table) {
                if (empty($table->getNote())) {
                    $tablesWithoutNotes[] = $table->getName();
                }
            }

            if (! empty($tablesWithoutNotes)) {
                $errors[] = 'Tables missing notes: ' . implode(', ', $tablesWithoutNotes);
            }

            // Validate column notes
            $columnsWithoutNotes = [];
            foreach ($schema->getTables() as $table) {
                foreach ($table->getColumns() as $column) {
                    if (empty($column->getNote())) {
                        $columnsWithoutNotes[] = $table->getName() . '.' . $column->getName();
                    }
                }
            }

            if (! empty($columnsWithoutNotes)) {
                $count = count($columnsWithoutNotes);
                $sample = array_slice($columnsWithoutNotes, 0, 5);
                $errors[] = "Columns missing notes ({$count} total, showing first 5): " . implode(', ', $sample);
            }
        } catch (Exception $e) {
            $errors[] = 'DBML parse error: ' . $e->getMessage();
        }

        return $errors;
    }
}
