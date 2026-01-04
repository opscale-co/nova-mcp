<?php

namespace Opscale\NovaMCP\MCP\Resources;

use Butschster\Dbml\DbmlParserFactory;
use Exception;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;
use Opscale\NovaMCP\Contracts\DomainResolver;

/**
 * Domain Resource - Provides the domain schema for CRUD operations.
 *
 * This resource exposes the business domain model in DBML format, enabling
 * understanding of:
 * - Entity relationships and dependencies (which records require other records)
 * - Required fields and data types for creating/updating records
 * - Business context through notes explaining the purpose of each entity and field
 *
 * The schema is essential for performing accurate CRUD operations as it defines
 * what information is needed when creating or modifying records.
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
    public string $description = 'Domain schema for CRUD operations - defines entities, their relationships, required fields, and what information is needed to manage records';

    /**
     * The resource MIME type
     */
    public string $mimeType = 'text/plain';

    /**
     * Domain resolver instance for providing DBML
     */
    protected DomainResolver $domainResolver;

    /**
     * Constructor
     */
    public function __construct(DomainResolver $domainResolver)
    {
        $this->domainResolver = $domainResolver;
    }

    /**
     * Handle the resource request
     */
    public function handle(): Response
    {
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
