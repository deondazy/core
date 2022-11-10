<?php

namespace Deondazy\Core\Routing;

interface RouterInterface
{
    /**
     * Add a router to the routes array
     *
     * @param string $method
     * @param string $path
     * @param callable|array|string|null $handler
     */
    public function addRoute(string $method, string $path, $handler = null): void;

    /**
     * Run the routes
     *
     * @return void
     */
    public function run(): void;
}
