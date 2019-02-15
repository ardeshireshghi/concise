<?php

namespace Concise\Middleware\Factory;

use function Concise\FP\curry;
use function Concise\FP\partial;
use function Concise\FP\compose;

function create($middlewareFn)
{
  return curry(function ($middlewareFn, $routeHandler, $middlewareParams) {
    return curry($middlewareFn)($routeHandler, $middlewareParams);
  })($middlewareFn);
}
