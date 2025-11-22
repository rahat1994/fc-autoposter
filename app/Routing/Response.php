<?php
/**
 * HTTP Response Handler
 * 
 * Handles HTTP responses with various formats
 */

namespace FCAutoposter\Routing;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Response {
    
    /**
     * @var mixed Response data
     */
    protected $data;
    
    /**
     * @var int HTTP status code
     */
    protected $status = 200;
    
    /**
     * @var array Response headers
     */
    protected $headers = [];
    
    /**
     * Create a new response instance
     */
    public function __construct($data = null, $status = 200, $headers = []) {
        $this->data = $data;
        $this->status = $status;
        $this->headers = $headers;
    }
    
    /**
     * Create a JSON response
     */
    public static function json($data, $status = 200, $headers = []) {
        $response = new static($data, $status, $headers);
        $response->header('Content-Type', 'application/json');
        return $response;
    }
    
    /**
     * Create a success response
     */
    public static function success($message = 'Success', $data = null, $status = 200) {
        return static::json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }
    
    /**
     * Create an error response
     */
    public static function error($message = 'Error', $errors = null, $status = 400) {
        return static::json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $status);
    }
    
    /**
     * Create a created response (201)
     */
    public static function created($data = null, $message = 'Resource created successfully') {
        return static::success($message, $data, 201);
    }
    
    /**
     * Create a no content response (204)
     */
    public static function noContent() {
        return new static(null, 204);
    }
    
    /**
     * Create a not found response (404)
     */
    public static function notFound($message = 'Resource not found') {
        return static::error($message, null, 404);
    }
    
    /**
     * Create an unauthorized response (401)
     */
    public static function unauthorized($message = 'Unauthorized') {
        return static::error($message, null, 401);
    }
    
    /**
     * Create a forbidden response (403)
     */
    public static function forbidden($message = 'Forbidden') {
        return static::error($message, null, 403);
    }
    
    /**
     * Create a validation error response (422)
     */
    public static function validationError($errors, $message = 'Validation failed') {
        return static::error($message, $errors, 422);
    }
    
    /**
     * Set response header
     */
    public function header($key, $value) {
        $this->headers[$key] = $value;
        return $this;
    }
    
    /**
     * Set response status
     */
    public function status($code) {
        $this->status = $code;
        return $this;
    }
    
    /**
     * Get response data
     */
    public function getData() {
        return $this->data;
    }
    
    /**
     * Get status code
     */
    public function getStatus() {
        return $this->status;
    }
    
    /**
     * Get headers
     */
    public function getHeaders() {
        return $this->headers;
    }
    
    /**
     * Convert response to WordPress REST response
     */
    public function toWpResponse() {
        if (function_exists('rest_ensure_response')) {
            $wp_response = rest_ensure_response($this->data);
            $wp_response->set_status($this->status);
            
            foreach ($this->headers as $key => $value) {
                $wp_response->header($key, $value);
            }
            
            return $wp_response;
        }
        
        return $this->data;
    }
    
    /**
     * Send the response
     */
    public function send() {
        // Set HTTP status code
        http_response_code($this->status);
        
        // Set headers
        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }
        
        // Output data
        if ($this->data !== null) {
            if (isset($this->headers['Content-Type']) && 
                strpos($this->headers['Content-Type'], 'application/json') !== false) {
                echo json_encode($this->data);
            } else {
                echo $this->data;
            }
        }
        
        return $this;
    }
}
