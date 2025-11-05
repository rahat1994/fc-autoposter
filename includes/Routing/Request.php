<?php
/**
 * HTTP Request Handler
 * 
 * Handles incoming HTTP requests and provides easy access to request data
 */

namespace FCAutoposter\Routing;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Request {
    
    /**
     * @var array Request parameters
     */
    protected $params = [];
    
    /**
     * @var array Query parameters
     */
    protected $query = [];
    
    /**
     * @var array Request body
     */
    protected $body = [];
    
    /**
     * @var array Request headers
     */
    protected $headers = [];
    
    /**
     * @var string HTTP method
     */
    protected $method;
    
    /**
     * @var string Request URI
     */
    protected $uri;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri = $_SERVER['REQUEST_URI'] ?? '';
        $this->query = $_GET;
        $this->headers = $this->getHeaders();
        $this->parseBody();
    }
    
    /**
     * Get all request headers
     */
    protected function getHeaders() {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace('_', '-', substr($key, 5));
                $headers[$header] = $value;
            }
        }
        return $headers;
    }
    
    /**
     * Parse request body
     */
    protected function parseBody() {
        $content_type = $this->header('Content-Type', '');
        
        if (strpos($content_type, 'application/json') !== false) {
            $json = file_get_contents('php://input');
            $this->body = json_decode($json, true) ?? [];
        } else {
            $this->body = $_POST;
        }
    }
    
    /**
     * Get HTTP method
     */
    public function method() {
        return $this->method;
    }
    
    /**
     * Get request URI
     */
    public function uri() {
        return $this->uri;
    }
    
    /**
     * Get all route parameters
     */
    public function params() {
        return $this->params;
    }
    
    /**
     * Get a route parameter
     */
    public function param($key, $default = null) {
        return $this->params[$key] ?? $default;
    }
    
    /**
     * Set route parameters
     */
    public function setParams($params) {
        $this->params = $params;
    }
    
    /**
     * Get query parameter
     */
    public function query($key = null, $default = null) {
        if ($key === null) {
            return $this->query;
        }
        return $this->query[$key] ?? $default;
    }
    
    /**
     * Get input from body
     */
    public function input($key = null, $default = null) {
        if ($key === null) {
            return $this->body;
        }
        return $this->body[$key] ?? $default;
    }
    
    /**
     * Get all input (query + body)
     */
    public function all() {
        return array_merge($this->query, $this->body);
    }
    
    /**
     * Get request header
     */
    public function header($key, $default = null) {
        $key = strtoupper(str_replace('-', '_', $key));
        return $this->headers[$key] ?? $default;
    }
    
    /**
     * Check if request has input
     */
    public function has($key) {
        return isset($this->body[$key]) || isset($this->query[$key]);
    }
    
    /**
     * Get only specified inputs
     */
    public function only($keys) {
        $keys = is_array($keys) ? $keys : func_get_args();
        $results = [];
        $input = $this->all();
        
        foreach ($keys as $key) {
            if (isset($input[$key])) {
                $results[$key] = $input[$key];
            }
        }
        
        return $results;
    }
    
    /**
     * Get all except specified inputs
     */
    public function except($keys) {
        $keys = is_array($keys) ? $keys : func_get_args();
        $input = $this->all();
        
        foreach ($keys as $key) {
            unset($input[$key]);
        }
        
        return $input;
    }
    
    /**
     * Check if request is AJAX
     */
    public function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Check if request expects JSON
     */
    public function expectsJson() {
        $accept = $this->header('Accept', '');
        return strpos($accept, 'application/json') !== false;
    }
}
