<?php
/**
 * Google Studio Provider
 * 
 * Integration with Google AI Studio (Gemini) API using WordPress HTTP API
 */

namespace FCAutoposter\Services\AI;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class GoogleStudioProvider extends BaseAIProvider {
    
    /**
     * API base URL
     *
     * @var string
     */
    protected const API_BASE_URL = 'https://generativelanguage.googleapis.com/v1beta';
    
    /**
     * Default model
     *
     * @var string
     */
    protected $defaultModel = 'gemini-1.5-flash';
    
    /**
     * Get the provider name
     *
     * @return string
     */
    public function getName(): string {
        return 'google_studio';
    }
    
    /**
     * Load API key from WordPress options
     *
     * @return string
     */
    protected function loadApiKey(): string {
        return get_option('fc_autoposter_google_api_key', '');
    }
    
    /**
     * Get the API base URL
     *
     * @return string
     */
    protected function getBaseUrl(): string {
        return self::API_BASE_URL;
    }
    
    /**
     * Get default request headers for Google API
     * Google uses API key as query parameter, not in Authorization header
     *
     * @return array
     */
    protected function getDefaultHeaders(): array {
        return [
            'Content-Type' => 'application/json',
        ];
    }
    
    /**
     * Make an HTTP request to Google API
     * Override to add API key as query parameter
     *
     * @param string $endpoint API endpoint
     * @param string $method HTTP method
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
        
        // Add API key as query parameter (URL encoded for safety)
        $separator = strpos($endpoint, '?') !== false ? '&' : '?';
        $url = $this->getBaseUrl() . $endpoint . $separator . 'key=' . rawurlencode($this->apiKey);
        
        $headers = array_merge($this->getDefaultHeaders(), $headers);
        
        $args = [
            'method' => $method,
            'headers' => $headers,
            'timeout' => $this->timeout,
            'sslverify' => true,
        ];
        
        if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $encoded = wp_json_encode($data);
            if ($encoded === false) {
                return [
                    'success' => false,
                    'error' => 'Failed to encode request data as JSON',
                    'error_code' => 'json_encode_error'
                ];
            }
            $args['body'] = $encoded;
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
     * Send a chat completion request
     *
     * @param array $messages Array of message objects with 'role' and 'content'
     * @param array $options Additional options like model, temperature, max_tokens
     * @return array Response data or error information
     */
    public function chatCompletion(array $messages, array $options = []): array {
        $model = $options['model'] ?? $this->defaultModel;
        
        // Convert OpenAI-style messages to Google format
        $contents = $this->convertMessagesToGoogleFormat($messages);
        
        $data = [
            'contents' => $contents,
        ];
        
        // Add generation config with validation
        $generationConfig = [];
        
        // Google temperature range: 0.0 to 1.0
        if (isset($options['temperature'])) {
            $validated = $this->validateNumericOption($options['temperature'], 0.0, 1.0);
            if ($validated !== null) {
                $generationConfig['temperature'] = $validated;
            }
        }
        
        // maxOutputTokens must be positive integer
        if (isset($options['max_tokens'])) {
            $validated = $this->validatePositiveIntOption($options['max_tokens']);
            if ($validated !== null) {
                $generationConfig['maxOutputTokens'] = $validated;
            }
        }
        
        // top_p range: 0.0 to 1.0
        if (isset($options['top_p'])) {
            $validated = $this->validateNumericOption($options['top_p'], 0.0, 1.0);
            if ($validated !== null) {
                $generationConfig['topP'] = $validated;
            }
        }
        
        // top_k must be positive integer
        if (isset($options['top_k'])) {
            $validated = $this->validatePositiveIntOption($options['top_k']);
            if ($validated !== null) {
                $generationConfig['topK'] = $validated;
            }
        }
        
        if (!empty($generationConfig)) {
            $data['generationConfig'] = $generationConfig;
        }
        
        // Add safety settings if provided (validated as array)
        if (!empty($options['safety_settings']) && is_array($options['safety_settings'])) {
            $data['safetySettings'] = $options['safety_settings'];
        }
        
        $endpoint = "/models/{$model}:generateContent";
        $response = $this->makeRequest($endpoint, 'POST', $data);
        
        if (!$response['success']) {
            return $response;
        }
        
        // Extract content from response
        $content = $this->extractContentFromResponse($response['data']);
        
        if ($content === null) {
            return $this->createErrorResponse(
                'No content generated. Check safety filters or prompt.',
                'no_content'
            );
        }
        
        return $this->createSuccessResponse($content, [
            'model' => $model,
            'usage' => $this->extractUsageFromResponse($response['data']),
            'finish_reason' => $response['data']['candidates'][0]['finishReason'] ?? null,
            'safety_ratings' => $response['data']['candidates'][0]['safetyRatings'] ?? [],
        ]);
    }
    
    /**
     * Convert OpenAI-style messages to Google format
     *
     * @param array $messages OpenAI-style messages
     * @return array Google-style contents
     */
    protected function convertMessagesToGoogleFormat(array $messages): array {
        $contents = [];
        $systemInstruction = null;
        
        foreach ($messages as $message) {
            $role = $message['role'];
            $content = $message['content'];
            
            // Handle system messages
            if ($role === 'system') {
                // Prepend system message to first user message
                $systemInstruction = $content;
                continue;
            }
            
            // Map roles: 'assistant' -> 'model' in Google API
            $googleRole = $role === 'assistant' ? 'model' : 'user';
            
            // If we have a system instruction, prepend it to the first user message
            if ($systemInstruction !== null && $googleRole === 'user') {
                $content = "System: {$systemInstruction}\n\nUser: {$content}";
                $systemInstruction = null;
            }
            
            $contents[] = [
                'role' => $googleRole,
                'parts' => [
                    ['text' => $content]
                ]
            ];
        }
        
        return $contents;
    }
    
    /**
     * Extract text content from Google API response
     *
     * @param array $responseData Response data
     * @return string|null Extracted content or null
     */
    protected function extractContentFromResponse(array $responseData): ?string {
        $candidates = $responseData['candidates'] ?? [];
        
        if (empty($candidates)) {
            return null;
        }
        
        $content = $candidates[0]['content'] ?? [];
        $parts = $content['parts'] ?? [];
        
        if (empty($parts)) {
            return null;
        }
        
        $textParts = [];
        foreach ($parts as $part) {
            if (isset($part['text'])) {
                $textParts[] = $part['text'];
            }
        }
        
        return implode('', $textParts);
    }
    
    /**
     * Extract usage information from Google API response
     *
     * @param array $responseData Response data
     * @return array Usage data
     */
    protected function extractUsageFromResponse(array $responseData): array {
        $usageMetadata = $responseData['usageMetadata'] ?? [];
        
        return [
            'prompt_tokens' => $usageMetadata['promptTokenCount'] ?? 0,
            'completion_tokens' => $usageMetadata['candidatesTokenCount'] ?? 0,
            'total_tokens' => $usageMetadata['totalTokenCount'] ?? 0,
        ];
    }
    
    /**
     * Generate text completion
     *
     * @param string $prompt The prompt to complete
     * @param array $options Additional options
     * @return array Response data or error information
     */
    public function textCompletion(string $prompt, array $options = []): array {
        $messages = [
            ['role' => 'user', 'content' => $prompt]
        ];
        
        if (!empty($options['system_prompt'])) {
            array_unshift($messages, ['role' => 'system', 'content' => $options['system_prompt']]);
        }
        
        return $this->chatCompletion($messages, $options);
    }
    
    /**
     * Get available models from Google AI Studio
     *
     * @return array List of available model identifiers
     */
    public function getAvailableModels(): array {
        $response = $this->makeRequest('/models', 'GET');
        
        if (!$response['success']) {
            // Return default models on error
            return [
                'gemini-1.5-pro',
                'gemini-1.5-flash',
                'gemini-1.0-pro',
            ];
        }
        
        $models = [];
        foreach ($response['data']['models'] ?? [] as $model) {
            // Only include generative models that support generateContent
            $supportedMethods = $model['supportedGenerationMethods'] ?? [];
            if (in_array('generateContent', $supportedMethods)) {
                $name = $model['name'] ?? '';
                // Remove 'models/' prefix
                $modelId = str_replace('models/', '', $name);
                $models[] = [
                    'id' => $modelId,
                    'name' => $model['displayName'] ?? $modelId,
                    'description' => $model['description'] ?? '',
                    'input_token_limit' => $model['inputTokenLimit'] ?? null,
                    'output_token_limit' => $model['outputTokenLimit'] ?? null,
                ];
            }
        }
        
        return $models;
    }
    
    /**
     * Get a simplified list of model IDs
     *
     * @return array List of model ID strings
     */
    public function getModelIds(): array {
        $models = $this->getAvailableModels();
        
        if (empty($models)) {
            return [];
        }
        
        // Check if it's already just IDs (array of strings)
        if (isset($models[0]) && is_string($models[0])) {
            return $models;
        }
        
        // Check if it's an array of model objects with 'id' key
        if (isset($models[0]) && is_array($models[0]) && isset($models[0]['id'])) {
            return array_column($models, 'id');
        }
        
        return [];
    }
    
    /**
     * Validate the API key
     *
     * @return array Response with success status and message
     */
    public function validateApiKey(): array {
        if (!$this->isConfigured()) {
            return $this->createErrorResponse('API key is not configured', 'not_configured');
        }
        
        // Try to list models as a validation check
        $response = $this->makeRequest('/models', 'GET');
        
        if ($response['success']) {
            return [
                'success' => true,
                'message' => 'Google AI Studio API key is valid'
            ];
        }
        
        return $this->createErrorResponse(
            $response['error'] ?? 'Failed to validate API key',
            (string) ($response['error_code'] ?? 'validation_failed')
        );
    }
    
    /**
     * Set the default model
     *
     * @param string $model Model identifier
     * @return static
     */
    public function setDefaultModel(string $model): self {
        $this->defaultModel = $model;
        return $this;
    }
    
    /**
     * Count tokens for given content
     *
     * @param string|array $content Text content or messages
     * @param string|null $model Model to use for counting
     * @return array Token count or error
     */
    public function countTokens($content, ?string $model = null): array {
        $model = $model ?? $this->defaultModel;
        
        // Prepare contents
        if (is_string($content)) {
            $contents = [
                [
                    'role' => 'user',
                    'parts' => [['text' => $content]]
                ]
            ];
        } else {
            $contents = $this->convertMessagesToGoogleFormat($content);
        }
        
        $data = ['contents' => $contents];
        
        $endpoint = "/models/{$model}:countTokens";
        $response = $this->makeRequest($endpoint, 'POST', $data);
        
        if (!$response['success']) {
            return $response;
        }
        
        return [
            'success' => true,
            'token_count' => $response['data']['totalTokens'] ?? 0,
        ];
    }
}
