<?php

use PHPUnit\Framework\TestCase;
use function SuperServer\app as app;

function spy(callable $fn, &$callArgs, &$callCounter) {
  return function() use ($fn, &$callArgs, &$callCounter) {
    $callArgs[] = func_get_args();
    call_user_func_array($fn, func_get_args());
    $callCounter += 1;
  };
}

class appTest extends TestCase
{
  public function testAppExist()
  {
    $this->assertTrue(function_exists('SuperServer\app'));
  }

  public function testAppRouteHandlerCalled()
  {
    $_SERVER['HTTP_HOST']= 'google.com';
    $_SERVER['REQUEST_URI']= '/api/user/20';
    $_SERVER['REQUEST_METHOD'] = 'DELETE';

    $deleteHandlercallCount = 0;
    $handlerCallArgs = [];
    $deleteHandlerSpy = spy(function(){}, $handlerCallArgs, $deleteHandlercallCount);

    app([
      [
        'method'  => 'POST',
        'pattern' => '/api/user',
        'handler' => function(){},
        'regex'    => '/\/api\/user/',
        'params'   => []
      ],
      [
        'method' => 'GET',
        'pattern' => '/api/user/:id',
        'handler' => function(){},
        'regex'   => '/\/api\/user\/(?<id>[\w\-]+)/',
        'params'   => ['id']
      ],
      [
        'method' => 'DELETE',
        'pattern' => '/api/user/:id',
        'handler' => $deleteHandlerSpy,
        'regex'   => '/\/api\/user\/(?<id>[\w\-]+)/',
        'params'   => ['id']
      ],
      [
        'method' => 'PUT',
        'pattern' => '/api/user/:id',
        'handler' => function(){},
        'regex'   => '/\/api\/user\/(?<id>[\w\-]+)/',
        'params'   => ['id']
      ]
    ]);

    $handlerFirstCallFirstArg = $handlerCallArgs[0][0];

    $this->assertEquals(1, $deleteHandlercallCount);
    $this->assertEquals([
      'id' => '20'
    ], $handlerFirstCallFirstArg);
  }
}

?>
