<?php
/**
 * Router
 * 
 * Main routing system that handles route registration and dispatching
 */

namespace FCAutoposter\Routing;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Router {
    
    /**
     * @var array Collection of routes
     */
    protected $routes = [];
    
    /**
     * @var array Route name mapping
     */
    protected $namedRoutes = [];
    
    /**
     * @var array Current route group attributes
     */
    protected $groupAttributes = [];
    
    /**
     * @var array Registered middleware
     */
    protected $middleware = [];
    
    /**
     * Register a GET route
     */
    public function get($uri, $action) {
        return $this->addRoute(['GET'], $uri, $action);
    }
    
    /**
     * Register a POST route
     */
    public function post($uri, $action) {
        return $this->addRoute(['POST'], $uri, $action);
    }
    
    /**
     * Register a PUT route
     */
    public function put($uri, $action) {
        return $this->addRoute(['PUT'], $uri, $action);
    }
    
    /**
     * Register a PATCH route
     */
    public function patch($uri, $action) {
        return $this->addRoute(['PATCH'], $uri, $action);
    }
    
    /**
     * Register a DELETE route
     */
    public function delete($uri, $action) {
        return $this->addRoute(['DELETE'], $uri, $action);
    }
    
    /**
     * Register a route for any HTTP method
     */
    public function any($uri, $action) {
        return $this->addRoute(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], $uri, $action);
    }
    
    /**
     * Register a route for multiple HTTP methods
     */
    public function match($methods, $uri, $action) {
        return $this->addRoute($methods, $uri, $action);
    }
    
    /**
     * Register a RESTful resource route
     */
    public function resource($uri, $controller) {
        $routes = [];
        
        // Index - GET /resource
        $routes[] = $this->get($uri, $controller . '@index')->name($uri . '.index');
        
        // Store - POST /resource
        $routes[] = $this->post($uri, $controller . '@store')->name($uri . '.store');
        
        // Show - GET /resource/{id}
        $routes[] = $this->get($uri . '/{id}', $controller . '@show')->name($uri . '.show');
        
        // Update - PUT /resource/{id}
        $routes[] = $this->put($uri . '/{id}', $controller . '@update')->name($uri . '.update');
        
        // Destroy - DELETE /resource/{id}
        $routes[] = $this->delete($uri . '/{id}', $controller . '@destroy')->name($uri . '.destroy');
        
        return $routes;
    }
    
    /**
     * Create a route group with shared attributes
     */
    public function group($attributes, $callback) {
        $previousAttributes = $this->groupAttributes;
        
        // Merge attributes
        $this->groupAttributes = $this->mergeGroupAttributes($attributes);
        
        // Execute callback
        call_user_func($callback, $this);
        
        // Restore previous attributes
        $this->groupAttributes = $previousAttributes;
    }
    
    /**
     * Merge group attributes
     */
    protected function mergeGroupAttributes($attributes) {
        $merged = $this->groupAttributes;
        
        // Merge prefix
        if (isset($attributes['prefix'])) {
            $prefix = trim($attributes['prefix'], '/');
            $merged['prefix'] = isset($merged['prefix']) 
                ? trim($merged['prefix'], '/') . '/' . $prefix
                : $prefix;
        }
        
        // Merge middleware
        if (isset($attributes['middleware'])) {
            $middleware = is_array($attributes['middleware']) 
                ? $attributes['middleware'] 
                : [$attributes['middleware']];
            
            $merged['middleware'] = isset($merged['middleware'])
                ? array_merge($merged['middleware'], $middleware)
                : $middleware;
        }
        
        // Merge namespace
        if (isset($attributes['namespace'])) {
            $merged['namespace'] = isset($merged['namespace'])
                ? $merged['namespace'] . '\\' . $attributes['namespace']
                : $attributes['namespace'];
        }
        
        return $merged;
    }
    
    /**
     * Add a route to the collection
     */
    protected function addRoute($methods, $uri, $action) {
        $route = new Route($methods, $uri, $action);
        
        // Apply group attributes
        if (isset($this->groupAttributes['prefix'])) {
            $route->prefix($this->groupAttributes['prefix']);
        }
        
        if (isset($this->groupAttributes['middleware'])) {
            $route->middleware($this->groupAttributes['middleware']);
        }
        
        // Store route
        $this->routes[] = $route;
        
        return $route;
    }
    
    /**
     * Register middleware
     */
    public function registerMiddleware($name, $class) {
        $this->middleware[$name] = $class;
    }
    
    /**
     * Get middleware class
     */
    public function getMiddleware($name) {
        return $this->middleware[$name] ?? null;
    }
    
    /**
     * Find a route that matches the request
     */
    public function findRoute($method, $uri) {
        foreach ($this->routes as $route) {
            if ($route->matches($method, $uri)) {
                return $route;
            }
        }
        
        return null;
    }
    
    /**
     * Dispatch the request to the matched route
     */
    public function dispatch($request) {
        $route = $this->findRoute($request->method(), $request->uri());
        
        if (!$route) {
            return Response::notFound('Route not found');
        }
        
        // Run middleware pipeline
        $response = $this->runMiddleware($route, $request);
        
        if ($response instanceof Response) {
            return $response;
        }
        
        // Run route action
        return $route->run($request);
    }
    
    /**
     * Run middleware pipeline
     */
    protected function runMiddleware($route, $request) {
        $middlewareList = $route->getMiddleware();
        
        foreach ($middlewareList as $middleware) {
            // Get middleware class
            $middlewareClass = $this->getMiddleware($middleware);
            
            if (!$middlewareClass) {
                continue;
            }
            
            // Instantiate and run middleware
            $instance = new $middlewareClass();
            
            if (method_exists($instance, 'handle')) {
                $result = $instance->handle($request, function($request) {
                    return $request;
                });
                
                // If middleware returns a response, stop execution
                if ($result instanceof Response) {
                    return $result;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Get all registered routes
     */
    public function getRoutes() {
        return $this->routes;
    }
    
    /**
     * Get route by name
     */
    public function getByName($name) {
        foreach ($this->routes as $route) {
            if ($route->getName() === $name) {
                return $route;
            }
        }
        
        return null;
    }
    
    /**
     * Generate URL for named route
     */
    public function url($name, $parameters = []) {
        $route = $this->getByName($name);
        
        if (!$route) {
            return null;
        }
        
        $uri = $route->getUri();
        
        // Replace parameters in URI
        foreach ($parameters as $key => $value) {
            $uri = str_replace('{' . $key . '}', $value, $uri);
        }
        
        return rest_url('fc-autoposter/v1' . $uri);
    }
}
