=== Referrer Analytics ===
Contributors: bmarshall511
Tags: analytics, referrer, google analytics, google analytics add-on, statistics, stats
Donate link: https://benmarshall.me
Requires at least: 5.2
Tested up to: 5.4.2
Requires PHP: 7.1
Stable tag: 1.3.0
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
* The type of referring traffic: <em>organic, bots, backlinks, etc.</em>
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

= v1.1.0 =

* Added additional pre-defined referrer hosts
* Added paging to the Referrer Log
* Log now get's synced with updated referrer & known hosts

= v1.2.0 =

* Fixed PHP notice for `Undefined index: redirect`
* Updated the cookie name convention
* Changed the helper function `referrer_analytics_parsed_log` to `referrer_analytics_parse_log`
* Added more insight charts
* Minor bug fixes
* Added URL field to Defined Referrer Hosts
* Added UTM cookies

= v1.3.0 =

* Added additional pre-defined referrer hosts
* Various admin UI improvements
