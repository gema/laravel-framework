<?php

namespace GemaDigital\Http\Requests\API\Traits;

use Closure;
use Illuminate\Support\Facades\Gate;
use ReflectionFunction;

trait GateCheckTrait
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $route = $this->route();
        $uses = $route->action['uses'];

        // String apis
        if (is_string($uses)) {
            [$controller, $method] = explode('@', $uses);
            $parameters = $route->parameters;
        }

        // Closure apis
        if ($uses instanceof Closure) {
            $reflectionFunction = new ReflectionFunction($uses);

            $method = $reflectionFunction->getName();
            $controller = $reflectionFunction->getClosureScopeClass()?->getName();
            $parameters = $reflectionFunction->getParameters();
        }

        return Gate::check($method ?? '', [$controller ?? null, ...array_values($parameters ?? [])]);
    }
}
