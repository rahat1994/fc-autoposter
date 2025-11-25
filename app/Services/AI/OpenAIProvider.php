<?php
/**
 * OpenAI Provider
 * 
 * Integration with OpenAI API using WordPress HTTP API
 */

namespace FCAutoposter\Services\AI;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class OpenAIProvider extends BaseAIProvider {
    
    /**
     * API base URL
     *
     * @var string
     */
    protected const API_BASE_URL = 'https://api.openai.com/v1';
    
    /**
     * Default model
     *
     * @var string
     */
    protected $defaultModel = 'gpt-3.5-turbo';
    
    /**
     * Get the provider name
     *
     * @return string
     */
    public function getName(): string {
        return 'openai';
    }
    
    /**
     * Load API key from WordPress options
     *
     * @return string
     */
    protected function loadApiKey(): string {
        return get_option('fc_autoposter_openai_api_key', '');
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
        // OpenAI temperature range: 0.0 to 2.0
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
        ]);
    }
    
    /**
     * Generate text completion (legacy completions endpoint)
     *
     * @param string $prompt The prompt to complete
     * @param array $options Additional options
     * @return array Response data or error information
     */
    public function textCompletion(string $prompt, array $options = []): array {
        // Convert text prompt to chat format for newer models
        $messages = [
            ['role' => 'user', 'content' => $prompt]
        ];
        
        if (!empty($options['system_prompt'])) {
            array_unshift($messages, ['role' => 'system', 'content' => $options['system_prompt']]);
        }
        
        return $this->chatCompletion($messages, $options);
    }
    
    /**
     * Get available models for this provider
     *
     * @return array List of available model identifiers
     */
    public function getAvailableModels(): array {
        $response = $this->makeRequest('/models', 'GET');
        
        if (!$response['success']) {
            // Return default models on error
            return [
                'gpt-4',
                'gpt-4-turbo',
                'gpt-4o',
                'gpt-4o-mini',
                'gpt-3.5-turbo',
            ];
        }
        
        $models = [];
        // Chat completion model patterns - covers current and future naming conventions
        $chatModelPatterns = [
            '/^gpt-/',       // GPT models
            '/^o\d+/',       // o1, o2, etc. models
            '/^chatgpt-/',   // ChatGPT models
        ];
        
        foreach ($response['data']['data'] ?? [] as $model) {
            $modelId = $model['id'] ?? '';
            if (empty($modelId) || !is_string($modelId)) {
                continue;
            }
            
            // Check if model matches any chat model pattern
            foreach ($chatModelPatterns as $pattern) {
                if (preg_match($pattern, $modelId)) {
                    $models[] = $modelId;
                    break;
                }
            }
        }
        
        sort($models);
        return $models;
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
                'message' => 'OpenAI API key is valid'
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
     * Generate embeddings for text
     *
     * @param string|array $input Text or array of texts to embed
     * @param string $model Model to use (default: text-embedding-ada-002)
     * @return array Response with embeddings or error
     */
    public function createEmbeddings($input, string $model = 'text-embedding-ada-002'): array {
        $data = [
            'model' => $model,
            'input' => $input,
        ];
        
        $response = $this->makeRequest('/embeddings', 'POST', $data);
        
        if (!$response['success']) {
            return $response;
        }
        
        return [
            'success' => true,
            'embeddings' => array_map(function ($item) {
                return $item['embedding'];
            }, $response['data']['data'] ?? []),
            'usage' => $response['data']['usage'] ?? [],
        ];
    }
}
