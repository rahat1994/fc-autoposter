<?php
/**
 * Migration: Create FC FA Agents Table
 * 
 * Creates the agents table for storing AI agent configurations
 */

namespace FCAutoposter\Database\Migrations;

use FCAutoposter\Database\Migration;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CreateFcFaAgentsTable extends Migration {
    
    /**
     * Migration version
     */
    protected $version = '1.0.0';
    
    /**
     * Table name without prefix
     */
    protected $table = 'fc_fa_agents';
    
    /**
     * Run the migration
     */
    public function up() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . $this->table;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            type varchar(100) NOT NULL,
            model varchar(100) NOT NULL,
            status enum('active', 'inactive') NOT NULL DEFAULT 'active',
            system_prompt longtext NOT NULL,
            web_search tinyint(1) NOT NULL DEFAULT 0,
            file_processing tinyint(1) NOT NULL DEFAULT 0,
            create_user tinyint(1) NOT NULL DEFAULT 0,
            user_id bigint(20) unsigned NULL,
            username varchar(100) NULL,
            user_email varchar(100) NULL,
            interactions bigint(20) unsigned NOT NULL DEFAULT 0,
            settings longtext NULL COMMENT 'JSON encoded settings',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_agent_type (type),
            KEY idx_agent_status (status),
            KEY idx_agent_user_id (user_id),
            KEY idx_agent_created_at (created_at),
            UNIQUE KEY uk_agent_name (name)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Check if table was created successfully
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            throw new \Exception("Failed to create table: $table_name");
        }
        
        // Add any initial data if needed
        $this->seed();
        
        return true;
    }
    
    /**
     * Reverse the migration
     */
    public function down() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . $this->table;
        
        $sql = "DROP TABLE IF EXISTS $table_name";
        $wpdb->query($sql);
        
        return true;
    }
    
    /**
     * Seed initial data (optional)
     */
    protected function seed() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . $this->table;
        
        // You can add default agents here if needed
        // Example:
        /*
        $default_agents = [
            [
                'name' => 'Content Writer Bot',
                'description' => 'AI agent for creating engaging content',
                'type' => 'content-writer',
                'model' => 'gpt-4',
                'status' => 'active',
                'system_prompt' => 'You are a creative content writer specializing in engaging social media posts and blog content. Write in a conversational tone that resonates with the target audience.',
                'web_search' => 1,
                'file_processing' => 1,
                'create_user' => 0,
                'interactions' => 0
            ]
        ];
        
        foreach ($default_agents as $agent) {
            $wpdb->insert($table_name, $agent);
        }
        */
    }
    
    /**
     * Check if migration is needed
     */
    public function shouldRun() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . $this->table;
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        if (!$table_exists) {
            return true;
        }
        
        // Check if we need to update the table structure
        // You can add version checks here if needed
        $current_version = get_option('fc_fa_agents_table_version', '0.0.0');
        
        return version_compare($current_version, $this->version, '<');
    }
    
    /**
     * Post migration actions
     */
    public function postMigration() {
        // Update the version option
        update_option('fc_fa_agents_table_version', $this->version);
        
        // Clear any relevant caches
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
        // Log the migration
        error_log("FC Autoposter: Agents table migration completed - Version: {$this->version}");
    }
}
