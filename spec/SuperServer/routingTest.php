<?php

use PHPUnit\Framework\TestCase;
use function SuperServer\FP\map;
use function SuperServer\Routing\route;

class RoutingTest extends TestCase
{
  public function testRoute()
  {
    $handlerFn = function () {
    };
    $expected = [
    'method'   => 'GET',
    'pattern'  => '/api/user/:id',
    'handler'  => $handlerFn,
    'regex'   => '/\/api\/user\/(?<id>[\w\-]+)/',
    'params'   => ['id']
  ];

    $this->assertEquals($expected, route('GET')('/api/user/:id')($handlerFn));
  }
}
