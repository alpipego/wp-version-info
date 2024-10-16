<?php
/**
 * Plugin Name: Version Info
 * Plugin URI: https://wordpress.org/plugins/version-info
 * Description: Show current WordPress, PHP, Web Server, and MySQL versions in the admin footer, WP-Admin bar, with settings control.
 * Author: Gaucho Plugins
 * Author URI: https://gauchoplugins.com
 * Version: 1.3.2
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: version-info
 */

namespace GauchoPlugins\VersionInfo;

use wpdb;

class VersionInfo {
    private $db;

    /**
     * Constructor to initialize the plugin.
     * Registers actions and filters necessary for the plugin to function.
     */
    public function __construct(wpdb $wpdb) {
        $this->db = $wpdb;
        add_action('plugins_loaded', [$this, 'load_text_domain']);
        add_filter('update_footer', [$this, 'version_in_footer'], 11);
        add_action('admin_bar_menu', [$this, 'add_version_info_to_admin_bar'], 100);
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Load the plugin's text domain for translation.
     */
    public function load_text_domain() {
        load_plugin_textdomain('version-info', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * Add version info to the WP-Admin bar (admin-only, controlled by settings).
     */
    public function add_version_info_to_admin_bar($wp_admin_bar) {
        if (!get_option('version_info_show_admin_bar', false) || !current_user_can('administrator')) {
            return; // Prevent non-admins from seeing the admin bar node.
        }

        $wp_admin_bar->add_node([
            'id' => 'version_info_admin_bar',
            'title' => $this->get_version_details(),
            'parent' => 'top-secondary',
        ]);
    }
    
    /**
     * Display version info in the admin footer (admin-only, controlled by settings).
     */
    public function version_in_footer() {
        if (!get_option('version_info_show_footer', true) || !current_user_can('administrator')) {
            return ''; // Only show footer version info to admins if enabled.
        }
        return $this->get_version_details();
    }

    /**
     * Retrieve version details for display.
     * Fetches the current WordPress, PHP, Web Server, and MySQL versions.
     */
    private function get_version_details() {
        $update = core_update_footer();
        $wp_version = strpos($update, '<strong>') === 0 
            ? get_bloginfo('version') . ' (' . $update . ')' 
            : get_bloginfo('version');

        $server_software = sanitize_text_field($_SERVER['SERVER_SOFTWARE'] ?? __('Unknown', 'version-info'));
        $mysql_version = $this->db->get_var('SELECT VERSION()');
        
        // Handle potential MySQL version fetch error.
        if (is_wp_error($mysql_version)) {
            $mysql_version = __('Error fetching version', 'version-info');
        }

        // Return the formatted version details.
        return sprintf(
            __('You are running WordPress %s | PHP %s | Web Server %s | MySQL %s', 'version-info'),
            $wp_version,
            phpversion(),
            $server_software,
            esc_html($mysql_version)
        );
    }

    /**
     * Add a settings page under Settings > Version Info.
     */
    public function add_settings_page() {
        add_options_page(
            __('Version Info Settings', 'version-info'),
            __('Version Info', 'version-info'),
            'manage_options',
            'version-info-settings',
            [$this, 'render_settings_page']
        );
    }

    /**
     * Register plugin settings with validation.
     */
    public function register_settings() {
        register_setting('version_info_settings_group', 'version_info_show_footer', [
            'sanitize_callback' => [$this, 'validate_boolean_option'],
        ]);
        register_setting('version_info_settings_group', 'version_info_show_admin_bar', [
            'sanitize_callback' => [$this, 'validate_boolean_option'],
        ]);
    }

    /**
     * Validate boolean options.
     * Ensures that input is properly cast to a boolean value.
     */
    public function validate_boolean_option($input) {
        return filter_var($input, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Render the settings page with proper nonce verification for security.
     * Ensures that only valid requests can change the settings.
     */
    public function render_settings_page() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['version_info_settings_nonce']) || 
                !wp_verify_nonce($_POST['version_info_settings_nonce'], 'version_info_settings_action')) {
                wp_die(__('Security check failed.', 'version-info'));
            }
        }
        ?>
        <div class="wrap">
            <h1><?php _e('Version Info Settings', 'version-info'); ?></h1>
            <form method="post" action="options.php">
                <?php 
                settings_fields('version_info_settings_group'); 
                wp_nonce_field('version_info_settings_action', 'version_info_settings_nonce'); 
                do_settings_sections('version-info-settings'); 
                ?>
                <table class="form-table">
                    <tr>
                        <th><?php _e('Show Version Info in Admin Bar', 'version-info'); ?></th>
                        <td>
                            <input type="checkbox" name="version_info_show_admin_bar" value="1" 
                            <?php checked(1, get_option('version_info_show_admin_bar', false)); ?> />
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e('Show Version Info in Footer', 'version-info'); ?></th>
                        <td>
                            <input type="checkbox" name="version_info_show_footer" value="1" 
                            <?php checked(1, get_option('version_info_show_footer', true)); ?> />
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

// Initialize the plugin by creating an instance of the VersionInfo class.
global $wpdb;
new VersionInfo($wpdb);

// Plugin activation hook to initialize default settings (if necessary).
register_activation_hook(__FILE__, function () {
    if (!get_option('version_info_show_footer')) {
        update_option('version_info_show_footer', true);
    }
    if (!get_option('version_info_show_admin_bar')) {
        update_option('version_info_show_admin_bar', false);
    }
});
