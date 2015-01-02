<?php

namespace Pt;

class Expectation extends Stub
{

    private $_times;
    private $_expected_times;


    public function __construct($mock_name, $method_name, $logger = null)
    {
        parent::__construct($mock_name, $method_name, $logger);
        $this->_prefix_error = "Expectation [$method_name]: ";
        $this->_times = 1;
        $this->_expected_times = 1;
    }


    public function times($times)
    {
        if ($times > 0) {
            $this->_times = $times;
            $this->_expected_times = $times;
        }
        return $this;
    }


    public function never()
    {
        $this->_times = null;
        $this->_expected_times = null;
        return $this;
    }


    public function is_matched()
    {
        if ($this->_times === 0) {
            return true;
        }
        return false;
    }


    public function _get_result_of_call()
    {
        if (is_null($this->_times)) {
            $message = "[{$this->_mock_name}]\n\nMethod ({$this->_method_name}) called but is expected to not be called";
            $this->_log("err", $message);
            $this->_errors[] = $message;
            throw new MockException($message);
        } elseif ($this->_times === 0) {
            $message = "[{$this->_mock_name}]\n\nMethod ({$this->_method_name}) expected to be called {$this->_times} times but called at least one more";
            $this->_log("err", $message);
            $this->_errors[] = $message;
            throw new MockException($message);
        } else {
            $this->_times -= 1;
            return parent::_get_result_of_call();
        }
    }


    public function verify()
    {
        if ($this->_times >= 1) {
            $times_called = $this->_expected_times - $this->_times;
            $message = "[{$this->_mock_name}]\n\nMethod ({$this->_method_name}) expected to be called {$this->_expected_times} times, but called {$times_called}";
            $this->_log("err", $message);
            throw new MockException($message);
        }
        return true;
    }
}
