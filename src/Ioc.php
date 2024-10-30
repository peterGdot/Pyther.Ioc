<?php
namespace Pyther\Ioc;

use Pyther\Ioc\Exceptions\ResolveException;

/**
 * The "Inversion of Control" container.
 */
class Ioc
{
    #region statics

    /**
     * The default Ioc container.
     *
     * @var Ioc
     */
    public static Ioc $default;

    /**
     * Add a new implementation to the container which can resolve to multiple instances. 
     *
     * @param string $name The name of the binding.
     * @param string|callable|null $implementation Reference to the implementation or a construct method.
     * @param array $args Optional constructor arguments.
     * @return static Returns the container itself.
     */
    public static function bindMultiple(string $name, string|callable|null $implementation, array $args = []): static
    {
        return static::$default->addMultiple($name, $implementation, $args);
    }

    /**
     * Add a new implementation to the container which act like a singleton. 
     *
     * @param string $name The name of the binding.
     * @param string|callable|null $implementation Reference to the implementation or a construct method.
     * @param array $args Optional constructor arguments.
     * @return static Returns the container itself.
     */
    public static function bindSingleton(string $name, string|callable|null|object $implementation, array $args = []): static
    {
        return static::$default->addSingleton($name, $implementation, $args);
    }

    /**
     * Resolve the binding.
     *
     * @param string $name The name of the binding.
     * @param array|null $args Optional constructor arguments for "multiple" instances.
     * @return object|null
     */
    public static function get(string $name, ?array $args = null): ?object
    {
        return static::$default->resolve($name, $args);    
    }

    /**
     * Check if a binding exists.
     *
     * @param string $name The name of the binding.
     * @return boolean
     */
    public static function has(string $name): bool
    {
        return isset(static::$default->bindings[$name]);    
    }

    #endregion

    private array $bindings = [];

    /**
     * Add a new implementation to the container which can resolve to multiple instances. 
     *
     * @param string $name The name of the binding.
     * @param string|callable|null $implementation Reference to the implementation or a construct method.
     * @param array $args Optional constructor arguments.
     * @return static Returns the container itself.
     */
    public function addMultiple(string $name, string|callable|null $implementation, array $args = []): static
    {
        $this->bindings[$name] = new Binding($this, $name, BindingType::Multiple, $implementation, $args);
        return $this;
    }

    /**
     * Add a new implementation to the container which act like a singleton. 
     *
     * @param string $name The name of the binding.
     * @param string|callable|null $implementation Reference to the implementation or a construct method.
     * @param array $args Optional constructor arguments.
     * @return static Returns the container itself.
     */
    public function addSingleton(string $name, string|callable|null|object $implementation, array $args = []): static
    {
        $this->bindings[$name] = new Binding($this, $name, BindingType::Singleton, $implementation, $args);
        return $this;
    }

    /**
     * Check if a binding exists.
     *
     * @param string $name The name of the binding.
     * @return boolean
     */
    public function hasBinding(string $name)
    {
        return isset($this->bindings[$name]);
    }

    /**
     * Clear all bindings.
     *
     * @return void
     */
    public function clear()
    {
        $this->bindings = [];
    }

    /**
     * Resolve the binding.
     *
     * @param string $name The name of the binding.
     * @param array|null $args Optional constructor arguments for "multiple" instances.
     * @return object|null
     */  
    public function resolve(string $name, ?array $args = null): ?object
    {
        try {
            if (!isset($this->bindings[$name])) {
                throw new ResolveException("Binding \"{$name}\" not found!");
            }
            $binding = $this->bindings[$name];
            if ($args !== null && $binding->type === BindingType::Singleton) {
                throw new ResolveException("Ioc get only allow the second argument for multiple bindings!");
            }
            return $binding->resolve($args);
        } catch (\Exception $ex) {
            throw new ResolveException("Ioc: Can't resolve '$name' (".$ex->getMessage().")");
        }
    }
}

Ioc::$default = new Ioc();