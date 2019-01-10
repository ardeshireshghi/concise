<?php

use PHPUnit\Framework\TestCase;
use function Concise\Http\Adapter\response as responseAdapter;
use TestUtils\RawRequestBody;

class ResponseAdapterTest extends TestCase
{

  public function testResponseAdapterBodyOutput()
  {
    $response = [
      'headers' => [
        'Content-Type'  => 'application/json',
        'Cache-Control' => 'max-age=0'
      ],

      'body' => 'some output'
    ];

    ob_start();
    responseAdapter($response);

    $output = ob_get_clean();

    $this->assertEquals('some output', $output);
  }
}
