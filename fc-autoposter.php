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

// Load Composer autoloader
if (file_exists(FC_AUTOPOSTER_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once FC_AUTOPOSTER_PLUGIN_DIR . 'vendor/autoload.php';
} else {
    // Fallback error message
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>';
        echo '<strong>FC Autoposter:</strong> Composer autoloader not found. ';
        echo 'Please run <code>composer install</code> in the plugin directory.';
        echo '</p></div>';
    });
    return;
}

// Bootstrap routing system
function fc_autoposter_bootstrap_routing() {
    $routeProvider = new \FCAutoposter\Routing\RouteServiceProvider();
    $routeProvider->boot();
}
add_action('init', 'fc_autoposter_bootstrap_routing');

/**
 * Plugin activation hook
 */
function fc_autoposter_activate() {
    try {
        error_log('FC Autoposter: Starting plugin activation...');
        
        // Run migrations on activation
        $migrationManager = new \FCAutoposter\Database\MigrationManager();
        
        // Check if there are any migrations to run
        if ($migrationManager->hasPendingMigrations()) {
            error_log('FC Autoposter: Found pending migrations, running...');
            $results = $migrationManager->runMigrations();
            
            $success_count = 0;
            $failed_count = 0;
            
            // Log activation migration results
            foreach ($results as $migration_name => $result) {
                if ($result['status'] === 'success') {
                    $success_count++;
                    error_log("FC Autoposter Activation: Migration completed - {$migration_name}");
                } elseif ($result['status'] === 'skipped') {
                    // Skipped migrations are not failures
                    error_log("FC Autoposter Activation: Migration skipped (already applied) - {$migration_name}");
                } else {
                    $failed_count++;
                    error_log("FC Autoposter Activation: Migration failed - {$migration_name}: " . ($result['error'] ?? $result['message']));
                }
            }
            
            error_log("FC Autoposter Activation: Migration summary - {$success_count} successful, {$failed_count} failed");
            
            // If any migrations failed, stop activation
            if ($failed_count > 0) {
                throw new \Exception("Migration failed: {$failed_count} migrations could not be completed");
            }
        } else {
            error_log('FC Autoposter: No pending migrations found');
        }
        
        // Set plugin activation flag and version
        update_option('fc_autoposter_activated', true);
        update_option('fc_autoposter_version', FC_AUTOPOSTER_VERSION);
        update_option('fc_autoposter_activation_time', time());
        
        error_log('FC Autoposter: Plugin activation completed successfully');
        
    } catch (\Exception $e) {
        error_log('FC Autoposter Activation Error: ' . $e->getMessage());
        
        // Clean up any partial activation
        delete_option('fc_autoposter_activated');
        delete_option('fc_autoposter_version');
        
        // Deactivate plugin if migration fails
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            '<h1>FC Autoposter Activation Failed</h1>' .
            '<p><strong>Error:</strong> ' . esc_html($e->getMessage()) . '</p>' .
            '<p>Please check the error logs for more details.</p>' .
            '<br><a href="' . admin_url('plugins.php') . '">&larr; Back to Plugins</a>'
        );
    }
}
register_activation_hook(__FILE__, 'fc_autoposter_activate');

/**
 * Plugin deactivation hook
 */
function fc_autoposter_deactivate() {
    error_log('FC Autoposter: Starting plugin deactivation...');
    
    // Clear activation flags
    delete_option('fc_autoposter_activated');
    
    // Optionally keep version for potential reactivation
    // delete_option('fc_autoposter_version');
    
    // Clear WordPress cache
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
    
    // Clear any transients related to our plugin
    delete_transient('fc_autoposter_migration_status');
    
    error_log('FC Autoposter: Plugin deactivated successfully');
}
register_deactivation_hook(__FILE__, 'fc_autoposter_deactivate');

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
    
    // Add submenu for database status (for debugging)
    if (defined('WP_DEBUG') && WP_DEBUG) {
        add_submenu_page(
            'fc-autoposter',           // Parent slug
            'Database Status',         // Page title
            'Database Status',         // Menu title
            'manage_options',          // Capability
            'fc-autoposter-db-status', // Menu slug
            'fc_autoposter_db_status_page' // Function
        );
    }
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
 * Render database status page (debug only)
 */
function fc_autoposter_db_status_page() {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        wp_die('This page is only available in debug mode.');
    }
    
    try {
        $migrationManager = new \FCAutoposter\Database\MigrationManager();
        $status = $migrationManager->getStatus();
        $stats = \FCAutoposter\Models\Agent::getStats();
        
        ?>
        <div class="wrap">
            <h1>FC Autoposter - Database Status</h1>
            
            <h2>Migration Status</h2>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Migration</th>
                        <th>Version</th>
                        <th>Should Run</th>
                        <th>Class</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($status as $migration): ?>
                    <tr>
                        <td><?php echo esc_html($migration['name']); ?></td>
                        <td><?php echo esc_html($migration['version']); ?></td>
                        <td>
                            <span class="<?php echo $migration['should_run'] ? 'dashicons dashicons-warning' : 'dashicons dashicons-yes'; ?>"></span>
                            <?php echo $migration['should_run'] ? 'Pending' : 'Applied'; ?>
                        </td>
                        <td><code><?php echo esc_html($migration['class']); ?></code></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <h2>Agent Statistics</h2>
            <table class="widefat">
                <tbody>
                    <tr><th>Total Agents</th><td><?php echo esc_html($stats['total_agents']); ?></td></tr>
                    <tr><th>Active Agents</th><td><?php echo esc_html($stats['active_agents']); ?></td></tr>
                    <tr><th>Inactive Agents</th><td><?php echo esc_html($stats['inactive_agents']); ?></td></tr>
                    <tr><th>Total Interactions</th><td><?php echo esc_html($stats['total_interactions']); ?></td></tr>
                    <tr><th>Average Interactions</th><td><?php echo esc_html($stats['avg_interactions']); ?></td></tr>
                </tbody>
            </table>
            
            <h2>Database Tables</h2>
            <?php
            global $wpdb;
            $table_name = $wpdb->prefix . 'fc_fa_agents';
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
            ?>
            <p>
                <strong>Agents Table:</strong> 
                <span class="<?php echo $table_exists ? 'dashicons dashicons-yes' : 'dashicons dashicons-warning'; ?>"></span>
                <?php echo $table_exists ? 'Exists' : 'Missing'; ?>
            </p>
            
            <?php if ($table_exists): ?>
                <p>
                    <strong>Table Structure:</strong>
                </p>
                <pre style="background: #f1f1f1; padding: 10px; overflow-x: auto;">
<?php
$columns = $wpdb->get_results("DESCRIBE $table_name");
foreach ($columns as $column) {
    echo sprintf("%-20s %-15s %s\n", 
        $column->Field, 
        $column->Type, 
        $column->Null === 'NO' ? 'NOT NULL' : 'NULL'
    );
}
?>
                </pre>
            <?php endif; ?>
        </div>
        <?php
        
    } catch (\Exception $e) {
        ?>
        <div class="wrap">
            <h1>FC Autoposter - Database Status</h1>
            <div class="notice notice-error">
                <p><strong>Error:</strong> <?php echo esc_html($e->getMessage()); ?></p>
            </div>
        </div>
        <?php
    }
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
        
        // Localize script with WordPress data for development
        wp_localize_script('fc-autoposter-dev-app', 'fcAutoposterAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_rest'),
            'customNonce' => wp_create_nonce('fc_autoposter_nonce'),
            'restUrl' => rest_url('fc-autoposter/v1/'),
            'pluginUrl' => FC_AUTOPOSTER_PLUGIN_URL,
            'currentUser' => get_current_user_id(),
        ]);
        
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
            
            // Localize script with WordPress data for production
            wp_localize_script('fc-autoposter-app', 'fcAutoposterAdmin', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wp_rest'),
                'customNonce' => wp_create_nonce('fc_autoposter_nonce'),
                'restUrl' => rest_url('fc-autoposter/v1/'),
                'pluginUrl' => FC_AUTOPOSTER_PLUGIN_URL,
                'currentUser' => get_current_user_id(),
            ]);
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

/**
 * Show activation notice
 */
function fc_autoposter_activation_notice() {
    // Only show on admin pages and only once after activation
    if (!is_admin()) {
        return;
    }
    
    $activation_time = get_option('fc_autoposter_activation_time');
    $notice_dismissed = get_option('fc_autoposter_activation_notice_dismissed', false);
    
    // Show notice if plugin was recently activated and notice hasn't been dismissed
    if ($activation_time && !$notice_dismissed && (time() - $activation_time) < 300) { // Show for 5 minutes
        ?>
        <div class="notice notice-success is-dismissible" data-dismissible="fc-autoposter-activation">
            <p>
                <strong>FC Autoposter activated successfully!</strong> 
                Database tables have been created and the plugin is ready to use.
                <a href="<?php echo admin_url('admin.php?page=fc-autoposter'); ?>">Get started</a>
            </p>
        </div>
        <script>
        jQuery(document).ready(function($) {
            $(document).on('click', '[data-dismissible="fc-autoposter-activation"] .notice-dismiss', function() {
                $.post(ajaxurl, {
                    action: 'fc_autoposter_dismiss_activation_notice',
                    nonce: '<?php echo wp_create_nonce('fc_autoposter_dismiss_notice'); ?>'
                });
            });
        });
        </script>
        <?php
    }
}
add_action('admin_notices', 'fc_autoposter_activation_notice');

/**
 * Handle activation notice dismissal
 */
function fc_autoposter_dismiss_activation_notice() {
    if (check_ajax_referer('fc_autoposter_dismiss_notice', 'nonce', false)) {
        update_option('fc_autoposter_activation_notice_dismissed', true);
        wp_die(); // Ajax response
    }
}
add_action('wp_ajax_fc_autoposter_dismiss_activation_notice', 'fc_autoposter_dismiss_activation_notice');
