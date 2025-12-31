<?php
/**
 * Authentication Middleware
 * 
 * Ensures user is authenticated before accessing routes
 */

namespace FCAutoposter\Middleware;

use FCAutoposter\Routing\Response;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class AuthMiddleware extends Middleware {
    
    /**
     * Handle an incoming request
     */
    public function handle($request, $next) {
        if (!is_user_logged_in()) {
            return Response::unauthorized('You must be logged in to access this resource.');
        }
        
        return $next($request);
    }
}
