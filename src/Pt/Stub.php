<?php

namespace Pt;

class Stub extends Common
{

    protected $mock_name;
    protected $method_name;
    protected $args;
    protected $hash;
    protected $value_to_return;
    protected $exception_to_raise;


    public function __construct($mock_name, $method_name, $logger = null)
    {
        parent::__construct($logger);
        $this->mock_name = $mock_name;
        $this->method_name = $method_name;
        $this->args = null;
        $this->hash = null;
        $this->value_to_return = null;
        $this->exception_to_raise = null;
        $this->prefixError = "Stub [$method_name]: ";
    }


    public function with()
    {
        $args = func_get_args();
        $this->args = $this->sortArgs($args);
        $this->hash = $this->getHash($this->args);
        $this->log('debug', "Defined args for ({$this->hash}):\n".print_r($this->args, true));
        return $this;
    }


    public function returns($value)
    {
        $this->value_to_return = $value;
        return $this;
    }


    public function raises($exception)
    {
        $this->exception_to_raise = $exception;
        return $this;
    }


    public function getResultOfCall()
    {
        if (!is_null($this->value_to_return)) {
            $response = is_object($this->value_to_return) ? "Object:".spl_object_hash($this->value_to_return) : print_r($this->value_to_return, true);
            $this->log("debug", "Response is: ".$response);
            return $this->value_to_return;
        } elseif (!is_null($this->exception_to_raise)) {
            $this->log("debug", "Raises exception [".get_class($this->exception_to_raise)."]:".$this->exception_to_raise->getMessage());
            throw $this->exception_to_raise;
        } else {
            $this->log("debug", "Response is: null");
            return null;
        }
    }


    public function getArgs()
    {
        return $this->args;
    }


    public function getArgsHash()
    {
        return $this->hash;
    }
}
