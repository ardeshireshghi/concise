<?php
use PHPUnit\Framework\TestCase;
use function Concise\Http\Request\url;
use function Concise\Http\Request\headers;

class RequestTest extends TestCase
{

  public function teardown()
  {
    unset(
      $_SERVER['CONTENT_LENGTH'],
      $_SERVER['CONTENT_TYPE'],
      $_SERVER['HTTP_CONNECTION'],
      $_SERVER['HTTP_HOST'],
      $_SERVER['HTTP_KEEP_ALIVE'],
      $_SERVER['HTTP_X_FORWARDED_HOST'],
      $_SERVER['REQUEST_URI']
    );
  }

  public function testUrlWithNoProxy()
  {
    $_SERVER['HTTP_HOST'] = 'bbc.co.uk';
    $_SERVER['REQUEST_URI'] = '/homepage?v=5';
    // isset($server['HTTP_X_FORWARDED_HOST']) ? $server['HTTP_X_FORWARDED_HOST'] : $server['HTTP_HOST'];

    $this->assertEquals('http://bbc.co.uk/homepage?v=5', url());
  }

  public function testUrlWithProxy()
  {
    $_SERVER['HTTP_HOST'] = '172.10.0.56'; // some proxy IP (e.g. Load balancer)
    $_SERVER['HTTP_X_FORWARDED_HOST'] = 'bbc.co.uk';
    $_SERVER['REQUEST_URI'] = '/homepage?v=5';

    $this->assertEquals('http://bbc.co.uk/homepage?v=5', url());
  }

  public function testHeaders()
  {
    $_SERVER['HTTP_HOST'] = '172.10.0.56'; // some proxy IP (e.g. Load balancer)
    $_SERVER['HTTP_X_FORWARDED_HOST'] = 'bbc.co.uk';
    $_SERVER['HTTP_CONNECTION'] = 'keep-alive';

    $this->assertEquals([
      'Host' => '172.10.0.56',
      'X-Forwarded-Host' => 'bbc.co.uk',
      'Connection' => 'keep-alive'
    ], headers());
  }

  public function testContentPrefixHeaders()
  {
    $_SERVER['HTTP_HOST'] = '172.10.0.56'; // some proxy IP (e.g. Load balancer)
    $_SERVER['HTTP_X_FORWARDED_HOST'] = 'bbc.co.uk';
    $_SERVER['HTTP_CONNECTION'] = 'keep-alive';
    $_SERVER['CONTENT_LENGTH'] = 103043;

    $this->assertEquals([
      'Host' => '172.10.0.56',
      'X-Forwarded-Host' => 'bbc.co.uk',
      'Connection' => 'keep-alive',
      'Content-Length' => 103043
    ], headers());

    unset($_SERVER['CONTENT_LENGTH'], $_SERVER['HTTP_X_FORWARDED_HOST'], $_SERVER['HTTP_CONNECTION']);
  }
}


