<?php

namespace TestUtils;

class RawRequestBody
{
  public function __construct($rawBody = '')
  {
    stream_wrapper_unregister('php');
    stream_wrapper_register('php', MockPhpStream::class);

    file_put_contents('php://input', $rawBody);
  }

  public function empty()
  {
    stream_wrapper_restore('php');
  }
}
