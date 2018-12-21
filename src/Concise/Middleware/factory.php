<?php

namespace Concise\Middleware\Factory;

use function Concise\FP\curry;
use function Concise\FP\partial;
use function Concise\FP\compose;

function createMiddlewareFunction()
{
  return function ($middlewareFn, $routeHandler, $middlewareParams) {
    return partial($middlewareFn, [ $routeHandler, $middlewareParams ]);
  };
}

function create($middlewareFn)
{
  return curry(createMiddlewareFunction())($middlewareFn);
}
