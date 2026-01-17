# Espresso PHP Router
[![PHP](https://img.shields.io/badge/php-%3E%3D8.0-8892BF.svg)](https://www.php.net/)

A lightweight PHP router for quickly building microservices and APIs. Development happens in `src/` (PSR-4); a single-file artifact is produced in `dist/` for simple deployments.

## Inspiration

I created Espresso to solve a specific problem: I wanted expressive routing with middleware while supporting limited PHP environments such as cPanel/shared hosting. No build process or dependencies necessary. Espresso is heavily inspired by [Hono](https://hono.dev/), so Node.js developers familiar with that API should feel comfortable using Espresso.

## Requirements

- PHP 8.0 or higher

## Installation

Preferred (Composer):

```bash
composer require alecbush/espresso
```

Or download the single-file build (for simple deployments). Use the command appropriate
for your operating system or shell:

macOS / Linux:

```bash
curl -O https://raw.githubusercontent.com/alecbush/espresso/main/dist/espresso.php
```

Windows (PowerShell):

```powershell
# Preferred: use PowerShell's cmdlet
Invoke-WebRequest -Uri "https://raw.githubusercontent.com/alecbush/espresso/main/dist/espresso.php" -OutFile "espresso.php"

# Or call the real curl executable if you have it installed
curl.exe -O https://raw.githubusercontent.com/alecbush/espresso/main/dist/espresso.php
```

Windows (cmd):

```cmd
powershell -Command "Invoke-WebRequest -Uri 'https://raw.githubusercontent.com/alecbush/espresso/main/dist/espresso.php' -OutFile 'espresso.php'"
```

WSL (Windows Subsystem for Linux):

```bash
wsl curl -O https://raw.githubusercontent.com/alecbush/espresso/main/dist/espresso.php
```

Note: PowerShell ships an alias named `curl` that maps to `Invoke-WebRequest` — using
`curl -O` in PowerShell can prompt for parameters. Use `curl.exe` or `Invoke-WebRequest`
as shown above.

## Quick Start

Recommended (Composer / autoload):

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
```

If you prefer the single-file build from `dist/`:

```php
<?php
require_once __DIR__ . '/espresso.php';
```
Minimal application:

```php
use Espresso\Espresso;

$app = new Espresso();

$app->get('/', function ($c) {
    return $c->text('Hello World');
});

$app->run();
```

## Usage

### Basic Routing

```php
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

$app->run();
```

### Route Parameters

```php
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

### Query Parameters

```php
$app->get('/search', function ($c) {
    $query = $c->req->query('q');
    $page = $c->req->query('page') ?? 1;
    
    return $c->json([
        'query' => $query,
        'page' => (int)$page
    ]);
});
```

### Request Body

```php
$app->post('/items', function ($c) {
    $body = $c->req->parseBody();
    return $c->json($body, 201);
});
```

### Middleware

```php
// Global middleware
$app->use(function ($c, $next) {
    error_log($c->req->path());
    return $next();
});

// Path-specific middleware
$app->use('/admin/*', function ($c, $next) {
    $token = $c->req->header('Authorization');
    
    if (!$token) {
        return $c->json(['error' => 'Unauthorized'], 401);
    }
    
    return $next();
});
```

### Error Handling

```php
$app->notFound(function ($c) {
    return $c->json(['error' => 'Not Found'], 404);
});

$app->onError(function ($err, $c) {
    return $c->json(['error' => $err->getMessage()], 500);
});
```

### Mounting Sub-Applications

```php
// Create a sub-application for API routes
$api = new Espresso();
$api->get('/users', function ($c) {
    return $c->json(['users' => []]);
});
$api->get('/items', function ($c) {
    return $c->json(['items' => []]);
});

// Mount the sub-app at /api
$app = new Espresso();
$app->mount('/api', $api);

// Now /api/users and /api/items are available
$app->run();
```

## API Reference

### Application Methods

- `get(path, handler)` - Register GET route
- `post(path, handler)` - Register POST route
- `put(path, handler)` - Register PUT route
- `delete(path, handler)` - Register DELETE route
- `patch(path, handler)` - Register PATCH route
- `options(path, handler)` - Register OPTIONS route
- `all(path, handler)` - Register route for all methods
- `use(middleware)` - Add global middleware
- `use(path, middleware)` - Add path-specific middleware
- `notFound(handler)` - Set 404 handler
- `onError(handler)` - Set error handler
- `run()` - Start the application

### Context Methods

**Request:**
- `$c->req->param(key)` - Get route parameter
- `$c->req->query(key)` - Get query parameter
- `$c->req->header(key)` - Get request header
- `$c->req->method()` - Get request method
- `$c->req->path()` - Get request path
- `$c->req->json()` - Parse JSON body
- `$c->req->parseBody()` - Parse body (JSON or form data)

**Response:**
- `$c->text(text, status?)` - Return text response
- `$c->json(data, status?)` - Return JSON response
- `$c->html(html, status?)` - Return HTML response
- `$c->redirect(url, status?)` - Redirect to URL
- `$c->header(key, value)` - Set response header
- `$c->status(code)` - Set response status

**Context:**
- `$c->set(key, value)` - Set context variable
- `$c->get(key)` - Get context variable

## Server Configuration

### Apache (.htaccess)

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [L]
```

### Nginx

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### PHP Built-in Server

```bash
php -S localhost:8000
```

## Examples

See the `examples/` directory for complete examples:

- `basic.php` - Basic routing
- `middleware.php` - Middleware usage
- `sub-routing.php` - Sub-routing demo

## Testing

```bash
php tests/index.php
```

## License

MIT
