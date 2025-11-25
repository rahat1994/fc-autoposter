<?php
/**
 * AI Service
 * 
 * Main service class for managing AI providers and making AI requests
 */

namespace FCAutoposter\Services\AI;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class AIService {
    
    /**
     * Registered providers
     *
     * @var array<string, AIProviderInterface>
     */
    protected $providers = [];
    
    /**
     * Default provider name
     *
     * @var string
     */
    protected $defaultProvider = 'openai';
    
    /**
     * Singleton instance
     *
     * @var static|null
     */
    protected static $instance = null;
    
    /**
     * Get singleton instance
     *
     * @return static
     */
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor - registers default providers
     */
    public function __construct() {
        $this->registerDefaultProviders();
    }
    
    /**
     * Register default AI providers
     */
    protected function registerDefaultProviders(): void {
        $this->registerProvider('openai', new OpenAIProvider());
        $this->registerProvider('openrouter', new OpenRouterProvider());
        $this->registerProvider('google_studio', new GoogleStudioProvider());
    }
    
    /**
     * Register a provider
     *
     * @param string $name Provider name
     * @param AIProviderInterface $provider Provider instance
     * @return static
     */
    public function registerProvider(string $name, AIProviderInterface $provider): self {
        $this->providers[$name] = $provider;
        return $this;
    }
    
    /**
     * Get a provider by name
     *
     * @param string|null $name Provider name (null for default)
     * @return AIProviderInterface|null
     */
    public function getProvider(?string $name = null): ?AIProviderInterface {
        $name = $name ?? $this->defaultProvider;
        return $this->providers[$name] ?? null;
    }
    
    /**
     * Get all registered providers
     *
     * @return array<string, AIProviderInterface>
     */
    public function getProviders(): array {
        return $this->providers;
    }
    
    /**
     * Get list of available provider names
     *
     * @return array
     */
    public function getAvailableProviderNames(): array {
        return array_keys($this->providers);
    }
    
    /**
     * Get configured providers (those with valid API keys)
     *
     * @return array<string, AIProviderInterface>
     */
    public function getConfiguredProviders(): array {
        return array_filter($this->providers, function ($provider) {
            return $provider->isConfigured();
        });
    }
    
    /**
     * Set the default provider
     *
     * @param string $name Provider name
     * @return static
     * @throws \InvalidArgumentException If provider is not registered
     */
    public function setDefaultProvider(string $name): self {
        if (!isset($this->providers[$name])) {
            throw new \InvalidArgumentException("Provider '{$name}' is not registered");
        }
        $this->defaultProvider = $name;
        return $this;
    }
    
    /**
     * Get the default provider name
     *
     * @return string
     */
    public function getDefaultProviderName(): string {
        return $this->defaultProvider;
    }
    
    /**
     * Send a chat completion request using specified or default provider
     *
     * @param array $messages Array of message objects
     * @param array $options Options including 'provider' to specify which provider to use
     * @return array Response data
     */
    public function chatCompletion(array $messages, array $options = []): array {
        $providerName = $options['provider'] ?? $this->defaultProvider;
        unset($options['provider']);
        
        $provider = $this->getProvider($providerName);
        
        if (!$provider) {
            return [
                'success' => false,
                'error' => "Provider '{$providerName}' is not registered",
                'error_code' => 'invalid_provider'
            ];
        }
        
        if (!$provider->isConfigured()) {
            return [
                'success' => false,
                'error' => "Provider '{$providerName}' is not configured. Please add an API key.",
                'error_code' => 'not_configured'
            ];
        }
        
        return $provider->chatCompletion($messages, $options);
    }
    
    /**
     * Send a text completion request using specified or default provider
     *
     * @param string $prompt The prompt to complete
     * @param array $options Options including 'provider' to specify which provider to use
     * @return array Response data
     */
    public function textCompletion(string $prompt, array $options = []): array {
        $providerName = $options['provider'] ?? $this->defaultProvider;
        unset($options['provider']);
        
        $provider = $this->getProvider($providerName);
        
        if (!$provider) {
            return [
                'success' => false,
                'error' => "Provider '{$providerName}' is not registered",
                'error_code' => 'invalid_provider'
            ];
        }
        
        if (!$provider->isConfigured()) {
            return [
                'success' => false,
                'error' => "Provider '{$providerName}' is not configured. Please add an API key.",
                'error_code' => 'not_configured'
            ];
        }
        
        return $provider->textCompletion($prompt, $options);
    }
    
    /**
     * Get available models for a provider
     *
     * @param string|null $providerName Provider name (null for default)
     * @return array List of models or error
     */
    public function getAvailableModels(?string $providerName = null): array {
        $provider = $this->getProvider($providerName);
        
        if (!$provider) {
            return [
                'success' => false,
                'error' => "Provider is not registered",
                'error_code' => 'invalid_provider'
            ];
        }
        
        return [
            'success' => true,
            'models' => $provider->getAvailableModels()
        ];
    }
    
    /**
     * Validate API key for a provider
     *
     * @param string|null $providerName Provider name (null for default)
     * @return array Validation result
     */
    public function validateApiKey(?string $providerName = null): array {
        $provider = $this->getProvider($providerName);
        
        if (!$provider) {
            return [
                'success' => false,
                'error' => "Provider is not registered",
                'error_code' => 'invalid_provider'
            ];
        }
        
        return $provider->validateApiKey();
    }
    
    /**
     * Get status of all providers
     *
     * @return array Provider status information
     */
    public function getProvidersStatus(): array {
        $status = [];
        
        foreach ($this->providers as $name => $provider) {
            $status[$name] = [
                'name' => $provider->getName(),
                'configured' => $provider->isConfigured(),
            ];
        }
        
        return $status;
    }
    
    /**
     * Generate content using AI with automatic fallback
     *
     * @param string $prompt The prompt
     * @param array $options Options
     * @param array $fallbackProviders Array of provider names to try if primary fails
     * @return array Response data
     */
    public function generateWithFallback(string $prompt, array $options = [], array $fallbackProviders = []): array {
        $primaryProvider = $options['provider'] ?? $this->defaultProvider;
        $providers = array_merge([$primaryProvider], $fallbackProviders);
        
        $lastError = null;
        
        foreach ($providers as $providerName) {
            $provider = $this->getProvider($providerName);
            
            if (!$provider || !$provider->isConfigured()) {
                continue;
            }
            
            $options['provider'] = $providerName;
            $result = $this->textCompletion($prompt, $options);
            
            if ($result['success']) {
                $result['provider_used'] = $providerName;
                return $result;
            }
            
            $lastError = $result;
        }
        
        return $lastError ?? [
            'success' => false,
            'error' => 'No configured providers available',
            'error_code' => 'no_providers'
        ];
    }
}
