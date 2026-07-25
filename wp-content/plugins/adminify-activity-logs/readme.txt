=== Adminify Activity Log & Audit Trail ===
Contributors: pixarlabs, litonice13
Donate link: https://ko-fi.com/litonarefin
Tags: activity log, wordpress activity log, audit log, event log, user tracking
Requires at least: 5.0
Tested up to: 7.0
Requires PHP: 7.0
Stable tag: 1.0.9
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Track every change in your WordPress dashboard. Free user activity log with role, time, component, and action filters. No setup, no paid tier.

== Description ==

= Adminify Activity Logs: Free WordPress User Activity Log =

**Adminify Activity Logs** records every change made inside the WordPress dashboard. Who did what, when, and from which IP. Built for site owners and agencies who want a clean audit trail without paying for a premium tier or installing a heavy security suite. Everything in this readme is in the free plugin. No locked filters, no upsell modal, no nag screens.

➡️ [Explore More](https://wpadminify.com/activity-logs?&utm_source=wordpressorg&utm_medium=readme&utm_campaign=adminify-activity-logs) | 📖 [Documentation](https://wpadminify.com/docs/activity-logs/al-installation?utm_source=wordpressorg&utm_medium=readme&utm_campaign=adminify-activity-logs) | 💬 [Support](https://wordpress.org/support/plugin/adminify-activity-logs/)

**Why run a WordPress activity log**

If you don't know who deactivated that plugin or deleted that post, you can't tell a mistake from a security incident. Activity logs answer who-did-what-when. Any WordPress site with more than one person in the dashboard eventually needs one. Agency builds, membership sites, and multi-author blogs all run into the same problem: someone made a change, nobody owns up, and you spend an afternoon working out what happened. An audit trail saves that afternoon.

= What Adminify Activity Logs does that the others don't =

* **[Free with no Pro tier upsell](https://wpadminify.com/activity-logs?utm_source=wordpressorg&utm_medium=readme&utm_campaign=adminify-activity-logs):** Every filter, every retention setting, and every detail view ships in the free plugin. Other activity log plugins lock IP tracking or longer retention behind a paid plan.
* **[Real-time logging](https://wpadminify.com/activity-logs?utm_source=wordpressorg&utm_medium=readme&utm_campaign=adminify-activity-logs):** Activities appear in the log as soon as they happen. No cron, no batch delay.
* **[IP address tracking](https://wpadminify.com/activity-logs?utm_source=wordpressorg&utm_medium=readme&utm_campaign=adminify-activity-logs):** Each entry records the source IP. Useful when you're investigating a login or a change you didn't expect.
* **[Time, role, user, component, and action filters](https://wpadminify.com/activity-logs?utm_source=wordpressorg&utm_medium=readme&utm_campaign=adminify-activity-logs):** Narrow the log by time frame (today, yesterday, week, month), user role, specific user, WordPress component (Post, Plugin, Theme, etc.), or action type (Updated, Deleted, Activated).
* **[Configurable retention](https://wpadminify.com/activity-logs?utm_source=wordpressorg&utm_medium=readme&utm_campaign=adminify-activity-logs):** Default is 30 days. Bump it up if you need a longer compliance window. Bring it down if database size is tight.
* **[Searchable records](https://wpadminify.com/activity-logs?utm_source=wordpressorg&utm_medium=readme&utm_campaign=adminify-activity-logs):** Find a specific change by user, action, or keyword without scrolling.
* **[Multisite compatible](https://wpadminify.com/activity-logs?utm_source=wordpressorg&utm_medium=readme&utm_campaign=adminify-activity-logs):** Works across WordPress network installations.
* **[Built by Pixar Labs](https://wpadminify.com/activity-logs?utm_source=wordpressorg&utm_medium=readme&utm_campaign=adminify-activity-logs):** Same team that ships WP Adminify, Admin Bar Editor, Master Addons, and Loginfy.

= Detailed Feature Breakdown =

**What gets logged for every entry**

* **Date:** Timestamp when the action happened.
* **Author:** Username of the person who did it.
* **IP Address:** Source IP for location tracking.
* **Type:** Category of the action (post update, plugin installation, etc.).
* **Label:** Short description of the activity.
* **Action:** Specific operation (edited, deleted, added, etc.).
* **Description:** Detailed explanation of the activity.
* **Delete control:** Remove individual log entries with one click.

**Time-frame filters**

* All time
* Today
* Yesterday
* Week
* Month

**Role-based filters**

View activities by user role: Administrator, Editor, Author, Contributor, Subscriber, Customer, Shop Manager, Guest, plus any custom roles registered on the site.

**User filters**

* All users
* Specific users (select individual users to track)

**Component filters**

Track activities tied to specific WordPress components:

* Attachments
* Comments
* Options
* Plugin
* Post
* Taxonomy
* Theme
* User

**Action types tracked**

* Activated
* Added
* Approved
* Created
* Deactivated
* Deleted
* Installed
* Trashed
* Unapproved
* Updated

**Configuration options**

* **Data Retention:** Adjust the "Data Store for" value (default 30 days).
* **Search & Filter:** Filter logs by user, action, or keyword via the search bar.

**Common use cases**

* **Security investigation:** Spot unauthorized access attempts and unusual activity.
* **Accountability:** Know who did what and when across the site.
* **Troubleshooting:** Trace recent changes when something breaks.
* **Compliance:** Keep an audit trail for regulatory requirements (GDPR, HIPAA, internal policy).
* **Team transparency:** Track contributions across multi-author sites.
* **Disaster recovery:** See what changed in the hours before a problem occurred.

**[⚡ Upgrade to Adminify Pro](https://wpadminify.com/activity-logs?utm_source=wordpressorg&utm_medium=readme&utm_campaign=adminify-activity-logs)** to get the full Adminify Pro suite. 60+ admin customization features beyond activity logging: white labeling, menu editor, dashboard widgets, login customizer.

== Installation ==

= Automatic installation (recommended) =

1. Log in to your WordPress dashboard.
2. Navigate to `Plugins > Add New`.
3. Search for **"Adminify Activity Logs"**.
4. Install the plugin developed by **Pixar Labs**.
5. Click **Activate**.

= Manual installation =

1. Download the `adminify-activity-logs` plugin ZIP from WordPress.org.
2. In your dashboard, go to `Plugins > Add New > Upload Plugin`.
3. Upload the ZIP file and click **Install Now**.
4. After installation, click **Activate Plugin**.

= Getting started =

1. After activation, open **Activity Logs** in the WordPress dashboard menu.
2. The log starts populating immediately. No setup needed.
3. Use the filters at the top of the log table to narrow by time, role, user, component, or action.
4. Open the plugin settings to adjust the data retention period.

== Frequently Asked Questions ==

= What does Adminify Activity Logs track? =

Every change made inside the WordPress dashboard: post updates, plugin installs, theme changes, user logins, settings changes, taxonomy edits, attachment uploads, comments, and option changes. Each entry records the user, the action, the IP address, and a description.

= Will this plugin slow down my site? =

No. Adminify Activity Logs only runs in the WordPress admin area. It doesn't load on frontend page views, so visitor page speed isn't affected.

= How long are activity logs stored? =

Default retention is 30 days. Adjust it in the plugin's settings panel. Longer if you need a bigger compliance window. Shorter if database size is tight.

= Can I export the activity logs? =

Not yet. The current version doesn't ship a built-in export button. You can copy the log table contents into a spreadsheet manually if needed.

= Does it track frontend visitor activity? =

No. Adminify Activity Logs only tracks actions inside the WordPress dashboard (the admin area). It doesn't log frontend visitor browsing.

= Will it track activities from all users? =

Yes. The plugin records actions from all registered users who do anything in the WordPress dashboard, regardless of role.

= Can I get notified when specific actions occur? =

Not yet. Notifications aren't in the current version. Check the log periodically using the search and filter controls to find specific actions.

= Is there a limit to how many actions it can track? =

No. There's no cap on the number of logged actions. Entries older than your retention setting get cleaned up automatically to keep the database size in check.

= Is this plugin really free? =

Yes. Every feature listed in this readme is in the free version. There's no premium tier for Adminify Activity Logs as a standalone plugin. If you want broader admin customization (white labeling, menu editor, dashboard widgets, login customizer), look at the full Adminify plugin.

= Does it work on WordPress multisite? =

Yes. The plugin is compatible with WordPress network installations.

= Where do I report security bugs found in this plugin? =

Report security issues to the Jewel Theme team via the [Adminify Activity Logs product page](https://wpadminify.com/activity-logs?utm_source=wordpressorg&utm_medium=readme&utm_campaign=adminify-activity-logs). Include reproduction steps and the plugin version so the team can verify and ship a patch.

= Where do I report security bugs found in this plugin? =

Please report security bugs found in the source code of the undefined plugin through the [Patchstack Vulnerability Disclosure  Program](https://patchstack.com/database/vdp/aece5f5b-6031-422f-9630-b5e1e51f8a1a). The Patchstack team will assist you with verification, CVE assignment, and notify the developers of this plugin.

== Screenshots ==

1. Activity Logs dashboard showing the full event timeline.
2. Filter controls for time, role, user, component, and action type.
3. Detail view of a single activity log entry showing IP address and full description.
4. Plugin settings panel with data retention period and search options.
5. Activity log filtered by Administrator role and Plugin component.
6. Search box returning matched activity entries.

== Changelog ==
= 1.0.9 (21-05-2026) =
Updated: Plugin Title Update 

= 1.0.8 (21-05-2026) =
Added: Recommended suggest plugins options added.
Updated: Performance Improved.
Updated: WordPress 7.0 compatibility checked

= 1.0.7 (05-02-2026) =
* Fixed: URL routing conflict with the Loginfy plugin resolved.

= 1.0.6 (12-10-2025) =
* Fixed: Class missing fatal error resolved.

**➡️ [View Full Changelog](https://wpadminify.com/docs/activity-logs?utm_source=wordpressorg&utm_medium=readme&utm_campaign=adminify-activity-logs)**

== Upgrade Notice ==

= 1.0.7 =
Compatibility fix for URL routing conflict with the Loginfy plugin. Recommended for sites running both. ➡️ [Upgrade Now](https://wpadminify.com/activity-logs?utm_source=wordpressorg&utm_medium=readme&utm_campaign=adminify-activity-logs)

= 1.0.6 =
Class missing fatal error resolved. Recommended for all users. ➡️ [Upgrade Now](https://wpadminify.com/activity-logs?utm_source=wordpressorg&utm_medium=readme&utm_campaign=adminify-activity-logs)
