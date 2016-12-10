<?php
/**
 * WordPress Version Info
 *
 * @package     wp-version-info
 * @author      Alexander Goller <alpipego@gmail.com>
 * @copyright   2016 Alexander Goller
 * @license     MIT
 *
 * @wordpress-plugin
 *
 * Plugin Name: WordPress Version Info
 * Plugin URI: https://wordpress.org/plugins/version-info
 * Description: Show current WordPress, PHP, Web Server and MySQL versions in admin footer
 * Author: alpipego
 * Author URI: http://alpipego.com/
 * Version: 1.1.0
 * License: MIT
 * GitHub Plugin URI: https://github.com/alpipego/wp-version-info
 * Text Domain: version-info
 */
// pseudo namespace
class VersionInfo {

	public function __construct() {
		add_filter( 'update_footer', array( $this, 'version_in_footer' ), 11 );
	}

	public function version_in_footer() {
		$update     = core_update_footer();
		$wp_version = strpos( $update, '<strong>' ) === 0 ? get_bloginfo( 'version' ) . ' (' . $update . ')' : get_bloginfo( 'version' );

		$mysqli       = new mysqli( DB_HOST, DB_USER, DB_PASSWORD );
		$mysql_server = explode( '-', mysqli_get_server_info( $mysqli ) );
		$mysqli->close();

		return sprintf( esc_attr__('You are running WordPress %s  | PHP %s | %s | MySQL %s', 'version-info'), $wp_version, phpversion(), $_SERVER['SERVER_SOFTWARE'], $mysql_server[0] );
	}
}

new VersionInfo();
