# API Reference

## Espresso Class

### Routing Methods

#### `get(string $path, callable $handler): self`
Register a GET route.

#### `post(string $path, callable $handler): self`
Register a POST route.

#### `put(string $path, callable $handler): self`
Register a PUT route.

#### `delete(string $path, callable $handler): self`
Register a DELETE route.

#### `patch(string $path, callable $handler): self`
Register a PATCH route.

#### `options(string $path, callable $handler): self`
Register an OPTIONS route.

#### `all(string $path, callable $handler): self`
Register a route for all HTTP methods.

### Middleware

#### `use(callable $handler): self`
Add global middleware.

#### `use(string $path, callable $handler): self`
Add path-specific middleware.

### Error Handling

#### `notFound(callable $handler): self`
Set a custom 404 handler.

#### `onError(callable $handler): self`
Set an error handler.

### Sub-applications

#### `mount(string $basePath, Espresso $subApp): self`
Mount a sub-application under a base path.

### Execution

#### `run(): void`
Run the application and send the response.

#### `fetch(?string $method, ?string $path): Response`
Fetch a response for the given method and path (useful for testing).

## Context Class

The context object is passed to route handlers and middleware.

### Response Methods

#### `text(string $text, int $status = 200): Response`
Return a plain text response.

#### `json(mixed $data, int $status = 200): Response`
Return a JSON response.

#### `html(string $html, int $status = 200): Response`
Return an HTML response.

#### `redirect(string $url, int $status = 302): Response`
Return a redirect response.

#### `status(int $code): self`
Set the response status code.

#### `header(string $key, string $value): self`
Set a response header.

### Request Access

#### `$c->req->param(string $key): ?string`
Get a route parameter.

#### `$c->req->query(string $key): ?string`
Get a query parameter.

#### `$c->req->header(string $key): ?string`
Get a request header.

#### `$c->req->method(): string`
Get the HTTP method.

#### `$c->req->path(): string`
Get the request path.

#### `$c->req->json(): mixed`
Parse JSON request body.

#### `$c->req->raw(): string`
Get raw request body.

#### `$c->req->parseBody(): array`
Parse request body (JSON or form data).

### Variables

#### `set(string $key, mixed $value): void`
Set a variable on the context.

#### `get(string $key): mixed`
Get a variable from the context.