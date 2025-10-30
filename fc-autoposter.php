<?php
/**
 * Plugin Name: FC Autoposter
 * Plugin URI: https://github.com/rahat1994/fc-autoposter
 * Description: A WordPress plugin with Vue and Vite admin panel
 * Version: 1.0.0
 * Author: Rahat
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: fc-autoposter
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('FC_AUTOPOSTER_VERSION', '1.0.0');
define('FC_AUTOPOSTER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FC_AUTOPOSTER_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Add admin menu
 */
function fc_autoposter_add_admin_menu() {
    add_menu_page(
        'FC Autoposter',           // Page title
        'FC Autoposter',           // Menu title
        'manage_options',          // Capability
        'fc-autoposter',           // Menu slug
        'fc_autoposter_admin_page', // Function
        'dashicons-share',         // Icon
        30                         // Position
    );
}
add_action('admin_menu', 'fc_autoposter_add_admin_menu');

/**
 * Render admin page
 */
function fc_autoposter_admin_page() {
    ?>
    <div class="wrap">
        <div id="fc-autoposter-app"></div>
    </div>
    <?php
}

/**
 * Enqueue admin scripts and styles
 */
function fc_autoposter_enqueue_admin_scripts($hook) {
    // Only load on our plugin page
    if ($hook !== 'toplevel_page_fc-autoposter') {
        return;
    }

    // Get the manifest file
    $manifest_path = FC_AUTOPOSTER_PLUGIN_DIR . 'admin/dist/manifest.json';
    
    if (!file_exists($manifest_path)) {
        // Development mode - load from Vite dev server
        // Only enable in development environments
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $dev_server_url = defined('FC_AUTOPOSTER_DEV_SERVER') ? FC_AUTOPOSTER_DEV_SERVER : 'http://localhost:5173';
            
            wp_enqueue_script(
                'fc-autoposter-vite-client',
                $dev_server_url . '/@vite/client',
                array(),
                null,
                true
            );
            wp_enqueue_script(
                'fc-autoposter-app',
                $dev_server_url . '/src/main.js',
                array(),
                null,
                true
            );
        } else {
            // Production build missing - show admin notice
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>';
                echo 'FC Autoposter: Production build not found. Please run <code>npm run build</code> in the admin directory.';
                echo '</p></div>';
            });
        }
    } else {
        // Production mode - load from dist
        $manifest_content = file_get_contents($manifest_path);
        if ($manifest_content === false) {
            error_log('FC Autoposter: Unable to read manifest file');
            return;
        }
        
        $manifest = json_decode($manifest_content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('FC Autoposter: Invalid JSON in manifest file: ' . json_last_error_msg());
            return;
        }
        
        if (!isset($manifest['src/main.js'])) {
            error_log('FC Autoposter: Invalid manifest structure');
            return;
        }
        
        // Enqueue CSS
        if (isset($manifest['src/main.js']['css'])) {
            foreach ($manifest['src/main.js']['css'] as $css_file) {
                wp_enqueue_style(
                    'fc-autoposter-style',
                    FC_AUTOPOSTER_PLUGIN_URL . 'admin/dist/' . $css_file,
                    array(),
                    FC_AUTOPOSTER_VERSION
                );
            }
        }
        
        // Enqueue JS
        if (isset($manifest['src/main.js']['file'])) {
            wp_enqueue_script(
                'fc-autoposter-app',
                FC_AUTOPOSTER_PLUGIN_URL . 'admin/dist/' . $manifest['src/main.js']['file'],
                array(),
                FC_AUTOPOSTER_VERSION,
                true
            );
        }
    }
}
add_action('admin_enqueue_scripts', 'fc_autoposter_enqueue_admin_scripts');
