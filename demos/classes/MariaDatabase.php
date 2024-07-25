<?php
namespace Demo;

class MariaDatabase implements IDatabase
{
    private static $instanceCount = 0;
    private string $name;

    function __construct(Configurations $configs, string $name = "123")
    {
        $this->name = $name;
        echo "create '".__CLASS__."' instance #".++static::$instanceCount."\r\n";
    }

    public function getName(): string {
        return "MariaDb ($this->name)";
    }
}