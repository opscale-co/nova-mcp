<?php

namespace Opscale\NovaMCP\MCP\Concerns;

use Exception;
use Illuminate\Support\Facades\Config;

trait ResolvesModel
{
    /**
     * Resolve the model class from a resource URI key using Nova resources.
     *
     * @param  string  $resourceUri  The URI key to match (e.g., "users", "posts")
     * @return string|null The model class name or null if not found
     */
    protected function resolveModelClass(string $resourceUri): ?string
    {
        $resources = Config::get('nova-mcp.resources', []);

        foreach ($resources as $resourceClass) {
            if (! class_exists($resourceClass)) {
                continue;
            }

            // Get the URI key from the Nova resource
            $uriKey = $resourceClass::uriKey();

            // Check if it matches the provided resource URI
            if ($uriKey === $resourceUri) {
                // Return the model class from the Nova resource
                return $this->getModelFromResource($resourceClass);
            }
        }

        return null;
    }

    /**
     * Get the model class from a Nova resource.
     *
     * @param  string  $resourceClass  The Nova resource class
     * @return string|null The model class name
     */
    protected function getModelFromResource(string $resourceClass): ?string
    {
        // Try to get the model from the static $model property
        if (property_exists($resourceClass, 'model')) {
            return $resourceClass::$model;
        }

        // Try to instantiate and call the model() method
        try {
            $resource = new $resourceClass(null);
            if (method_exists($resource, 'model')) {
                return $resource->model();
            }
        } catch (Exception $e) {
            // If instantiation fails, continue
        }

        return null;
    }

    /**
     * Get all available resource URIs from the config.
     *
     * @return array Array of available resource URI keys
     */
    protected function getAvailableResources(): array
    {
        $resources = Config::get('nova-mcp.resources', []);
        $availableUris = [];

        foreach ($resources as $resourceClass) {
            if (class_exists($resourceClass)) {
                try {
                    $availableUris[] = $resourceClass::uriKey();
                } catch (Exception $e) {
                    // Skip if we can't get the URI key
                    continue;
                }
            }
        }

        return $availableUris;
    }
}
