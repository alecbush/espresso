# Usage

## Quick Start

### Recommended (Composer / autoload)

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
```

### If you prefer the single-file build from `dist/`

```php
<?php
require_once __DIR__ . '/espresso.php';
```

### Minimal application

```php
<?php

use Espresso\Espresso;

$app = new Espresso();

$app->get('/', function ($c) {
    return $c->text('Hello World');
});

$app->run();
```

## Basic Routing

```php
<?php

$app = new Espresso();

$app->get('/items', function ($c) {
    return $c->json(['items' => []]);
});

$app->post('/items', function ($c) {
    return $c->json(['created' => true], 201);
});

$app->put('/items/:id', function ($c) {
    $id = $c->req->param('id');
    return $c->json(['id' => $id, 'updated' => true]);
});

$app->delete('/items/:id', function ($c) {
    $id = $c->req->param('id');
    return $c->json(['id' => $id, 'deleted' => true]);
});

$app->query('/items/search', function ($c) {
    $payload = $c->req->raw();
    return $c->json([
        'method' => $c->req->method(),
        'payload' => $payload
    ]);
});

$app->run();
```

## Route Parameters

```php
<?php

$app->get('/users/:id', function ($c) {
    $id = $c->req->param('id');
    return $c->json(['id' => $id]);
});

$app->get('/users/:userId/items/:itemId', function ($c) {
    $userId = $c->req->param('userId');
    $itemId = $c->req->param('itemId');
    return $c->json(['userId' => $userId, 'itemId' => $itemId]);
});

// Shorthand username route: captures a username after an @ prefix
$app->get('/@:username', function ($c) {
    $username = $c->req->param('username');
    return $c->json([
        'username' => $username,
        'profile' => 'Profile of ' . $username
    ]);
});
```

## Query Parameters

```php
<?php

$app->get('/search', function ($c) {
    $query = $c->req->query('q');
    $page = $c->req->query('page') ?? 1;
    
    return $c->json([
        'query' => $query,
        'page' => (int)$page
    ]);
});
```