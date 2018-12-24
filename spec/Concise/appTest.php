<?php

use PHPUnit\Framework\TestCase;
use function Concise\app as app;

function spy(callable $fn, &$callArgs, &$callCounter)
{
  return function () use ($fn, &$callArgs, &$callCounter) {
    $callArgs[] = func_get_args();
    call_user_func_array($fn, func_get_args());
    $callCounter += 1;
  };
}

function appMockRoutes()
{
  return [
    [
      'method'  => 'POST',
      'pattern' => '/api/user',
      'handler' => function () {
      },
      'regex'    => '/\/api\/user/',
      'params'   => []
    ],
    [
      'method' => 'GET',
      'pattern' => '/api/user/:id',
      'handler' => function () {
      },
      'regex'   => '/\/api\/user\/(?<id>[\w\-]+)/',
      'params'   => ['id']
    ],
    [
      'method' => 'PUT',
      'pattern' => '/api/user/:id',
      'handler' => function () {
      },
      'regex'   => '/\/api\/user\/(?<id>[\w\-]+)/',
      'params'   => ['id']
    ]
  ];
}

class AppTest extends TestCase
{
  public function testAppRouteHandlerCalledWhenRouteExists()
  {
    $_SERVER['HTTP_HOST']= 'google.com';
    $_SERVER['REQUEST_URI']= '/api/user/20';
    $_SERVER['REQUEST_METHOD'] = 'DELETE';

    $deleteHandlercallCount = 0;
    $handlerCallArgs = [];
    $deleteHandlerSpy = spy(function () {
    }, $handlerCallArgs, $deleteHandlercallCount);

    $routes = appMockRoutes();
    $deleteUserRoute = [
      [
        'method' => 'DELETE',
        'pattern' => '/api/user/:id',
        'handler' => $deleteHandlerSpy,
        'regex'   => '/\/api\/user\/(?<id>[\w\-]+)/',
        'params'   => ['id']
      ]
    ];

    app(array_merge($routes, $deleteUserRoute));

    $handlerFirstCallFirstArg = $handlerCallArgs[0][0];

    $this->assertEquals(1, $deleteHandlercallCount);
    $this->assertEquals([
      'id' => '20'
    ], $handlerFirstCallFirstArg);
  }

  public function testAppRouteNotFound()
  {
    $_SERVER['HTTP_HOST']= 'google.com';
    $_SERVER['REQUEST_URI']= '/not/found/100';
    $_SERVER['REQUEST_METHOD'] = 'POST';

    $expectedOutputBody = 'Route for path: \"/not/found/100\" not found';
    $app = app();

    ob_start();
    $response = $app(appMockRoutes());
    $output = ob_get_clean();

    $this->assertEquals([
      'headers' => [
        'Content-Type' => 'text/html'
      ],
      'body' =>  $expectedOutputBody,
      'status' => 404
    ], $response);

    $this->assertEquals($expectedOutputBody, $output);
  }
}
