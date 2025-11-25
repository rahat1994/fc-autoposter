<?php
/**
 * OpenRouter Provider
 * 
 * Integration with OpenRouter API using WordPress HTTP API
 * OpenRouter provides access to multiple AI models through a unified API
 */

namespace FCAutoposter\Services\AI;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class OpenRouterProvider extends BaseAIProvider {
    
    /**
     * API base URL
     *
     * @var string
     */
    protected const API_BASE_URL = 'https://openrouter.ai/api/v1';
    
    /**
     * Default model
     *
     * @var string
     */
    protected $defaultModel = 'openai/gpt-3.5-turbo';
    
    /**
     * Site URL for OpenRouter referrer
     *
     * @var string
     */
    protected $siteUrl;
    
    /**
     * Site name for OpenRouter
     *
     * @var string
     */
    protected $siteName;
    
    /**
     * Constructor
     *
     * @param string|null $apiKey Optional API key
     */
    public function __construct(?string $apiKey = null) {
        parent::__construct($apiKey);
        $this->siteUrl = get_site_url();
        $this->siteName = get_bloginfo('name');
    }
    
    /**
     * Get the provider name
     *
     * @return string
     */
    public function getName(): string {
        return 'openrouter';
    }
    
    /**
     * Load API key from WordPress options
     *
     * @return string
     */
    protected function loadApiKey(): string {
        return get_option('fc_autoposter_openrouter_api_key', '');
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
     * Get default request headers including OpenRouter specific headers
     *
     * @return array
     */
    protected function getDefaultHeaders(): array {
        return [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->apiKey,
            'HTTP-Referer' => $this->siteUrl,
            'X-Title' => $this->siteName,
        ];
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
        
        $data = [
            'model' => $model,
            'messages' => $messages,
        ];
        
        // Add optional parameters with validation
        // Temperature range: 0.0 to 2.0
        if (isset($options['temperature'])) {
            $validated = $this->validateNumericOption($options['temperature'], 0.0, 2.0);
            if ($validated !== null) {
                $data['temperature'] = $validated;
            }
        }
        
        // max_tokens must be positive integer
        if (isset($options['max_tokens'])) {
            $validated = $this->validatePositiveIntOption($options['max_tokens']);
            if ($validated !== null) {
                $data['max_tokens'] = $validated;
            }
        }
        
        // top_p range: 0.0 to 1.0
        if (isset($options['top_p'])) {
            $validated = $this->validateNumericOption($options['top_p'], 0.0, 1.0);
            if ($validated !== null) {
                $data['top_p'] = $validated;
            }
        }
        
        // frequency_penalty range: -2.0 to 2.0
        if (isset($options['frequency_penalty'])) {
            $validated = $this->validateNumericOption($options['frequency_penalty'], -2.0, 2.0);
            if ($validated !== null) {
                $data['frequency_penalty'] = $validated;
            }
        }
        
        // presence_penalty range: -2.0 to 2.0
        if (isset($options['presence_penalty'])) {
            $validated = $this->validateNumericOption($options['presence_penalty'], -2.0, 2.0);
            if ($validated !== null) {
                $data['presence_penalty'] = $validated;
            }
        }
        
        // OpenRouter specific options with validation
        // See: https://openrouter.ai/docs/transforms
        if (isset($options['transforms']) && is_array($options['transforms'])) {
            // 'middle-out': Compression technique that prioritizes context relevance
            $allowedTransforms = ['middle-out'];
            $data['transforms'] = array_values(array_filter(
                $options['transforms'],
                function ($transform) use ($allowedTransforms) {
                    return is_string($transform) && in_array($transform, $allowedTransforms, true);
                }
            ));
        }
        
        // See: https://openrouter.ai/docs/features/routing
        if (isset($options['route']) && is_string($options['route'])) {
            // 'fallback': Enables automatic fallback to alternative providers on error
            $allowedRoutes = ['fallback'];
            if (in_array($options['route'], $allowedRoutes, true)) {
                $data['route'] = $options['route'];
            }
        }
        
        $response = $this->makeRequest('/chat/completions', 'POST', $data);
        
        if (!$response['success']) {
            return $response;
        }
        
        // Extract content from response
        $content = $response['data']['choices'][0]['message']['content'] ?? '';
        
        return $this->createSuccessResponse($content, [
            'model' => $response['data']['model'] ?? $model,
            'usage' => $response['data']['usage'] ?? [],
            'finish_reason' => $response['data']['choices'][0]['finish_reason'] ?? null,
            'id' => $response['data']['id'] ?? null,
        ]);
    }
    
    /**
     * Generate text completion
     *
     * @param string $prompt The prompt to complete
     * @param array $options Additional options
     * @return array Response data or error information
     */
    public function textCompletion(string $prompt, array $options = []): array {
        // Convert text prompt to chat format
        $messages = [
            ['role' => 'user', 'content' => $prompt]
        ];
        
        if (!empty($options['system_prompt'])) {
            array_unshift($messages, ['role' => 'system', 'content' => $options['system_prompt']]);
        }
        
        return $this->chatCompletion($messages, $options);
    }
    
    /**
     * Get available models from OpenRouter
     *
     * @return array List of available model identifiers
     */
    public function getAvailableModels(): array {
        $response = $this->makeRequest('/models', 'GET');
        
        if (!$response['success']) {
            // Return some common default models on error
            return [
                'openai/gpt-4',
                'openai/gpt-4-turbo',
                'openai/gpt-3.5-turbo',
                'anthropic/claude-3-opus',
                'anthropic/claude-3-sonnet',
                'anthropic/claude-3-haiku',
                'google/gemini-pro',
                'meta-llama/llama-3-70b-instruct',
            ];
        }
        
        $models = [];
        foreach ($response['data']['data'] ?? [] as $model) {
            $models[] = [
                'id' => $model['id'],
                'name' => $model['name'] ?? $model['id'],
                'context_length' => $model['context_length'] ?? null,
                'pricing' => $model['pricing'] ?? null,
            ];
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
                'message' => 'OpenRouter API key is valid'
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
     * Get credit balance information
     *
     * @return array Credit balance data or error
     */
    public function getCreditBalance(): array {
        $response = $this->makeRequest('/auth/key', 'GET');
        
        if (!$response['success']) {
            return $response;
        }
        
        return [
            'success' => true,
            'data' => $response['data']['data'] ?? $response['data'],
        ];
    }
}
