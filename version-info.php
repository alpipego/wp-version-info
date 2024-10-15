<?php
/**
 * Plugin Name: Version Info
 * Plugin URI: https://wordpress.org/plugins/version-info
 * Description: Show current WordPress, PHP, Web Server, and MySQL versions in the admin footer, WP-Admin bar, and dashboard widget with settings control.
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
     */
    public function __construct(wpdb $wpdb) {
        $this->db = $wpdb;
        add_action('plugins_loaded', [$this, 'load_text_domain']);
        add_filter('update_footer', [$this, 'version_in_footer'], 11);
        add_action('admin_bar_menu', [$this, 'add_version_info_to_admin_bar'], 100);
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('wp_dashboard_setup', [$this, 'setup_dashboard_widget']);
        add_action('wp_ajax_save_widget_visibility', [$this, 'handle_ajax_widget_visibility']);
    }

    /**
     * Load the plugin's text domain for translation.
     */
    public function load_text_domain() {
        load_plugin_textdomain('version-info', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * Add version info to the WP-Admin bar.
     */
    public function add_version_info_to_admin_bar($wp_admin_bar) {
        if (!get_option('version_info_show_admin_bar', false) || !current_user_can('administrator')) {
            return;
        }

        $wp_admin_bar->add_node([
            'id' => 'version_info_admin_bar',
            'title' => $this->get_version_details(),
            'parent' => 'top-secondary',
        ]);
    }

    /**
     * Display version info in the admin footer.
     */
    public function version_in_footer() {
        if (!get_option('version_info_show_footer', true) || !current_user_can('administrator')) {
            return '';
        }
        return $this->get_version_details();
    }

    /**
     * Retrieve version details for display.
     */
    private function get_version_details() {
        $update = core_update_footer();
        $wp_version = strpos($update, '<strong>') === 0
            ? get_bloginfo('version') . ' (' . $update . ')'
            : get_bloginfo('version');

        $server_software = sanitize_text_field($_SERVER['SERVER_SOFTWARE'] ?? __('Unknown', 'version-info'));
        $mysql_version = $this->db->get_var('SELECT VERSION()');

        if (is_wp_error($mysql_version)) {
            $mysql_version = __('Error fetching version', 'version-info');
        }

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
     */
    public function validate_boolean_option($input) {
        return filter_var($input, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Render the settings page.
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Version Info Settings', 'version-info'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('version_info_settings_group');
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

    /**
     * Set up the dashboard widget for administrators.
     */
    public function setup_dashboard_widget() {
        if (!current_user_can('administrator')) {
            return;
        }

        wp_add_dashboard_widget(
            'version_info_dashboard_widget',
            __('Version Info', 'version-info'),
            [$this, 'display_dashboard_widget']
        );

        $this->set_widget_hidden_by_default();
    }

    /**
     * Set the widget to be hidden by default using user meta.
     */
    private function set_widget_hidden_by_default() {
        $user_id = get_current_user_id();
        $hidden = get_user_option('metaboxhidden_dashboard', $user_id) ?: [];

        if (!in_array('version_info_dashboard_widget', $hidden)) {
            $hidden[] = 'version_info_dashboard_widget';
            update_user_option($user_id, 'metaboxhidden_dashboard', $hidden, true);
        }
    }

    /**
     * Handle AJAX request to save widget visibility.
     */
    public function handle_ajax_widget_visibility() {
        check_ajax_referer('meta-box-order', 'nonce');

        $user_id = get_current_user_id();
        if (isset($_POST['hidden']) && is_array($_POST['hidden'])) {
            $hidden = array_map('sanitize_text_field', $_POST['hidden']);
            update_user_option($user_id, 'metaboxhidden_dashboard', $hidden, true);
        }

        wp_send_json_success();
    }

    /**
     * Display content for the dashboard widget.
     */
    public function display_dashboard_widget() {
        global $wpdb;

        echo '<ul>';
        echo '<li><strong>' . esc_html__('WordPress Version:', 'version-info') . '</strong> ' . esc_html(get_bloginfo('version')) . '</li>';
        echo '<li><strong>' . esc_html__('PHP Version:', 'version-info') . '</strong> ' . esc_html(phpversion()) . '</li>';
        echo '<li><strong>' . esc_html__('Web Server:', 'version-info') . '</strong> ' . esc_html(sanitize_text_field($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown')) . '</li>';
        echo '<li><strong>' . esc_html__('MySQL Version:', 'version-info') . '</strong> ' . esc_html($wpdb->db_version()) . '</li>';
        echo '</ul>';
    }
}

// Initialize the plugin.
global $wpdb;
new VersionInfo($wpdb);

// Plugin activation hook.
register_activation_hook(__FILE__, function () {
    if (!current_user_can('activate_plugins')) {
        wp_die(__('You are not allowed to activate plugins.', 'version-info'));
    }
});
