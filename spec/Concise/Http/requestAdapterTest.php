<?php

use PHPUnit\Framework\TestCase;
use function Concise\Http\Adapter\request as requestAdapter;
use TestUtils\RawRequestBody;

class RequestAdapterTest extends TestCase
{
  public function setup()
  {
    parent::setup();

    $_POST = [];
    $_GET = [];
    $_SERVER['CONTENT_TYPE'] = null;
  }

  public function testGetRequestNoQueryparam()
  {
    $_SERVER['HTTP_HOST']= 'localhost';
    $_SERVER['REQUEST_URI']= '/api/user/20';
    $_SERVER['REQUEST_METHOD'] = 'GET';

    $getMockRouteNoQueryParams = [
      'method' => 'GET',
      'pattern' => '/api/user/:id',
      'regex'   => '/\/api\/user\/(?<id>[\w\-]+)/',
      'params'   => ['id'],
      'handler' => function () {
      }
    ];

    $request = requestAdapter($getMockRouteNoQueryParams);

    $this->assertEquals([
      'params' => [
        'id' => '20'
      ],
      'query' => [],
      'body' => [],
      'method' => 'GET',
      'headers' => [
        'Host' => 'localhost',
        'Content-Type' => null
      ]
    ], $request);
  }

  public function testPostRequestWithUrlEncodedBody()
  {
    $_SERVER['HTTP_HOST']= 'localhost';
    $_SERVER['REQUEST_URI']= '/api/user';
    $_SERVER['REQUEST_METHOD'] = 'POST';

    $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';

    $_POST['name'] = 'Ardi';
    $_POST['tel'] = '+44183840304';

    $postMockRoute = [
      'method' => 'POST',
      'pattern' => '/api/user',
      'regex'   => '/\/api\/user/',
      'params'   => [],
      'handler' => function () {
      }
    ];

    $request = requestAdapter($postMockRoute);

    $this->assertEquals([
      'params' => [],
      'query' => [],
      'body' => [
        'name' => 'Ardi',
        'tel' => '+44183840304'
      ],
      'method' => 'POST',
      'headers' => [
        'Host' => 'localhost',
        'Content-Type' => 'application/x-www-form-urlencoded'
      ]
    ], $request);
  }

  public function testPostRequestContentTypeJson()
  {
    $_SERVER['HTTP_HOST']= 'localhost';
    $_SERVER['REQUEST_URI']= '/api/user';
    $_SERVER['REQUEST_METHOD'] = 'POST';

    $_SERVER['CONTENT_TYPE'] = 'application/json';

    $rawBody = new RawRequestBody(json_encode([
      'name' => 'Ardi',
      'tel'  => '+44183840304'
    ]));

    $postMockRoute = [
      'method' => 'POST',
      'pattern' => '/api/user',
      'regex'   => '/\/api\/user/',
      'params'   => [],
      'handler' => function () {
      }
    ];

    $request = requestAdapter($postMockRoute);

    $this->assertEquals([
      'params' => [],
      'query' => [],
      'body' => [
        'name' => 'Ardi',
        'tel' => '+44183840304'
      ],
      'method' => 'POST',
      'headers' => [
        'Host' => 'localhost',
        'Content-Type' => 'application/json'
      ]
    ], $request);

    $rawBody->empty();
  }

  public function testPostRequestJsonPayload()
  {
    $_SERVER['HTTP_HOST']= 'localhost';
    $_SERVER['REQUEST_URI']= '/api/user';
    $_SERVER['REQUEST_METHOD'] = 'POST';

    $_SERVER['CONTENT_TYPE'] = 'text/plain';

    $rawBody = new RawRequestBody(json_encode([
      'name' => 'Ardi',
      'tel'  => '+44183840304'
    ]));

    $postMockRoute = [
      'method' => 'POST',
      'pattern' => '/api/user',
      'regex'   => '/\/api\/user/',
      'params'   => [],
      'handler' => function () {
      }
    ];

    $request = requestAdapter($postMockRoute);

    $this->assertEquals([
      'params' => [],
      'query' => [],
      'body' => [
        'name' => 'Ardi',
        'tel' => '+44183840304'
      ],
      'method' => 'POST',
      'headers' => [
        'Host' => 'localhost',
        'Content-Type' => 'text/plain'
      ]
    ], $request);

    $rawBody->empty();
  }

  public function testPostRequestRawBody()
  {
    $_SERVER['HTTP_HOST']= 'localhost';
    $_SERVER['REQUEST_URI']= '/api/data';
    $_SERVER['REQUEST_METHOD'] = 'POST';

    $_SERVER['CONTENT_TYPE'] = 'text/plain';

    $mockBody = <<<'EOF'
This is the payload of the request
EOF;

    $rawBody = new RawRequestBody($mockBody);

    $postMockRoute = [
      'method' => 'POST',
      'pattern' => '/api/data',
      'regex'   => '/\/api\/data/',
      'params'   => [],
      'handler' => function () {
      }
    ];

    $request = requestAdapter($postMockRoute);

    $this->assertEquals([
      'params' => [],
      'query' => [],
      'body' => $mockBody,
      'method' => 'POST',
      'headers' => [
        'Host' => 'localhost',
        'Content-Type' => 'text/plain'
      ]
    ], $request);

    $rawBody->empty();
  }


  public function testNoRouteQueryParams()
  {
    $_SERVER['HTTP_HOST']= 'localhost';
    $_SERVER['REQUEST_URI']= '/api/not/found';
    $_SERVER['REQUEST_METHOD'] = 'GET';

    $_GET['type'] = 'latest';

    $request = requestAdapter();

    $this->assertEquals([
      'params' => [],
      'body' => [],
      'query' => [
        'type' => 'latest'
      ],
      'method' => 'GET',
      'headers' => [
        'Host' => 'localhost',
        'Content-Type' => null
      ]
    ], $request);
  }
}
