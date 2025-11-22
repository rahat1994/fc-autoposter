<?php
/**
 * Agent Model
 * 
 * Handles CRUD operations for AI agents
 */

namespace FCAutoposter\Models;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Agent extends BaseModel {
    
    /**
     * Table name without prefix
     */
    protected static $table = 'fc_fa_agents';
    
    /**
     * Fillable attributes
     */
    protected $fillable = [
        'name', 'description', 'type', 'model', 'status',
        'system_prompt', 'web_search', 'file_processing',
        'create_user', 'user_id', 'username', 'user_email',
        'interactions', 'settings', 'created_at', 'updated_at'
    ];
    
    /**
     * Casts
     */
    protected $casts = [
        'settings' => 'array',
        'web_search' => 'boolean',
        'file_processing' => 'boolean',
        'create_user' => 'boolean',
        'interactions' => 'integer',
        'user_id' => 'integer'
    ];
    
    /**
     * Find agent by name
     */
    public static function findByName($name) {
        global $wpdb;
        
        $table_name = static::getTableName();
        
        $result = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE name = %s", $name),
            ARRAY_A
        );
        
        return $result ? new static($result) : null;
    }
    
    /**
     * Get active agents
     */
    public static function active() {
        return static::all(); // Filter is applied in all() if needed, but base all() gets everything. 
        // Wait, original active() called all('active'). 
        // BaseModel::all() doesn't take arguments.
        // We should implement active() using get_results or similar, or just use paginate logic?
        // Original all($status) was: SELECT * FROM table WHERE status = $status
        
        // Let's reimplement active() properly using the DB directly or a new method in BaseModel if we wanted generic "where".
        // But for now, let's just do it here.
        
        global $wpdb;
        $table_name = static::getTableName();
        $results = $wpdb->get_results("SELECT * FROM {$table_name} WHERE status = 'active' ORDER BY created_at DESC", ARRAY_A);
        
        return array_map(function($result) {
            return new static($result);
        }, $results);
    }
    
    /**
     * Get agents by type
     */
    public static function byType($type) {
        global $wpdb;
        
        $table_name = static::getTableName();
        
        $results = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE type = %s ORDER BY created_at DESC", $type),
            ARRAY_A
        );
        
        return array_map(function($result) {
            return new static($result);
        }, $results);
    }
    
    /**
     * Create a new agent
     */
    public static function create($data) {
        // Set defaults
        $defaults = [
            'description' => '',
            'status' => 'active',
            'web_search' => 0,
            'file_processing' => 0,
            'create_user' => 0,
            'user_id' => null,
            'username' => '',
            'user_email' => '',
            'interactions' => 0,
        ];
        
        $data = array_merge($defaults, $data);
        
        return parent::create($data);
    }
    
    /**
     * Increment interactions count
     */
    public function incrementInteractions() {
        global $wpdb;
        
        if (!isset($this->attributes['id'])) {
            return false;
        }
        
        $table_name = static::getTableName();
        
        $result = $wpdb->query(
            $wpdb->prepare("UPDATE {$table_name} SET interactions = interactions + 1 WHERE id = %d", $this->id)
        );
        
        if ($result !== false) {
            $this->interactions = ($this->interactions ?? 0) + 1;
            return true;
        }
        
        return false;
    }
    
    /**
     * Get agent statistics
     */
    public static function getStats() {
        global $wpdb;
        
        $table_name = static::getTableName();
        
        $stats = $wpdb->get_row("
            SELECT 
                COUNT(*) as total_agents,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_agents,
                COUNT(CASE WHEN status = 'inactive' THEN 1 END) as inactive_agents,
                SUM(interactions) as total_interactions,
                AVG(interactions) as avg_interactions
            FROM {$table_name}
        ", ARRAY_A);
        
        return [
            'total_agents' => (int) ($stats['total_agents'] ?? 0),
            'active_agents' => (int) ($stats['active_agents'] ?? 0),
            'inactive_agents' => (int) ($stats['inactive_agents'] ?? 0),
            'total_interactions' => (int) ($stats['total_interactions'] ?? 0),
            'avg_interactions' => round((float) ($stats['avg_interactions'] ?? 0), 2),
        ];
    }
}