<?php

namespace CraftShop\Core;

/**
 * Simple Router - Maps URLs to Controllers
 */
class Router
{
    private array $routes = [];
    
    public function get(string $path, string $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }
    
    public function post(string $path, string $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }
    
    public function resolve(string $method, string $uri): ?array
    {
        // First try exact match
        if (isset($this->routes[$method][$uri])) {
            return $this->parseHandler($this->routes[$method][$uri]);
        }
        
        // Then try pattern matching for parameters
        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = $this->convertRouteToRegex($route);
            
            if (preg_match($pattern, $uri, $matches)) {
                $result = $this->parseHandler($handler);
                
                // Extract parameters
                array_shift($matches); // Remove full match
                $params = [];
                if (preg_match_all('/{(\w+)}/', $route, $paramNames)) {
                    foreach ($paramNames[1] as $index => $name) {
                        $params[$name] = $matches[$index] ?? null;
                    }
                }
                $result['params'] = $params;
                
                return $result;
            }
        }
        
        return null;
    }
    
    private function parseHandler(string $handler): array
    {
        [$controller, $action] = explode('@', $handler);
        return [
            'controller' => $controller,
            'action' => $action,
            'params' => []
        ];
    }
    
    private function convertRouteToRegex(string $route): string
    {
        // Convert {param} to regex capture group
        $pattern = preg_replace('/{(\w+)}/', '(\w+)', $route);
        return '#^' . $pattern . '$#';
    }
}