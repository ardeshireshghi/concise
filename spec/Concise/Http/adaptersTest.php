<?php

use PHPUnit\Framework\TestCase;
use function Concise\Http\Adapter\request as requestAdapter;

class AdaptersTest extends TestCase
{
  public function setup()
  {
    parent::setup();

    $_POST = [];
    $_GET = [];
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
      'method' => 'GET'
    ], $request);
  }

  public function testPostRequestWithBody()
  {
    $_SERVER['HTTP_HOST']= 'localhost';
    $_SERVER['REQUEST_URI']= '/api/user';
    $_SERVER['REQUEST_METHOD'] = 'POST';

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
      'method' => 'POST'
    ], $request);
  }
}
