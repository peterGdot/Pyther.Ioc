# Pyther.Ioc

A simple lightweight PHP Inversion of Control (IoC) container with the following features:

- allow the creation of multiple or singleton instances
- can bind to classes, methods and functions
- resolve nested dependencies, based on constructor DI
- can bind with optional arguments
- detect cyclic dependencies during the object creation phase
- support for multiple, independent containers
- easy to use

## Requirements
- php 8.3 or higher

## Usage

### Basic Example
let's say we have this two imaginary classes:
```php
class MariaDatabase implements IDatabase
{
    function __construct(Configurations $configs)
    {
        ...
    }
}
```
and
```php
class Configurations {
    function __construct(string $path)
    {
        ...
    }
}
```

We can use the IoC to handle it's creation on demand.

```php
// bind a database class, which use a "Configuration" class by constructor DI
// using "bindMultiple" you will always get a new object on request
Ioc::bindMultiple(IDatabase::class, MariaDatabase::class);

// bind the Configuration class (since the instances are created on first use, the order doesn't matter)
// we are also setting the "path" parameter
// using "bindSingleton" only one instance will be created on first request
Ioc::bindSingleton(Configurations::class, Configurations::class, ["path" => "./config.json"]);

// use "get" to get a new object.
// This will first create the "Configuration" object behind the scene, used by the Database class.
$db = Ioc::get(IDatabase::class);
$db->...
```

### Example using callback functions

without custom arguments
```php
Ioc::bindSingleton(Configurations::class, function() {
    return new Configurations("./config.json);
});
```

with custom arguments
```php
Ioc::bindSingleton(Configurations::class, function(string $path) {
    return new Configurations($path);
}, ['path' => "./config.json"]);
```

### Example using pre-created objects
```php
Ioc::bindSingleton(Configurations::class, new Configurations("./config.json"));
```

to be continued ...