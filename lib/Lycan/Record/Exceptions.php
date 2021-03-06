<?php 

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Lycan\Record;

class InvalidPropertyException extends \Lycan\Exception
{
    
    public function __construct($class, $property)
    {
        parent::__construct("Invalid property {$class}::{$property}");
    }

    public function __toString()
    {
        return $this->getMessage();
    }
}

class InvalidMethodException extends \Lycan\Exception
{
    
    public function __construct($class, $method)
    {
        parent::__construct("Invalid method {$class}::{$method}");
    }

    public function __toString()
    {
        return $this->getMessage();
    }
}
