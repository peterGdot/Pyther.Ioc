<?php

use Demo\Configurations;
use Demo\IDatabase;
use Demo\MariaDatabase;
use Demo\Person;
use Pyther\Ioc\Ioc;

header("Content-Type: text/plain");

$autoloader = require_once __DIR__."/../vendor/autoload.php";
$autoloader->addPsr4('Demo\\', 'classes');

try 
{
    echo "Test simple class\r\n\r\n";

    Ioc::bindSingleton(Person::class, Person::class);
    $person = Ioc::get(Person::class);
    echo $person->greet()."\r\n";

    Ioc::$default->clear();
    echo "--------------------------------------------------------------------------------\r\n";    
    echo "Test simple class with custom arguments.\r\n\r\n";

    Ioc::bindSingleton(Person::class, Person::class, ["name" => "Peter Parker"]);
    $person = Ioc::get(Person::class);
    echo $person->greet()."\r\n";

    Ioc::$default->clear();
    echo "--------------------------------------------------------------------------------\r\n";  
    echo "Test simple class, create from function.\r\n\r\n";

    Ioc::bindSingleton(Person::class, function() {
        return new Person();
    });
    $person = Ioc::get(Person::class);
    echo $person->greet()."\r\n";

    Ioc::$default->clear();
    echo "--------------------------------------------------------------------------------\r\n"; 
    echo "Test simple class, create from function, with custom arguments.\r\n\r\n";

    Ioc::bindSingleton(Person::class, function($name) {
        return new Person($name);
    }, ["name" => "Peter Parker"]);
    $person = Ioc::get(Person::class);
    echo $person->greet()."\r\n";

    Ioc::$default->clear();
    echo "--------------------------------------------------------------------------------\r\n"; 
    echo "Test simple class, pre-created.\r\n\r\n";

    Ioc::bindSingleton(Person::class, new Person("Peter Parker"));
    $person = Ioc::get(Person::class);
    echo $person->greet()."\r\n";

    Ioc::$default->clear();
    echo "--------------------------------------------------------------------------------\r\n"; 
    echo "Test recursive dependency.\r\n\r\n";

    Ioc::bindSingleton(IDatabase::class, MariaDatabase::class, ["name" => "Test Database"]);
    Ioc::bindSingleton(Configurations::class, Configurations::class);
        
    $db = Ioc::get(IDatabase::class);
    echo $db->getName();

} catch (Exception $ex) {
    die($ex->getMessage());
}