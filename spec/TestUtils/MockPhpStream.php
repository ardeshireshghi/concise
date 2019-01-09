<?php

namespace TestUtils;

/**
 * @see http://news-from-the-basement.blogspot.com/2011/07/mocking-phpinput.html
 */
class MockPhpStream
{
  protected $index = 0;
  protected $length = null;
  protected $data = '';

  public $context;

  public function __construct()
  {
    if (file_exists($this->buffer_filename())) {
      $this->data = file_get_contents($this->buffer_filename());
    } else {
      $this->data = '';
    }
    $this->index = 0;
    $this->length = strlen($this->data);
  }

  protected function buffer_filename()
  {
    return sys_get_temp_dir().'/php_input.txt';
  }

  public function stream_open($path, $mode, $options, &$opened_path)
  {
    return true;
  }

  public function stream_close()
  {
  }

  public function stream_stat()
  {
    return array();
  }

  public function stream_flush()
  {
    return true;
  }

  public function stream_read($count)
  {
    if (is_null($this->length) === true) {
      $this->length = strlen($this->data);
    }
    $length = min($count, $this->length - $this->index);
    $data = substr($this->data, $this->index);
    $this->index = $this->index + $length;
    return $data;
  }

  public function stream_eof()
  {
    return ($this->index >= $this->length ? true : false);
  }

  public function stream_write($data)
  {
    return file_put_contents($this->buffer_filename(), $data);
  }
}
