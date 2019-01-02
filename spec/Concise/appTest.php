<?php

use PHPUnit\Framework\TestCase;
use function Concise\app as app;
use function Concise\Middleware\Factory\create as createMiddleware;

function spy(callable $fn, &$callArgs, &$callCounter)
{
  return function (...$thisArgs) use ($fn, &$callArgs, &$callCounter) {
    $callArgs[] = $thisArgs;
    $callCounter += 1;
    return $fn(...$thisArgs);

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

    app(array_merge($routes, $deleteUserRoute))([]);

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

    $expectedOutputBody = 'Route for path: "/not/found/100" not found';

    ob_start();
    $response = app(appMockRoutes())([]);
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

  public function testMiddlewareCalledBeforeAppRouteNotFound()
  {
    $_SERVER['HTTP_HOST']= 'google.com';
    $_SERVER['REQUEST_URI']= '/not/found/100';
    $_SERVER['REQUEST_METHOD'] = 'POST';

    $expectedOutputBody = 'Route for path: "/not/found/100" not found';
    $middlewareHandlercallCount = 0;
    $handlerCallArgs = [];
    $middlewareHandler = spy(function (callable $notFoundHandler, $middlewareParams) {
      return $notFoundHandler();
    }, $handlerCallArgs, $middlewareHandlercallCount);

    $mockMiddleware = createMiddleware($middlewareHandler);

    ob_start();
    $response = app(appMockRoutes(), [ $mockMiddleware ]);
    $output = ob_get_clean();

    $this->assertEquals([
      'headers' => [
        'Content-Type' => 'text/html'
      ],
      'body' =>  $expectedOutputBody,
      'status' => 404
    ], $response);

    $this->assertEquals($expectedOutputBody, $output);
    $this->assertEquals(1, $middlewareHandlercallCount);
  }

  public function testSingleAppMiddlewareInvokedWhenRouteMatches()
  {
    $_SERVER['HTTP_HOST']= 'google.com';
    $_SERVER['REQUEST_URI']= '/api/user/20/orders/100';
    $_SERVER['REQUEST_METHOD'] = 'DELETE';

    $middlewareHandlercallCount = 0;
    $handlerCallArgs = [];
    $middlewareHandler = spy(function () {
    }, $handlerCallArgs, $middlewareHandlercallCount);

    $mockMiddleware = createMiddleware($middlewareHandler);

    $routes = appMockRoutes();
    $deleteUserRoute = [
      [
        'method' => 'DELETE',
        'pattern' => '/api/user/:id/orders/:order_id',
        'handler' => function () {},
        'regex'   => '/\/api\/user\/(?<id>[\w\-]+)\/orders\/(?<order_id>[\w\-]+)/',
        'params'   => ['id']
      ]
    ];

    app(array_merge($routes, $deleteUserRoute))([ $mockMiddleware ]);

    $middlewareFirstCallThirdArg = $handlerCallArgs[0][2];

    $this->assertEquals(1, $middlewareHandlercallCount);
    $this->assertEquals([
      'id' => '20',
      'order_id' => '100'
    ], $middlewareFirstCallThirdArg);
  }

  public function testMultipleMiddlewaresInvokedWhenRouteMatches()
  {
    $_SERVER['HTTP_HOST']= 'google.com';
    $_SERVER['REQUEST_URI']= '/api/user/20/orders/100';
    $_SERVER['REQUEST_METHOD'] = 'DELETE';

    $expectedParams = [
      'id' => '20',
      'order_id' => '100'
    ];

    $firstMiddlewareHandlerCallCount = 0;
    $firstMiddlewareHandlerCallArgs = [];
    $firstMiddlewareHandler = spy(function (callable $nextRouteHandler, array $middlewareParams = array(), array $reqParams = array())
      {
        return $nextRouteHandler($reqParams);
      }, $firstMiddlewareHandlerCallArgs, $firstMiddlewareHandlerCallCount);

    $secondMiddlewareHandlerCallCount = 0;
    $secondMiddlewareHandlerCallArgs = [];
    $secondMiddlewareHandler = spy(function () {
    }, $secondMiddlewareHandlerCallArgs, $secondMiddlewareHandlerCallCount);

    $firstMockMiddleware = createMiddleware($firstMiddlewareHandler);
    $secondMockMiddleware = createMiddleware($secondMiddlewareHandler);

    $routes = appMockRoutes();
    $deleteUserRoute = [
      [
        'method' => 'DELETE',
        'pattern' => '/api/user/:id/orders/:order_id',
        'handler' => function () {},
        'regex'   => '/\/api\/user\/(?<id>[\w\-]+)\/orders\/(?<order_id>[\w\-]+)/',
        'params'   => ['id']
      ]
    ];

    app(array_merge($routes, $deleteUserRoute), [ $firstMockMiddleware, $secondMockMiddleware ]);

    $firstMiddlewareFirstCallThirdArg = $firstMiddlewareHandlerCallArgs[0][2];
    $secondMiddlewareFirstCallThirdArg = $secondMiddlewareHandlerCallArgs[0][2];

    $this->assertEquals(1, $firstMiddlewareHandlerCallCount, 'should call the first middleware once');
    $this->assertEquals(1, $secondMiddlewareHandlerCallCount,  'should call the second middleware once');
    $this->assertEquals($expectedParams, $firstMiddlewareFirstCallThirdArg, 'should call the first middleware with correct params');
    $this->assertEquals($expectedParams, $secondMiddlewareFirstCallThirdArg, 'should call the second middleware with correct params');
  }
}
