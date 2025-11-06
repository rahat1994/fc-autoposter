<?php
/**
 * Route Definition
 * 
 * Represents a single route in the routing system
 */

namespace FCAutoposter\Routing;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Route {
    
    /**
     * @var array HTTP methods
     */
    protected $methods = [];
    
    /**
     * @var string Route URI
     */
    protected $uri;
    
    /**
     * @var mixed Route action (callback or controller)
     */
    protected $action;
    
    /**
     * @var array Route middleware
     */
    protected $middleware = [];
    
    /**
     * @var string Route name
     */
    protected $name;
    
    /**
     * @var array Route parameters
     */
    protected $parameters = [];
    
    /**
     * @var string Route prefix
     */
    protected $prefix = '';
    
    /**
     * Constructor
     */
    public function __construct($methods, $uri, $action) {
        $this->methods = is_array($methods) ? $methods : [$methods];
        $this->uri = $uri;
        $this->action = $action;
    }
    
    /**
     * Add middleware to route
     */
    public function middleware($middleware) {
        if (is_array($middleware)) {
            $this->middleware = array_merge($this->middleware, $middleware);
        } else {
            $this->middleware[] = $middleware;
        }
        return $this;
    }
    
    /**
     * Set route name
     */
    public function name($name) {
        $this->name = $name;
        return $this;
    }
    
    /**
     * Set route prefix
     */
    public function prefix($prefix) {
        $this->prefix = trim($prefix, '/');
        return $this;
    }
    
    /**
     * Get route methods
     */
    public function getMethods() {
        return $this->methods;
    }
    
    /**
     * Get route URI
     */
    public function getUri() {
        $uri = $this->uri;
        if ($this->prefix) {
            $uri = $this->prefix . '/' . ltrim($uri, '/');
        }
        return '/' . trim($uri, '/');
    }
    
    /**
     * Get route action
     */
    public function getAction() {
        return $this->action;
    }
    
    /**
     * Get route middleware
     */
    public function getMiddleware() {
        return $this->middleware;
    }
    
    /**
     * Get route name
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * Check if route matches request
     */
    public function matches($method, $uri) {
        // Check if method matches
        if (!in_array($method, $this->methods)) {
            return false;
        }
        
        // Build the pattern
        $pattern = $this->buildPattern();
        
        // Match URI against pattern
        if (preg_match($pattern, $uri, $matches)) {
            // Extract parameters
            $this->extractParameters($matches);
            return true;
        }
        
        return false;
    }
    
    /**
     * Build regex pattern from URI
     */
    protected function buildPattern() {
        $uri = $this->getUri();
        
        // Replace parameter placeholders with regex
        $pattern = preg_replace_callback(
            '/\{(\w+)(\?)?\}/',
            function ($matches) {
                $param = $matches[1];
                $optional = isset($matches[2]) ? '?' : '';
                return '(?P<' . $param . '>[^/]+)' . $optional;
            },
            $uri
        );
        
        // Escape forward slashes
        $pattern = str_replace('/', '\/', $pattern);
        
        return '/^' . $pattern . '$/';
    }
    
    /**
     * Extract parameters from matched URI
     */
    protected function extractParameters($matches) {
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $this->parameters[$key] = $value;
            }
        }
    }
    
    /**
     * Get route parameters
     */
    public function getParameters() {
        return $this->parameters;
    }
    
    /**
     * Run the route action
     */
    public function run($request) {
        // Set route parameters on request
        $request->setParams($this->parameters);
        
        // Handle controller action
        if (is_string($this->action) && strpos($this->action, '@') !== false) {
            return $this->runControllerAction($request);
        }
        
        // Handle callable action
        if (is_callable($this->action)) {
            return call_user_func($this->action, $request);
        }
        
        return Response::error('Invalid route action', null, 500);
    }
    
    /**
     * Run controller action
     */
    protected function runControllerAction($request) {
        list($controller, $method) = explode('@', $this->action);
        
        // Add namespace if not present
        if (strpos($controller, '\\') === false) {
            $controller = 'FCAutoposter\\Controllers\\' . $controller;
        }
        
        // Check if controller exists
        if (!class_exists($controller)) {
            return Response::error('Controller not found: ' . $controller, null, 500);
        }
        
        // Instantiate controller
        $instance = new $controller();
        
        // Check if method exists
        if (!method_exists($instance, $method)) {
            return Response::error('Method not found: ' . $method, null, 500);
        }
        
        // Call method
        return $instance->$method($request);
    }
}
