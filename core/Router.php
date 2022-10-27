<?php

namespace Deondazy\Core;

use Deondazy\Core\Exceptions\InvalidArgumentException;

class Router
{
    private array $handlers;

    private $notFoundHandler;

    private const GET = 'GET';
    private const POST = 'POST';
    private const PUT = 'PUT';
    private const PATCH = 'PATCH';
    private const DELETE = 'DELETE';


    public function get(string $path, $handler): void
    {
        $this->addHandler(self::GET, $path, $handler);
    }

    public function post(string $path, $handler): void
    {
        $this->addHandler(self::POST, $path, $handler);
    }

    public function put(string $path, $handler): void
    {
        $this->addHandler(self::PUT, $path, $handler);
    }

    public function patch(string $path, $handler): void
    {
        $this->addHandler(self::PATCH, $path, $handler);
    }

    public function delete(string $path, $handler): void
    {
        $this->addHandler(self::DELETE, $path, $handler);
    }

    public function notFoundHandler($handler): void
    {
        $this->notFoundHandler = $handler;
    }

    private function addHandler(string $method, string $path, $handler): void
    {
        $this->handlers[$method . $path] = [
            'path'    => $path,
            'method'  => $method,
            'handler' => $handler,
        ];
    }

    public function run()
    {
        $requestUri = parse_url($_SERVER['REQUEST_URI']);
        $requestPath = $requestUri['path'];
        $requestPath = $requestPath === '/' ? $requestPath : rtrim($requestPath, '/');
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        $callback = null;

        foreach ($this->handlers as $handler) {
            if ($handler['path'] === $requestPath && $handler['method'] === $requestMethod) {
                $callback = $handler['handler'];
                break;
            }
        }

        if (is_string($callback)) {
            $callback = explode('@', $callback);

            if (count($callback) !== 2) {
                throw new InvalidArgumentException('Invalid callback');
            }

            $controller = new $callback[0]();
            $callback = [$controller, $callback[1]];
        }

        if (!$callback) {
            header('HTTP/1.0 404 Not Found');

            if (!empty($this->notFoundHandler)) {
                $callback = $this->notFoundHandler;
            }
        }

        call_user_func_array($callback, [
            array_merge($_GET, $_POST),
        ]);
    }

    // TODO: Remove this method
    // private function route(string $path, $handler)
    // {
    //     $ROOT = CORE_ROOT;


    //     if ($path == "/404") {
    //         include_once("$ROOT/$handler");
    //         exit();
    //     }

    //     $request_url = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
    //     $request_url = rtrim($request_url, '/');
    //     $request_url = strtok($request_url, '?');

    //     $path_parts = explode('/', $path);

    //     $request_url_parts = explode('/', $request_url);

    //     array_shift($path_parts);
    //     array_shift($request_url_parts);

    //     if ($path_parts[0] == '' && count($request_url_parts) == 0) {
    //         include_once("$ROOT/$handler");
    //         exit();
    //     }

    //     if (count($path_parts) != count($request_url_parts)) {
    //         return;
    //     }

    //     $parameters = [];

    //     for ($__i__ = 0; $__i__ < count($path_parts); $__i__++) {
    //         $path_part = $path_parts[$__i__];

    //         if (preg_match("/^[$]/", $path_part)) {
    //             $path_part = ltrim($path_part, '$');
    //             array_push($parameters, $request_url_parts[$__i__]);
    //             $$path_part = $request_url_parts[$__i__];
    //         } else if ($path_parts[$__i__] != $request_url_parts[$__i__]) {
    //             return;
    //         }
    //     }

    //     // Callback function
    //     if (is_callable($handler)) {
    //         call_user_func($handler);
    //         exit();
    //     }

    //     include_once("$ROOT/$handler");
    //     exit();
    // }
}
