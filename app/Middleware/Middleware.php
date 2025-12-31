<?php
/**
 * Base Middleware
 * 
 * Base interface for all middleware
 */

namespace FCAutoposter\Middleware;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

abstract class Middleware {
    
    /**
     * Handle an incoming request
     * 
     * @param \FCAutoposter\Routing\Request $request
     * @param \Closure $next
     * @return mixed
     */
    abstract public function handle($request, $next);
}
