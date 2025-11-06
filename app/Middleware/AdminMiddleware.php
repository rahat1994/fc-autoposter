<?php
/**
 * Admin Middleware
 * 
 * Ensures user has admin capabilities before accessing routes
 */

namespace FCAutoposter\Middleware;

use FCAutoposter\Routing\Response;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class AdminMiddleware extends Middleware {
    
    /**
     * Handle an incoming request
     */
    public function handle($request, $next) {
        if (!current_user_can('manage_options')) {
            return Response::forbidden('You do not have permission to access this resource.');
        }
        
        return $next($request);
    }
}
