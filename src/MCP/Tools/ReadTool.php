<?php

namespace Opscale\NovaMCP\MCP\Tools;

use Exception;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Opscale\NovaMCP\MCP\Concerns\ResolvesModel;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\Exceptions\InvalidFilterQuery;
use Spatie\QueryBuilder\Exceptions\InvalidIncludeQuery;
use Spatie\QueryBuilder\Exceptions\InvalidSortQuery;
use Spatie\QueryBuilder\QueryBuilder;

class ReadTool extends Tool
{
    use ResolvesModel;

    protected string $name = 'read-resource';

    protected string $title = 'Read Resource';

    protected string $description = 'Search and view your items with flexible filtering, sorting, and relationship options. Uses JSON:API conventions for querying. Specify what you want to find and how you want to see it.';

    /**
     * Define the input schema for the tool.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'resource' => $schema->string()
                ->description('The type of items you want to view (e.g., "users", "posts", "orders")')
                ->required(),

            'filter' => $schema->object()
                ->description('Narrow down your results by specific criteria (JSON:API filter format). Example: {"email": "john@example.com", "status": "active"}')
                ->default([]),

            'sort' => $schema->string()
                ->description('Order your results by specific fields (JSON:API sort format). Use "-" before a field name for newest-first. Example: "-created_at,name"')
                ->default(''),

            'include' => $schema->string()
                ->description('Include related information in your results (JSON:API include format). Example: "posts,posts.comments,profile"')
                ->default(''),

            'fields' => $schema->object()
                ->description('Choose which specific fields to show (JSON:API sparse fieldsets). Example: {"users": "id,name,email"}')
                ->default([]),

            'page' => $schema->object([
                'number' => $schema->integer()
                    ->min(1)
                    ->default(1)
                    ->description('Which page of results to show (JSON:API page[number])'),
                'size' => $schema->integer()
                    ->min(1)
                    ->max(100)
                    ->default(15)
                    ->description('How many items to show per page (JSON:API page[size], maximum 100)'),
            ])
                ->description('Split your results into pages for easier viewing (JSON:API pagination)')
                ->default(['number' => 1, 'size' => 15]),

            'append' => $schema->string()
                ->description('Add computed information to your results. Example: "full_name,avatar_url"')
                ->default(''),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'resource' => 'required|string',
                'filter' => 'array',
                'sort' => 'string',
                'include' => 'string',
                'fields' => 'array',
                'page' => 'array',
                'page.number' => 'integer|min:1',
                'page.size' => 'integer|min:1|max:100',
                'append' => 'string',
            ]);

            // Get the model class from the resource name
            $resource = $validated['resource'];
            $modelClass = $this->resolveModelClass($resource);

            if (! $modelClass) {
                $availableResources = $this->getAvailableResources();
                $suggestion = ! empty($availableResources)
                    ? ' Available collections: ' . implode(', ', $availableResources)
                    : ' No collections are currently configured.';

                return Response::error("The collection '{$resource}' you're trying to access doesn't exist in the system." . $suggestion);
            }

            if (! is_subclass_of($modelClass, Model::class)) {
                return Response::error("The collection '{$resource}' is not properly configured for data retrieval.");
            }

            // Create a mock HTTP request with JSON:API query parameters
            $httpRequest = $this->createHttpRequest($validated, $resource);

            // Build the query using QueryBuilder with JSON:API conventions
            $query = QueryBuilder::for($modelClass, $httpRequest)
                ->allowedFilters($this->getAllowedFilters($modelClass, $validated['filter'] ?? []))
                ->allowedSorts($this->getAllowedSorts($modelClass, $validated['sort'] ?? ''))
                ->allowedFields($this->getAllowedFields($validated['fields'] ?? []))
                ->allowedIncludes($this->getAllowedIncludes($modelClass, $validated['include'] ?? ''));

            // Apply JSON:API pagination
            $pageNumber = $validated['page']['number'] ?? 1;
            $pageSize = $validated['page']['size'] ?? 15;
            $results = $query->paginate($pageSize, ['*'], 'page[number]', $pageNumber);

            // Handle appends manually on the collection
            $appendFields = $this->parseCommaSeparated($validated['append'] ?? '');
            if (! empty($appendFields)) {
                $results->getCollection()->each(function ($item) use ($appendFields) {
                    $item->append($appendFields);
                });
            }

            // Return JSON:API compliant response structure
            return Response::json([
                'success' => true,
                'data' => $results->items(),
                'metadata' => [
                    'current_page' => $results->currentPage(),
                    'per_page' => $results->perPage(),
                    'total' => $results->total(),
                    'last_page' => $results->lastPage(),
                    'from' => $results->firstItem(),
                    'to' => $results->lastItem(),
                ],
                'links' => [
                    'first' => $this->buildQueryString($validated, ['page' => ['number' => 1, 'size' => $pageSize]]),
                    'last' => $this->buildQueryString($validated, ['page' => ['number' => $results->lastPage(), 'size' => $pageSize]]),
                    'prev' => $results->previousPageUrl(),
                    'next' => $results->nextPageUrl(),
                ],
            ]);
        } catch (ValidationException $e) {
            return Response::error('The search criteria provided is invalid: ' . json_encode($e->errors()));
        } catch (InvalidFilterQuery $e) {
            return Response::error('The filter you specified cannot be applied (JSON:API filter format required): ' . $e->getMessage());
        } catch (InvalidSortQuery $e) {
            return Response::error('The sorting option you chose is not available (JSON:API sort format required): ' . $e->getMessage());
        } catch (InvalidIncludeQuery $e) {
            return Response::error('The related information you requested is not available (JSON:API include format required): ' . $e->getMessage());
        } catch (Exception $e) {
            return Response::error('Unable to retrieve your items: ' . $e->getMessage());
        }
    }

    /**
     * Create a mock HTTP request with JSON:API query parameters for QueryBuilder.
     */
    protected function createHttpRequest(array $validated, string $resource): HttpRequest
    {
        $query = [];

        // Add filters
        if (! empty($validated['filter'])) {
            $query['filter'] = $validated['filter'];
        }

        // Add sorts
        if (! empty($validated['sort'])) {
            $query['sort'] = $validated['sort'];
        }

        // Add includes
        if (! empty($validated['include'])) {
            $query['include'] = $validated['include'];
        }

        // Add fields
        if (! empty($validated['fields'])) {
            $query['fields'] = $validated['fields'];
        }

        // Add page
        if (! empty($validated['page'])) {
            $query['page'] = $validated['page'];
        }

        // Add appends
        if (! empty($validated['append'])) {
            $query['append'] = $validated['append'];
        }

        // Create a new HTTP request with the query parameters
        $httpRequest = HttpRequest::create('/' . $resource, 'GET', $query);

        return $httpRequest;
    }

    /**
     * Get allowed filters based on the model and requested filters.
     */
    protected function getAllowedFilters(string $modelClass, array $filters): array
    {
        $allowedFilters = [];

        foreach (array_keys($filters) as $field) {
            // Support exact, partial, and scope filters
            // Check if model has a scope for this filter
            $scopeMethod = 'scope' . Str::studly($field);

            if (method_exists($modelClass, $scopeMethod)) {
                $allowedFilters[] = AllowedFilter::scope($field);
            } else {
                // Default to exact match, but also allow partial searches for string fields
                $allowedFilters[] = AllowedFilter::exact($field);
                $allowedFilters[] = AllowedFilter::partial($field);
            }
        }

        return $allowedFilters;
    }

    /**
     * Get allowed sorts based on the model.
     */
    protected function getAllowedSorts(string $modelClass, string $sort): array
    {
        if (empty($sort)) {
            return [];
        }

        $sortFields = $this->parseCommaSeparated($sort);
        $allowedSorts = [];

        foreach ($sortFields as $field) {
            // Remove leading "-" if present
            $fieldName = ltrim($field, '-');

            // Check if the model has a custom sort scope
            $scopeMethod = 'scopeSortBy' . Str::studly($fieldName);

            if (method_exists($modelClass, $scopeMethod)) {
                $allowedSorts[] = AllowedSort::custom($fieldName, 'sortBy' . Str::studly($fieldName));
            } else {
                $allowedSorts[] = $fieldName;
            }
        }

        return $allowedSorts;
    }

    /**
     * Get allowed includes based on the model.
     */
    protected function getAllowedIncludes(string $modelClass, string $include): array
    {
        if (empty($include)) {
            return [];
        }

        $includes = $this->parseCommaSeparated($include);
        $allowedIncludes = [];
        $model = new $modelClass;

        foreach ($includes as $relationship) {
            // For nested relationships, check the first level
            $firstLevel = Str::before($relationship, '.');

            if (method_exists($model, $firstLevel)) {
                $allowedIncludes[] = AllowedInclude::relationship($relationship);
            }
        }

        return $allowedIncludes;
    }

    /**
     * Get allowed fields.
     */
    protected function getAllowedFields(array $fields): array
    {
        $allowedFields = [];

        foreach ($fields as $resource => $fieldList) {
            $allowedFields[$resource] = $this->parseCommaSeparated($fieldList);
        }

        return $allowedFields;
    }

    /**
     * Parse comma-separated string into array.
     */
    protected function parseCommaSeparated(string $value): array
    {
        if (empty($value)) {
            return [];
        }

        return array_map('trim', explode(',', $value));
    }

    /**
     * Build a query string for pagination links.
     */
    protected function buildQueryString(array $params, array $override = []): string
    {
        $merged = array_merge($params, $override);
        $query = http_build_query($merged);

        return '/' . $merged['resource'] . '?' . $query;
    }
}
