<?php

namespace Pt;

class Common
{

    protected $_logger;
    protected $_pre_error_message;


    public function __construct($logger = null)
    {
        $this->_logger = $logger;
        $this->_prefix_error = "";
    }


    public function _log($level, $message)
    {
        if (!is_null($this->_logger)) {
            foreach (explode("\n", trim($message)) as $line) {
                $this->_logger->$level("{$this->_prefix_error} $line");
            }
        }
    }


    protected function _sort_args($args)
    {
        ksort($args);
        foreach ($args as $key => $value) {
            if (is_array($value)) {
                $args[$key] = $this->_sort_args($value);
            }
            if (is_object($value)) {
                $args[$key] = "Object: (".spl_object_hash($value).")";
            }
        }
        return $args;
    }


    protected function _get_hash($args)
    {
        return md5(print_r($args, true));
    }
}
