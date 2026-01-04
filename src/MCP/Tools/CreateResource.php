<?php

namespace Opscale\NovaMCP\MCP\Tools;

use Enigma\ValidatorTrait;
use Exception;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Opscale\NovaMCP\MCP\Concerns\ResolvesModel;

class CreateResource extends Tool
{
    use ResolvesModel;
    protected string $description = 'Add a new item to your collection. Your data will be automatically checked for correctness before saving.';

    /**
     * Define the input schema for the tool.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'resource' => $schema->string()
                ->description('The type of item you want to add (e.g., "users", "posts", "orders")')
                ->required(),

            'payload' => $schema->object()
                ->description('The information for your new item. Each field should have its corresponding value. Example: {"name": "John Doe", "email": "john@example.com"}')
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

                return Response::error("The collection '{$resource}' you're trying to add to doesn't exist in the system." . $suggestion);
            }

            if (! is_subclass_of($modelClass, Model::class)) {
                return Response::error("The collection '{$resource}' is not properly configured for data storage.");
            }

            // Check if the model uses ValidatorTrait
            if (! $this->usesValidatorTrait($modelClass)) {
                return Response::error("The collection '{$resource}' doesn't support automatic data validation. Please contact your system administrator.");
            }

            // Create a new model instance and fill with payload
            $model = new $modelClass;
            $model->fill($validated['payload']);

            // Validate the model using ValidatorTrait
            try {
                $model->validate();
            } catch (ValidationException $e) {
                return Response::error('Some required information is missing or incorrect: ' . json_encode($e->errors()));
            }

            // Save the model
            $model->save();

            // Refresh to get any database defaults or computed values
            $model->refresh();

            return Response::json([
                'success' => true,
                'message' => 'Your item has been successfully added',
                'data' => $model->toArray(),
                'metadata' => [
                    'resource' => $resource,
                    'id' => $model->getKey(),
                    'created_at' => $model->created_at?->toIso8601String(),
                ],
            ]);
        } catch (ValidationException $e) {
            return Response::error('The information provided is incomplete or invalid: ' . json_encode($e->errors()));
        } catch (Exception $e) {
            return Response::error('Unable to add your item: ' . $e->getMessage());
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
