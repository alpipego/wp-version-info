<?php
/*
Plugin Name: WordPress and Environment Version Number in Footer
Plugin URI: https://wordpress.org/plugins/version-info
Description: Show current WordPress, PHP, Web Server and MySQL versions in admin footer
Author: alpipego
Version: 1.0.1
Author URI: http://alpipego.com/
*/

// pseudo namespace
class VersionInfo {

	public function __construct() {
		add_filter( 'update_footer', array($this, 'version_in_footer'), 11 );
	}

	public function version_in_footer() {
		$update     = core_update_footer();
		$wp_version = strpos( $update, '<strong>' ) === 0 ? get_bloginfo( 'version' ) . ' (' . $update . ')' : get_bloginfo( 'version' );

		$mysqli       = new mysqli( DB_HOST, DB_USER, DB_PASSWORD );
		$mysql_server = explode( '-', mysqli_get_server_info( $mysqli ) );
		$mysqli->close();

		return sprintf( 'You are running WordPress %s  | PHP %s | %s | MySQL %s', $wp_version, phpversion(), $_SERVER['SERVER_SOFTWARE'], $mysql_server[0] );
	}
}

new VersionInfo();
