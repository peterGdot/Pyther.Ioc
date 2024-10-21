<?php
namespace Pyther\Ioc;

abstract class ParameterResolver
{
    /**
     * Resolve method or function parameters and handle dependencies.
     *
     * @param Ioc $ioc The IoC container used to resolve dependencies.
     * @param array $parameters List of ctor/method parameter coming from reflection "getParameters".
     * @param array $overrides Optional associative list of parameters override values indexd by parameter name.
     * @return array Return the final array of parameter values.
     */
    public static function resolve(Ioc $ioc, array $parameters, array $overrides = []): array
    {
        $resolved = [];
        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();
            
            // override parameter value?
            if (isset($overrides[$name])) {
                $resolved[] = $overrides[$name];
            } else {
                $iocType = $type != null ? ltrim($type, "?") : null;
                // parameter, that can be resolved?
                if ($iocType != null && $ioc->hasBinding($iocType)) {
                    $resolved[] = $ioc->resolve($iocType);
                }
                // parameter has default value?
                else if ($parameter->isDefaultValueAvailable()) {
                    $resolved[] = $parameter->getDefaultValue();
                } else {
                    throw new \Exception("No parameter value for '$name' given!");
                }
            }
        }
        return $resolved;
    }
}