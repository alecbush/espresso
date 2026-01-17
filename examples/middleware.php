<?php
/**
 * Middleware example
 * 
 * This example demonstrates the use of middleware functions in an Espresso
 * application. It includes global middleware for logging request details and
 * path-specific middleware for authentication on protected routes.
 */

require __DIR__ . '/../src/Espresso.php';

use Espresso\Espresso;

$app = new Espresso();

// Global middleware - logging
$app->use(function ($c, $next) {
    $start = microtime(true);
    $response = $next();
    $duration = round((microtime(true) - $start) * 1000, 2);
    error_log("[{$c->req->method()}] {$c->req->path()} - {$duration}ms");
    return $response;
});

// Path-specific middleware - authentication
$app->use('/admin/*', function ($c, $next) {
    $token = $c->req->header('Authorization');
    
    if (!$token || $token !== 'Bearer secret-token') {
        return $c->json(['error' => 'Unauthorized'], 401);
    }
    
    return $next();
});

$app->get('/', function ($c) {
    return $c->text('Public route');
});

$app->get('/admin', function ($c) {
    return $c->text('Protected route');
});

$app->get('/admin/users', function ($c) {
    return $c->json(['users' => ['admin1', 'admin2']]);
});

$app->run();
