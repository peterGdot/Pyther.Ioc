# Pyther.Ioc

A simple lightweight PHP Inversion of Control (IoC) container with the following features:

- allow the creation of multiple or singleton instances
- goes hand in hand with _Constructor Dependency Injection_
- can bind to classes, methods, functions and instances
- resolve nested dependencies, based on constructor DI
- can bind with optional arguments
- allow per instance arguments for non singletons during resolve phase
- detect cyclic dependencies on first use
- take default constructor arguments into account 
- support for multiple, independent containers
- no external dependencies
- easy to use

## Requirements
- php 8.1 or higher

## Quickstart
Install the [Composer Package](https://packagist.org/packages/pyther/ioc)

`composer require pyther/ioc`

And here is a fictional example. _Inversion of Control_ is a two way process. First you have to _bind_ implementations of interfaces or classes to the container and then _resolve_ (one or multile times) the creation of instances.  

```php
use Pyther\Ioc\Ioc;

// Let's bind an implementation of “MariaDatabase” as a singleton to an interface.
// This ist afast process and will NOT trigger the autoloader.
Ioc::bindSingleton(IDatabase::class, MariaDatabase::class);

// later anywhere in your code, resolve to an instance of a MariaDatabase class
$db = Ioc::get(IDatabase::class);
```

## Binding

There are two general ways to bind implementations to the container.

### BindSingleton
```php
Ioc::bindSingleton(ShoppingCart::class, ShoppingCart::class);
```

If you bind using `bindSingleton` the instance will be created on first use of `Ioc::get(ShoppingCart::class)`.
All subsequent calls to the `get` method will always return the same instance.

### BindMultiple
```php
Ioc::bindMultiple(Product::class, Product::class);
```

This way any call of `Ioc::get(Product::class)` will return a new instance of the `Product` class.

## Dependency Injection
A "Inversion of Control" container goes hand in hand with [Constructor Dependency Injection](https://en.wikipedia.org/wiki/Dependency_injection#Constructor_injection).

Let's look at an example. Imagine we have a shopping cart, that depends of the current logged in customer:

```php
class ShoppingCart
{
    function __construct(Customer $customer)
    {
        ...
    }
}
```

If we bind the `ShoppingCart` and `Customer` class to the IoC container

```php
// hint: the order of binding doesn't matter.
Ioc::bindSingleton(ShippingCart::class, ShoppingCart::class);
Ioc::bindSingleton(Customer::class, Customer::class);
```

and we resolve the `ShoppingCart` class using

```php
$cart = Ioc::get(ShoppingCart::class);
```

the container want to create a new shopping cart and see it requires a customer class. 
For this reason, a `Customer` instance is first created and passed as a parameter to the constructor of the ShoppingCart. 
This nesting is recursive and takes into account “singletons” and “multiple” instances.
Of course, multiple constructor arguments are supported and cyclic dependencies are recognized when resolving via the `get` method and will fire a `Pyther\Ioc\Exceptions\ResolveException` exception.

## More control
This library gives you a lot control how objects will be instanced.

### More binding control 

For example you can add your own constructor arguments:

```php
class Configurations
{
    function __construct(?string $path = null)
    {
        ...
    }
}
...
Ioc::bindSingleton(Configurations::class, Configurations::class, ["path" => "./config.json"]);
```

As you can see, the default values for constructors are taken into account if no arguments are found.

Another way is to create a anonymous construct function: 

```php
Ioc::bindSingleton(Configurations::class, function() {
    return new Configurations("./config.json);
});
```

This function is only executed later when the object is first instantiated.

This way you can also have function arguments:

```php
Ioc::bindSingleton(Configurations::class, function(string $path) {
    return new Configurations($path);
}, ['path' => "./config.json"]);
```

This construct function can be also be a static method of another object:

```php
$this->bindSingleton(IInterface::class, [AnyClass::class, "createObject"]);
// or
$this->bindSingleton(IInterface::class, "My\Namespace\AnyClass::createObject");

or with arguments

$this->bindSingleton(IInterface::class, [AnyClass::class, "createObject"], [
    "text" => "abc", "number"=> 123
]);
// or
$this->bindSingleton(IInterface::class, "My\Namespace\AnyClass::createObject", [
    "text" => "abc", "number"=> 123
]);
```

Or as a method of an already instanced object:

```php
$this->bindSingleton(IInterface::class, [$obj, "createObject"]);
// or with arguments
$this->bindSingleton(IInterface::class, [$obj, "createObject"],  [
    "text" => "abc", "number"=> 123
]);
```

Auch ein binding auf null ist legitim:

```php
Ioc::bindSingleton(Configurations::class, null);
```

In diesem Fall wird beim Auflösen keine Exception ausgelöst, sondern auf null aufgelöst.

And finally you can also bind an already existing objects:

```php
$configs = new Configurations("./config.json");
...
Ioc::bindSingleton(Configurations::class, $configs);
```

Although this is possible, it should be avoided. On the one hand, the creation of the instances should be left to the container, on the other hand this triggers the autoloader and executes code that may never be used. 

### More resolve control

For non singleton instances you can specifiy constructor arguments during the _resolve_ phase:

```
Ioc::bindMultiple(Product::class, Product::class);
...
Ioc::get(Product::class, ["sku" => "Product 001"];
...
Ioc::get(Product::class, ["sku" => "Product 002"];
```

## Check if binding exists

To check if a binding exists, you can use the `has` method:

```php
$exists = Ioc::has(Configurations::class);
```

## Multiple Containers

If you have the rare case where you need more than one container, here we are.
The static methods of `Ioc::bindSingleton(...)`, `Ioc::bindMultiple(...)` and `Ioc::get(...)` are suggar forms of

```php
IoC::$default->addMultiple(...)
IoC::$default->addSingleton(...)
IoC::$default->resolve(...)
```

where `Ioc::$default` is the default container. This way you can have separated multiple containers:

```php
$containerA = new Ioc();
$containerB = new Ioc();

$containerA->addSingleton(...);
$containerB->addMutliple(...);

$containerA->get(...);
```

I'm sure you get it :)
