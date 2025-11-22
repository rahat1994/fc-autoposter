<?php
/**
 * Post Model
 * 
 * Handles CRUD operations for posts pivot table
 */

namespace FCAutoposter\Models;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Post extends BaseModel {
    
    /**
     * Table name without prefix
     */
    protected static $table = 'fc_fa_posts';
    
    /**
     * Fillable attributes
     */
    protected $fillable = [
        'fcom_post_id', 'agent_id', 'instruction_id',
        'created_at', 'updated_at'
    ];
    
    /**
     * Casts
     */
    protected $casts = [
        'fcom_post_id' => 'integer',
        'agent_id' => 'integer',
        'instruction_id' => 'integer'
    ];
    
    /**
     * Get post with details
     */
    public static function getWithDetails($page = 1, $perPage = 10) {
        global $wpdb;
        
        $table_name = static::getTableName();
        $fcom_posts_table = $wpdb->prefix . 'fcom_posts';
        $agents_table = Agent::getTableName();
        
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $total = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
        
        // Get items with joins
        $sql = "
            SELECT 
                p.*,
                fp.title as post_title,
                fp.status as post_status,
                fp.created_at as post_date,
                a.name as agent_name
            FROM {$table_name} p
            LEFT JOIN {$fcom_posts_table} fp ON p.fcom_post_id = fp.id
            LEFT JOIN {$agents_table} a ON p.agent_id = a.id
            ORDER BY p.created_at DESC
            LIMIT %d OFFSET %d
        ";
        
        $results = $wpdb->get_results($wpdb->prepare($sql, $perPage, $offset), ARRAY_A);
        
        return [
            'data' => $results,
            'meta' => [
                'current_page' => (int) $page,
                'per_page' => (int) $perPage,
                'total' => (int) $total,
                'last_page' => ceil($total / $perPage)
            ]
        ];
    }
}
