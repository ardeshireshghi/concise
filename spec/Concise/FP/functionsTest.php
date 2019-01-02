<?php

use PHPUnit\Framework\TestCase;
use function Concise\FP\tryCatch;
use function Concise\Routing\route;

class FPTest extends TestCase
{
  public function testTryCatchNotThrowing()
  {
    $tryer = function (array $data = [])
    {
      return $data['x'];
    };

    $catcher = function()
    {
      return false;
    };

    $this->assertTrue(tryCatch($tryer, $catcher)([ 'x' => true ]));
  }

  public function testTryCatchThrowingError()
  {
    $tryer = function (array $data = [])
    {
      return not_exist_function('fdfds');
    };

    $catcher = function(\Error $e)
    {
      return $e->getMessage();
    };

    $this->assertEquals('Call to undefined function not_exist_function()', tryCatch($tryer, $catcher)([ 'data' => [1, 2, 3] ]));
  }
}
