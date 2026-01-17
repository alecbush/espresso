<?php
/**
 * Sub-routing example
 *
 * This example demonstrates mounting multiple sub-applications under different
 * path prefixes. Each sub-app defines its own routes relative to the mount
 * point. Routes here simply return JSON messages to keep the focus on
 * sub-routing rather than functional behavior.
 */

require __DIR__ . '/../src/Espresso.php';

use Espresso\Espresso;

// Create a sub-app for API routes
$api = new Espresso();
$api->get('/items', function ($c) {
    return $c->json(['message' => 'API: list items (demo)']);
});
$api->get('/items/:id', function ($c) {
    $id = (int)$c->req->param('id');
    return $c->json(['message' => 'API: get item (demo)', 'id' => $id]);
});

// Create a separate sub-app for admin routes
$admin = new Espresso();
$admin->get('/dashboard', function ($c) {
    return $c->json(['message' => 'Admin: dashboard (demo)']);
});

// Main application
$app = new Espresso();

$app->get('/', function ($c) {
    $html = <<<HTML
    <h1>Sub-routing Example</h1>
    <p>This app mounts two sub-applications:</p>
    <ul>
        <li><strong>/api</strong> — API routes (e.g. <em>/api/items</em>)</li>
        <li><strong>/admin</strong> — Admin area (e.g. <em>/admin/dashboard</em>)</li>
    </ul>
    <p>Each sub-application defines routes relative to its mount path.</p>
    HTML;
    return $c->html($html);
});

// Mount sub-apps under different prefixes
$app->mount('/api', $api);
$app->mount('/admin', $admin);

// Run the main app
$app->run();
