<?php

use PHPUnit\Framework\TestCase;
use TestUtils\Spy;
use function Concise\app as app;
use function Concise\Middleware\Factory\create as createMiddleware;

function spy()
{
  return new Spy();
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
    $deleteHandlerSpy = (new Spy())->setFunction(function () {});

    $routes = appMockRoutes();
    $deleteUserRoute = [
      [
        'method' => 'DELETE',
        'pattern' => '/api/user/:id',
        'handler' => $deleteHandlerSpy['spy'],
        'regex'   => '/\/api\/user\/(?<id>[\w\-]+)/',
        'params'   => ['id']
      ]
    ];

    app(array_merge($routes, $deleteUserRoute))([]);

    $handlerFirstCallFirstArg = $deleteHandlerSpy->getCall(0)[0];

    $this->assertEquals(1, $deleteHandlerSpy->callCount());
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
    $middlewareHandlerSpy = (new Spy())->setFunction(function (callable $notFoundHandler, $middlewareParams) {
      return $notFoundHandler();
    });

    $mockMiddleware = createMiddleware($middlewareHandlerSpy['spy']);

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
    $this->assertEquals(1, $middlewareHandlerSpy->callCount());
  }

  public function testSingleAppMiddlewareInvokedWhenRouteMatches()
  {
    $_SERVER['HTTP_HOST']= 'google.com';
    $_SERVER['REQUEST_URI']= '/api/user/20/orders/100';
    $_SERVER['REQUEST_METHOD'] = 'DELETE';

    $middlewareHandlercallCount = 0;
    $handlerCallArgs = [];
    $middlewareHandlerSpy = (new Spy())->setFunction(function () {
    });

    $mockMiddleware = createMiddleware($middlewareHandlerSpy['spy']);

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

    $middlewareFirstCallThirdArg = $middlewareHandlerSpy->getCall(0)[2];

    $this->assertEquals(1, $middlewareHandlerSpy->callCount());
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

    $firstMiddlewareHandler = (new Spy())->setFunction(function (callable $nextRouteHandler, array $middlewareParams = array(), array $reqParams = array())
    {
      return $nextRouteHandler($reqParams);
    });

    $secondMiddlewareHandler = (new Spy())->setFunction(function (){});

    $firstMockMiddleware = createMiddleware($firstMiddlewareHandler['spy']);
    $secondMockMiddleware = createMiddleware($secondMiddlewareHandler['spy']);

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

    $firstMiddlewareFirstCallThirdArg = $firstMiddlewareHandler->getCall(0)[2];
    $secondMiddlewareFirstCallThirdArg = $secondMiddlewareHandler->getCall(0)[2];

    $this->assertEquals(1, $firstMiddlewareHandler->callCount(), 'should call the first middleware once');
    $this->assertEquals(1, $secondMiddlewareHandler->callCount(),  'should call the second middleware once');
    $this->assertEquals($expectedParams, $firstMiddlewareFirstCallThirdArg, 'should call the first middleware with correct params');
    $this->assertEquals($expectedParams, $secondMiddlewareFirstCallThirdArg, 'should call the second middleware with correct params');
  }
}
