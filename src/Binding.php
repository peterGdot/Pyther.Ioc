<?php
namespace Pyther\Ioc;

use Exception;

enum BindingType: int
{
    case Multiple = 1;
    case Singleton = 2;
}

/**
 * Class of a single Ioc Binding.
 */
class Binding {
    /**
     * The IoC conatiner this binding belongs to.
     * @var Ioc
     */
    private Ioc $ioc;

    /**
     * The binding name.
     * @var string
     */
    private string $name; 

    /**
     * The binding type (Mutliple or Singleton).
     * @var BindingType
     */
    private BindingType $type;

    /**
     * The bound implementation (class name, callable, object or null).
     * @var string|callable|object|null
     */
    private $implementation;

    /**
     * Optional associative array of parameter values.
     * @var array|null
     */
    private array $overrides = [];

    /**
     * The finally created object.
     * @var object|null
     */
    private ?object $object = null;

    /**
     * Flag that defines the resolving state (used for cylcle detection)
     * @var boolean
     */
    private bool $isResolving = false;

    /**
     * Option to fire an exception on failure.
     * @var boolean
     */
    private bool $fireExceptions = true;

    /**
     * Create a new binding instance.
     * @param Ioc $ioc The IoC conatiner this binding belongs to.
     * @param string $name The binding name.
     * @param BindingType $type The binding type (Mutliple or Singleton).
     * @param string|callable|object|null $implementation The bind implementation (class name, callable, object or null).
     * @param array $overrides Optional associative array of parameter values.
     */
    function __construct(Ioc $ioc, string $name, BindingType $type, string|callable|object|null $implementation, $overrides = []) {
        $this->ioc = $ioc;
        $this->name = $name;
        $this->type = $type;
        $this->implementation = $implementation;
        $this->overrides = $overrides;
    }

    /**
     * Enable or Disable firing exceptions.
     */
    public function setFireExceptions(bool $value) {
        $this->fireExceptions = $value;
    }

    /**
     * Resolve this binding and return an instanced object on success.
     * This method will return null, ich an explicit "null" bound was given.
     * If a resolve fails, this method will return null if setFireExceptions() was set to 'false' or fires an exception otherwise.
     * @return object|null
     */
    public function resolve(): ?object
    {
        try {
            if ($this->isResolving) {
                throw new Exception("cyclic dependency detected");
            }
            $this->isResolving = true;

            // if it's a singleton, resolve object only once
            if ($this->object !== null && $this->type === BindingType::Singleton) {
                return $this->object;
            }

            // a) null => return null without exceptions
            if ($this->implementation === null) {
                $this->object == null;
            }
            // b) callable => return callable result
            else if (is_callable($this->implementation)) {
                if (is_array($this->implementation)) {
                    $refl = new \ReflectionMethod($this->implementation[0], $this->implementation[1]);
                } else {
                    $refl = new \ReflectionFunction($this->implementation);
                }
                $args = ParameterResolver::resolve($this->ioc, $refl->getParameters(), $this->overrides);
                $this->object = call_user_func_array($this->implementation, $args);
            }
            // c) create instance from class name
            else if (is_string($this->implementation)) {
                $refl = new \ReflectionClass($this->implementation);
                $constructor = $refl->getConstructor();
                if ($constructor !== null && $constructor->isPublic() && count($constructor->getParameters()) > 0) {
                    $args = ParameterResolver::resolve($this->ioc, $constructor->getParameters(), $this->overrides);
                    $this->object = $refl->newInstanceArgs($args);
                } else {
                    $this->object = new $this->implementation();
                }
            }
            // d) implementation is already an object => use it
            else if (is_object($this->implementation)) {
                $this->object = $this->implementation;
            }

            return $this->object;
        } catch (Exception $ex) {
            if ($this->fireExceptions) {
                throw $ex;
            } else {
                return null;
            }
        } finally {
            $this->isResolving = false;
        }
    }
}