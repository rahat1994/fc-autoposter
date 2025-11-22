<?php
/**
 * Content Instruction Model
 * 
 * Handles CRUD operations for content instructions
 */

namespace FCAutoposter\Models;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ContentInstruction extends BaseModel {
    
    /**
     * Table name without prefix
     */
    protected static $table = 'fc_fa_content_instructions';
    
    /**
     * Fillable attributes
     */
    protected $fillable = [
        'instruction', 'metadata', 'target_post_id', 'target_post_type',
        'status', 'attempts', 'last_attempt_at', 'ai_result', 'ai_model',
        'error_message', 'created_at', 'updated_at'
    ];
    
    /**
     * Casts
     */
    protected $casts = [
        'metadata' => 'array',
        'target_post_id' => 'integer',
        'attempts' => 'integer',
        'ai_result' => 'array' // Assuming AI result might be JSON
    ];
    
    /**
     * Get pending instructions
     */
    public static function pending($limit = 10) {
        global $wpdb;
        
        $table_name = static::getTableName();
        
        $results = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE status = 'pending' ORDER BY created_at ASC LIMIT %d", $limit),
            ARRAY_A
        );
        
        return array_map(function($result) {
            return new static($result);
        }, $results);
    }
    
    /**
     * Mark as processing
     */
    public function markAsProcessing() {
        $this->update([
            'status' => 'processing',
            'attempts' => ($this->attempts ?? 0) + 1,
            'last_attempt_at' => current_time('mysql')
        ]);
    }
    
    /**
     * Mark as completed
     */
    public function markAsCompleted($result, $model) {
        $this->update([
            'status' => 'completed',
            'ai_result' => $result,
            'ai_model' => $model
        ]);
    }
    
    /**
     * Mark as failed
     */
    public function markAsFailed($error) {
        $this->update([
            'status' => 'failed',
            'error_message' => $error
        ]);
    }
}
