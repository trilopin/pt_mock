<?php

namespace Pt;

class Stub extends Common
{

    protected $_mock_name;
    protected $_method_name;
    protected $_args;
    protected $_hash;
    protected $_value_to_return;
    protected $_exception_to_raise;


    public function __construct($mock_name, $method_name, $logger = null)
    {
        parent::__construct($logger);
        $this->_mock_name = $mock_name;
        $this->_method_name = $method_name;
        $this->_args = null;
        $this->_hash = null;
        $this->_value_to_return = null;
        $this->_exception_to_raise = null;
        $this->_prefix_error = "Stub [$method_name]: ";
    }


    public function with()
    {
        $args = func_get_args();
        $this->_args = $this->_sort_args($args);
        $this->_hash = $this->_get_hash($this->_args);
        $this->_log('debug', "Defined args for ({$this->_hash}):\n".print_r($this->_args, true));
        return $this;
    }


    public function returns($value)
    {
        $this->_value_to_return = $value;
        return $this;
    }


    public function raises($exception)
    {
        $this->_exception_to_raise = $exception;
        return $this;
    }


    public function _get_result_of_call()
    {
        if (!is_null($this->_value_to_return)) {
            $response = is_object($this->_value_to_return) ? "Object:".spl_object_hash($this->_value_to_return) : print_r($this->_value_to_return, true);
            $this->_log("debug", "Response is: ".$response);
            return $this->_value_to_return;
        } elseif (!is_null($this->_exception_to_raise)) {
            $this->_log("debug", "Raises exception [".get_class($this->_exception_to_raise)."]:".$this->_exception_to_raise->getMessage());
            throw $this->_exception_to_raise;
        } else {
            $this->_log("debug", "Response is: null");
            return null;
        }
    }


    public function _get_args()
    {
        return $this->_args;
    }


    public function _get_args_hash()
    {
        return $this->_hash;
    }
}
