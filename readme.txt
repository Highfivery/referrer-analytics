=== Referrer Analytics ===
Contributors: bmarshall511
Tags: analytics, referrer, google analytics, google analytics add-on, statistics, stats
Donate link: https://benmarshall.me
Requires at least: 5.2
Tested up to: 5.4.2
Requires PHP: 7.1
Stable tag: 1.2.0
License: GNU GPLv3
License URI: https://choosealicense.com/licenses/gpl-3.0/

Track & store where your users come from for better reporting data on-site, in Google Analytics, conversion tracking & more.

== Description ==
Track & store where you users come from for better reporting data in [Google Analytics](https://analytics.google.com/analytics/web/), on-site statistics, conversion tracking & more. Referrer Analytics allows you to make qualified decisions based on facts & figures, not conjecture.

= On-site Reporting =
Referrer Analytics stores user referrer data in a log file located in the `uploads` directory. You can view this log &amp; charts in the admin dashboard for insightful analytics regarding how users are referred to your site.

= Google Analytics =
Referrer Analytics also allows you to automatically add Google Analytic's [UTM data](https://support.google.com/analytics/answer/1033863?hl=en) to the incoming user's URL.

When a user searches something on Google, another search engine or finds your site via a backlink from another site, Referrer Analytics will automatically append the `utm_source`, `utm_medium` and `utm_campaign` to the destination URL.

Example:

1. User searches '<em>referrer analytics</em>' on Google
2. The plugin page for [Referrer Analytics](https://benmarshall.me/referrer-analytics) shows up in the results
3. The user clicks on the search result link: [https://benmarshall.me/referrer-analytics](https://benmarshall.me/referrer-analytics)
4. Their taken the the plugin page & then automatcally redirected with the referrer [UTM data](https://support.google.com/analytics/answer/1033863?hl=en): <em>https://benmarshall.me/referrer-analytics<strong>?utm_source=google.com&utm_medium=organic&utm_campaign=Google</strong></em>

You have the ability to configure what data is used for the UTM values & add custom referrers so you can define what those values should be.

In addition, if plugin cookies are enabled, the user's last known `utm_source`, `utm_medium`, `utm_campaign`, `utm_term` and `utm_content` are stored in a cookie allowing you to pass that data on to other 3rd-party application like [Pardot](https://pi.pardot.com/).

= Cookies =
Referrer Analytics allows you to store the user's referrer information in cookies. This allows you to integrate with 3rd-party applications, implement A/B testing, build more detailed reporting, etc.

== Installation ==
1. Upload the entire referrer-analytics folder to the /wp-content/plugins/ directory.
2. Activate the plugin through the Plugins screen (Plugins > Installed Plugins).

You will find Referrer Analytics settings menu in your WordPress admin screen under Settings.

For basic usage, have a look at the [plugin’s website](https://benmarshall.me/referrer-analytics).

== Frequently Asked Questions ==
= How does this plugin work? =
1. When a user visits a page on your site, Referrer Analytics attempts to determine where the user came from, the referrer (<em>i.e. Google, Bing, Yahoo, etc.</em>). Pre-defined referrers can be set in on the settings page.
2. <strong>Optional.</strong> Appended [UTM data](https://support.google.com/analytics/answer/1033863?hl=en) can be added to the end of the URL they're visited based on the referrer.
3. <strong>Optional.</strong> The referrer data can be stored in cookies.

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
