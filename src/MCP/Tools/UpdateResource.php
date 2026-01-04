<?php

namespace Opscale\NovaMCP\MCP\Tools;

use Enigma\ValidatorTrait;
use Exception;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Opscale\NovaMCP\MCP\Concerns\ResolvesModel;

class UpdateResource extends Tool
{
    use ResolvesModel;
    protected string $description = 'Modify an existing item in your collection. Your changes will be automatically checked for correctness before saving.';

    /**
     * Define the input schema for the tool.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'resource' => $schema->string()
                ->description('The type of item you want to modify (e.g., "users", "posts", "orders")')
                ->required(),

            'id' => $schema->string()
                ->description('The unique identifier of the item you want to update')
                ->required(),

            'payload' => $schema->object()
                ->description('The new information for this item. Only include the fields you want to change. Example: {"name": "Jane Doe", "email": "jane@example.com"}')
                ->required(),
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
                'payload' => 'required|array',
            ]);

            // Get the model class from the resource name
            $resource = $validated['resource'];
            $modelClass = $this->resolveModelClass($resource);

            if (! $modelClass) {
                $availableResources = $this->getAvailableResources();
                $suggestion = ! empty($availableResources)
                    ? ' Available collections: ' . implode(', ', $availableResources)
                    : ' No collections are currently configured.';

                return Response::error("The collection '{$resource}' you're trying to modify doesn't exist in the system." . $suggestion);
            }

            if (! is_subclass_of($modelClass, Model::class)) {
                return Response::error("The collection '{$resource}' is not properly configured for data storage.");
            }

            // Check if the model uses ValidatorTrait
            if (! $this->usesValidatorTrait($modelClass)) {
                return Response::error("The collection '{$resource}' doesn't support automatic data validation. Please contact your system administrator.");
            }

            // Find the existing model
            try {
                $model = $modelClass::findOrFail($validated['id']);
            } catch (ModelNotFoundException $e) {
                return Response::error("The item with ID '{$validated['id']}' could not be found in '{$resource}'.");
            }

            // Store the original data for comparison
            $originalData = $model->toArray();

            // Fill the model with new payload data
            $model->fill($validated['payload']);

            // Validate the model using ValidatorTrait
            try {
                $model->validate();
            } catch (ValidationException $e) {
                return Response::error('Some of the changes you made are invalid: ' . json_encode($e->errors()));
            }

            // Save the model
            $model->save();

            // Refresh to get any database defaults or computed values
            $model->refresh();

            // Determine which fields were changed
            $changedFields = array_keys(array_diff_assoc($model->toArray(), $originalData));

            return Response::json([
                'success' => true,
                'message' => 'Your item has been successfully updated',
                'data' => $model->toArray(),
                'metadata' => [
                    'resource' => $resource,
                    'id' => $model->getKey(),
                    'updated_at' => $model->updated_at?->toIso8601String(),
                    'changed_fields' => $changedFields,
                ],
            ]);
        } catch (ValidationException $e) {
            return Response::error('The changes provided are incomplete or invalid: ' . json_encode($e->errors()));
        } catch (Exception $e) {
            return Response::error('Unable to update your item: ' . $e->getMessage());
        }
    }

    /**
     * Check if the model uses the ValidatorTrait.
     */
    protected function usesValidatorTrait(string $modelClass): bool
    {
        $traits = class_uses_recursive($modelClass);

        return in_array(ValidatorTrait::class, $traits);
    }
}
