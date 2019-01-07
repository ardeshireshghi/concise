<?php

namespace Concise\Middleware\Reducer;

use function Concise\FP\reduce;

function combine(...$thisArgs)
{
  return reduce(function ($handlerWrapped, $currentMiddleware) {
    return $currentMiddleware($handlerWrapped)([]);
  })(...$thisArgs);
}
