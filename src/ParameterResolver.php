<?php
namespace Pyther\Ioc;


abstract class ParameterResolver {

    /**
     * Resolve method or function parameters and handle dependencies.
     *
     * @param Ioc $ioc The Ioc containter
     * @param array $parameters List of methods parameters coming from reflection "getParameters".
     * @param array $overrides Optional asocitative list of parameter values index by parameter name.
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
                // parameter, that can be resolved?
                if ($type != null && $ioc->hasBinding($type)) {
                    $resolved[] = $ioc->resolve($type);
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