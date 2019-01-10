<img src="https://raw.githubusercontent.com/ardeshireshghi/concise/master/images/logo.png" width="400" alt="Concise">

# Concise: Functional micro web framework in PHP

ConcisePHP is a PHP micro-framework that makes creating powerful web applications and APIs seemless.

### Prerequisites

You need PHP with version equal or higher than 7.1 in order to use Concise.

```
PHP >7.1
```

On Linux make sure `xml` and `mbstring` PHP modules are installed as well.

### Installation

A step by step series of examples that tell you how to get a development env running

Say what the step will be

```
composer require ardeshireshghi/concise:0.3.3
```

### Usage (Hello world)

Create `app.php` file and add the following content:

```
<?php

require './vendor/autoload.php';

use function Concise\app;
use function Concise\Routing\get;
use function Concise\Http\Response\response;
use function Concise\Http\Request\url;
use function Concise\Http\Request\path;
use function Concise\Middleware\Factory\create as createMiddleware;

function getLogger()
{
  static $logger = null;
  if (!$logger) {
    $outputFileHandler = fopen('php://stdout', 'w');
    $logger = function (string $message) use ($outputFileHandler) {
      fwrite($outputFileHandler, $message);
    };
  }

  return $logger;
}

$loggerMiddleware = createMiddleware(function (callable $nextRouteHandler, array $middlewareParams = [], array $request = []) {
  $logger = getLogger();

  $logger("\n\nRequest: ".json_encode($request));
  if (count($request['params']) > 0) {
    $logger("\nRoute with path".path().' matching. Params are: '.implode(',', $request['params'])."\n");
  } else {
    $logger("\nNo route matching for: ".url(). "\n");
  }

  return $nextRouteHandler($request);
});

$routes = [
  get('/hello/:name')(function ($request) {
    return response('Welcome to Concise, ' . $request['params']['name'], []);
  })
];

$middlewares = [
  $loggerMiddleware
];

getLogger()('Response: '.json_encode(app($routes)($middlewares))."\n");
```

And try to run it using PHP server:

```bash
$ php -S localhost:5000 app.php
```

Going to http://localhost:5000/hello/coder will now display "Welcome to Concise, coder".


## Development

### Installation

```bash
$ composer install
```

### Testing

To execute the test suite, you'll need phpunit.

```bash
$ phpunit --colors
```

### Example Web Api script

There is also an example API script in `/examples/web-api/api.php`. After cloning the repo, you can run it with:

```bash
$ composer run-script start:web_api --timeout 0
```

Use the following to test both the `authMiddleware` and parsing of JSON payload:

```bash
curl -X POST -H 'Authorization: Bearer abcd1234efgh5678' -H 'Content-Type: application/json' --data '{"filename": "test.jpg"}' http://127.0.0.1:5000/api/upload

```

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us.

## Authors

* **Ardeshir Eshghi** - [ardeshireshghi](https://github.com/ardeshireshghi)


## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

## Acknowledgments

* This framework/library is inspired by functional language frameworks like Clojure [Ring](https://github.com/ring-clojure/ring)

* Many of the functional language helper functions are inspired by JS Rambda library [Rambda](https://ramdajs.com/docs/)
