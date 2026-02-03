# Espresso PHP Router

[![PHP](https://img.shields.io/badge/php-%3E%3D8.0-8892BF.svg)](https://www.php.net/)

A lightweight PHP router for quickly building microservices and APIs. Development happens in `src/` (PSR-4); a single-file artifact is produced in `dist/` for simple deployments.

## Inspiration

I created Espresso to solve a specific problem: I wanted expressive routing with middleware while supporting limited PHP environments such as cPanel/shared hosting. No build process or dependencies necessary. Espresso is heavily inspired by [Hono](https://hono.dev/), so Node.js developers familiar with that API should feel comfortable using Espresso.

## Requirements

- PHP 8.0 or higher