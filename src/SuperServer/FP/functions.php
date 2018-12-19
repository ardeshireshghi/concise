<?php

namespace SuperServer\FP;

const CURRY_PLACEHOLDER = 'curry_placeholder';

function partial(callable $fn, array $args) {
  return function() use ($fn, $args) {
    $mergedArgs = array_merge($args, func_get_args());
    return call_user_func_array($fn, $mergedArgs);
  };
}

function curry(callable $fn, $arity = null) {
  $reflectionfn = new \ReflectionFunction($fn);
  $args = $reflectionfn->getParameters();
  $currentArity = ($arity !== null) ? $arity : count($args);

  $curryFunction = function() use ($currentArity, $fn) {
    $thisArgs = func_get_args();
    $argCount = count($thisArgs);

    // Call original function when arity matches
    if ($argCount === $currentArity) {
      return call_user_func_array($fn, $thisArgs);
    }

    $newFn = partial($fn, $thisArgs);
    return curry($newFn, $currentArity - $argCount);
  };

  return $curryFunction;
}

function map(callable $fn, array $data = null) {
  $handlerFn = 'array_map';

  if (is_array($data)) {
    return $handlerFn($fn, $data);
  }

  $curriedMap = curry($handlerFn);
  return $curriedMap($fn);
}

function filter(callable $fn, array $data = null) {
  $handlerFn = function($fn, $data) {
    return array_filter($data, $fn);
  };

  if (is_array($data)) {
    return $handlerFn($fn, $data);
  }

  $curriedFilter = curry($handlerFn);
  return $curriedFilter($fn);
}

function reduce(callable $fn, $initialValue = [], array $data = null) {
  $handlerFn = function($fn, $initialValue, $data) {
    $acc = $initialValue;

    foreach ($data as $index => $value) {
      $acc = $fn($acc, $value, $index, $data);
    }

    return $acc;
  };

  if (is_array($data)) {
    return $handlerFn($fn, $initialValue, $data);
  }

  $curriedReduce = curry($handlerFn);
  return $curriedReduce($fn, $initialValue);
}

function compose() {
  $fns = array_reverse(func_get_args());
  return function($data) use ($fns) {
    return reduce(function($acc, $currentFn) {
      return $currentFn($acc);
    }, $data, $fns);
  };
}

function ifElse() {
  return call_user_func_array(curry(function($conditionFn, $trueCallback, $falseCallback, $data) {
    if ($conditionFn($data)) {
      $trueCallback($data);
    } else {
      $falseCallback($data);
    }
  }), func_get_args());
}

function allPass() {
  $andFn = function() {
    $initialState = true;
    return reduce(function($res, $conditionCanBeFn) {
      return (is_callable($conditionCanBeFn)) ?
      ($res && $conditionCanBeFn()) :
        $res && ($conditionCanBeFn);
    }, $initialState, func_get_args());
  };

  $thisArgs = func_get_args();

  // Return partial when only one arg is passed
  if (count($thisArgs) === 1) {
    return partial($andFn, [ $thisArgs[0] ]);
  }

  return call_user_func_array($andFn, $thisArgs);
}

function somePass() {
  $orFn = function() {
    $initialState = false;
    return reduce(function($res, $conditionCanBeFn) {
      return (is_callable($conditionCanBeFn)) ?
        ($res || $conditionCanBeFn()) :
        $res || $conditionCanBeFn;
    }, $initialState, func_get_args());
  };

  $thisArgs = func_get_args();

  // Return partial when only one arg is passed
  if (count($thisArgs) === 1) {
    return partial($orFn, [ $thisArgs[0] ]);
  }

  return call_user_func_array($orFn, $thisArgs);
}
