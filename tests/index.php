<?php
/**
 * Test file for Espresso
 * Run: php tests/index.php
 */

require __DIR__ . '/../src/Espresso.php';

use Espresso\Espresso;

echo "Testing Espresso\n";
echo str_repeat("=", 50) . "\n\n";

$app = new Espresso();

// Setup routes
$app->get('/', function ($c) {
    return $c->text('Hello!');
});

$app->get('/api/hello', function ($c) {
    return $c->json([
        'ok' => true,
        'message' => 'Hello!'
    ]);
});

$app->get('/items/:id', function ($c) {
    $id = $c->req->param('id');
    return $c->json(['id' => $id, 'title' => 'Item ' . $id]);
});

$app->post('/items', function ($c) {
    return $c->json(['created' => true], 201);
});

$app->delete('/items/:id', function ($c) {
    $id = $c->req->param('id');
    return $c->text("Item $id deleted");
});

$app->get('/users/:userId/items/:itemId', function ($c) {
    return $c->json([
        'userId' => $c->req->param('userId'),
        'itemId' => $c->req->param('itemId')
    ]);
});

// Middleware test
$requestCount = 0;
$app->use(function ($c, $next) use (&$requestCount) {
    $requestCount++;
    return $next();
});

// Test function
function test(string $name, callable $testFn): void {
    echo "Test: $name ... ";
    try {
        $testFn();
        echo "[PASS]\n";
    } catch (Exception $e) {
        echo "[FAIL]: {$e->getMessage()}\n";
    }
}

// Run tests
test("GET /", function () use ($app) {
    $response = $app->fetch('GET', '/');
    if ($response->getBody() !== 'Hello!') {
        throw new Exception("Expected 'Hello!', got '{$response->getBody()}'");
    }
    if ($response->getStatus() !== 200) {
        throw new Exception("Expected status 200, got {$response->getStatus()}");
    }
});

test("GET /api/hello returns JSON", function () use ($app) {
    $response = $app->fetch('GET', '/api/hello');
    $data = json_decode($response->getBody(), true);
    if (!$data['ok'] || $data['message'] !== 'Hello!') {
        throw new Exception("JSON response incorrect");
    }
});

test("GET /items/:id with parameter", function () use ($app) {
    $response = $app->fetch('GET', '/items/123');
    $data = json_decode($response->getBody(), true);
    if ($data['id'] !== '123') {
        throw new Exception("Expected id '123', got '{$data['id']}'");
    }
});

test("POST /items", function () use ($app) {
    $response = $app->fetch('POST', '/items');
    if ($response->getStatus() !== 201) {
        throw new Exception("Expected status 201, got {$response->getStatus()}");
    }
});

test("DELETE /items/:id", function () use ($app) {
    $response = $app->fetch('DELETE', '/items/456');
    if (!str_contains($response->getBody(), '456')) {
        throw new Exception("Response should contain item id");
    }
});

test("Multiple route parameters", function () use ($app) {
    $response = $app->fetch('GET', '/users/10/items/20');
    $data = json_decode($response->getBody(), true);
    if ($data['userId'] !== '10' || $data['itemId'] !== '20') {
        throw new Exception("Multiple parameters not working correctly");
    }
});

test("404 Not Found", function () use ($app) {
    $response = $app->fetch('GET', '/nonexistent');
    if ($response->getStatus() !== 404) {
        throw new Exception("Expected 404, got {$response->getStatus()}");
    }
});

test("Middleware execution", function () use ($requestCount) {
    if ($requestCount < 6) {
        throw new Exception("Middleware should have run at least 6 times, ran $requestCount times");
    }
});

echo "\n" . str_repeat("=", 50) . "\n";
echo "Tests Complete ($requestCount)\n";
