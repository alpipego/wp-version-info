=== Version Info ===
Contributors: alpipego
Tags: admin, version, php, mysql, server, support
Stable tag: 1.2.0
License: MIT
Requires at least: 2.3.0
Tested up to: 5.5

Show current WordPress, PHP, Web Server and MySQL versions in admin footer

== Description ==
This plugin displays the current WordPress version number along with the following environment in the admin footer:

* Current WordPress version info, if there is an update available it displays the current and latest versions side by side
* PHP version
* Web Server used
* MySQL version

= What's the reason for building this Plugin =
I always disliked the fact that WordPress does not display the current version number in the footer whenever there is a new release.

If you are a plugin developer and a user experiences a problem, they could install this plugin and send you a screenshot of their admin footer (or copy the text of course) to inform you about the cornerstones of their setup.

== Screenshots ==

1. Default admin footer showing you the current (latest) Wordpress version
2. After activation you will get a lot more info in your admin footer
3. Default admin footer when you are not running the latest version of WordPress
4. If this plugin is active, you will see your currently installed version along the update info (and the additional info this plugin provides)

== Frequently Asked Questions ==

= Footer Version Info is not showing on mobile =

`common.css` hides the admin-footer on viewports smaller than 783px. To show the footer also on small viewports, add the following to a mu-plugin or your theme's functions.php, etc.

```
add_action('admin_enqueue_scripts', function () {
    wp_add_inline_style('common', '@media screen and (max-width: 782px){#wpfooter {display: block;}}');
    wp_add_inline_style('admin-menu', '@media only screen and (max-width: 960px){.auto-fold #wpfooter{margin-left: 0px;}}');
});
```

== Installation ==

1. Upload the plugin to your plugins directory (possibly `/wp-content/plugins/`), or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress

== Changelog ==

= 1.2.0 =

* If WP_ENVIRONMENT_TYPE is defined (either through PHP env var or constant), display this info.
