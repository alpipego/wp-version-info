=== Version Info ===
Contributors: alpipego
Tags: admin, version, php, mysql, server, support
Stable tag: 1.1.2
License: MIT
Requires at least: 2.3.0
Tested up to: 4.7

Show current WordPress, PHP, Web Server and MySQL versions in admin footer

== Description ==
This plugin displays the current WordPress version number along with the following environment in the admin footer:

* Current WordPress version info, when there is an update available it displays the current version and the latest version side by side
* PHP version
* Web Server used
* MySQL version

= What's the reason for building this Plugin =
I always disliked the fact that WordPress does not display the current version number in the footer whenever there is a new release.

If you are a plugin developer and a user experiences a problem, they could install this plugin and send you a screenshot of their admin footer (or copy the text of course) to inform you about the cornerstones of their setup.

I built this [Multivariate Virtual Machine for Debugging WordPress Plugin](https://github.com/alpipego/wp-version) that allows you to quickly change PHP versions, the used web server (nginx or Apache) and WordPress core, plugin and theme versions. As it is intended to quickly change environment variables, this plugin helped me keep track of what exactly I am testing at the moment.

== Screenshots ==

1. Default admin footer showing you the current (latest) Wordpress version
2. After activation you will get a lot more info in your admin footer
3. Default admin footer when you are not running the latest version of WordPress
4. If this plugin is active, you will see your currently installed version along the update info (and the additional info this plugin provides)

== Installation ==

1. Upload the plugin to your plugins directory (possibly `/wp-content/plugins/`), or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
