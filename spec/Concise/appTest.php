<?php

use PHPUnit\Framework\TestCase;
use TestUtils\Spy;
use function Concise\app as app;
use function Concise\Middleware\Factory\create as createMiddleware;
use function Concise\Http\Response\response;
use function Concise\Http\Response\statusCode;

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
  public function setup()
  {
    parent::setup();

    $_POST = [];
    $_GET = [];

    unset(
      $_SERVER['CONTENT_LENGTH'],
      $_SERVER['CONTENT_TYPE'],
      $_SERVER['HTTP_CONNECTION'],
      $_SERVER['HTTP_KEEP_ALIVE']
    );
  }

  public function testAppRouteHandlerCalledWhenRouteExists()
  {
    $_SERVER['HTTP_HOST']= 'google.com';
    $_SERVER['REQUEST_URI']= '/api/user/20';
    $_SERVER['REQUEST_METHOD'] = 'DELETE';

    $expectedRequest = [
      'params' => [
        'id' => '20'
      ],
      'method' => 'DELETE',
      'query' => [],
      'body' => [],
      'headers' => [
        'Host' => 'google.com'
      ],
      'meta' => [
        'hasRouteMatch' => true
      ]
    ];

    $deleteHandlerSpy = Spy::create(function () {
      return response('delete done', []);
    });

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
    $this->assertEquals($expectedRequest, $handlerFirstCallFirstArg);
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
    $middlewareHandlerSpy = (new Spy())->setFunction(function (callable $notFoundHandler, $middlewareParams, $request) {
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

    $expectedRequest = [
      'params' => [
        'id' => '20',
        'order_id' => '100'
      ],
      'method' => 'DELETE',
      'query' => [],
      'body' => [],
      'headers' => [
        'Host' => 'google.com'
      ],
      'meta' => [
        'hasRouteMatch' => true
      ]
    ];

    $middlewareHandlerSpy = Spy::create(function($nextHandler, $middlewareParams, $request) {
      return $nextHandler($request);
    });

    $mockMiddleware = createMiddleware($middlewareHandlerSpy['spy']);

    $routes = appMockRoutes();
    $deleteUserRoute = [
      [
        'method' => 'DELETE',
        'pattern' => '/api/user/:id/orders/:order_id',
        'handler' => function ($request) {
          return response('some response', []);
        },
        'regex'   => '/\/api\/user\/(?<id>[\w\-]+)\/orders\/(?<order_id>[\w\-]+)/',
        'params'   => ['id']
      ]
    ];

    app(array_merge($routes, $deleteUserRoute))([ $mockMiddleware ]);

    $middlewareFirstCallThirdArg = $middlewareHandlerSpy->getCall(0)[2];

    $this->assertEquals(1, $middlewareHandlerSpy->callCount());
    $this->assertEquals($expectedRequest, $middlewareFirstCallThirdArg);
  }

  public function testMultipleMiddlewaresInvokedWhenRouteMatches()
  {
    $_SERVER['HTTP_HOST']= 'google.com';
    $_SERVER['REQUEST_URI']= '/api/user/20/orders/100';
    $_SERVER['REQUEST_METHOD'] = 'DELETE';

    $expectedRequest = [
      'params' => [
        'id' => '20',
        'order_id' => '100'
      ],
      'method' => 'DELETE',
      'query' => [],
      'body' => [],
      'headers' => [
        'Host' => 'google.com'
      ],
      'meta' => [
        'hasRouteMatch' => true
      ]
    ];

    $firstMiddlewareHandler = Spy::create(function (callable $nextRouteHandler, array $middlewareParams = array(), array $request = array()) {
      return $nextRouteHandler($request);
    });

    $secondMiddlewareHandler = Spy::create(function (callable $nextRouteHandler, array $middlewareParams = array(), array $request = array()) {
      return $nextRouteHandler($request);
    });

    $routeHandler = Spy::create(function (array $request) {
      return response('OK')(statusCode(201, []));
    });

    $firstMockMiddleware = createMiddleware($firstMiddlewareHandler['spy']);
    $secondMockMiddleware = createMiddleware($secondMiddlewareHandler['spy']);

    $routes = appMockRoutes();
    $deleteUserRoute = [
      [
        'method' => 'DELETE',
        'pattern' => '/api/user/:id/orders/:order_id',
        'handler' => $routeHandler['spy'],
        'regex'   => '/\/api\/user\/(?<id>[\w\-]+)\/orders\/(?<order_id>[\w\-]+)/',
        'params'   => ['id', 'order_id']
      ]
    ];

    $response = app(array_merge($routes, $deleteUserRoute), [ $firstMockMiddleware, $secondMockMiddleware ]);

    $firstMiddlewareFirstCallThirdArg = $firstMiddlewareHandler->getCall(0)[2];
    $secondMiddlewareFirstCallThirdArg = $secondMiddlewareHandler->getCall(0)[2];

    $this->assertEquals(1, $firstMiddlewareHandler->callCount(), 'should call the first middleware once');
    $this->assertEquals(1, $secondMiddlewareHandler->callCount(), 'should call the second middleware once');
    $this->assertEquals(1, $routeHandler->callCount(), 'should call the route handler');

    $this->assertEquals($expectedRequest, $firstMiddlewareFirstCallThirdArg, 'should call the first middleware with correct params');
    $this->assertEquals($expectedRequest, $secondMiddlewareFirstCallThirdArg, 'should call the second middleware with correct params');
    $this->assertEquals([
      'headers' => [
        'Content-Type' => 'text/html'
      ],
      'body' => 'OK',
      'status' => 201
    ], $response);
  }
}
