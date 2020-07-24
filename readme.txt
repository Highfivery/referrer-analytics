=== Referrer Analytics ===
Contributors: bmarshall511
Tags: analytics, referrer, google analytics, google analytics add-on, statistics, stats
Donate link: https://benmarshall.me/donate/?utm_source=referrer_analytics&utm_medium=wordpress_repo&utm_campaign=donate
Requires at least: 5.2
Tested up to: 5.4.2
Requires PHP: 7.1
Stable tag: 1.6.0
License: GNU GPLv3
License URI: https://choosealicense.com/licenses/gpl-3.0/

Track & store where users come from for enhanced reporting in Google Analytics, on-site statistics, conversion tracking & more.

== Description ==

Get analytics from site referrers, insights into types of referring traffic, automated [GA campaign URLs](https://support.google.com/analytics/answer/1033863) and developer tools to send data to 3rd-party applications for conversation tracking.

= On-site Reporting & Statistics =

Referrer Analytics is a powerful tool allowing site admins to get valuable insights into referring traffic. View on-site reporting and statistics in the admin dashboard with interactive visual graphs and detailed logging including:

* Date & time users were referred
* IP addresses of referred users with quick links for [geolocation information](https://benmarshall.me/html5-geolocation/)
* Ability to track authenticated users that come from a referrer
* The type of referring traffic: <em>organic, bots, referral, etc.</em>
* Referring hostnames with human-readable versions such as <em>Google (United Kingdom)</em>
* Referred destination URLs & top landing pages from referrers
* Toplists of referrers, types of referrers and popular landing pages
* Helpful recommendations to block known malicious referrers

= Automated Google Analytics Integration =

Referrer Analytics also allows you to automatically track [Google Analytics campaign data](https://support.google.com/analytics/answer/1033863) via automated URLs from referring sources — no need to manually generate campaign URLs!

Here’s how it works:

1. A user comes to your site from a referring URL like Google or WordPress
2. Referrer Analytics automatically retrieves the referrers information from a list of known referrers and your own defined ones
3. The user will be smartly redirected to their destination with GA campaign data automatically appended to the URL (i.e. `utm_source`, `utm_medium` and `utm_campaign`)

You can also customize what values are used in the UTM parameters using defined referrers that are setup in the admin dashboard.

In addition, when plugin cookies are enabled, the user’s last known UTM values will be stored in a cookie that can be accessed by 3rd-party applications like [Pardot](https://pi.pardot.com/) for advanced conversion tracking.

= Built for WordPress Developers =

Referrer Analytics was built with WordPress developers in mind. Several useful helper functions are included allowing you to easily integrate with 3rd-party applications, implement advanced A/B testing, create referrer specific content & more.

== Installation ==

1. Upload the entire referrer-analytics folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the Plugins screen (Plugins > Installed Plugins).
3. Visit the plugin setting to configure as needed (Settings > Referrer Analytics).

For more information, see the [plugin’s website](https://benmarshall.me/referrer-analytics).

== Frequently Asked Questions ==

= Referrers not showing in the log? =

Referrer Analytics relies on `$_SERVER['HTTP_REFERER]` to get site referrers. Due to increasing privacy settings, there's a handful of edge cases where this variable is not set:

* The user was a "direct" visitor and typed the URL into the browser bar or used a bookmark.
* The user followed a link from outside the browser (for example from an email or mobile app).
* The user came to your non-secure `http` site from a secure `https` site and the browser hid the referrer for security reasons.
* The user modified their browser not to send a referrer (such as using a browser extension to hide the information).
* The user is using a proxy server that removes referrer headers.
* The user clicked a link that has an HTML5 [`rel=noreferrer` attribute](https://html.spec.whatwg.org/multipage/links.html#link-type-noreferrer).
* A site uses JavaScript to link to your site. Many browsers don't send a referrer when JavaScript uses `window.open` or `location.href` to open or set a URL.
* A page redirects to yours with a [`meta refresh`](https://stackoverflow.com/questions/2985579/does-http-equiv-refresh-keep-referrer-info-and-metadata/24283850#24283850). Browsers either reset or remove the original referrer with this type of redirect.
* The request was made by a robot that is not programmed to send referrer information. (Even legitimate robots such as Googlebot often do not send a referrer).

You can choose to have a URL parameter fallback if one exists such as `utm_source` if the `$_SERVER['HTTP_REFERER]` is unavailable. Note that some CMS like WordPress, automatcially add `rel=noreferrer` to external links. You can control this from the plugin settings page.

= What are the names of the referrer-specific cookies? =

* `referrer-analytics-referrer_name`<br /><em>Human-readable name of the user's referrer</em>
* `referrer-analytics-referrer_type`<br /><em>Type of the user's referrer</em>
* `referrer-analytics-referrer_host`<br /><em>Host of the user's referrer</em>
* `referrer-analytics-referrer_scheme`<br /><em>The scheme of the user's referrer</em>
* `referrer-analytics-referrer_url`<br /><em>The url where the user came from</em>
* `referrer-analytics-referrer_destination`<br /><em>The user's original destination</em>

= What are the names of the GA-specific cookies? =
Due to some server hosts like [Pantheon](https://pantheon.io/docs/pantheon_stripped) and the way they handle UTM parameters, the following cookies are set via JavaScript vs. server variables. JavaScript must be enable for these to work.

* `referrer-analytics-utm_source`<br /><em>The user's last known `utm_source`</em>
* `referrer-analytics-utm_medium`<br /><em>The user's last known `utm_medium`</em>
* `referrer-analytics-utm_campaign`<br /><em>The user's last known `utm_campaign`</em>
* `referrer-analytics-utm_term`<br /><em>The user's last known `utm_term`</em>
* `referrer-analytics-utm_content`<br /><em>The user's last known `utm_content`</em>

== Screenshots ==

1. Referrer Analytics statistics, charts & log screen
2. Referrer Analytics settings screen

== Changelog ==

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
