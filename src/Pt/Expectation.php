<?php

namespace Pt;

class Expectation extends Stub
{

    private $times;
    private $expected_times;


    public function __construct($mock_name, $method_name, $logger = null)
    {
        parent::__construct($mock_name, $method_name, $logger);
        $this->prefixError = "Expectation [$method_name]: ";
        $this->times = 1;
        $this->expected_times = 1;
    }


    public function times($times)
    {
        if ($times > 0) {
            $this->times = $times;
            $this->expected_times = $times;
        }
        return $this;
    }


    public function never()
    {
        $this->times = null;
        $this->expected_times = null;
        return $this;
    }


    public function is_matched()
    {
        if ($this->times === 0) {
            return true;
        }
        return false;
    }


    public function getResultOfCall()
    {
        if (is_null($this->times)) {
            $message = "[{$this->mock_name}]\n\nMethod ({$this->method_name}) called but is expected to not be called";
            $this->log("err", $message);
            $this->errors[] = $message;
            throw new MockException($message);
        } elseif ($this->times === 0) {
            $message = "[{$this->mock_name}]\n\nMethod ({$this->method_name}) expected to be called {$this->times} times but called at least one more";
            $this->log("err", $message);
            $this->errors[] = $message;
            throw new MockException($message);
        } else {
            $this->times -= 1;
            return parent::getResultOfCall();
        }
    }


    public function verify()
    {
        if ($this->times >= 1) {
            $times_called = $this->expected_times - $this->times;
            $message = "[{$this->mock_name}]\n\nMethod ({$this->method_name}) expected to be called {$this->expected_times} times, but called {$times_called}";
            $this->log("err", $message);
            throw new MockException($message);
        }
        return true;
    }
}
