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

class Agent {
    
    /**
     * Table name without prefix
     */
    protected static $table = 'fc_fa_agents';
    
    /**
     * Agent properties
     */
    protected $id;
    protected $name;
    protected $description;
    protected $type;
    protected $model;
    protected $status;
    protected $system_prompt;
    protected $web_search;
    protected $file_processing;
    protected $create_user;
    protected $user_id;
    protected $username;
    protected $user_email;
    protected $interactions;
    protected $settings;
    protected $created_at;
    protected $updated_at;
    
    /**
     * Constructor
     */
    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
    
    /**
     * Get table name with WordPress prefix
     */
    protected static function getTableName() {
        global $wpdb;
        return $wpdb->prefix . static::$table;
    }
    
    /**
     * Find agent by ID
     */
    public static function find($id) {
        global $wpdb;
        
        $table_name = static::getTableName();
        
        $result = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $id),
            ARRAY_A
        );
        
        return $result ? new static($result) : null;
    }
    
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
     * Get all agents
     */
    public static function all($status = null) {
        global $wpdb;
        
        $table_name = static::getTableName();
        
        $sql = "SELECT * FROM {$table_name}";
        
        if ($status) {
            $sql .= $wpdb->prepare(" WHERE status = %s", $status);
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $results = $wpdb->get_results($sql, ARRAY_A);
        
        return array_map(function($result) {
            return new static($result);
        }, $results);
    }
    
    /**
     * Get active agents
     */
    public static function active() {
        return static::all('active');
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
        global $wpdb;
        
        $table_name = static::getTableName();
        
        // Prepare data for insertion
        $insert_data = [
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'type' => $data['type'],
            'model' => $data['model'],
            'status' => $data['status'] ?? 'active',
            'system_prompt' => $data['system_prompt'],
            'web_search' => $data['web_search'] ?? 0,
            'file_processing' => $data['file_processing'] ?? 0,
            'create_user' => $data['create_user'] ?? 0,
            'user_id' => $data['user_id'] ?? null,
            'username' => $data['username'] ?? '',
            'user_email' => $data['user_email'] ?? '',
            'interactions' => 0,
            'settings' => isset($data['settings']) ? json_encode($data['settings']) : null,
        ];
        
        $result = $wpdb->insert($table_name, $insert_data);
        
        if ($result === false) {
            return false;
        }
        
        return static::find($wpdb->insert_id);
    }
    
    /**
     * Update the agent
     */
    public function update($data) {
        global $wpdb;
        
        if (!$this->id) {
            return false;
        }
        
        $table_name = static::getTableName();
        
        // Prepare data for update
        $update_data = [];
        $allowed_fields = [
            'name', 'description', 'type', 'model', 'status',
            'system_prompt', 'web_search', 'file_processing',
            'create_user', 'user_id', 'username', 'user_email',
            'interactions', 'settings'
        ];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed_fields)) {
                if ($key === 'settings' && is_array($value)) {
                    $update_data[$key] = json_encode($value);
                } else {
                    $update_data[$key] = $value;
                }
            }
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        $result = $wpdb->update($table_name, $update_data, ['id' => $this->id]);
        
        if ($result !== false) {
            // Update object properties
            foreach ($update_data as $key => $value) {
                $this->$key = $value;
            }
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete the agent
     */
    public function delete() {
        global $wpdb;
        
        if (!$this->id) {
            return false;
        }
        
        $table_name = static::getTableName();
        
        return $wpdb->delete($table_name, ['id' => $this->id]) !== false;
    }
    
    /**
     * Increment interactions count
     */
    public function incrementInteractions() {
        global $wpdb;
        
        if (!$this->id) {
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
    
    /**
     * Convert to array
     */
    public function toArray() {
        $data = [];
        $properties = [
            'id', 'name', 'description', 'type', 'model', 'status',
            'system_prompt', 'web_search', 'file_processing', 'create_user',
            'user_id', 'username', 'user_email', 'interactions', 'settings',
            'created_at', 'updated_at'
        ];
        
        foreach ($properties as $property) {
            if (isset($this->$property)) {
                if ($property === 'settings' && is_string($this->$property)) {
                    $data[$property] = json_decode($this->$property, true);
                } else {
                    $data[$property] = $this->$property;
                }
            }
        }
        
        return $data;
    }
    
    /**
     * Magic getter
     */
    public function __get($property) {
        if (property_exists($this, $property)) {
            if ($property === 'settings' && is_string($this->$property)) {
                return json_decode($this->$property, true);
            }
            return $this->$property;
        }
        
        return null;
    }
    
    /**
     * Magic setter
     */
    public function __set($property, $value) {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }
}