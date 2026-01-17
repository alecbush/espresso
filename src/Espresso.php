<?php
/**
 * Espresso PHP router
 * 
 * @author Alec Bush
 * @version 0.1.0
 * @license MIT
 */

declare(strict_types=1);

namespace Espresso;

/**
 * Context class - Provides access to request and response
 */
class Context
{
    private array $params = [];
    private array $query = [];
    private array $headers = [];
    private int $status = 200;
    private mixed $body = null;
    private string $method;
    private string $path;
    public array $vars = [];

    public function __construct(string $method, string $path)
    {
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->parseQuery();
        $this->headers = function_exists('getallheaders') ? getallheaders() : $this->getAllHeadersCompat();
    }

    private function getAllHeadersCompat(): array
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) === 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    private function parseQuery(): void
    {
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        if ($queryString) {
            parse_str($queryString, $this->query);
        }
    }

    public function getReq(): Request
    {
        return new Request($this);
    }

    public function __get(string $name): mixed
    {
        if ($name === 'req') {
            return $this->getReq();
        }
        return null;
    }

    public function setParam(string $key, string $value): void
    {
        $this->params[$key] = $value;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getQuery(string $key): ?string
    {
        return $this->query[$key] ?? null;
    }

    public function getHeader(string $key): ?string
    {
        $key = strtolower($key);
        foreach ($this->headers as $name => $value) {
            if (strtolower($name) === $key) {
                return $value;
            }
        }
        return null;
    }

    public function header(string $key, string $value): self
    {
        if (!headers_sent()) {
            header("$key: $value");
        }
        return $this;
    }

    public function status(int $code): self
    {
        $this->status = $code;
        if (!headers_sent()) {
            http_response_code($code);
        }
        return $this;
    }

    public function text(string $text, int $status = 200): Response
    {
        $this->status($status);
        $this->header('Content-Type', 'text/plain; charset=UTF-8');
        return new Response($text, $status, 'text/plain');
    }

    public function json(mixed $data, int $status = 200): Response
    {
        $this->status($status);
        $this->header('Content-Type', 'application/json; charset=UTF-8');
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return new Response($json, $status, 'application/json');
    }

    public function html(string $html, int $status = 200): Response
    {
        $this->status($status);
        $this->header('Content-Type', 'text/html; charset=UTF-8');
        return new Response($html, $status, 'text/html');
    }

    public function redirect(string $url, int $status = 302): Response
    {
        $this->status($status);
        if (!headers_sent()) {
            header("Location: $url");
        }
        return new Response('', $status, 'text/plain');
    }

    public function body(): string
    {
        return file_get_contents('php://input');
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function set(string $key, mixed $value): void
    {
        $this->vars[$key] = $value;
    }

    public function get(string $key): mixed
    {
        return $this->vars[$key] ?? null;
    }
}

/**
 * Request class - Provides request information
 */
class Request
{
    private Context $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    public function param(string $key): ?string
    {
        return $this->context->getParams()[$key] ?? null;
    }

    public function query(string $key): ?string
    {
        return $this->context->getQuery($key);
    }

    public function header(string $key): ?string
    {
        return $this->context->getHeader($key);
    }

    public function method(): string
    {
        return $this->context->getMethod();
    }

    public function path(): string
    {
        return $this->context->getPath();
    }

    public function json(): mixed
    {
        return json_decode($this->context->body(), true);
    }

    public function raw(): string
    {
        return $this->context->body();
    }

    public function parseBody(): array
    {
        $contentType = $this->header('Content-Type') ?? '';
        
        if (str_contains($contentType, 'application/json')) {
            return $this->json() ?? [];
        }
        
        return $_POST;
    }
}

/**
 * Response class - Represents an HTTP response
 */
class Response
{
    private string $body;
    private int $status;
    private string $contentType;

    public function __construct(string $body, int $status, string $contentType)
    {
        $this->body = $body;
        $this->status = $status;
        $this->contentType = $contentType;
    }

    public function send(): void
    {
        echo $this->body;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }
}

/**
 * Route class - Represents a single route
 */
class Route
{
    private string $method;
    private string $path;
    private string $pattern;
    private array $paramNames = [];
    private $handler;
    private array $middleware = [];

    public function __construct(string $method, string $path, callable $handler)
    {
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->handler = $handler;
        $this->compilePattern();
    }

    private function compilePattern(): void
    {
        // Convert route path to regex pattern
        $pattern = preg_replace_callback(
            '/:([a-zA-Z_][a-zA-Z0-9_]*)/',
            function ($matches) {
                $this->paramNames[] = $matches[1];
                return '([^/]+)';
            },
            $this->path
        );

        // Handle wildcard routes
        $pattern = str_replace('/*', '(/.*)?', $pattern);
        $this->pattern = '#^' . $pattern . '$#';
    }

    public function match(string $method, string $path): bool
    {
        if ($this->method !== strtoupper($method) && $this->method !== 'ALL') {
            return false;
        }
        return preg_match($this->pattern, $path) === 1;
    }

    public function extractParams(string $path): array
    {
        if (preg_match($this->pattern, $path, $matches)) {
            array_shift($matches); // Remove full match
            $params = [];
            foreach ($this->paramNames as $index => $name) {
                $params[$name] = $matches[$index] ?? null;
            }
            return $params;
        }
        return [];
    }

    public function getHandler(): callable
    {
        return $this->handler;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function addMiddleware(callable $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }
}

/**
 * Espresso class - Main application class
 */
class Espresso
{
    private array $routes = [];
    private array $middleware = [];
    private array $errorHandlers = [];
    private $notFoundHandler = null;

    /**
     * Register GET route
     */
    public function get(string $path, callable $handler): self
    {
        $this->addRoute('GET', $path, $handler);
        return $this;
    }

    /**
     * Register POST route
     */
    public function post(string $path, callable $handler): self
    {
        $this->addRoute('POST', $path, $handler);
        return $this;
    }

    /**
     * Register PUT route
     */
    public function put(string $path, callable $handler): self
    {
        $this->addRoute('PUT', $path, $handler);
        return $this;
    }

    /**
     * Register DELETE route
     */
    public function delete(string $path, callable $handler): self
    {
        $this->addRoute('DELETE', $path, $handler);
        return $this;
    }

    /**
     * Register PATCH route
     */
    public function patch(string $path, callable $handler): self
    {
        $this->addRoute('PATCH', $path, $handler);
        return $this;
    }

    /**
     * Register OPTIONS route
     */
    public function options(string $path, callable $handler): self
    {
        $this->addRoute('OPTIONS', $path, $handler);
        return $this;
    }

    /**
     * Register route for all HTTP methods
     */
    public function all(string $path, callable $handler): self
    {
        $this->addRoute('ALL', $path, $handler);
        return $this;
    }

    /**
     * Add middleware
     */
    public function use(...$args): self
    {
        if (count($args) === 1) {
            // Global middleware: use(callable)
            $this->middleware[] = ['path' => '/*', 'handler' => $args[0]];
        } elseif (count($args) === 2) {
            // Path-specific middleware: use(path, callable)
            $this->middleware[] = ['path' => $args[0], 'handler' => $args[1]];
        }
        return $this;
    }

    /**
     * Set custom 404 handler
     */
    public function notFound(callable $handler): self
    {
        $this->notFoundHandler = $handler;
        return $this;
    }

    /**
     * Set error handler
     */
    public function onError(callable $handler): self
    {
        $this->errorHandlers[] = $handler;
        return $this;
    }

    private function addRoute(string $method, string $path, callable $handler): void
    {
        $route = new Route($method, $path, $handler);
        $this->routes[] = $route;
    }

    private function findRoute(string $method, string $path): ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->match($method, $path)) {
                return $route;
            }
        }
        return null;
    }

    private function getMatchingMiddleware(string $path): array
    {
        $matching = [];
        foreach ($this->middleware as $mw) {
            $pattern = $mw['path'];
            // Convert wildcard to regex
            $pattern = str_replace('/*', '(/.*)?', $pattern);
            $pattern = '#^' . $pattern . '$#';
            
            if (preg_match($pattern, $path)) {
                $matching[] = $mw['handler'];
            }
        }
        return $matching;
    }

    private function executeMiddleware(array $middleware, Context $context, callable $final): Response
    {
        $index = 0;
        
        $next = function () use (&$index, &$middleware, &$context, &$final, &$next) {
            if ($index >= count($middleware)) {
                return $final();
            }
            
            $currentMiddleware = $middleware[$index++];
            return $currentMiddleware($context, $next);
        };
        
        return $next();
    }

    public function fetch(?string $method = null, ?string $path = null): Response
    {
        // Get method and path
        $method = $method ?? $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path = $path ?? parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // Normalize path
        $path = '/' . trim($path, '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        $context = new Context($method, $path);

        try {
            // Find route
            $route = $this->findRoute($method, $path);

            if (!$route) {
                return $this->handleNotFound($context);
            }

            // Extract params
            $params = $route->extractParams($path);
            foreach ($params as $key => $value) {
                $context->setParam($key, $value);
            }

            // Get applicable middleware
            $middlewareChain = $this->getMatchingMiddleware($path);

            // Execute middleware and handler
            return $this->executeMiddleware(
                $middlewareChain,
                $context,
                function () use ($route, $context) {
                    $handler = $route->getHandler();
                    return $handler($context);
                }
            );
        } catch (\Throwable $e) {
            return $this->handleError($context, $e);
        }
    }

    private function handleNotFound(Context $context): Response
    {
        if ($this->notFoundHandler) {
            return call_user_func($this->notFoundHandler, $context);
        }
        return $context->text('404 Not Found', 404);
    }

    private function handleError(Context $context, \Throwable $error): Response
    {
        foreach ($this->errorHandlers as $handler) {
            try {
                return $handler($error, $context);
            } catch (\Throwable $e) {
                // Continue to next error handler
            }
        }

        // Default error response
        $message = 'Internal Server Error';
        if (ini_get('display_errors')) {
            $message .= ': ' . $error->getMessage();
        }
        return $context->text($message, 500);
    }

    public function run(): void
    {
        $response = $this->fetch();
        $response->send();
    }

    public function mount(string $basePath, Espresso $subApp): self
    {
        foreach ($subApp->routes as $route) {
            $fullPath = rtrim($basePath, '/') . '/' . ltrim($route->getPath(), '/');
            $this->addRoute($route->getMethod(), $fullPath, $route->getHandler());
        }
        return $this;
    }
}
