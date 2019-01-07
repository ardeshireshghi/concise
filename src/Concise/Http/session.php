<?php

namespace Concise\Http\Session;

use function Concise\Middleware\Factory\create as createMiddleware;
use function Concise\FP\ifElse;

const DEFAULT_SESSION_NAME = 'super_server_fp_';
const DEFAULT_SESSION_LIFETIME_SECONDS = 3600;

function defaultConfig()
{
  return [
    'lifetime'      => DEFAULT_SESSION_LIFETIME_SECONDS,
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
  return function (callable $nextRouteHandler, $middlewareParams, array $request = array()) {
    initialise($middlewareParams);
    return $nextRouteHandler($request);
  };
}

function middleware(...$thisArgs)
{
  return createMiddleware(middlewareHandler())(...$thisArgs);
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
