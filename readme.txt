=== AppStore Reviews Viewer ===
Contributors: gilthonwe
Donate link: http://bit.ly/1LbC1U9
Tags: iOS, App Store, iTunes, apps, appstore, iphone, ipad, customer, reviews, ratings, review, rating, store, country, comment
Requires at least: 3.1
Tested up to: 4.2
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds a shortcode that displays reviews and ratings of an app from the iOS AppStore’s country you chose.

== Description ==

This plugin allows you to use the shortcode `ios_app_review` to embed iOS app reviews and ratings directly into your blog.

What does it do?
The first time a page with the shortcode is loaded, the plugin will try to download the reviews from Apple's servers. Then it will store them in a file and nicely display them on your page.
Every time someone opens the page, instead of downloading the reviews again, the plugin will read them from the cache file. If the cache file is too old, the plugin will try to download new reviews from Apple's servers.

Check out some examples of what it produces here: http://www.gilthonwe.com (My iOS Apps section)

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Place the shortcode `[ios_app_review id="1234"]` (where "1234" is your application's App Store ID) anywhere in a post or page.
4. More options are available on the Settings page of the plugin

== Upgrade Notice ==

No special requirements when upgrading.

== Frequently Asked Questions ==

= Why do I see nothing? =
You may not have any review for the app in the country you selected. If you have reviews and still see nothing, it is because the iTunesConnect API sometimes doesn't work properly when trying to retrieve the Customer Reviews. That's why this plugin caches them once it downloaded the most recent ones.

== Screenshots ==


== Changelog ==

= 1.0.1 =
Small fix with the link to the Settings page

= 1.0 =
* Initial public release on Wordpress.org