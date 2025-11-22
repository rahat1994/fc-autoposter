<?php
/**
 * Migration: Create FC FA Posts Table
 * 
 * Creates the posts pivot table for tracking generated content
 */

namespace FCAutoposter\Database\Migrations;

use FCAutoposter\Database\Migration;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CreateFcFaPostsTable extends Migration {
    
    /**
     * Migration version
     */
    protected $version = '1.0.0';
    
    /**
     * Table name without prefix
     */
    protected $table = 'fc_fa_posts';
    
    /**
     * Run the migration
     */
    public function up() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . $this->table;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            fcom_post_id bigint(20) unsigned NOT NULL,
            agent_id bigint(20) unsigned NOT NULL,
            instruction_id bigint(20) unsigned NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_fcom_post_id (fcom_post_id),
            KEY idx_agent_id (agent_id),
            KEY idx_created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Check if table was created successfully
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            throw new \Exception("Failed to create table: $table_name");
        }
        
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
        
        // Check version tracking
        $current_version = get_option('fc_fa_posts_table_version', '0.0.0');
        $version_outdated = version_compare($current_version, $this->version, '<');
        
        if ($version_outdated) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Post migration actions
     */
    public function postMigration() {
        // Update the version option
        update_option('fc_fa_posts_table_version', $this->version);
        
        // Clear any relevant caches
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
    }
}
