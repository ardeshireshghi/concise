<?php

namespace TestUtils;

class Spy extends \ArrayObject
{
  private $_callCount = 0;
  private $_calls = [];

  public static function create(callable $fn = null)
  {
    return (new static())->setFunction(is_null($fn) ? function () {
    } : $fn);
  }

  public function setFunction(callable $fn)
  {
    $this->fnToInvoke = $fn;
    $instance = $this;

    $this['spy'] = function (...$thisArgs) use ($instance) {
      $instance->_calls[] = $thisArgs;
      $instance->_callCount += 1;
      $fnToCall = $instance->fnToInvoke;
      return $fnToCall(...$thisArgs);
    };

    return $this;
  }

  public function callCount()
  {
    return $this->_callCount;
  }

  public function getCall($callIndex)
  {
    if ($callIndex >= count($this->callCount())) {
      throw new \Exception('Invalid Spy call index');
    }

    return $this->_calls[$callIndex];
  }
}
