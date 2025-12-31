<?php
/**
 * Base Model
 * 
 * Abstract base class for models to handle common CRUD operations
 */

namespace FCAutoposter\Models;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

abstract class BaseModel {
    
    /**
     * Table name without prefix
     * @var string
     */
    protected static $table;
    
    /**
     * Primary key
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * Attributes
     * @var array
     */
    protected $attributes = [];
    
    /**
     * Fillable attributes
     * @var array
     */
    protected $fillable = [];
    
    /**
     * Casts
     * @var array
     */
    protected $casts = [];
    
    /**
     * Constructor
     */
    public function __construct($data = []) {
        $this->fill($data);
    }
    
    /**
     * Fill attributes
     */
    public function fill($data) {
        foreach ($data as $key => $value) {
            $this->__set($key, $value);
        }
    }
    
    /**
     * Get table name with WordPress prefix
     */
    public static function getTableName() {
        global $wpdb;
        return $wpdb->prefix . static::$table;
    }
    
    /**
     * Find model by ID
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
     * Get all records
     */
    public static function all() {
        global $wpdb;
        
        $table_name = static::getTableName();
        
        $results = $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY created_at DESC", ARRAY_A);
        
        return array_map(function($result) {
            return new static($result);
        }, $results);
    }
    
    /**
     * Create a new record
     */
    public static function create($data) {
        global $wpdb;
        
        $instance = new static();
        $table_name = static::getTableName();
        
        // Filter data based on fillable
        $insert_data = [];
        if (!empty($instance->fillable)) {
            foreach ($data as $key => $value) {
                if (in_array($key, $instance->fillable)) {
                    $insert_data[$key] = $instance->prepareValueForStorage($key, $value);
                }
            }
        } else {
            $insert_data = $data;
        }
        
        $result = $wpdb->insert($table_name, $insert_data);
        
        if ($result === false) {
            return false;
        }
        
        return static::find($wpdb->insert_id);
    }
    
    /**
     * Update the record
     */
    public function update($data) {
        global $wpdb;
        
        if (!isset($this->attributes[$this->primaryKey])) {
            return false;
        }
        
        $table_name = static::getTableName();
        
        // Filter data based on fillable
        $update_data = [];
        if (!empty($this->fillable)) {
            foreach ($data as $key => $value) {
                if (in_array($key, $this->fillable)) {
                    $update_data[$key] = $this->prepareValueForStorage($key, $value);
                }
            }
        } else {
            $update_data = $data;
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        $result = $wpdb->update(
            $table_name, 
            $update_data, 
            [$this->primaryKey => $this->attributes[$this->primaryKey]]
        );
        
        if ($result !== false) {
            $this->fill($data);
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete the record
     */
    public function delete() {
        global $wpdb;
        
        if (!isset($this->attributes[$this->primaryKey])) {
            return false;
        }
        
        $table_name = static::getTableName();
        
        return $wpdb->delete(
            $table_name, 
            [$this->primaryKey => $this->attributes[$this->primaryKey]]
        ) !== false;
    }
    
    /**
     * Paginate records
     */
    public static function paginate($page = 1, $perPage = 10, $where = [], $orderBy = 'created_at', $order = 'DESC') {
        global $wpdb;
        
        $table_name = static::getTableName();
        $offset = ($page - 1) * $perPage;
        
        $where_clause = "";
        $params = [];
        
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                $conditions[] = "$key = %s";
                $params[] = $value;
            }
            $where_clause = "WHERE " . implode(" AND ", $conditions);
        }
        
        // Get total count
        $count_sql = "SELECT COUNT(*) FROM {$table_name} {$where_clause}";
        if (!empty($params)) {
            $total = $wpdb->get_var($wpdb->prepare($count_sql, $params));
        } else {
            $total = $wpdb->get_var($count_sql);
        }
        
        // Get items
        $sql = "SELECT * FROM {$table_name} {$where_clause} ORDER BY {$orderBy} {$order} LIMIT %d OFFSET %d";
        $params[] = $perPage;
        $params[] = $offset;
        
        $results = $wpdb->get_results($wpdb->prepare($sql, $params), ARRAY_A);
        
        $items = array_map(function($result) {
            return new static($result);
        }, $results);
        
        return [
            'data' => $items,
            'meta' => [
                'current_page' => (int) $page,
                'per_page' => (int) $perPage,
                'total' => (int) $total,
                'last_page' => ceil($total / $perPage)
            ]
        ];
    }
    
    /**
     * Prepare value for storage based on casts
     */
    protected function prepareValueForStorage($key, $value) {
        if (isset($this->casts[$key])) {
            switch ($this->casts[$key]) {
                case 'array':
                case 'json':
                    return is_array($value) || is_object($value) ? json_encode($value) : $value;
                case 'int':
                case 'integer':
                    return (int) $value;
                case 'bool':
                case 'boolean':
                    return (bool) $value;
                case 'float':
                case 'double':
                    return (float) $value;
            }
        }
        return $value;
    }
    
    /**
     * Cast value from storage
     */
    protected function castValue($key, $value) {
        if (isset($this->casts[$key])) {
            switch ($this->casts[$key]) {
                case 'array':
                case 'json':
                    return is_string($value) ? json_decode($value, true) : $value;
                case 'int':
                case 'integer':
                    return (int) $value;
                case 'bool':
                case 'boolean':
                    return (bool) $value;
                case 'float':
                case 'double':
                    return (float) $value;
            }
        }
        return $value;
    }
    
    /**
     * Convert to array
     */
    public function toArray() {
        $data = [];
        foreach ($this->attributes as $key => $value) {
            $data[$key] = $this->castValue($key, $value);
        }
        return $data;
    }
    
    /**
     * Magic getter
     */
    public function __get($key) {
        if (array_key_exists($key, $this->attributes)) {
            return $this->castValue($key, $this->attributes[$key]);
        }
        return null;
    }
    
    /**
     * Magic setter
     */
    public function __set($key, $value) {
        $this->attributes[$key] = $value;
    }
    
    /**
     * Check if property exists
     */
    public function __isset($key) {
        return isset($this->attributes[$key]);
    }
}
