<?php
/**
 * Base Migration Class
 * 
 * Provides common functionality for database migrations
 */

namespace FCAutoposter\Database;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

abstract class Migration {
    
    /**
     * Migration version
     */
    protected $version = '1.0.0';
    
    /**
     * Table name without prefix
     */
    protected $table = '';
    
    /**
     * Run the migration (create tables, add columns, etc.)
     * 
     * @return bool
     */
    abstract public function up();
    
    /**
     * Reverse the migration (drop tables, remove columns, etc.)
     * 
     * @return bool
     */
    abstract public function down();
    
    /**
     * Check if this migration should run
     * 
     * @return bool
     */
    public function shouldRun() {
        return true;
    }
    
    /**
     * Actions to perform after migration completes
     */
    public function postMigration() {
        // Override in child classes if needed
    }
    
    /**
     * Get the full table name with WordPress prefix
     * 
     * @param string $table_name
     * @return string
     */
    protected function getTableName($table_name = null) {
        global $wpdb;
        
        $table = $table_name ?: $this->table;
        return $wpdb->prefix . $table;
    }
    
    /**
     * Check if a table exists
     * 
     * @param string $table_name
     * @return bool
     */
    protected function tableExists($table_name = null) {
        global $wpdb;
        
        $full_table_name = $this->getTableName($table_name);
        
        return $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") == $full_table_name;
    }
    
    /**
     * Check if a column exists in a table
     * 
     * @param string $column_name
     * @param string $table_name
     * @return bool
     */
    protected function columnExists($column_name, $table_name = null) {
        global $wpdb;
        
        $full_table_name = $this->getTableName($table_name);
        
        $column = $wpdb->get_results(
            $wpdb->prepare(
                "SHOW COLUMNS FROM `{$full_table_name}` LIKE %s",
                $column_name
            )
        );
        
        return !empty($column);
    }
    
    /**
     * Add a column to a table
     * 
     * @param string $column_name
     * @param string $column_definition
     * @param string $table_name
     * @param string $after_column
     * @return bool
     */
    protected function addColumn($column_name, $column_definition, $table_name = null, $after_column = null) {
        global $wpdb;
        
        if ($this->columnExists($column_name, $table_name)) {
            return true; // Column already exists
        }
        
        $full_table_name = $this->getTableName($table_name);
        
        $sql = "ALTER TABLE `{$full_table_name}` ADD COLUMN `{$column_name}` {$column_definition}";
        
        if ($after_column) {
            $sql .= " AFTER `{$after_column}`";
        }
        
        return $wpdb->query($sql) !== false;
    }
    
    /**
     * Drop a column from a table
     * 
     * @param string $column_name
     * @param string $table_name
     * @return bool
     */
    protected function dropColumn($column_name, $table_name = null) {
        global $wpdb;
        
        if (!$this->columnExists($column_name, $table_name)) {
            return true; // Column doesn't exist
        }
        
        $full_table_name = $this->getTableName($table_name);
        
        $sql = "ALTER TABLE `{$full_table_name}` DROP COLUMN `{$column_name}`";
        
        return $wpdb->query($sql) !== false;
    }
    
    /**
     * Add an index to a table
     * 
     * @param string $index_name
     * @param array $columns
     * @param string $table_name
     * @param string $type (INDEX, UNIQUE, etc.)
     * @return bool
     */
    protected function addIndex($index_name, array $columns, $table_name = null, $type = 'INDEX') {
        global $wpdb;
        
        $full_table_name = $this->getTableName($table_name);
        
        $columns_str = '`' . implode('`, `', $columns) . '`';
        
        $sql = "ALTER TABLE `{$full_table_name}` ADD {$type} `{$index_name}` ({$columns_str})";
        
        return $wpdb->query($sql) !== false;
    }
    
    /**
     * Drop an index from a table
     * 
     * @param string $index_name
     * @param string $table_name
     * @return bool
     */
    protected function dropIndex($index_name, $table_name = null) {
        global $wpdb;
        
        $full_table_name = $this->getTableName($table_name);
        
        $sql = "ALTER TABLE `{$full_table_name}` DROP INDEX `{$index_name}`";
        
        return $wpdb->query($sql) !== false;
    }
    
    /**
     * Execute a raw SQL query
     * 
     * @param string $sql
     * @return mixed
     */
    protected function execute($sql) {
        global $wpdb;
        
        return $wpdb->query($sql);
    }
    
    /**
     * Get the migration name from the class name
     * 
     * @return string
     */
    public function getName() {
        return get_class($this);
    }
    
    /**
     * Get migration version
     * 
     * @return string
     */
    public function getVersion() {
        return $this->version;
    }
}