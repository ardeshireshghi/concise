<?php

use PHPUnit\Framework\TestCase;
use function Concise\Http\Response\response;
use function Concise\Http\Response\setHeader;
use function Concise\Http\Response\send;
use function Concise\Http\Response\statusCode;

class ResponseTest extends TestCase
{
  public function testResponse()
  {
    $result = response('Show some output', []);
    $this->assertEquals([
      'headers' => [
        'Content-Type' => 'text/html'
      ],
      'body' => 'Show some output'
    ], $result);
  }

  public function testSetHeader()
  {
    $result = setHeader('Content-Type', 'application/json', []);
    $this->assertEquals([
      'headers' => [
        'Content-Type' => 'application/json'
      ],
      'body' => ''
    ], $result);
  }

  public function testStatusCode()
  {
    $result = statusCode(400, []);
    $this->assertEquals([
      'headers' => [
        'Content-Type' => 'text/html'
      ],
      'body' => '',
      'status' => 400
    ], $result);
  }

  public function testSetResponseWithSetHeaderAndSend()
  {
    $contentType = setHeader('Content-Type');
    $cacheControl = setHeader('Cache-Control');

    $result = statusCode(201)(response(json_encode(['name' => 'bob']))($cacheControl('public, max-age=31536000')($contentType('application/json', []))));

    $this->assertEquals([
      'headers' => [
        'Content-Type' => 'application/json',
        'Cache-Control' => 'public, max-age=31536000'
      ],
      'body' => '{"name":"bob"}',
      'status' => 201
    ], $result);
  }
}
