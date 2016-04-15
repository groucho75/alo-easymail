=== ALO EasyMail Newsletter ===
Contributors: eventualo
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=9E6BPXEZVQYHA
Tags: send, mail, newsletter, subscription, mailing list, subscribe, batch sending, bounce, mail throttling, signup, multilanguage
Requires at least: 3.6
Tested up to: 4.5
Stable tag: 2.9.0
License: GPLv2 or later

To send newsletters. Features: collect subscribers on registration or with an ajax widget, mailing lists, cron batch sending, multilanguage, bounces.

== Description ==

ALO EasyMail Newsletter is a plugin for WordPress that allows to write and send newsletters, and to gather and manage the subscribers. It supports internationalization and multilanguage.

*Plugin links: [homepage](http://www.eventualo.net/blog/wp-alo-easymail-newsletter/) | [guide](http://www.eventualo.net/blog/wp-alo-easymail-newsletter-guide/) | [faq](http://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/) | [for developers](http://www.eventualo.net/blog/easymail-newsletter-for-developers/) | [forum](http://www.eventualo.net/forum/) | [news](http://www.eventualo.net/blog/category/alo-easymail-newsletter/)*

**Here you are a short screencast:** [How to create and send a newsletter](http://www.eventualo.net/blog/wp-alo-easymail-newsletter-guide/)

**Features:**

* **write and send html/text newsletters, simple like writing posts**: newsletter is a custom post type, using the standard WordPress GUI and API
* **select the recipients to send to**: registered users, subscribers, mailing lists
* **batch sending using WP cron system**: it sends a number of emails every 5 minutes, until all recipients have been included
* **collect subscribers**: on registration form and with an ajax widget/page, there is no limit to the number of subscribers
* **import/export subscribers**: import from existing registered users or from a CSV file
* **create and manage mailing lists**: only admin can assign subscribers to them, or subscribers can freely choose them
* **newsletter themes**: using html/php files in plugin or theme folder
* **newsletter placeholders**: a lot of tags that in each message will be replaced with e.g. recipient name, latest posts...
* **manage subscribers**: search, delete, edit subscription to mailing lists
* **manage capabilities**: choose the roles that can send newsletter, manage subscribers and settings
* **view sending report**: how many subscribers have opened the newsletter and clicked on links inside it
* **bounce management**: you can check the bounced emails and keep your email list clean
* **multilanguage**: set all texts and options, you can write multilanguage newsletters - full integration with [WPML](http://wpml.org/), [qTranslate-X](http://wordpress.org/plugins/qtranslate-x/), [Polylang](http://wordpress.org/plugins/polylang/)
* **debug tool**: rather than the recipients, you can send all emails of a newsletter to the author or you can have them recorded into a log file

**Internationalization**

*Available in more than 20 languages.*

You can add or update the translation in your language. To make the plugin package lighter you can find only the .MO files inside it.
You can visit [translate.wordpress.org/projects/wp-plugins/alo-easymail](https://translate.wordpress.org/projects/wp-plugins/alo-easymail) to look for the most updated language files (.MO and .PO files), you can contribute to translate the plugin in your language


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

= 2.9.0 =
* Updated: the cron-based bounce management is removed, now it works only manually

= 2.8.2 =
* Fixed: a CRSF/XSS vulnerability

= 2.8.1 =
* Fixed: update sanitization of html options that removed html instead of keep it

= 2.8.0 =
* Added: stop collecting subscriber IP addresses, there is an option to enable/disable it
* Updated: autosave of the preview-newsletter-in-theme to avoid issues if autosave disabled (e.g. by qTranslate-x)
* Updated: removed the iframe screencast in help tab and replaced with a link
* Updated: report popup size now is dynamic and is adjusted according to screen size
* Fixed: now required custom fields don't give errors and don't block the add new user dashboard form

= 2.7.0 =
* Added: export subscribers of a single mailing list
* Added: an option to remove subscribers when the related users are deleted
* Added: list of clicked urls in newsletter report
* Fixed: cache recipient counts to save resources especially on newsletter list screen
* Fixed: a CRSF/XSS vulnerability in options page, credits to Mohsen Lotfi (fox_one_fox_one)
* Fixed: a type in English strings: 'e-email' now become 'e-mail'
* Fixed: the preview-newsletter-in-theme now show the most recent between autosaved and saved versions
* Updated: the deprecated $wpdb->escape()
* Updated: css in subscriber list page
* Updated: now dashicon icon in the admin bar and dashboard side menu, settings label in admin bar

*The full changelog is in changelog.txt inside plugin folder*

== Upgrade Notice ==

= 2.8.2 =
Fixed a CRSF/XSS vulnerability.

= 2.9.0 =
For security reason the cron-based bounce management is removed, now it works only manually