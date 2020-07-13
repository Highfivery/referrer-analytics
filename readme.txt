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

Track & store where you users come from for better reporting data on-site, in Google Analytics, conversion tracking & more.

== Description ==
Track & store where you users come from for better reporting data in [Google Analytics](https://analytics.google.com/analytics/web/), on-site statistics, conversion tracking & more. Referrer Analytics allows you to make qualified decisions based on facts & figures, not conjecture.

= On-site Reporting =
Referrer Analytics stores user referrer data in a log file in the uploads directory. It also provides a useful log &amp; statistics admin screen that shows the log along with insightful charts to see where your user's are coming from.

= Google Analytics =
Referrer Analytics also you to automatically add Google Analytic's [UTM data](https://support.google.com/analytics/answer/1033863?hl=en) to the incoming user's URL.

When a user searches something on Google, another search engine or finds your site via a backlink from another site, Referrer Analytics will automatically append the `utm_source`, `utm_medium` and `utm_campaign` to the destination URL.

Example:

1. User searches '<em>referrer analytics</em>' on Google
2. The plugin page for [Referrer Analytics](https://benmarshall.me/referrer-analytics) shows up in the results
3. The user clicks on the search result link: [https://benmarshall.me/referrer-analytics](https://benmarshall.me/referrer-analytics)
4. Their taken the the plugin page & then automatcally redirected with the referrer [UTM data](https://support.google.com/analytics/answer/1033863?hl=en): <em>https://benmarshall.me/referrer-analytics<strong>?utm_source=google.com&utm_medium=organic&utm_campaign=Google</strong></em>

Referrer Analytics allows you to configure what data is used for the UTM values & add custom referrers so you can define what those values should be.

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

= What are the names of the cookies it stores? =
* `referrer_analytics_referrer_name`<br />Human-readable name of the user's referrer
* `referrer_analytics_referrer_type`<br />Type of the user's referrer
* `referrer_analytics_referrer_host`<br />Host of the user's referrer
* `referrer_analytics_referrer_raw`<br />The raw hostname of the user's referrer
* `referrer_analytics_referrer_scheme`<br />he scheme of the user's referrer
* `referrer_analytics_referrer_url`<br />The url where the user came from
* `referrer_analytics_referrer_destination`<br />The user's original destination

== Changelog ==
= v1.2.0 =
* Added additional pre-defined referrer hosts
* Added paging to the Referrer Log
* Log now get's syned with updated referrer & known hosts
