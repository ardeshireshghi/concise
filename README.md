<img src="https://raw.githubusercontent.com/ardeshireshghi/concise/master/images/logo.png" width="400" alt="Concise">

# Concise: Functional micro web framework in PHP

ConcisePHP is a PHP micro-framework that makes creating powerful web applications and APIs seemless.

### Prerequisites

You need PHP with version higher than 7 in order to use Concise.

```
PHP >7.0.0
```

### Installation

A step by step series of examples that tell you how to get a development env running

Say what the step will be

```
composer require ardeshireshghi/concise
```

### Usage (Hello world)

Create `app.php` file and add the following content:

```
<?php

require './vendor/autoload.php';

use function Concise\app;
use function Concise\Routing\get;
use function Concise\Http\Response\response;

$middlewares = [];

app([
  get('/welcome/:name')(function ($params) {
    return response('Welcome to Concise, ' . $params['name']);
  }))
])($middlewares);

```

And try to run it using PHP server:

```bash
$ php -S localhost:5000 app.php
```

Going to http://localhost:5000/welcome/coder will now display "Welcome to Concise, coder".

There is also an example API script in `/examples/web-api/api.php`.

## Tests

To execute the test suite, you'll need phpunit.

```bash
$ phpunit --colors
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
