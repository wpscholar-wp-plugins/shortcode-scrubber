=== Shortcode Scrubber ===
Contributors: wpscholar
Donate link: https://www.paypal.me/wpscholar
Tags: shortcode
Requires PHP: 5.6
Requires at least: 3.2
Tested up to: 5.6
Stable tag: 1.0.3
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A powerful tool for cleaning up shortcodes on your site and confidently managing plugins and themes that use shortcodes.

== Description ==

The **Shortcode Scrubber** plugin allows to you to confidently manage plugins and themes that use shortcodes.

* Have you ever gone to disable a WordPress plugin not realizing that it had shortcodes you were using?
* Have you ever wondered what shortcodes are available for use on your site?
* Have you ever wondered what plugin or theme provided a specific shortcode so you know where to look for documentation?
* Have you ever discovered broken shortcodes displayed on your site?
* Have you ever wanted to prevent content editors from using a specific shortcode?
* Have you ever wanted to clean up old shortcode content?
* Have you ever run into shortcodes that won't process other shortcodes?

If you answered yes to any of the questions above, then the Shortcode Scrubber can help!

https://www.youtube.com/watch?v=cOjUfgkgvzA

= Features =

* When active, all broken shortcodes displayed on the site are automatically hidden.
* When active, all shortcodes are auto-magically nestable.
* Ability to view all registered shortcodes and whether it is provided by WordPress core, the active theme or a plugin.
* Ability to easily find all uses of a specific shortcode across all posts, pages, custom post types and widgets.
* Ability to disable individual shortcodes temporarily or permanently remove all uses of a shortcode.
* Ability for developers to create extensions that can manage shortcode migrations (e.g. Visual Composer to bootstrap compatible markup or shortcode to Gutenberg block conversions).
* Clean, well written code that won't bog down your site.

== Installation ==

= Prerequisites =
If you don't meet the below requirements, this plugin will not work. I highly recommend you upgrade your WordPress install or move to a web host that supports a more recent version of PHP.

* Requires WordPress version 3.2 or greater
* Requires PHP version 5.6 or greater

= The Easy Way =

1. In your WordPress admin, go to 'Plugins' and then click on 'Add New'.
2. In the search box, type in 'Shortcode Scrubber' and hit enter.  This plugin should be the first and likely the only result.
3. Click on the 'Install' link.
4. Once installed, click the 'Activate this plugin' link.

= The Hard Way =

1. Download the .zip file containing the plugin.
2. Upload the file into your `/wp-content/plugins/` directory and unzip
3. Find the plugin in the WordPress admin on the 'Plugins' page and click 'Activate'

= Usage Instructions =

Once the plugin is installed and activated, go to the 'Shortcodes' menu item in the left admin menu when you are logged in and on the administrative section of the site.

Note: You must have the 'manage_options' capability to see the 'Shortcodes' menu item. Typically, only site administrators will have this capability.

== Screenshots ==

1. See all of the shortcodes registered in WordPress and easily find out where they may be in use.
2. View all of the posts in WordPress (custom post types included) that have shortcodes. Shortcodes left behind by plugins and themes that are no longer in use are shown in red.
3. View all of the widgets in WordPress that have shortcodes.  Shortcodes left behind by plugins and themes that are no longer in use are shown in red.
4. Easily create shortcode filters that can control how, or if, shortcodes are shown on your site.
5. Easily create shortcode filters that can control how, or if, shortcodes are shown on your site. Developers can create additional shortcode filter handlers.

== Changelog ==

= 1.0.3 =
* Fix broken deployment.

= 1.0.2 =
* Minor code refactor to use Composer packages.

= 1.0.1 =
* Tested in WordPress version 5.1
* Updated code to abide by coding standards.

= 1.0.0 =
* Initial commit; tested in WordPress version 4.9.8

== Upgrade Notice ==

= 1.0.3 =
* Update to fix incomplete release of previous version.

= 1.0.2 =
* Minor code improvements.

= 1.0.1 =
* Tested in WordPress version 5.1
