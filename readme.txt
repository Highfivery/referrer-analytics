=== Referrer Analytics ===
Contributors: bmarshall511
Tags: analytics, referrer, google analytics, google analytics add-on, statistics, stats
Donate link: https://benmarshall.me/donate/?utm_source=referrer_analytics&utm_medium=wordpress_repo&utm_campaign=donate
Requires at least: 5.2
Tested up to: 5.4.2
Requires PHP: 7.1
Stable tag: 2.0.0
License: GNU GPLv3
License URI: https://choosealicense.com/licenses/gpl-3.0/

Track & store where users come from for enhanced reporting in Google Analytics, on-site statistics, conversion tracking & more.

== Description ==

A powerful site referrer analytics plugin. Get insights into types of referring traffic, automated [GA campaign URLs](https://support.google.com/analytics/answer/1033863), and useful tracking data for advanced reporting, conversation tracking, and 3rd-party integration.

= On-site Reporting & Statistics =

A powerful tool that allows owners to gain valuable insights into referring traffic. View on-site reporting and statistics with interactive visual graphs and detailed logging including:

* Date & time users were referred
* IP addresses of referred users & their [geolocation](https://benmarshall.me/html5-geolocation/)
* Types of referring traffic: <em>organic, bots, referral, etc.</em>
* Human-readable referrers (ex. Google (United Kingdom), Facebook, etc.)
* Referred destination URLs & top landing pages from referrers
* Toplists of referrers, types of referrers, popular landing pages & more
* Helpful recommendations to block known malicious referrers

= Automated Google Analytics Integration =

Referrer Analytics also allows you to automatically track [Google Analytics campaign data](https://support.google.com/analytics/answer/1033863) via automated URLs from referring sources — no need to manually generate campaign URLs!

Here’s how it works:

1. User comes to your site from a referring URL like Google or WordPress
2. Referrer Analytics automatically retrieves the referrers information from a list of known referrers and your own defined ones
3. The user will be smartly redirected to their destination with GA campaign data automatically appended to the URL (i.e. `utm_source`, `utm_medium` and `utm_campaign`)

You can customize values used in UTM parameters using defined hosts setup in the admin dashboard.

When plugin cookies are enabled, the user’s last known UTM values and referrer information will be stored in cookies that can be accessed by 3rd-party applications like [Pardot](https://pi.pardot.com/) for advanced reporting, conversion tracking, etc.

== Installation ==

1. Upload the entire referrer-analytics folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the Plugins screen (Plugins > Installed Plugins).
3. Visit the plugin setting to configure as needed (Settings > Referrer Analytics).

For more information, see the [plugin’s website](https://benmarshall.me/referrer-analytics).

== Frequently Asked Questions ==

= What is a "self-referral"? =

A "self-referral" is traffic coming to the site that has been referred by the same site. Referrer Analytics will typically ignore this type of traffic except when ran on certain hosts such as Pantheon.io. Learn more about "self-referrals" in [Pantheon's documentation](https://pantheon.io/docs/pantheon_stripped).

= What aren't my user referrers being logged? =

Referrer Analytics relies on `$_SERVER['HTTP_REFERER]`. Due to increasing privacy settings, there's a handful of edge cases where this variable isn't available:

* "direct" visitors (typed a URL into the browser bar or used a bookmark)
* Followed a link from outside the browser (for example from an email or mobile app)
* Referred from non-secure `http` to `https` and the browser hid the referrer for security
* Browser modified to not send referrer (such as using a browser extension)
* Using a proxy server that removes referrer headers
* Clicked a link that has an HTML5 [`rel=noreferrer` attribute](https://html.spec.whatwg.org/multipage/links.html#link-type-noreferrer)
* Uses JavaScript to link to your site (`window.open` or `location.href`)
* [`meta refresh`](https://stackoverflow.com/questions/2985579/does-http-equiv-refresh-keep-referrer-info-and-metadata/24283850#24283850) redirects (browsers remove the original referrer with this type of redirect)
* Request was made by a robot (even legitimate robots such as Googlebot often do not send a referrer)

You can choose to have a URL parameter fallback if one exists such as `utm_source` if the `$_SERVER['HTTP_REFERER]` is unavailable. Note that some CMS like WordPress, automatcially add `rel=noreferrer` to external links. You can control this from the plugin settings page.

= What referrer cookies are available =

When plugin cookies are enabled, referrer-specific cookies are set and can be used for advanced tracking and reporting (ex. pass UTM values to hidden form inputs).

The following cookies are available:

* `referrer-analytics-referrer_name`
* `referrer-analytics-referrer_type`
* `referrer-analytics-referrer_host`
* `referrer-analytics-referrer_scheme`
* `referrer-analytics-referrer_url`
* `referrer-analytics-referrer_destination`

= What Google Analytics cookies are available? =

You can access [Google Analytics UTM values](https://support.google.com/analytics/answer/1033863?hl=en) via cookies when plugin cookies are enabled. This can be useful with certain hosts like [Pantheon](https://pantheon.io/docs/pantheon_stripped), that strip this information on the backend. They also persist during the user's session allowing advanced tracking and reporting (ex. pass UTM values to hidden form inputs).

The following cookies are available:

* `referrer-analytics-utm_source`
* `referrer-analytics-utm_medium`
* `referrer-analytics-utm_campaign`
* `referrer-analytics-utm_term`
* `referrer-analytics-utm_content`

== Screenshots ==

1. Referrer Analytics statistics & charts
2. Referrer Analytics statistics & charts
3. Referrer Analytics log
4. Referrer Analytics settings screen

== Changelog ==

= v2.0.0 =

* Optimized JS & resource loading
* Enhanced statistics dashboard
* Re-write of the code base

= v1.7.2 =

* Fixed overflow issue with top lists
* Added additional pre-defined referrer hosts

= v1.7.1 =

* Added additional pre-defined referrer hosts
* Fixed the line charts direction
* Various UI enhancements

= v1.7.0 =

* Various performance enhancements
* Various UI enhancements
* Added additional pre-defined referrer hosts

= v1.6.1 =

* Fix for cached CSS files

= v1.6.0 =

* Added additional pre-defined referrer hosts
* Added "(UTM Source)" to referrers that use the "URL Referrer Fallback"
* Added more charts to the Referrer Analytics dashboard
* Added a "inferred" attribute to known referrers that are defined by the `utm_source`

= v1.5.0 =

* Fixed sorting issue on the log table
* Added additional pre-defined referrer hosts
* Added more charts

= v1.4.1 =

* Added additional pre-defined referrer hosts
* Changed 'backlink' default to 'referral'
* Enhanced the log table

= v1.4.0 =

* Added additional pre-defined referrer hosts
* Added ability to have a fallback referrer if `$_SERVER['HTTP_REFERER']` is unavailable
* Added the ability to prevent WP from automatcially adding `rel="noreferrer"` tags to external links
* Fixed issue with some plugin form fields not saving

= v1.3.1 =

* Fixed PHP warning header output issue on the log page after a log is deleted for some hosts
* Fixed duplicate comma seperated IP addresses on some hosts like Pantheon
* Removed log files & now storing referred traffic in the database

= v1.3.0 =

* Added additional pre-defined referrer hosts
* Various admin UI improvements

= v1.2.0 =

* Fixed PHP notice for `Undefined index: redirect`
* Updated the cookie name convention
* Changed the helper function `referrer_analytics_parsed_log` to `referrer_analytics_parse_log`
* Added more insight charts
* Minor bug fixes
* Added URL field to Defined Referrer Hosts
* Added UTM cookies

= v1.1.0 =

* Added additional pre-defined referrer hosts
* Added paging to the Referrer Log
* Log now get's synced with updated referrer & known hosts
