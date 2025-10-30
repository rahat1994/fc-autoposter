<?php
/**
 * FC Autoposter Admin Assets Integration
 * 
 * This file demonstrates how to properly enqueue the built Vite assets in WordPress
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class FC_Autoposter_Admin_Assets {
    
    private $plugin_url;
    private $manifest_path;
    private $manifest_data;
    
    public function __construct($plugin_url) {
        $this->plugin_url = trailingslashit($plugin_url);
        $this->manifest_path = plugin_dir_path(__FILE__) . 'dist/manifest.json';
        $this->load_manifest();
    }
    
    /**
     * Load the Vite manifest file
     */
    private function load_manifest() {
        if (file_exists($this->manifest_path)) {
            $manifest_content = file_get_contents($this->manifest_path);
            $this->manifest_data = json_decode($manifest_content, true);
        }
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook_suffix) {
        // Only load on our admin pages
        if (!$this->is_fc_autoposter_page($hook_suffix)) {
            return;
        }
        
        if (!$this->manifest_data) {
            return;
        }
        
        // Get main entry from manifest
        $main_entry = $this->manifest_data['src/main.js'] ?? null;
        
        if (!$main_entry) {
            return;
        }
        
        // Enqueue CSS
        if (!empty($main_entry['css'])) {
            foreach ($main_entry['css'] as $css_file) {
                wp_enqueue_style(
                    'fc-autoposter-admin-css',
                    $this->plugin_url . 'admin/dist/' . $css_file,
                    [],
                    $this->get_file_version($css_file),
                    'all'
                );
            }
        }
        
        // Enqueue JS
        wp_enqueue_script(
            'fc-autoposter-admin-js',
            $this->plugin_url . 'admin/dist/' . $main_entry['file'],
            [],
            $this->get_file_version($main_entry['file']),
            true
        );
        
        // Add module type for modern JavaScript
        add_filter('script_loader_tag', [$this, 'add_module_type'], 10, 3);
        
        // Localize script with WordPress data
        wp_localize_script('fc-autoposter-admin-js', 'fcAutoposterAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fc_autoposter_nonce'),
            'pluginUrl' => $this->plugin_url,
            'currentUser' => get_current_user_id(),
            'restUrl' => rest_url('fc-autoposter/v1/'),
        ]);
    }
    
    /**
     * Add type="module" to our Vue.js script
     */
    public function add_module_type($tag, $handle, $src) {
        if ('fc-autoposter-admin-js' === $handle) {
            $tag = str_replace('<script ', '<script type="module" ', $tag);
        }
        return $tag;
    }
    
    /**
     * Get file version for cache busting
     */
    private function get_file_version($file) {
        $file_path = plugin_dir_path(__FILE__) . 'dist/' . $file;
        return file_exists($file_path) ? filemtime($file_path) : '1.0.0';
    }
    
    /**
     * Check if current page is FC Autoposter admin page
     */
    private function is_fc_autoposter_page($hook_suffix) {
        // Adjust this based on your admin page hook
        $fc_pages = [
            'toplevel_page_fc-autoposter',
            'fc-autoposter_page_fc-autoposter-settings',
            'fc-autoposter_page_fc-autoposter-schedule',
        ];
        
        return in_array($hook_suffix, $fc_pages);
    }
}

// Example usage in your main plugin file:
/*
// In your main plugin class or functions.php

$fc_admin_assets = new FC_Autoposter_Admin_Assets(plugin_dir_url(__FILE__));
add_action('admin_enqueue_scripts', [$fc_admin_assets, 'enqueue_admin_assets']);

// Create admin page with Vue app container
function fc_autoposter_admin_page() {
    ?>
    <div class="wrap">
        <div id="fc-autoposter-app"></div>
    </div>
    <?php
}
*/