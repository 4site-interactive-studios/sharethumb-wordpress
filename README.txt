=== sharethumb ===

Tags: ShareThumb
Requires at least: 5.9
Tested up to: 6.4.2
Stable tag: 1.1.1
Requires PHP: 7.4
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

ShareThumb - Automated social media share images for your website

== Description ==

ShareThumb - Automated social media share images for your website

After installation, update the settings page at /wp-admin/admin.php?page=sharethumb.  You can find your API Key and Domain Validation key at https://app.sharethumb.io/dashboard. Any changes to the settings page will update your settings in your ShareThumb account.

You can optionally override the settings for individual content.  You can find the override settings in the right sidebar when editing the content.  If the sidebar override settings do not appear, ensure you have enabled overrides for that post type on the main settings page.


== Third Party Services ==

This plugin makes calls to the sharethumb API, at the following endpoints: https://api.sharethumb.app/ and https://use.sharethumb.io/.  When you save your global settings (after entering your correct API key & validate your domain), it will contact the relevant endpoint to update your remote settings for the ShareThumb service.  Additionally, when you view an overrideable post, it will attempt to fetch the up-to-date version of the ShareThumb thumbnail using your override settings, via the relevant endpoint.

For more information, see the ShareThumb website (https://sharethumb.io/) and the ShareThumb Terms of Use (https://www.4sitestudios.com/products/sharethumb/legal/).
