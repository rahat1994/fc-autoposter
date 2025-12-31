<?php
/**
 * Plugin Uninstall Script
 * 
 * This file runs when the plugin is deleted from WordPress admin.
 * It handles complete cleanup of plugin data.
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Load plugin constants if not already loaded
if (!defined('FC_AUTOPOSTER_VERSION')) {
    define('FC_AUTOPOSTER_VERSION', '1.0.0');
    define('FC_AUTOPOSTER_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

// Load Composer autoloader if available
if (file_exists(FC_AUTOPOSTER_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once FC_AUTOPOSTER_PLUGIN_DIR . 'vendor/autoload.php';
}

/**
 * Clean up plugin data
 */
function fc_autoposter_uninstall_cleanup() {
    global $wpdb;
    
    error_log('FC Autoposter: Starting uninstall cleanup...');
    
    try {
        // Remove plugin options
        delete_option('fc_autoposter_activated');
        delete_option('fc_autoposter_version');
        delete_option('fc_autoposter_activation_time');
        delete_option('fc_autoposter_activation_notice_dismissed');
        delete_option('fc_fa_agents_table_version');
        
        // Remove any transients
        delete_transient('fc_autoposter_migration_status');
        
        // Remove custom tables (uncomment if you want to remove data on uninstall)
        /*
        $table_name = $wpdb->prefix . 'fc_fa_agents';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
        error_log('FC Autoposter: Dropped agents table');
        */
        
        // Clear any caches
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
        error_log('FC Autoposter: Uninstall cleanup completed successfully');
        
    } catch (\Exception $e) {
        error_log('FC Autoposter Uninstall Error: ' . $e->getMessage());
    }
}

// Run the cleanup
fc_autoposter_uninstall_cleanup();
?>