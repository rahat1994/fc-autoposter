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
 * Get environment configuration
 */
function fc_autoposter_get_env_config() {
    $env_config_path = FC_AUTOPOSTER_PLUGIN_DIR . 'admin/env-config.php';
    
    if (file_exists($env_config_path)) {
        return include $env_config_path;
    }
    
    // Default to production if no config file
    return [
        'mode' => 'production',
        'dev_server' => 'http://localhost:5173',
        'timestamp' => 0
    ];
}

/**
 * Check if development server is running
 */
function fc_autoposter_is_dev_server_running($dev_server_url) {
    // Simple check to see if dev server is accessible
    $context = stream_context_create([
        'http' => [
            'timeout' => 1,
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents($dev_server_url, false, $context);
    return $response !== false;
}

/**
 * Enqueue admin scripts and styles
 */
function fc_autoposter_enqueue_admin_scripts($hook) {
    // Only load on our plugin page
    if ($hook !== 'toplevel_page_fc-autoposter') {
        return;
    }

    // Get environment configuration
    $env_config = fc_autoposter_get_env_config();
    $is_dev_mode = $env_config['mode'] === 'development';
    $dev_server_url = $env_config['dev_server'];
    
    // Add environment info to page for debugging
    if (defined('WP_DEBUG') && WP_DEBUG) {
        echo "<!-- FC Autoposter Debug: Mode = {$env_config['mode']}, Timestamp = {$env_config['timestamp']} -->\n";
    }
    
    if ($is_dev_mode && fc_autoposter_is_dev_server_running($dev_server_url)) {
        // Development mode - load from Vite dev server
        
        // Add CORS headers for development
        add_action('admin_head', function() use ($dev_server_url) {
            $parsed_url = parse_url($dev_server_url);
            $dev_origin = $parsed_url['scheme'] . '://' . $parsed_url['host'] . ':' . $parsed_url['port'];
            
            echo '<meta name="fc-autoposter-dev-server" content="' . esc_attr($dev_server_url) . '">' . "\n";
            echo '<script>window.FC_AUTOPOSTER_DEV_SERVER = "' . esc_js($dev_server_url) . '";</script>' . "\n";
        });
        
        wp_enqueue_script(
            'fc-autoposter-vite-client',
            $dev_server_url . '/@vite/client',
            array(),
            null,
            true
        );
        
        // Add type="module" and crossorigin to dev server scripts
        add_filter('script_loader_tag', function($tag, $handle, $src) use ($dev_server_url) {
            if ($handle === 'fc-autoposter-vite-client' || $handle === 'fc-autoposter-dev-app') {
                // Add type="module" and crossorigin attributes
                $tag = str_replace('<script ', '<script type="module" crossorigin ', $tag);
                
                // Add integrity attribute for security (optional)
                if (strpos($src, $dev_server_url) !== false) {
                    // For dev server, we trust the local connection
                    return $tag;
                }
            }
            return $tag;
        }, 10, 3);
        
        wp_enqueue_script(
            'fc-autoposter-dev-app',
            $dev_server_url . '/src/main.js',
            array('fc-autoposter-vite-client'),
            null,
            true
        );
        
        // // Add admin notice for development mode
        // if (defined('WP_DEBUG') && WP_DEBUG) {
        //     add_action('admin_notices', function() use ($dev_server_url) {
        //         echo '<div class="notice notice-info"><p>';
        //         echo '<strong>FC Autoposter:</strong> Running in development mode. ';
        //         echo 'Dev server: <code>' . esc_html($dev_server_url) . '</code>';
        //         echo '<br><small>If you see CORS errors, make sure your local WordPress domain is added to the Vite config.</small>';
        //         echo '</p></div>';
        //     });
        // }
        
    } else {
        // Production mode - load from dist
        $manifest_path = FC_AUTOPOSTER_PLUGIN_DIR . 'admin/dist/manifest.json';
        
        if (!file_exists($manifest_path)) {
            // Production build missing
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>';
                echo '<strong>FC Autoposter:</strong> Production build not found. ';
                echo 'Please run <code>npm run build</code> in the admin directory.';
                echo '</p></div>';
            });
            return;
        }
        
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
            
            // Add type="module" for modern JavaScript
            add_filter('script_loader_tag', function($tag, $handle, $src) {
                if ($handle === 'fc-autoposter-app') {
                    return str_replace('<script ', '<script type="module" ', $tag);
                }
                return $tag;
            }, 10, 3);
        }
        
        // Add admin notice for production mode
        // if (defined('WP_DEBUG') && WP_DEBUG) {
        //     add_action('admin_notices', function() {
        //         echo '<div class="notice notice-success"><p>';
        //         echo '<strong>FC Autoposter:</strong> Running in production mode with built assets.';
        //         echo '</p></div>';
        //     });
        // }
    }
}
add_action('admin_enqueue_scripts', 'fc_autoposter_enqueue_admin_scripts');
