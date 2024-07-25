<?php
namespace Demo;

class Person
{
    private string $name;

    function __construct($name = "John Doe")
    {
        $this->name = $name;    
    }

    public function greet() {
        return "Hi, I'm $this->name";
    }
}