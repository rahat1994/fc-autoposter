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
        // Try custom nonce first (X-FC-Nonce), then fallback to standard headers
        $customNonce = $request->header('X-FC-Nonce') ?: $request->input('_fc_nonce');
        $wpNonce = $request->header('X-WP-Nonce') ?: $request->input('_wpnonce');
        
        error_log("FC Autoposter NonceMiddleware: Processing request - Custom nonce: " . ($customNonce ?: 'NONE') . " - WP nonce: " . ($wpNonce ?: 'NONE'));
        
        // Check custom nonce first
        if ($customNonce) {
            $nonce_valid = wp_verify_nonce($customNonce, 'fc_autoposter_nonce');
            error_log("FC Autoposter NonceMiddleware: Custom nonce validation - Nonce: {$customNonce} - Valid: " . ($nonce_valid ? 'YES' : 'NO'));
            
            if ($nonce_valid) {
                error_log("FC Autoposter NonceMiddleware: Custom nonce validation passed - proceeding");
                return $next($request);
            }
        }
        
        // Fallback to WordPress REST nonce
        if ($wpNonce) {
            $nonce_valid = wp_verify_nonce($wpNonce, 'wp_rest');
            error_log("FC Autoposter NonceMiddleware: WP REST nonce validation - Nonce: {$wpNonce} - Valid: " . ($nonce_valid ? 'YES' : 'NO'));
            
            if ($nonce_valid) {
                error_log("FC Autoposter NonceMiddleware: WP REST nonce validation passed - proceeding");
                return $next($request);
            }
        }
        
        error_log("FC Autoposter NonceMiddleware: No valid nonce found - returning 403");
        return Response::forbidden('Invalid or missing security token.');
    }
}
