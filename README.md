# Espresso PHP Router
[![PHP](https://img.shields.io/badge/php-%3E%3D8.0-8892BF.svg)](https://www.php.net/)

A lightweight PHP router for quickly building microservices and APIs. No build process or dependencies necessary.

## Requirements

- PHP 8.0 or higher

## Installation

```bash
composer require alecbush/espresso
```

Or download the single-file build:

```bash
curl -O https://raw.githubusercontent.com/alecbush/espresso/main/dist/espresso.php
```

## Quick Start

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Espresso\Espresso;

$app = new Espresso();

$app->get('/', function ($c) {
    return $c->text('Hello World');
});

$app->run();
```

## Documentation

For full documentation, examples, and API reference, see the [docs](docs/) directory.

## License

MIT
