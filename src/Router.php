<?php

namespace Deondazy\Core;

use Deondazy\Core\Exceptions\InvalidArgumentException;

class Router
{
    /**
     * The registered routes
     *
     * @var array
     */
    private array $routes = [];

    /**
     * The not found handler
     *
     * @var callable
     */
    private $notFoundHandler;

    /**
     * The GET request method
     *
     * @var string
     */
    private const GET = 'GET';

    /**
     * The POST request method
     *
     * @var string
     */
    private const POST = 'POST';

    /**
     * The PUT request method
     *
     * @var string
     */
    private const PUT = 'PUT';

    /**
     * The PATCH request method
     *
     * @var string
     */
    private const PATCH = 'PATCH';

    /**
     * The DELETE request method
     *
     * @var string
     */
    private const DELETE = 'DELETE';

    /**
     * Register a GET route
     *
     * @param string $path
     * @param \Closure|array|null|string $handler
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public function get($path, $handler = null)
    {
        $this->addRoute(self::GET, $path, $handler);
    }

    /**
     * Register a POST route
     *
     * @param string $path
     * @param \Closure|array|null|string $handler
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public function post($path, $handler = null)
    {
        $this->addRoute(self::POST, $path, $handler);
    }

    /**
     * Register a PUT route
     *
     * @param string $path
     * @param \Closure|array|null|string $handler
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public function put($path, $handler = null)
    {
        $this->addRoute(self::PUT, $path, $handler);
    }

    /**
     * Register a PATCH route
     *
     * @param string $path
     * @param \Closure|array|null|string $handler
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public function patch($path, $handler = null)
    {
        $this->addRoute(self::PATCH, $path, $handler);
    }

    /**
     * Register a DELETE route
     *
     * @param string $path
     * @param \Closure|array|null|string $handler
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public function delete($path, $handler = null)
    {
        $this->addRoute(self::DELETE, $path, $handler);
    }

    /**
     * Set the not found handler
     *
     * @param \Closure $handler
     *
     * @return void
     */
    public function notFoundHandler($handler)
    {
        $this->notFoundHandler = $handler;
    }

    /**
     * Add a route to the routes array
     *
     * @param string $method
     * @param string $path
     * @param \Closure|array|null|string $handler
     *
     * @return void
     */
    private function addRoute($method, $path, $handler = null)
    {
        $this->routes[$method . $path] = [
            'method'  => $method,
            'path'    => $path,
            'handler' => $handler,
        ];
    }

    /**
     * Match the route path parameter with the request url parameter
     *
     * @param array $routePath
     * @param array $urlPath
     *
     * @return array
     *
     * @throws InvalidArgumentException
     */
    private function getParameters(array $routePath, array $urlPath)
    {
        $parameters = [];

        foreach ($routePath as $key => $value) {
            if (strpos($value, '$') !== false && isset($urlPath[$key])) {
                $parameters[substr($value, 1)] = $urlPath[$key];
            }
        }

        return $parameters;
    }

    /**
     * Run the router
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public function run()
    {
        $requestUri    = parse_url($_SERVER['REQUEST_URI']);
        $requestPath   = $requestUri['path'];
        $requestPath   = ($requestPath === '/') ? $requestPath: rtrim($requestPath, '/');
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        $callback      = null;
        $parameters    = [];

        foreach ($this->routes as $route) {
            $routePart = explode('/', $route['path']);
            $urlPart = explode('/', $requestPath);

            array_shift($routePart);
            array_shift($urlPart);

            // Check if the route path and the request url path are the same
            if (count($routePart) === count($urlPart) && $routePart[0] === $urlPart[0]) {
                // Check if the route method and the request method are the same
                if ($route['method'] === $requestMethod) {
                    $callback = $route['handler'];
                    $parameters = $this->getParameters($routePart, $urlPart);
                    break;
                }
            }
        }

        // If callback is a string, it means that the handler is a controller
        if (is_string($callback)) {
            $callback = explode('@', $callback);

            if (count($callback) !== 2) {
                throw new InvalidArgumentException('Invalid callback');
            }

            $controller = new $callback[0]();
            $callback = [$controller, $callback[1]];

            if (!is_callable($callback)) {
                throw new InvalidArgumentException('Invalid callback');
            }
        }

        // if callback is an array, it means that the handler is a controller
        if (is_array($callback)) {
            $controller = new $callback[0]();
            $callback = [$controller, $callback[1]];

            if (!is_callable($callback)) {
                throw new InvalidArgumentException('Invalid callback');
            }
        }

        // If callback is null, it means we have a 404
        if (is_null($callback)) {
            header('HTTP/1.0 404 Not Found');

            $callback = $this->notFoundHandler;
        }

        echo call_user_func_array($callback, $parameters);
    }
}
