<?php
/**
 * Simple Router
 * Haklai SaaS - Desenvolvido por: Jefter Ruthes
 */

namespace Haklai\Core;

class Router
{
    private array $routes = [];
    private array $middlewares = [];
    
    public function get(string $path, $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }
    
    public function post(string $path, $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }
    
    private function addRoute(string $method, string $path, $handler, array $middleware): void
    {
        $this->routes[] = compact('method', 'path', 'handler', 'middleware');
    }
    
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;
            
            $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';
            
            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                // Execute middleware
                foreach ($route['middleware'] as $mw) {
                    if (is_callable($mw)) {
                        $result = $mw();
                        if ($result === false) return;
                    }
                }
                
                // Execute handler
                if (is_callable($route['handler'])) {
                    call_user_func_array($route['handler'], $params);
                } elseif (is_string($route['handler'])) {
                    [$controller, $action] = explode('@', $route['handler']);
                    $controller = "Haklai\\Controllers\\{$controller}";
                    (new $controller)->$action(...array_values($params));
                }
                return;
            }
        }
        
        http_response_code(404);
        echo "404 - Page Not Found";
    }
}
