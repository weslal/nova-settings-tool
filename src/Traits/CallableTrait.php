<?php

namespace WesLal\NovaSettingsTool\Traits;

use Closure;
use Illuminate\Routing\RouteDependencyResolverTrait;
use ReflectionFunction;
use ReflectionException;

/**
 * Trait CallableTrait
 * @package WesLal\NovaSettingsTool\Traits
 */
trait CallableTrait
{
    use RouteDependencyResolverTrait;

    /**
     * Perform a callback.
     * @param Closure|null $callback
     * @param null $caller
     * @return null
     * @throws ReflectionException
     */
    public function call(Closure $callback = null, $caller = null)
    {
        if ($callback instanceof Closure) {
            $parameters = $this->resolveMethodDependencies(
                [$caller], new ReflectionFunction($callback)
            );
            call_user_func_array($callback, $parameters);
        }

        return $caller;
    }
}