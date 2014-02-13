=== ALO EasyMail Newsletter ===
Contributors: eventualo
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=9E6BPXEZVQYHA
Tags: send, mail, newsletter, widget, subscription, mailing list, subscribe, cron, batch sending, bounce, mail throttling, signup, multilanguage
Requires at least: 3.6
Tested up to: 3.8
Stable tag: 2.5.01
License: GPLv2 or later

To send newsletters. Features: collect subscribers on registration or with an ajax widget, mailing lists, cron batch sending, multilanguage.

== Description ==

ALO EasyMail Newsletter is a plugin for WordPress that allows to write and send newsletters, and to gather and manage the subscribers. It supports internationalization and multilanguage.

*Plugin links: [homepage](http://www.eventualo.net/blog/wp-alo-easymail-newsletter/) | [guide](http://www.eventualo.net/blog/wp-alo-easymail-newsletter-guide/) | [faq](http://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/) | [for developers](http://www.eventualo.net/blog/easymail-newsletter-for-developers/) | [forum](http://www.eventualo.net/forum/) | [news](http://www.eventualo.net/blog/category/alo-easymail-newsletter/)*

**Here you are a short screencast:** [How to create and send a newsletter](http://www.eventualo.net/blog/wp-alo-easymail-newsletter-guide/)

**Features:** 

* **write and send html/text newsletters, simple like writing posts**: newsletter is a custom post type, using the standard WordPress GUI and API
* **select the recipients to send to**: registered users, subscribers, mailing lists
* **batch sending using WP cron system**: it sends a number of emails every 5 minutes, until all recipients have been included
* **collect subscribers**: on registration form and with an ajax widget/page
* **import/export subscribers**: import from existing registered users or from a CSV file
* **create and manage mailing lists**: only admin can assign subscribers to them, or subscribers can freely choose them
* **newsletter themes**: using html/php files in plugin or theme folder
* **manage subscribers**: search, delete, edit subscription to mailing lists 
* **manage capabilities**: choose the roles that can send newsletter, manage subscribers and settings
* **view sending report**: how many subscribers have opened the newsletter and clicked on links inside it
* **bounce management**: the bounced email addresses are automatically unsubscribed
* **multilanguage**: set all texts and options, you can write multilanguage newsletters - full integration with [WPML](http://wpml.org/), [qTranslate](http://wordpress.org/plugins/qtranslate/), [Polylang](http://wordpress.org/plugins/polylang/)
* **debug tool**: rather than the recipients, you can send all emails of a newsletter to the author or you can have them recorded into a log file

**Internationalization**

*Available in more than 20 languages.*

You can add or update the translation in your language. To make the plugin package lighter you can find only the .MO files inside it.
You can visit [www.translators.hunstart.hu/projects/plugins/alo-easymail](http://www.translators.hunstart.hu/projects/plugins/alo-easymail) to look for the most updated language files (.MO and .PO files), you can download and share translation files.


**For developers** 

Developers can easily add own code using plugin action and filter hooks.
Inside plugin package there is a *mu-plugins* folders that contains some useful samples, e.g.: "latest posts" placeholder, "multiple posts" placeholder, include attachments, add custom fields in subscription form. 
You can move one or more of those files into *wp-content/mu-plugins* (if the directory doesn”t exist, simply create it) to activate them.
You can use them as starting point for your development. Other samples at: [plugin developer page](http://www.eventualo.net/blog/easymail-newsletter-for-developers).

*On Github you can find a repository with latest plugin version: [github.com/groucho75/alo-easymail](https://github.com/groucho75/alo-easymail)*

== Installation ==

= INSTALLATION =
1. Upload `alo-easymail` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the `Plugins` menu in WordPress
1. If you are **upgrading** an EasyMail previous version, be sure to **upload all files** and to **activate the plugin again**

= QUICK START =
1. Go to `Appearance > Widget` to add subscription widget
1. Go to `Newsletters > Add new` to write a newsletter
1. Go to `Newsletters > Newsletters` to create recipient list and start newsletter sending

= MORE OPTIONS =
1. Go to `Newsletters > Settings` to setup options
1. Go to `Newsletters > Subscribers` to manage subscribers
1. Go to `Pages > All Pages` to customize the Newsletter page

Plugin links: [homepage](http://www.eventualo.net/blog/wp-alo-easymail-newsletter/) | [guide](http://www.eventualo.net/blog/wp-alo-easymail-newsletter-guide/) | [faq](http://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/) | [for developers](http://www.eventualo.net/blog/easymail-newsletter-for-developers/) | [forum](http://www.eventualo.net/forum/) | [news](http://www.eventualo.net/blog/category/alo-easymail-newsletter/)

== Frequently Asked Questions ==

Plugin links: [homepage](http://www.eventualo.net/blog/wp-alo-easymail-newsletter/) | [guide](http://www.eventualo.net/blog/wp-alo-easymail-newsletter-guide/) | [faq](http://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/) | [for developers](http://www.eventualo.net/blog/easymail-newsletter-for-developers/) | [forum](http://www.eventualo.net/forum/) | [news](http://www.eventualo.net/blog/category/alo-easymail-newsletter/)

== Screenshots ==

1. The subscription option on registration form
2. The widget for registered (left side) and not-registered (right side) users
3. You can add recipients to sending queue or you can send newsletter immediately
4. The ajax engine to generate list of recipients
5. The list of subscribers in administration

== Changelog ==

= 2.5.01 =
* Fixed: now subscriber ip address is not always null
* Fixed: a missing alt attribute in an img tag

= 2.5 =
* Added: a /mu-plugins folder containing custom hooks, the alo-easymail_custom-hooks.php is now deprecated
* Added: a hook: 'alo_easymail_bounce_email_unsubscribed'
* Fixed: some missing alt attribute in image tags
* Some fixes and improvements by Wojtek Szałkiewicz
* New post placeholders by Wojtek Szałkiewicz: /mu-plugins/alo-easymail_multiple-posts-placeholder.php

= 2.4.18 =
* Added: bounce management (thanks to: https://github.com/cfortune/PHP-Bounce-Handler)
* Added: compatibility with Polylang (multilingual plugin)
* Updated: the script that creates the newsletter plain text (thanks to Thomas Heinen)
* Updated: the priority of cron schedule filter now is very low so it could works also if other plugins break the schedule array
* Updated: some javascript code to be compatible with jquery 1.9
* Fixed: the ajax admin url used in frontend should work if FORCE_SSL_ADMIN is enabled and admin side is not https

= 2.4.17 =
* Fixed: now the "send a test newsletter" email goes out properly.

= 2.4.16 =
* Updated: jQuery.noConflict() in backend javascript, to decrease opportunity of issues and conflicts

= 2.4.15 =
* Added: preview in newsletter theme
* Updated: the recipient list now is loaded in a jquery modal, not in a thickbox (no more hack to load javascript into thickbox, so a lot of javascript issues with other plugins should be avoided)
* Added: some hook filters: 'alo_easymail_get_userid_by_subscriber'

= 2.4.14 =
* Fixed: newsletters were sent only to one mailing list though more lists were chosen, now send to all selected lists 
* Updated: some limits in batch sending settings
* Added: yes/no texts in report
* Added: some hook filters: 'alo_easymail_widget_ok_class', 'alo_easymail_widget_error_class', 'alo_easymail_placeholders_title_easymail_post_vars'

= 2.4.13 =
* Added: new capabilities for newsletter post type and new permission settings
* Added: [USER-EMAIL] placeholder
* Added: now recipient object has subscriber custom fields as properties
* Added: there is automatically a placehoolder for each custom field (e.g. 'cf_country' => [USER-CF_COUNTRY])
* Added: some filters ('alo_easymail_recipient_in_queue', 'alo_easymail_ip_address')
* Fixed: a divide-by-0 warning in a rate calculation

= 2.4.12 =
* Updated: the sending engine in cron batch, so maybe reduced the opportunity of multiple emails to same recipients
* Added: new [CUSTOM-LINK] placeholder
* Added: filter to add attachments in newsletter ('alo_easymail_newsletter_attachments')
* Added: in subscriber list screen now avatars are shown/hidden as set up in main WP option
* Added: in subscriber list screen now you can delete subscribers and unsubscribe their emails
* Added: you can export the list of emails of who unsubscribed the newsletter
* Fixed: the include link for PHP Simple HTML DOM (optional and deprecated)
* Fixed: edit button text in unsubscription page is under plugin textdomain

= 2.4.11 =
* Fixed: PHP Simple HTML DOM Parser (added in 2.4.10) is now disabled because it causes some issues in newsletter content: so now all links in newsletters now are not trackable (instead the links generated by placeholdes like [POST-TITLE] or [SITE-LINK] are always trackable)
* Added: the 'alo_easymail_ajaxloop_recipient_limit' filter to customise limit of recipients per time during ajax list generation

= 2.4.10 =
* Added: role filter for registered user recipients
* Added: some debug info about wp cron in batch sending settings
* Added: the 'alo_easymail_newsletter_headers' filter to customise newsletter headers
* Added: check for newsletter themes also in 'wp-content/alo-easymail-themes'
* Fixed: a potential issue on definition of batch interval
* Fixed: now all links in newsletters now really made trackable (using PHP Simple HTML DOM Parser)

= 2.4.9 =
* Added: option to append campaign vars to newsletter links (e.g. Google Analytics)
* Added: all links in newsletters now are made trackable
* Added: the IP address of subscribers now is stored on subscription
* Added: when a new subscriber submits the widget form with success, the form fields will be cleaned
* Added: in case of empty excerpt, the [POST-EXCERPT] placeholder uses the beginning of post content (only WP 3.3+)
* Added: new action hook on subscriber activation ('alo_easymail_subscriber_activated')
* Added: in report now the list of clicked links is shown
* Updated: all the .MO language files from [http://code.google.com/p/alo-easymail/](http://code.google.com/p/alo-easymail/)
* Fixed: in subscriber list screen now the pagination is set up using WP screen options (top right menu)
* Fixed: the issue with a big number of subscribers, in newsletter screen the countings are cached (faster loading of page)
* Fixed: plugin url and path now use the correct functions, useful for ssl enabled sites (thanks to Hansjörg Schwarz)
* Fixed: a bug in mailing list dropdown in subscriber list screen
* Fixed: now the plugin css file in child theme is properly loaded (thanks to obiweb)

= 2.4.8 =
* Fixed: XSS vulnerabilities
* Fixed: name "unikey" in activation email
* Fixed: some php warnings
* Added: some help pointers in admin (WP 3.3)

= 2.4.7 =
* Updated: now the batch interval is 5 minutes and the maximum number of emails per batch is 30
* Fixed: activation link is properly encoded when email address has "+" char
* Fixed: [USER-UNSUBSCRIBE] now is properly replaced in English
* Added: some more help info in cron batch tab in settings page
* Added: category filter in [LATEST-POSTS] in custom hooks file

= 2.4.6 =
* Fixed: a bug that causes an infinite loop of the mail engine on the same recipient if the email is not correct (thanks to david of dot4all.it)

= 2.4.5 =
* Fixed: XSS vulnerabilities
* Added: _nonce fields in subcription form and unsubscription page

= 2.4.4 =
* Fixed: XSS vulnerabilities
* Fixed: submitted email addresses with "+" char is properly encoded

= 2.4.3 =
* Fixed: files now formatted with \n as the line ending (Unix line endings)
* Fixed: on plugin uninstallation now the unsubscribed table is deleted
* Updated: the [DATE] placeholder now is based on get_option('date_format')
* Updated: mb_detect_encoding no more used so mbstring module is not required

= 2.4.2 =
* Fixed: when an admin edits a subscriber inline, the "last activity" is not updated
* Fixed: if newsletters are not published on blog, there is not the "preview" link in edit screen messages
* Fixed: on registration, if a new user does not subscribe, the email is now added to unsubscribed db table

= 2.4.1 =
* Fixed: a serious bug in db table installation/upgrade so errors on subscriptions
* Added: a list of placeholders in newsletter content editor

= 2.4 =
* Added: a new db table to store unsubscribed emails
* Added: last_activity column in subscriber db table
* Added: custom fields (thanks to eqhes!)
* Added: clickable palceholders (post url, read online, site url) in newsletters are now tracked
* Updated: smartupdater.js v.3.2
* Updated: help screens and toolbar now use WP 3.3 API
* Fixed: newsletter themes are now utf-8
* Fixed: when on blog 'alo_easymail_newsletter_content' filter now is applied only to newsletters 

= 2.3 =
* Added: support for newsletter themes in .php (thanks to eqhes!)
* Added: recipients of previous newsletters can be archived, for best query performances
* Added: KEY indexes in plugin database tables, for best query performances
* Added: [USER-NAME] and [USER-FIRST-NAME] work in newsletter title
* Added: pagination of recipients in report screen
* Added: an option to publish or not newsletters online
* Added: auto-deletion of post meta when Duplicate Post plugin copies newsletters
* Updated: glob() function for list theme files
* Updated: not using GLOB_BRACE param in glob(), for compatibilty
* Fixed: permission settings properly maintained when plugin de/re-activated
* Fixed: add a default text when auto-generated alternative plain text is empty

= 2.2.1 =
* Fixed: now query that gets recipients in queue should be very faster

= 2.2 =
* Added: compatibility with WPML (WordPress Multilingual Plugin)
* Fixed: now html theme loaded also when recipient is a registered user
* Fixed: now translation of text in html theme file
* Added: 'updates from plugin developer' in dashboard only for administrators
* Fixed: alo_easymail_print_archive() and [ALO-EASYMAIL-ARCHIVE] print newsletter properly localised

= 2.1.3 =
* Fixed: now the list of recipients is properly created when including registered users
* Fixed: unwanted redirect to dashboard using IE when saving subscriber in inline edit
* Fixed: the theme dropdown select in newsletter edit screen now saves properly
* Fixed: a better replacement in theme files about theme folder url from relative to absolute
* Fixed: improvements in newsletter text-plain version (thanks to sanderbontje!)
* Fixed: missing escape of form button text
* Added: when importing csv the exiting emails can be assigned to other lists
* Added: sender email is customisable in activation mail too (thanks to Matthias!)

= 2.1.2 =
* Added: option for alternative js library for recipient list (PeriodicalUpdater.js)
* Added: an admin notice if plugin db tables are missing
* Added: new filters for thumb and gallery placeholders (thanks to iwan!)
* Added: "enter" key disabled on edit-inline subscriber fields
* Added: a filter for args of 'register_post_type' newsletter

= 2.1.1 =
* Added: inline-edit in admin list of subscribers
* Added: script checks to avoid fatal error if post thumbnail is not supported
* Fixed: alo_em_get_recipients_in_queue() should work better

= 2.1 =
* Added: newsletter themes using html files in plugin or theme folder (thanks to iwan, again!)
* Added: placeholders for newsletter content
* Added: an option to create list or recipients without ajax
* Added: 'Newsletter' settings tab
* Updated: smartupdater.js v.3.1

= 2.0.3 =
* Fixed: now loading "registration.php", it solves a bug about creation of recipients' list and batch sending
* Fixed: "load_plugin_textdomain" now runs properly before "register_post_type"
* New css in Settings screen, more like WP style (thanks to iwan!)

= 2.0.2 =
* Added: option to load only plugin js on creation list of recipients thickbox
* Fixed: some bugs

= 2.0.1 =
* Fixed: some bugs

= 2.0 =
* Re-written the code about creation, editing and sending of newsletters
* Now Newsletter is custom post type
* Ajax long polling engine to create list of recipients
* New database plugin tables to decrease memory usage
* Solved the bug about sending to a large numeber or recipients (multiple/missing sendings)
* Added action and filter hooks, useful for developers

= 1.8.7 =
* Added: some css samples in alo-easymail.css
* Added: a couple of options to use or not text filters and shortcodes in newsletter text
* Added: when a user changes email or name, the subscription is updated too
* Added: css classes in form for registered and not-registered users
* Fixed: add-link thickbox in editor on WP 3.1
* Fixed: when plugin re-activated, no update of subscription page's texts
* Fixed: users with only "send_easymail_newsletter" capability view properly the sending page
* Fixed: now admin-bar works and newsletter submenu depends on user role
* Fixed: deleted size attributes from form inputs

= 1.8.6 =
* Added: Direct subscription without activation e-mail now available
* Added: Time interval between emails of same batch
* Added: Debug newsletters: send all emails to author or write them into a log file
* Fixed: ALO_em_get_recipients_registered() gets properly members (definitely, I hope)
* Fixed: checkboxes properly work in Settings
* Added: "open in a new window" button in report thickbox
* Added: alert and help about timeout to increase

= 1.8.5 =
* Added: Customisation of available languages
* Added: New options on importation: lists, languages
* Added: Policy claim at widget/page bottom
* Added: newsletter menu in Admin bar (WP 3.1)
* Added: [POST-TITLE] shows the post title in reports
* Added: css classes and ids in forms
* Added: contextual help and credit banners in back-end
* Fixed: now compatible with WP_CONTENT_URL and WP_PLUGIN_DIR
* Fixed: ALO_em_get_recipients_registered() gets properly members
* Fixed: custom English texts should work when English is the only available language

= 1.8.4 =
* Added: Native multilanguage functionality in back-end and front-end
* Added: Full integration with qTranslate multilanguage plugin
* Added: "post-title" tag now works in newsletter subject
* Added: sender's name option 
* Added: activation edit bulk action in subscriber manage page
* Updated: no file extension check on csv importation
* Updated: the subscription page is deleted only if complete uninstall is required
* Fixed: registered user importation on multisite (thanks to RavanH)
* FIxed: "user-name" and "user-first-name" tags now should work properly

= 1.8.3 =
* Fixed: the newsletter content-type
* Fixed: some escape/stripslashes

= 1.8.2 =
* Added: newsletter templates
* Added: embed css file for styling plugin
* Added: subscribers exportation
* Added: an option about max sendings per batch
* Added: an option to choose the subscription page
* Fixed: newsletter datetimes now use GMT blog datetime
* Fixed: alert on subscription if email address is already subscribed
* Fixed: remove -br- in content when rendering an html table
* Fixed: all plugin paths and urls
* Updated: now newsletter transfer encoding is 8bit
* Updated: send multipart newsletters (html and text) to make them less spamish
* Updated: new tab layout on sending page

= 1.8.1 =
* Fixed: the "updating..." msg should not get stuck anymore
* Fixed: in subscription page the list form appears only if there is at least a list
* Fixed: the csv importation system should work better
* The subscription page generated on installation now is titled simply "Newsletter"

= 1.8 =
* Added: mailing lists
* Added: subscription choice on registration form
* Added: tracking system when subscribers open newsletter
* Added: subscribers importation from existing members or from a csv file
* Added: use capabilities and not user_level, so better permission managing
* Added: an option to show subscription page
* Added: dashboard widget and favorite menu link
* Updated: a better formatting in admin side
* Fixed: now admin can modify subscription on user profile page
* Fixed: now easymail page and its option are properly deleted on deactivation
* Fixed: encode entities in newsletter header and subject
* Fixed: a lot of php warnings and wp notices

= 1.7 =
* Added: internationalization (with .mo and .po files)
* Added: tabs navigation in options page
* Fixed: forced collation on db tables installation
* Fixed: optin/optout texts move from widget options to main option page

= 1.6.1 =
* Fixed: now unsubscribe link is printed
* Added: the mail charset is not UTF-8 but the same of the blog

= 1.6 =
* Added: a configurable cron batch to send newsletter in scheduled sendings
* Added: a report with delivery summary for each newsletter
* Added: a bit of ajax in the widget
* Added: the subscription form in the newsletter page
* Added: an option to choose sender email address
* Added: css classes to format texts, to be specified in theme css
* Added: a link to the post in the [POST-TITLE] tag
* Added: address list and tamplate are now usermeta and not blog option
* Fixed: WP is_email() function instead of ereg()
* Fixed: the unsubscribe link now uses 'home' option instead of 'siteurl' option

= 1.5 =
* Added: not-registered visitors can subscribe the newsletter
* Updated: Greg's widget (see 0.9.3 Reloaded) to collect pubblic subscriptions 
* Added: a admin page to manage subscribers
* Added: the result page opens in a popup with report of each recipients
* Added: a [POST-CONTENT] tag 

= 0.9.3 Reloaded by GREG LAMBERT =
* THANKS to Greg for the following important features:
* Added: widget to manage user subscription
* Added: optin/out option to the user's profile 
* Added an option to allow editors to send email 
* Added: a [USER-FIRST-NAME] tag
* Fixed: a couple of warning messages where indexes were not defined
* Tested on WP 2.9.1

= 0.9.3 =
* Fixed: delete line breaks in tag POST-EXCERPT
* Tested with WP 2.9

= 0.9.2 =
* Added: using wp_mail() function instead of mail()
* Added: saving emails' list for next sending

= 0.9.1 =
* Updated: tinymce's media buttons compatible with WP v.2.8.4
* Fixed: correct stripslashing when updating content in option page

= 0.9 =
* First release

== Upgrade Notice ==

= 1.5 =
Very important release with new features.

= 1.6 =
New feature: now the plugin uses the wp_cron system. Please read about a known bug of the wp_cron of some WP versions.

= 1.7 =
Upgrade your WP installation to 3.0: the wp_cron bug seems to be solved. New feature: internationalization.

= 1.8 =
New features: mailing lists, subscribers importation, tracking system.

= 1.8.1 =
Release to fix some bugs.

= 1.8.2 =
New features: templates, subscribers exportation, a lot of improvements.

= 1.8.3 =
Release to fix some bugs.

= 1.8.4 =
New features: multilanguage, integration with qTranslate plugin.

= 1.8.5 =
Some new features and bug fixes.

= 1.8.6 =
Some new features and bug fixes.

= 1.8.7 =
Some new features and bug fixes.

= 2.0 =
Re-written the code about newsletters. Solved the bug about sending. If you are upgrading from 1.x, before start make a backup of plugin db tables.

= 2.0.3 =
Fixed some important bugs.

= 2.1.1 =
New inline-edit subscribers. Some bug fixes.

= 2.1.2 =
Option for alternative js library for recipient list.

= 2.1.3 =
Solved major bug: now the list of recipients is properly created when including registered users

= 2.2 =
Added compatubility with WPML (WordPress Multilingual Plugin)

= 2.2.1 =
Query that gets recipients in queue was optimized: so the batch sending should be faster

= 2.3 =
Better performances in database queries. Newsletter themes also in php.

= 2.4 =
Added custom fields for subscription form, newsletter click tracking, db table for unsubscribed emails.

= 2.4.1 =
Fixed a serious bug that could causes errors on subscriptions

= 2.4.2 =
Fixed little bugs

= 2.4.3 =
Files now formatted with \n as the line ending (Unix line endings)

= 2.4.5 =
Security update! Fixed XSS vulnerabilities

= 2.4.6 =
Fixed a bug that causes an infinite loop of the mail engine on the same recipient if the email is not correct

= 2.4.7 =
Now batch every 5 minutes. Fixed little bugs.

= 2.4.8 =
Security update! Fixed XSS vulnerabilities

= 2.4.9 =
Some little improvements

= 2.4.10 =
Role filter for registered user recipients. Some little improvements. Some fixes.

= 2.4.11 =
PHP Simple HTML DOM Parser (added in 2.4.10) is now disabled because it causes some issues in newsletter content.

= 2.4.12 =
Some improvements

= 2.4.13 =
Capability settings changed: you have to go to Newsletters → Settings → tab Permissions and update settings

= 2.4.14 =
Fixed a bug about sending to more than one mailing list.

= 2.4.15 =
Added preview in newsletter theme. Updated recipient list in modal (maybe javascript issues fixed).

= 2.4.16 =
Added jQuery.noConflict() in backend javascript, to decrease opportunity of issues and conflicts.

= 2.4.17 =
Fixed a bug about "send a test newsletter": now it goes out properly.

= 2.4.18 =
Added bounce management and compatibility with Polylang.

= 2.5 =
Some improvements

= 2.5.01 =
Some bug fixes.
