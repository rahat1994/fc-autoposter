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
        
        // Log route registration
        error_log("FC Autoposter: Registering route - {$this->namespace}/{$pattern} - Methods: " . implode(',', $methods) . " - Middleware: " . implode(',', $route->getMiddleware()));
        
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
            // Log incoming request
            error_log("FC Autoposter: Handling request - URI: " . $route->getUri() . " - Method: " . $wp_request->get_method() . " - Middleware: " . implode(',', $route->getMiddleware()));
            
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
        
        error_log("FC Autoposter: Running middleware pipeline - Middleware count: " . count($middlewareList) . " - List: " . implode(',', $middlewareList));
        
        foreach ($middlewareList as $middleware) {
            // Get middleware class
            $middlewareClass = $this->router->getMiddleware($middleware);
            
            error_log("FC Autoposter: Processing middleware - Name: {$middleware} - Class: " . ($middlewareClass ?: 'NOT_FOUND'));
            
            if (!$middlewareClass || !class_exists($middlewareClass)) {
                error_log("FC Autoposter: Middleware class not found or doesn't exist - {$middleware}");
                continue;
            }
            
            // Instantiate and run middleware
            $instance = new $middlewareClass();
            
            if (method_exists($instance, 'handle')) {
                $result = $instance->handle($request, function($request) {
                    return $request;
                });
                
                error_log("FC Autoposter: Middleware {$middleware} executed - Result type: " . gettype($result));
                
                // If middleware returns a response, stop execution
                if ($result instanceof Response) {
                    error_log("FC Autoposter: Middleware {$middleware} returned response - Status: " . $result->getStatus());
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
        $middleware = $route->getMiddleware();
        
        // If route has no middleware, allow access (public route)
        if (empty($middleware)) {
            error_log("FC Autoposter: Permission check - No middleware, allowing access");
            return true;
        }
        
        // If route has auth middleware, check if user is logged in
        if (in_array('auth', $middleware)) {
            $is_logged_in = is_user_logged_in();
            error_log("FC Autoposter: Permission check - Auth middleware, user logged in: " . ($is_logged_in ? 'YES' : 'NO'));
            
            if (!$is_logged_in) {
                return new \WP_Error('rest_forbidden', 'Authentication required.', array('status' => 401));
            }
        }
        
        // If route has admin middleware, check if user can manage options
        if (in_array('admin', $middleware)) {
            $can_manage = current_user_can('manage_options');
            error_log("FC Autoposter: Permission check - Admin middleware, can manage options: " . ($can_manage ? 'YES' : 'NO'));
            
            if (!$can_manage) {
                return new \WP_Error('rest_forbidden', 'Admin access required.', array('status' => 403));
            }
        }
        
        error_log("FC Autoposter: Permission check - All checks passed");
        return true;
    }
    
    /**
     * Get router instance
     */
    public function getRouter() {
        return $this->router;
    }
}
