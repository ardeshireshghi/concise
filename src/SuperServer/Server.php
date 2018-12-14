<?php

namespace SuperServer;

class Server
{
  const ALL_METHOD = 'all';
  const VALID_METHODS = ['GET', 'POST', 'PUT', 'DELETE'];

  public static function create()
  {
    return new static();
  }

  public function __construct(array $routes = array())
  {
    $this->routes = $routes;
    $this->url = $this->_getUrl();
  }

  public function start()
  {
    if ($this->_routeMatches()) {
      $this->_invokeHandler();
    } else {
      $error = new \Exception('Invalid route');
      if (isset($this->errorHandler)) {
        $errorHandler = $this->errorHandler;
        $errorHandler($error);
      } else {
        throw $error;
      }
    }
  }

  public function addRoute($methodName, $path, callable $handler)
  {
    if ($methodName === static::ALL_METHOD) {
      foreach (static::VALID_METHODS as $method) {
        $this->addRoute($method, $path, $handler);
      }
    } else {
      $this->routes["$methodName$path"] = array(
        'method'  => $methodName,
        'handler' => $handler
      );
    }
  }

  public function error(callable $handler)
  {
    $this->errorHandler = $handler;
  }

  private function _routeMatches()
  {
    return ($this->_getRoute() !== null);
  }

  private function _invokeHandler()
  {
    $handler = $this->_getRoute()['handler'];
    $handler();
  }

  private function _getRoute()
  {
    return array_key_exists("{$this->_reqMethodLowered()}{$this->_reqPath()}", $this->routes) ?
      $this->routes["{$this->_reqMethodLowered()}{$this->_reqPath()}"] :
      null;
  }


  private function _reqPath()
  {
    return rtrim(parse_url($this->url, PHP_URL_PATH), '/');
  }

  private function _reqMethodLowered()
  {
    return strtolower($_SERVER['REQUEST_METHOD']);
  }

  private function _getUrl()
  {
    return (isset($_SERVER['HTTPS']) &&
      $_SERVER['HTTPS'] === 'on' ? "https" : "http")."://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
  }

  public function __call($method, $args)
  {
    if (in_array(strtoupper($method), static::VALID_METHODS) || $method === static::ALL_METHOD) {
      array_unshift($args, $method);
      call_user_func_array(array($this, 'addRoute'), $args);
    }

    return false;
  }
}
