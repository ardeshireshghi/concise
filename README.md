<img src="https://raw.githubusercontent.com/ardeshireshghi/concise/master/images/logo.png" width="400" alt="Concise">

# Concise: Functional micro web framework in PHP

ConcisePHP is a PHP micro-framework that makes creating [fast](#benchmarking) and powerful Web applications and APIs seemless.

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
composer require ardeshireshghi/concise:0.4.1
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
use function Concise\FP\curry;
use function Concise\FP\ifElse;

function createLogger()
{
  $outputFileHandler = fopen('php://stdout', 'w');

  return function (string $message, array $context = null) use ($outputFileHandler) {
    fwrite($outputFileHandler, $message);
    return $context;
  };
}

/**
* Curried logger which logs message and returns the context
*
* @param string $message Message to output
* @param array $context to be passed to the next function
* @return mixed Either the curried function or the array context
*/
function logger(...$thisArgs)
{
  static $logger = null;

  if (!$logger) {
    $logger = createLogger();
  }

  return curry($logger)(...$thisArgs);
}

function loggerMiddleware()
{
  return createMiddleware(function (callable $nextRouteHandler, array $middlewareParams = [], array $request) {
    return $nextRouteHandler(ifElse(
      function($request) {
        return $request['meta']['hasRouteMatch'];
      },
      logger("\n\nRoute with path".path().' matching. Params are: '.implode(',', $request['params'])."\n"),
      logger("\nNo route matching for: ".url(). "\n")
    )(logger("\n\nRequest: ".json_encode($request))($request)));
  });
}

function routes()
{
  return [
    get('/hello/:name')(function ($request) {
      return response('Welcome to Concise, ' . $request['params']['name'], []);
    })
  ];
}

function middlewares()
{
  return [
    loggerMiddleware()
  ];
}

logger("\nResponse: ".json_encode(app(routes())(middlewares()))."\n")([]);
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

### Example Web API script

There is also an example API script in `/examples/web-api/api.php`. After cloning the repo, you can run it with:

```bash
$ composer run-script start:web_api --timeout 0
```

Use the following to test both the `authMiddleware` and parsing of JSON payload:

```bash
curl -X POST -H 'Authorization: Bearer abcd1234efgh5678' -H 'Content-Type: application/json' --data '{"filename": "test.jpg"}' http://127.0.0.1:5000/api/upload

```
## Benchmarking

The benchmark is done on a 1 core cpu 2GB RAM Centos 7.5 cloud server on DigitalOcean after setting up Nginx with default config, php-fpm (PHP 7.3). The performance has been compared with Laravel, by disabling the Laravel middlewares in `Http/Kernel.php` and changing the home controller to just say 'Hello world'. Also using the above code example for `Concise` also without the logging middleware. The test sends 500 requests to each app with the concurrency of 50 requests. Below is the output of Apache Bench command:

### Concise result

`$  ab -n 500 -c 50 http://localhost:82/hello/world`
```
This is ApacheBench, Version 2.3 <$Revision: 1430300 $>
Copyright 1996 Adam Twiss, Zeus Technology Ltd, http://www.zeustech.net/
Licensed to The Apache Software Foundation, http://www.apache.org/

Benchmarking 127.0.0.1 (be patient)
Completed 100 requests
Completed 200 requests
Completed 300 requests
Completed 400 requests
Completed 500 requests
Finished 500 requests


Server Software:        nginx/1.12.2
Server Hostname:        127.0.0.1
Server Port:            82

Document Path:          /hello/ardi
Document Length:        24 bytes

Concurrency Level:      50
Time taken for tests:   1.154 seconds
Complete requests:      500
Failed requests:        0
Write errors:           0
Total transferred:      151500 bytes
HTML transferred:       12000 bytes
Requests per second:    433.18 [#/sec] (mean)
Time per request:       115.425 [ms] (mean)
Time per request:       2.308 [ms] (mean, across all concurrent requests)
Transfer rate:          128.18 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:        0    2   0.9      2       4
Processing:     7  110  25.8    114     162
Waiting:        5  108  25.4    113     160
Total:          8  111  25.7    117     163

Percentage of the requests served within a certain time (ms)
  50%    117
  66%    119
  75%    122
  80%    123
  90%    123
  95%    163
  98%    163
  99%    163
 100%    163 (longest request)
```

### Laravel result

`$  ab -n 500 -c 50 http://localhost:81/`
```
This is ApacheBench, Version 2.3 <$Revision: 1430300 $>
Copyright 1996 Adam Twiss, Zeus Technology Ltd, http://www.zeustech.net/
Licensed to The Apache Software Foundation, http://www.apache.org/

Benchmarking 127.0.0.1 (be patient)
Completed 100 requests
Completed 200 requests
Completed 300 requests
Completed 400 requests
Completed 500 requests
Finished 500 requests


Server Software:        nginx/1.12.2
Server Hostname:        127.0.0.1
Server Port:            81

Document Path:          /
Document Length:        11 bytes

Concurrency Level:      50
Time taken for tests:   15.738 seconds
Complete requests:      500
Failed requests:        0
Write errors:           0
Total transferred:      122500 bytes
HTML transferred:       5500 bytes
Requests per second:    31.77 [#/sec] (mean)
Time per request:       1573.850 [ms] (mean)
Time per request:       31.477 [ms] (mean, across all concurrent requests)
Transfer rate:          7.60 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:        0    0   0.5      0       2
Processing:   202 1535 228.8   1525    2180
Waiting:      200 1535 228.7   1525    2180
Total:        203 1535 228.8   1525    2180

Percentage of the requests served within a certain time (ms)
  50%   1525
  66%   1574
  75%   1648
  80%   1690
  90%   1837
  95%   1913
  98%   2048
  99%   2102
 100%   2180 (longest request)
```

### Benchmark Conclusion

`Concise` serves request 14 times faster (with mean of 433 requests/second vs Laravel's 32 requests/second)

This might not be a great comparison as Laravel is a Framework which is naturally heavier and Concise is a micro framework which is light. But it showcases that `Concise` can definitely be a good choice for building micro services.

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us.

## Authors

* **Ardeshir Eshghi** - [ardeshireshghi](https://github.com/ardeshireshghi)


## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

## Acknowledgments

* This framework/library is inspired by functional language frameworks like Clojure [Ring](https://github.com/ring-clojure/ring)

* Many of the functional language helper functions are inspired by JS Rambda library [Rambda](https://ramdajs.com/docs/)
