<?php

namespace Pt;

class Mock extends Common
{

    private static $_mocks = array();
    private $_name;
    private $_expects;
    private $_stubs;
    private $_errors = array();


    public function __construct($name, $logger = null)
    {
        parent::__construct($logger);

        $this->_name = $name;
        $this->_expects = array();
        $this->_stubs = array();
        $this->_prefix_error = "mock [{$name}]: ";
        self::$_mocks[] = $this;
    }


    public function expects($method_name)
    {
        $this->_log('debug', "Defined ({$method_name}) as expectation");
        $expect = new Expectation($this->_name, $method_name, $this->_logger);
        $this->_expects[$method_name][] = $expect;
        return $expect;
    }


    public function stubs($method_name)
    {
        $this->_log('debug', "Defined ({$method_name}) as stub");
        $stub = new Stub($this->_name, $method_name, $this->_logger);
        $this->_stubs[$method_name][] = $stub;
        return $stub;
    }


    public static function reset_all()
    {
        foreach (self::$_mocks as $mock) {
            $mock->reset();
        }
        self::$_mocks = array();
    }


    public function reset()
    {
        $this->_expects = array();
        $this->_stubs = array();
        $this->_errors = array();
    }


    public static function verify_all()
    {
        $errors = array();
        foreach (self::$_mocks as $mock) {
            try {
                $mock->verify();
            } catch (MockException $e) {
                $errors[] = $e->getMessage();
            }
        }
        self::reset_all();

        if (count($errors) > 0) {
            throw new MockException(implode("\n", $errors));
        } else {
            return true;
        }
    }


    public function verify()
    {
        foreach ($this->_expects as $method_name => $expectations) {
            foreach ($expectations as $expect) {
                try {
                    $expect->verify();
                } catch (MockException $e) {
                    $this->_errors[] = $e->getMessage();
                }
            }
        }

        if (count($this->_errors) > 0) {
            $this->_log('info', "Does not verify");
            throw new MockException(implode("\n", $this->_errors));
        } else {
            $this->_log('info', "Verify");
            return true;
        }
    }


    public function __call($name, $args)
    {
        $args = $this->_sort_args($args);
        $hash = count($args) ? $this->_get_hash($args) : '_null_';

        $this->_log("debug", "Received call for method ({$name}) with args ($hash):\n".print_r($args, true));

        $options = array();

        if (isset($this->_stubs[$name])) {
            foreach (array_reverse($this->_stubs[$name]) as $stub) {
                $stub_hash = is_null($stub->_get_args_hash()) ? '_null_' : $stub->_get_args_hash();
                $options[$stub_hash] = $stub;
            }
        }

        if (isset($this->_expects[$name])) {
            foreach (array_reverse($this->_expects[$name]) as $expect) {
                $expect_hash = is_null($expect->_get_args_hash()) ? '_null_' : $expect->_get_args_hash();
                if (!$expect->is_matched()) {
                    $options[$expect_hash] = $expect;
                }
            }
        }

        try {
            if (isset($options[$hash])) {
                return $options[$hash]->_get_result_of_call();
            }
            if (isset($options['_null_'])) {
                return $options['_null_']->_get_result_of_call();
            }

            if (count($options) === 0) {
                $message = "[{$this->_name}]\n\nCannot find any stub or expecation for call [{$name}] with arguments:\n".print_r($args, true);
                $this->_errors[] = "[{$this->_name}]: {$message}";
                throw new MockException($message);
            } elseif (count($options) === 1) {
                $option = array_shift($options);
                $message = "[{$this->_name}]\n\nExpected parameters for [{$name}]:\n".print_r($option->_get_args(), true)."\n But received :".print_r($args, true);
                $this->_errors[] = "[{$this->_name}]: {$message}";
                throw new MockException($message);
            } else {
                $message  = "[{$this->_name}]\n\nCannot match any stub or expecation for call [{$name}] with arguments:\n".print_r($args, true)."\n";
                $message .= "Similar expectations are :\n";
                foreach ($options as $option) {
                    $message .= get_class($option)." with args:\n".print_r($option->_get_args(), true)."\n";
                }

                $this->_errors[] = "[{$this->_name}]: {$message}";
                throw new MockException($message);
            }
        } catch (MockException $e) {
            $this->_log('err', $e->getMessage());
            throw $e;
        }
    }
}
