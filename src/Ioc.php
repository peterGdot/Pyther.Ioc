<?php
namespace Pyther\Ioc;

use Exception;

/**
    Syntax:
    a = name | interface name | class name
    b = class name | callable | null
    c = class name | callable | null | object

    // "default" container
    IoC::$default->add(a, b, ... ctor args)
    IoC::$default->addSingleton(a, c, ... ctor args)
    IoC::$default->resolve(a) -> object | null

    IoC::bind(a, b, ... ctor args) -> Ioc
    IoC::bindSingleton(a, c, ... ctor args) -> Ioc
    IoC::get(a) -> object | null

    Ioc::auto()
*/

class Ioc {

    #region statics

    /**
     * The default Ioc container.
     *
     * @var Ioc
     */
    public static Ioc $default;

    public static function bindMultiple(string $name, string|callable|null $implementation, array $args = []): static {
        return static::$default->addMultiple($name, $implementation, $args);
    }

    public static function bindSingleton(string $name, string|callable|null|object $implementation, array $args = []): static {
        return static::$default->addSingleton($name, $implementation, $args);
    }

    public static function get(string $name): ?object {
        return static::$default->resolve($name);    
    }    

    #endregion

    private array $bindings = [];

    public function addMultiple(string $name, string|callable|null $implementation, array $args = []): static {
        $this->bindings[$name] = new Binding($this, $name, BindingType::Multiple, $implementation, $args);
        return $this;
    }

    public function addSingleton(string $name, string|callable|null|object $implementation, array $args = []): static {
        $this->bindings[$name] = new Binding($this, $name, BindingType::Singleton, $implementation, $args);
        return $this;
    }

    public function hasBinding(string $name) {
        return isset($this->bindings[$name]);
    }

    public function clear() {
        $this->bindings = [];
    }

    public function resolve(string $name): ?object {
        try {
            if (!isset($this->bindings[$name])) {
                throw new Exception("Binding \"{$name}\" not found!");
            }
            $binding = $this->bindings[$name];
            return $binding->resolve();
        } catch (\Exception $ex) {
            throw new \Exception("Ioc: Can't resolve '$name' (".$ex->getMessage().")");
        }
    }
    
}

Ioc::$default = new Ioc();