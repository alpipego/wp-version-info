<?php
/**
 * Plugin Name: Version Info
 * Plugin URI: https://wordpress.org/plugins/version-info
 * Description: Show current WordPress, PHP, Web Server and MySQL versions in admin footer. 
 * Author: gauchoplugins
 * Author URI: http://gauchoplugins.com
 * Version: 1.3.1
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: version-info
 */
// pseudo namespace
class VersionInfo {

	private $db;

	public function __construct( wpdb $wpdb ) {
		$this->db = $wpdb;
		add_action( 'plugins_loaded', array( $this, 'load_text_domain' ) );
		add_filter( 'update_footer', array( $this, 'version_in_footer' ), 11 );
	}

	public function load_text_domain() {
		load_plugin_textdomain( 'version-info' );
	}

	public function version_in_footer() {
		$update     = core_update_footer();
		$wp_version = strpos( $update, '<strong>' ) === 0 ? get_bloginfo( 'version' ) . ' (' . $update . ')' : get_bloginfo( 'version' );

		$footer = sprintf( esc_attr__( 'You are running WordPress %s | PHP %s | %s | MySQL %s', 'version-info' ), $wp_version, phpversion(), $_SERVER['SERVER_SOFTWARE'], $this->db->get_var('SELECT VERSION();') );

		if ((getenv('WP_ENVIRONMENT_TYPE') || defined('WP_ENVIRONMENT_TYPE')) && function_exists('wp_get_environment_type')) {
            $footer .= sprintf(' | Environment <code>%s</code>', wp_get_environment_type());
        }

		return $footer;
	}
}

global $wpdb;
new VersionInfo( $wpdb );
