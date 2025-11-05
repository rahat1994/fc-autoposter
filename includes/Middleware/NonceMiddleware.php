<?php
/**
 * Nonce Verification Middleware
 * 
 * Verifies WordPress nonce for security
 */

namespace FCAutoposter\Middleware;

use FCAutoposter\Routing\Response;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class NonceMiddleware extends Middleware {
    
    /**
     * Handle an incoming request
     */
    public function handle($request, $next) {
        $nonce = $request->header('X-WP-Nonce') ?: $request->input('_wpnonce');
        
        if (!$nonce) {
            return Response::forbidden('Missing security token.');
        }
        
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return Response::forbidden('Invalid security token.');
        }
        
        return $next($request);
    }
}
