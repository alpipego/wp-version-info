<?php
/**
 * Plugin Name: Version Info
 * Plugin URI: https://wordpress.org/plugins/version-info
 * Description: Show current WordPress, PHP, Web Server, and MySQL versions in admin footer.
 * Author: Gaucho Plugins
 * Author URI: https://gauchoplugins.com
 * Version: 1.3.2
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: version-info
 */

namespace GauchoPlugins\VersionInfo; // Define a namespace to avoid conflicts

use wpdb;

class VersionInfo {
    private $db;

    public function __construct(wpdb $wpdb) {
        $this->db = $wpdb;
        add_action('plugins_loaded', array($this, 'load_text_domain'));
        add_filter('update_footer', array($this, 'version_in_footer'), 11);
    }

    public function load_text_domain() {
        load_plugin_textdomain('version-info', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function version_in_footer() {
        $update = core_update_footer();
        $wp_version = strpos($update, '<strong>') === 0 ? get_bloginfo('version') . ' (' . $update . ')' : get_bloginfo('version');

        // Fetch MySQL version with error handling
        $mysql_version = $this->db->get_var('SELECT VERSION()');
        if (is_wp_error($mysql_version)) {
            $mysql_version = __('Error fetching version', 'version-info');
        }

        // Translators: %s are the WordPress version, PHP version, server info, and MySQL version respectively.
        $footer = sprintf(
            esc_attr__('You are running WordPress %s | PHP %s | Web Server %s | MySQL %s', 'version-info'),
            $wp_version,
            phpversion(),
            sanitize_text_field($_SERVER['SERVER_SOFTWARE']),
            esc_html($mysql_version)
        );

        // Check for the environment type safely
        if ((getenv('WP_ENVIRONMENT_TYPE') || defined('WP_ENVIRONMENT_TYPE')) && function_exists('wp_get_environment_type')) {
            // Translators: %s is the environment type.
            $footer .= sprintf(' | ' . __('Environment <code>%s</code>', 'version-info'), wp_get_environment_type());
        }

        return $footer;
    }
}

// Globalize wpdb and instantiate the class
global $wpdb;
new VersionInfo($wpdb);

// Register activation and upgrade hooks
register_activation_hook(__FILE__, 'GauchoPlugins\VersionInfo\vidw_plugin_activation');
add_action('admin_init', 'GauchoPlugins\VersionInfo\vidw_check_previous_user');

// Function to set a flag when the plugin is activated (for new users)
function vidw_plugin_activation() {
    // Set an option to track that this user is a new user (only set if not already present)
    if (!get_option('vidw_plugin_version')) {
        update_option('vidw_plugin_version', '1.3.2'); // Updated version to match the current release
    }

    // Redirect to the dashboard after activation
    if (is_admin()) {
        // Add a transient to prevent immediate redirect loop
        set_transient('vidw_plugin_activation_redirect', true, 30);
    }
}

// Check for redirection after activation
add_action('admin_init', function() {
    // Check if the transient is set
    if (get_transient('vidw_plugin_activation_redirect')) {
        // Remove the transient
        delete_transient('vidw_plugin_activation_redirect');
        // Redirect to the dashboard
        wp_redirect(admin_url());
        exit; // Stop further execution
    }
});

// Function to check for previous users of the plugin (who updated from an older version)
function vidw_check_previous_user() {
    // Check if the user has interacted with an older version of the plugin
    if (get_option('vidw_plugin_version') !== '1.3.2') { // Ensure this matches the new version
        // For existing users (older version), hide the widget by default
        $user_id = get_current_user_id();
        $hidden = get_user_option('metaboxhidden_dashboard', $user_id);

        if (is_array($hidden) && !in_array('version_info_dashboard_widget', $hidden)) {
            $hidden[] = 'version_info_dashboard_widget';
            update_user_option($user_id, 'metaboxhidden_dashboard', $hidden, true);
        }

        // Set the version to 1.3.2 to indicate that the user has been upgraded
        update_option('vidw_plugin_version', '1.3.2');
    }
}

// Hook to add the dashboard widget
add_action('wp_dashboard_setup', 'GauchoPlugins\VersionInfo\add_version_info_dashboard_widget');

// Function to register the dashboard widget
function add_version_info_dashboard_widget() {
    // Check if the current user has the 'manage_options' capability (Admins)
    if (current_user_can('manage_options')) {
        wp_add_dashboard_widget(
            'version_info_dashboard_widget', // Widget slug
            __('Version Info', 'version-info'), // Title
            'GauchoPlugins\VersionInfo\display_version_info' // Display callback function
        );
    }
}

// Function to display the version information in the dashboard widget
function display_version_info() {
    global $wpdb;

    // Get server info
    $wp_version = get_bloginfo('version');         // WordPress version
    $php_version = phpversion();                   // PHP version
    $server_software = sanitize_text_field($_SERVER['SERVER_SOFTWARE']); // Sanitize Web Server info
    $mysql_version = $wpdb->db_version();          // MySQL version

    // Display the version info in the widget
    echo '<ul>';
    echo '<li><strong>' . __('WordPress Version:', 'version-info') . '</strong> ' . esc_html($wp_version) . '</li>';
    echo '<li><strong>' . __('PHP Version:', 'version-info') . '</strong> ' . esc_html($php_version) . '</li>';
    echo '<li><strong>' . __('Web Server:', 'version-info') . '</strong> ' . esc_html($server_software) . '</li>';
    echo '<li><strong>' . __('MySQL Version:', 'version-info') . '</strong> ' . esc_html($mysql_version) . '</li>';
    echo '</ul>';
}
