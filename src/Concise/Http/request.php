<?php

namespace Concise\Http\Request;

const URL_TRIM_CHAR = '/';

function url()
{
  return (isset($_SERVER['HTTPS']) &&
  $_SERVER['HTTPS'] === 'on' ? "https" : "http")."://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}

function path()
{
  return rtrim(rawPath(), URL_TRIM_CHAR);
}

function rawPath()
{
  return parse_url(url(), PHP_URL_PATH);
}

function method()
{
  return $_SERVER['REQUEST_METHOD'];
}

function parseRouteParamsFromPath($route)
{
  preg_match($route['regex'], path(), $params);
  return array_diff_key($params, [0 => true, 1 => true]);
}
