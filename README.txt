=== Lazy Loading and Navigation ===
Contributors: tayyebi
Tags: ajax, page loader, jquery, dynamic loading
Requires at least: 4.0
Tested up to: 6.8
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AJAX Page Loader allows you to navigate smoothly between pages.

== Description ==

AJAX Page Loader is a lightweight plugin that dynamically loads page content via AJAX with an integrated loading indicator.

== Installation ==
1. Upload the `lazy-loading-and-navigation` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. To initiate an AJAX page load, call the function `AjaxPageLoader.loadPage('your-url')` in your custom JavaScript code.

== Frequently Asked Questions ==
= How do I use the plugin? =
Call the JavaScript method `AjaxPageLoader.loadPage(url)` (where url is the page you want to load) from your theme or custom scripts.

= Where is the loading indicator? =
The plugin injects a simple loading indicator into your page. You can style it further by targeting the `#loading` element in your CSS.

== Changelog ==
= 1.0.0 =
* Initial release.

== Upgrade Notice ==
= 1.0.0 =
Initial release. There are no upgrade notices at this time.
