<?php
/**
 * AI Provider Interface
 * 
 * Defines the contract for all AI providers
 */

namespace FCAutoposter\Services\AI;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

interface AIProviderInterface {
    
    /**
     * Get the provider name
     *
     * @return string
     */
    public function getName(): string;
    
    /**
     * Check if the provider is configured and ready to use
     *
     * @return bool
     */
    public function isConfigured(): bool;
    
    /**
     * Send a chat completion request
     *
     * @param array $messages Array of message objects with 'role' and 'content'
     * @param array $options Additional options like model, temperature, max_tokens
     * @return array Response data or error information
     */
    public function chatCompletion(array $messages, array $options = []): array;
    
    /**
     * Generate text completion
     *
     * @param string $prompt The prompt to complete
     * @param array $options Additional options
     * @return array Response data or error information
     */
    public function textCompletion(string $prompt, array $options = []): array;
    
    /**
     * Get available models for this provider
     *
     * @return array List of available model identifiers
     */
    public function getAvailableModels(): array;
    
    /**
     * Validate the API key
     *
     * @return array Response with success status and message
     */
    public function validateApiKey(): array;
}
