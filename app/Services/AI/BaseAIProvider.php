<?php
/**
 * Base AI Provider
 * 
 * Abstract base class with common functionality for all AI providers
 */

namespace FCAutoposter\Services\AI;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

abstract class BaseAIProvider implements AIProviderInterface {
    
    /**
     * API key for authentication
     *
     * @var string
     */
    protected $apiKey;
    
    /**
     * Default request timeout in seconds
     *
     * @var int
     */
    protected $timeout = 60;
    
    /**
     * Constructor
     *
     * @param string|null $apiKey Optional API key (will be loaded from options if not provided)
     */
    public function __construct(?string $apiKey = null) {
        $this->apiKey = $apiKey ?? $this->loadApiKey();
    }
    
    /**
     * Load API key from WordPress options
     *
     * @return string
     */
    abstract protected function loadApiKey(): string;
    
    /**
     * Get the API base URL
     *
     * @return string
     */
    abstract protected function getBaseUrl(): string;
    
    /**
     * Get default request headers
     *
     * @return array
     */
    protected function getDefaultHeaders(): array {
        return [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->apiKey,
        ];
    }
    
    /**
     * Check if the provider is configured
     *
     * @return bool
     */
    public function isConfigured(): bool {
        return !empty($this->apiKey);
    }
    
    /**
     * Make an HTTP request using WordPress HTTP API
     *
     * @param string $endpoint API endpoint
     * @param string $method HTTP method (GET, POST, etc.)
     * @param array $data Request data
     * @param array $headers Additional headers
     * @return array Response data or error information
     */
    protected function makeRequest(string $endpoint, string $method = 'POST', array $data = [], array $headers = []): array {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'API key is not configured',
                'error_code' => 'not_configured'
            ];
        }
        
        $url = $this->getBaseUrl() . $endpoint;
        $headers = array_merge($this->getDefaultHeaders(), $headers);
        
        $args = [
            'method' => $method,
            'headers' => $headers,
            'timeout' => $this->timeout,
            'sslverify' => true,
        ];
        
        if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $args['body'] = wp_json_encode($data);
        }
        
        // Use WordPress HTTP API
        if ($method === 'GET') {
            $response = wp_remote_get($url, $args);
        } else {
            $response = wp_remote_post($url, $args);
        }
        
        return $this->handleResponse($response);
    }
    
    /**
     * Handle the HTTP response
     *
     * @param array|\WP_Error $response WordPress HTTP response or error
     * @return array Processed response data
     */
    protected function handleResponse($response): array {
        if (is_wp_error($response)) {
            error_log('FC Autoposter AI Provider Error: ' . $response->get_error_message());
            return [
                'success' => false,
                'error' => $response->get_error_message(),
                'error_code' => $response->get_error_code()
            ];
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);
        
        if ($status_code >= 200 && $status_code < 300) {
            return [
                'success' => true,
                'data' => $decoded,
                'status_code' => $status_code
            ];
        }
        
        // Handle error responses
        $error_message = $this->extractErrorMessage($decoded, $body);
        error_log("FC Autoposter AI Provider Error ({$status_code}): {$error_message}");
        
        return [
            'success' => false,
            'error' => $error_message,
            'error_code' => $status_code,
            'data' => $decoded
        ];
    }
    
    /**
     * Extract error message from response
     *
     * @param array|null $decoded Decoded JSON response
     * @param string $rawBody Raw response body
     * @return string Error message
     */
    protected function extractErrorMessage(?array $decoded, string $rawBody): string {
        if (!empty($decoded['error']['message'])) {
            return $decoded['error']['message'];
        }
        
        if (!empty($decoded['error'])) {
            return is_string($decoded['error']) ? $decoded['error'] : wp_json_encode($decoded['error']);
        }
        
        if (!empty($decoded['message'])) {
            return $decoded['message'];
        }
        
        return !empty($rawBody) ? substr($rawBody, 0, 200) : 'Unknown error occurred';
    }
    
    /**
     * Set request timeout
     *
     * @param int $timeout Timeout in seconds
     * @return static
     */
    public function setTimeout(int $timeout): self {
        $this->timeout = $timeout;
        return $this;
    }
    
    /**
     * Create a success response structure
     *
     * @param string $content The generated content
     * @param array $metadata Additional metadata
     * @return array
     */
    protected function createSuccessResponse(string $content, array $metadata = []): array {
        return array_merge([
            'success' => true,
            'content' => $content,
        ], $metadata);
    }
    
    /**
     * Create an error response structure
     *
     * @param string $message Error message
     * @param string $code Error code
     * @return array
     */
    protected function createErrorResponse(string $message, string $code = 'error'): array {
        return [
            'success' => false,
            'error' => $message,
            'error_code' => $code
        ];
    }
}
