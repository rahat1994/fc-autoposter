<?php
/**
 * Settings Controller
 * 
 * Handles settings-related API endpoints
 */

namespace FCAutoposter\Controllers;

use FCAutoposter\Routing\Response;
use FCAutoposter\Routing\Request;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class SettingsController {
    
    /**
     * Get all settings
     */
    public function getSettings(Request $request) {
        try {
            $settings = [
                'openrouter_api_key' => get_option('fc_autoposter_openrouter_api_key', ''),
                'openai_api_key' => get_option('fc_autoposter_openai_api_key', ''),
                'google_api_key' => get_option('fc_autoposter_google_api_key', ''),
            ];
            
            // Mask API keys for security
            foreach ($settings as $key => $value) {
                if (!empty($value)) {
                    $settings[$key] = $this->maskApiKey($value);
                }
            }
            
            return Response::success('Settings retrieved successfully', $settings);
            
        } catch (\Exception $e) {
            error_log('FC Autoposter Settings Controller Error: ' . $e->getMessage());
            return Response::error('Failed to retrieve settings', $e->getMessage(), 500);
        }
    }
    
    /**
     * Save settings
     */
    public function saveSettings(Request $request) {
        try {
            $data = $request->all();
            
            // Update OpenRouter API Key
            if (isset($data['openrouter_api_key'])) {
                // Only update if it's not masked (meaning it's a new key)
                if (!$this->isMasked($data['openrouter_api_key'])) {
                    update_option('fc_autoposter_openrouter_api_key', sanitize_text_field($data['openrouter_api_key']));
                }
            }
            
            // Update OpenAI API Key
            if (isset($data['openai_api_key'])) {
                if (!$this->isMasked($data['openai_api_key'])) {
                    update_option('fc_autoposter_openai_api_key', sanitize_text_field($data['openai_api_key']));
                }
            }
            
            // Update Google API Key
            if (isset($data['google_api_key'])) {
                if (!$this->isMasked($data['google_api_key'])) {
                    update_option('fc_autoposter_google_api_key', sanitize_text_field($data['google_api_key']));
                }
            }
            
            return Response::success('Settings saved successfully');
            
        } catch (\Exception $e) {
            error_log('FC Autoposter Settings Controller Error: ' . $e->getMessage());
            return Response::error('Failed to save settings', $e->getMessage(), 500);
        }
    }
    
    /**
     * Mask API key for display
     */
    private function maskApiKey($key) {
        if (empty($key) || strlen($key) < 8) {
            return '********';
        }
        
        return substr($key, 0, 4) . '********' . substr($key, -4);
    }
    
    /**
     * Check if a key is masked
     */
    private function isMasked($key) {
        return strpos($key, '********') !== false;
    }
}
