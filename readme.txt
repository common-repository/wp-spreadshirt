=== WP Spreadshirt ===
Contributors: ppfeufer
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=42MCT4879UETE
Tags: spreadshirt, shop, t-shirt
Requires at least: 3.0.1
Tested up to: 3.8-alpha
Stable tag: 1.6.3

Adding a shortcode to show your Spreadshirt-Articles in a page or post.

== Description ==

Adding a shortcode to show your Spreadshirt-Articles in a page or post. This shortcode has two options: shop_id and shop_url. Both are needed.

Example:
`[spreadshirt shop_url="http://chaoz.spreadshirt.de/" shop_id="602194" shop_location="eu/na"]`
or
`[spreadshirt shop_url="http://chaoz.spreadshirt.de/shop/" shop_id="602194" shop_location="eu/na"]`

The Shop-URL **must** contain the trailing slash.

== Installation ==

Search the Plugin in your Dashboard and install or upload the plugin to your site and activate it.

== Screenshots ==

1. The Shortcode
2. The Output

== Changelog ==

= 1.6.3 =
* (02. November 2012)
* Tested up to WordPress 3.8-alpha

= 1.6.2 =
* More sanitizing of article-uri. Spreadshirt has a very inconsistent way to rewrite special chars -.-

= 1.6.1 =
* Fixed sanitizing of the article-uri

= 1.6 =
* New option for shortcode. `shop_location` Set this to "eu" for european based shops or to "na" for northern america based shops. Spreadshirt has two separated APIs for this location. This will hopefully fix some problems displaying the wrong articles.

= 1.5 =
* Link sanitizing: Removed the comma (,) from the article-links.

= 1.4 =
* Changed the XML-URL for the API to prevent double entries. Thanks to [Thomas Sluyter](http://www.kilala.nl/) for the hint.

= 1.3 =
* You can enter the shop url now with or without "shop/" at the end.

= 1.2 =
* Added Translations

= 1.1 =
* Minor Code Changes
* Upload to WP Plugin Repo

= 1.0 =
* Initial Release

== Frequently Asked Questions ==

none

== Upgrade Notice ==

None