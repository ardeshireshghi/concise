<?php

namespace SuperServer\Http\Session;

use function SuperServer\Middleware\Factory\create as createMiddleware;
use function SuperServer\FP\ifElse;

const DEFAULT_SESSION_NAME = 'super_server_fp_';
const DEFAULT_SESSION_LIFETIME_SECONDS = 3600;

function defaultConfig()
{
  return [
    'lifetime'     => DEFAULT_SESSION_LIFETIME_SECONDS,
    'session_name'  => DEFAULT_SESSION_NAME
  ];
}

function initialise($config)
{
  $config = (count($config) === 0) ? defaultConfig() : $config;
  if (!headers_sent()) {
    ini_set('session.name', $config['session_name']);
    ini_set('session.gc_maxlifetime', $config['lifetime']);
    session_set_cookie_params($config['lifetime']);

    if (isset($config['save_path']) && file_exists($config['save_path'])) {
      session_save_path(realpath($config['save_path']));
    }
    session_start();
  }
}

function middlewareHandler()
{
  return function (callable $routeHandler, array $middlewareParams = array(), array $reqParams = array()) {
    initialise($middlewareParams);
    return $routeHandler($reqParams);
  };
}

function middleware($routeHandler)
{
  return createMiddleware(middlewareHandler())($routeHandler);
}

function set(array $sessions = array())
{
  return ifElse(function ($sessions) {
    return count($sessions) > 0;
  })(function ($sessions) {
    array_walk($sessions, function ($sessionValue, $sessionKey) {
      $_SESSION[$sessionKey] = $sessionValue;
    });
    return $_SESSION;
  })(function () {
    return $_SESSION;
  })($sessions);
}
