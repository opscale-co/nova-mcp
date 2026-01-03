<?php

namespace Opscale\NovaMCP\MCP\Tools;

use Exception;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;
use Opscale\NovaMCP\MCP\Concerns\ResolvesModel;

#[IsDestructive]
class DeleteTool extends Tool
{
    use ResolvesModel;
    protected string $description = 'Remove an item from your collection. This action cannot be undone unless the item is archived (soft delete).';

    /**
     * Define the input schema for the tool.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'resource' => $schema->string()
                ->description('The type of item you want to remove (e.g., "users", "posts", "orders")')
                ->required(),

            'id' => $schema->string()
                ->description('The unique identifier of the item you want to remove')
                ->required(),

            'force' => $schema->boolean()
                ->description('If true, permanently delete the item even if it was previously archived. Use with caution.')
                ->default(false),
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
                'id' => 'required',
                'force' => 'boolean',
            ]);

            // Get the model class from the resource name
            $resource = $validated['resource'];
            $modelClass = $this->resolveModelClass($resource);

            if (! $modelClass) {
                $availableResources = $this->getAvailableResources();
                $suggestion = ! empty($availableResources)
                    ? ' Available collections: ' . implode(', ', $availableResources)
                    : ' No collections are currently configured.';

                return Response::error("The collection '{$resource}' you're trying to remove from doesn't exist in the system." . $suggestion);
            }

            if (! is_subclass_of($modelClass, Model::class)) {
                return Response::error("The collection '{$resource}' is not properly configured for data storage.");
            }

            // Find the existing model
            try {
                $model = $modelClass::findOrFail($validated['id']);
            } catch (ModelNotFoundException $e) {
                return Response::error("The item with ID '{$validated['id']}' could not be found in '{$resource}'.");
            }

            // Store the data before deletion for the response
            $deletedData = $model->toArray();
            $deletedId = $model->getKey();

            // Perform the deletion
            try {
                $force = $validated['force'] ?? false;

                if ($force && method_exists($model, 'forceDelete')) {
                    // Force delete (for soft-deleted models)
                    $deleted = $model->forceDelete();
                    $deleteType = 'force_deleted';
                } elseif (method_exists($model, 'delete')) {
                    // Regular delete (may be soft delete if model uses SoftDeletes)
                    $deleted = $model->delete();
                    $deleteType = $this->usesSoftDeletes($modelClass) ? 'soft_deleted' : 'deleted';
                } else {
                    return Response::error('This item cannot be removed at this time.');
                }

                if (! $deleted) {
                    return Response::error('Unable to remove this item. The operation was not successful.');
                }

                return Response::json([
                    'success' => true,
                    'message' => 'Your item has been successfully removed',
                    'data' => $deletedData,
                    'metadata' => [
                        'resource' => $resource,
                        'id' => $deletedId,
                        'deleted_at' => now()->toIso8601String(),
                        'delete_type' => $deleteType,
                        'permanently_deleted' => in_array($deleteType, ['force_deleted', 'deleted']),
                    ],
                ]);
            } catch (QueryException $e) {
                // Handle foreign key constraint violations and other database errors
                if ($this->isForeignKeyConstraintError($e)) {
                    return Response::error(
                        "Cannot remove the item with ID '{$validated['id']}' from '{$resource}' because it's connected to other items in your system. " .
                        'Please remove the related items first.'
                    );
                }

                return Response::error('A system error occurred while removing this item: ' . $e->getMessage());
            }
        } catch (ValidationException $e) {
            return Response::error('The deletion request is invalid: ' . json_encode($e->errors()));
        } catch (Exception $e) {
            return Response::error('Unable to remove your item: ' . $e->getMessage());
        }
    }

    /**
     * Check if the model uses soft deletes.
     */
    protected function usesSoftDeletes(string $modelClass): bool
    {
        $traits = class_uses_recursive($modelClass);

        return in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, $traits);
    }

    /**
     * Check if the query exception is a foreign key constraint error.
     */
    protected function isForeignKeyConstraintError(QueryException $e): bool
    {
        // Check for common foreign key constraint error codes
        $errorCode = $e->getCode();
        $errorMessage = $e->getMessage();

        // MySQL/MariaDB foreign key constraint error
        if ($errorCode === '23000' || str_contains($errorMessage, 'foreign key constraint')) {
            return true;
        }

        // PostgreSQL foreign key constraint error
        if ($errorCode === '23503' || str_contains($errorMessage, 'violates foreign key constraint')) {
            return true;
        }

        // SQLite foreign key constraint error
        if (str_contains($errorMessage, 'FOREIGN KEY constraint failed')) {
            return true;
        }

        return false;
    }
}
