<?php
/**
 * Base Controller
 * 
 * Base class for all controllers with common functionality
 */

namespace FCAutoposter\Controllers;

use FCAutoposter\Routing\Response;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

abstract class Controller {
    
    /**
     * Validate request data
     */
    protected function validate($request, $rules, $messages = []) {
        $errors = [];
        $input = $request->all();
        
        foreach ($rules as $field => $ruleSet) {
            $ruleList = is_array($ruleSet) ? $ruleSet : explode('|', $ruleSet);
            
            foreach ($ruleList as $rule) {
                $error = $this->validateField($field, $input[$field] ?? null, $rule);
                
                if ($error) {
                    $message = $messages[$field] ?? $error;
                    $errors[$field][] = $message;
                    break; // Stop on first error for this field
                }
            }
        }
        
        if (!empty($errors)) {
            return Response::validationError($errors);
        }
        
        return null;
    }
    
    /**
     * Validate a single field
     */
    protected function validateField($field, $value, $rule) {
        // Handle rules with parameters (e.g., min:3, max:255)
        $parts = explode(':', $rule);
        $ruleName = $parts[0];
        $params = isset($parts[1]) ? explode(',', $parts[1]) : [];
        
        switch ($ruleName) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    return "The {$field} field is required.";
                }
                break;
                
            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return "The {$field} must be a valid email address.";
                }
                break;
                
            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    return "The {$field} must be a number.";
                }
                break;
                
            case 'integer':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                    return "The {$field} must be an integer.";
                }
                break;
                
            case 'string':
                if (!empty($value) && !is_string($value)) {
                    return "The {$field} must be a string.";
                }
                break;
                
            case 'array':
                if (!empty($value) && !is_array($value)) {
                    return "The {$field} must be an array.";
                }
                break;
                
            case 'min':
                if (!empty($params[0])) {
                    $min = $params[0];
                    if (is_string($value) && strlen($value) < $min) {
                        return "The {$field} must be at least {$min} characters.";
                    }
                    if (is_numeric($value) && $value < $min) {
                        return "The {$field} must be at least {$min}.";
                    }
                }
                break;
                
            case 'max':
                if (!empty($params[0])) {
                    $max = $params[0];
                    if (is_string($value) && strlen($value) > $max) {
                        return "The {$field} must not exceed {$max} characters.";
                    }
                    if (is_numeric($value) && $value > $max) {
                        return "The {$field} must not exceed {$max}.";
                    }
                }
                break;
                
            case 'in':
                if (!empty($value) && !in_array($value, $params)) {
                    return "The selected {$field} is invalid.";
                }
                break;
                
            case 'url':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                    return "The {$field} must be a valid URL.";
                }
                break;
        }
        
        return null;
    }
    
    /**
     * Get current user ID
     */
    protected function getCurrentUserId() {
        return get_current_user_id();
    }
    
    /**
     * Check if user is logged in
     */
    protected function isLoggedIn() {
        return is_user_logged_in();
    }
    
    /**
     * Check if user has capability
     */
    protected function userCan($capability) {
        return current_user_can($capability);
    }
    
    /**
     * Authorize request
     */
    protected function authorize($capability = 'manage_options') {
        if (!$this->userCan($capability)) {
            return Response::forbidden('You do not have permission to perform this action.');
        }
        
        return null;
    }
}
