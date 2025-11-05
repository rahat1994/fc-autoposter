<?php
/**
 * Route Service Provider
 * 
 * Registers routes and integrates the routing system with WordPress REST API
 */

namespace FCAutoposter\Routing;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RouteServiceProvider {
    
    /**
     * @var Router
     */
    protected $router;
    
    /**
     * API namespace
     */
    protected $namespace = 'fc-autoposter/v1';
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->router = new Router();
        $this->registerMiddleware();
    }
    
    /**
     * Bootstrap the routing system
     */
    public function boot() {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }
    
    /**
     * Register middleware
     */
    protected function registerMiddleware() {
        $this->router->registerMiddleware('auth', 'FCAutoposter\\Middleware\\AuthMiddleware');
        $this->router->registerMiddleware('admin', 'FCAutoposter\\Middleware\\AdminMiddleware');
        $this->router->registerMiddleware('nonce', 'FCAutoposter\\Middleware\\NonceMiddleware');
    }
    
    /**
     * Load route files
     */
    protected function loadRoutes() {
        $routes_path = FC_AUTOPOSTER_PLUGIN_DIR . 'routes/api.php';
        
        if (file_exists($routes_path)) {
            $router = $this->router;
            require $routes_path;
        }
    }
    
    /**
     * Register routes with WordPress REST API
     */
    public function registerRoutes() {
        $this->loadRoutes();
        
        $routes = $this->router->getRoutes();
        
        foreach ($routes as $route) {
            $this->registerRoute($route);
        }
    }
    
    /**
     * Register a single route with WordPress REST API
     */
    protected function registerRoute(Route $route) {
        $methods = $route->getMethods();
        $uri = ltrim($route->getUri(), '/');
        
        // Convert route URI to WordPress REST API format
        $pattern = $this->convertUriPattern($uri);
        
        // Register with WordPress REST API
        register_rest_route($this->namespace, $pattern, [
            'methods' => $methods,
            'callback' => function(\WP_REST_Request $wp_request) use ($route) {
                return $this->handleRequest($wp_request, $route);
            },
            'permission_callback' => function(\WP_REST_Request $wp_request) use ($route) {
                return $this->checkPermissions($wp_request, $route);
            }
        ]);
    }
    
    /**
     * Convert route URI pattern to WordPress REST API format
     */
    protected function convertUriPattern($uri) {
        // Convert {param} to (?P<param>[^/]+)
        return preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $uri);
    }
    
    /**
     * Handle incoming request
     */
    protected function handleRequest(\WP_REST_Request $wp_request, Route $route) {
        try {
            // Create our custom request object
            $request = $this->createRequest($wp_request);
            
            // Run middleware pipeline
            $middlewareResponse = $this->runMiddleware($route, $request);
            if ($middlewareResponse instanceof Response) {
                return $middlewareResponse->toWpResponse();
            }
            
            // Execute route action
            $response = $route->run($request);
            
            // Convert to WordPress response
            if ($response instanceof Response) {
                return $response->toWpResponse();
            }
            
            return $response;
            
        } catch (\Exception $e) {
            error_log('FC Autoposter Route Error: ' . $e->getMessage());
            return Response::error(
                'An error occurred while processing your request',
                $e->getMessage(),
                500
            )->toWpResponse();
        }
    }
    
    /**
     * Create custom request from WordPress request
     */
    protected function createRequest(\WP_REST_Request $wp_request) {
        $request = new Request();
        
        // Set route parameters from WordPress request
        $params = [];
        foreach ($wp_request->get_url_params() as $key => $value) {
            $params[$key] = $value;
        }
        $request->setParams($params);
        
        return $request;
    }
    
    /**
     * Run middleware pipeline
     */
    protected function runMiddleware(Route $route, Request $request) {
        $middlewareList = $route->getMiddleware();
        
        foreach ($middlewareList as $middleware) {
            // Get middleware class
            $middlewareClass = $this->router->getMiddleware($middleware);
            
            if (!$middlewareClass || !class_exists($middlewareClass)) {
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
     * Check permissions for route
     */
    protected function checkPermissions(\WP_REST_Request $wp_request, Route $route) {
        // Always allow - middleware will handle authorization
        // This is because WordPress REST API requires permission_callback
        return true;
    }
    
    /**
     * Get router instance
     */
    public function getRouter() {
        return $this->router;
    }
}
