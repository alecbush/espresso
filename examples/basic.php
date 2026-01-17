<?php
/**
 * Basic usage example
 * 
 * This example demonstrates basic routing capabilities of the Espresso framework.
 * It includes simple text and JSON responses, route parameters, and query parameters.
 */

require __DIR__ . '/../src/Espresso.php';

use Espresso\Espresso;

$app = new Espresso();

// Simple text response
$app->get('/', function ($c) {
    return $c->text('Hello World');
});

// JSON response
$app->get('/api/users', function ($c) {
    return $c->json([
        'users' => [
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob']
        ]
    ]);
});

// Route parameters
$app->get('/users/:id', function ($c) {
    $id = $c->req->param('id');
    return $c->json(['id' => $id, 'name' => 'User ' . $id]);
});

// Query parameters
$app->get('/search', function ($c) {
    $query = $c->req->query('q');
    $page = $c->req->query('page') ?? 1;
    
    return $c->json([
        'query' => $query,
        'page' => (int)$page
    ]);
});

$app->run();
