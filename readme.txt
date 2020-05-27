=== ALO EasyMail Newsletter ===
Contributors: eventualo
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=9E6BPXEZVQYHA
Tags: send, mail, newsletter, subscription, mailing list, subscribe, batch sending, bounce, mail throttling, signup, multilanguage
Requires at least: 4.4
Requires PHP: 5.2
Tested up to: 5.4
Stable tag: 2.12.3
License: GPLv2 or later

To send newsletters. Features: collect subscribers on registration or with an ajax widget, mailing lists, cron batch sending, multilanguage, bounces.

== Description ==

ALO EasyMail Newsletter is a plugin for WordPress that allows to write and send newsletters, and to gather and manage the subscribers. It supports internationalization and multilanguage.

*Plugin links: [homepage](https://www.eventualo.net/blog/wp-alo-easymail-newsletter/) | [guide](https://www.eventualo.net/blog/wp-alo-easymail-newsletter-guide/) | [faq](https://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/) | [for developers](https://www.eventualo.net/blog/easymail-newsletter-for-developers/) | [forum](https://wordpress.org/support/plugin/alo-easymail) | [news](https://www.eventualo.net/blog/category/alo-easymail-newsletter/)*

**Here you are a short screencast:** [How to create and send a newsletter](https://www.eventualo.net/blog/wp-alo-easymail-newsletter-guide/)

**Features:**

* **write and send html/text newsletters, simple like writing posts**: newsletter is a custom post type, using the standard WordPress GUI and API
* **select the recipients to send to**: registered users, subscribers, mailing lists
* **batch sending using WP cron system**: it sends a number of emails every 5 minutes, until all recipients have been included, SMTP ready (you can use a dedicated plugin to setup)
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
* **privacy**: privacy policy checkbox in subscription form, subscriber data in exporter/eraser tools, re-permission campaigns, by default unsubscribed emails are stored encrypted
* **debug tool**: rather than the recipients, you can send all emails of a newsletter to the author or you can have them recorded into a log file

**Internationalization**

*Available in more than 20 languages.*

You can add or update the translation in your language. To make the plugin package lighter you can find only the .MO files inside it.
You can visit [translate.wordpress.org/projects/wp-plugins/alo-easymail](https://translate.wordpress.org/projects/wp-plugins/alo-easymail) to look for the most updated language files (.MO and .PO files), you can contribute to translate the plugin in your language


**For developers**

Developers can easily add own code using plugin action and filter hooks.
Inside plugin package there is a *mu-plugins* folders that contains some useful samples, e.g.: "latest posts" placeholder, "multiple posts" placeholder, include attachments, add custom fields in subscription form.
You can move one or more of those files into *wp-content/mu-plugins* (if the directory doesn”t exist, simply create it) to activate them.
You can use them as starting point for your development. Other samples at: [plugin developer page](https://www.eventualo.net/blog/easymail-newsletter-for-developers).
The plugin includes a dozen of ready-to-use newsletter themes. Of course, you can create your own themes and, if you like, you can share them with the plugin author and other users. You can send them to me or make pull requests in plugin repository.

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

Plugin links: [homepage](https://www.eventualo.net/blog/wp-alo-easymail-newsletter/) | [guide](https://www.eventualo.net/blog/wp-alo-easymail-newsletter-guide/) | [faq](https://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/) | [for developers](https://www.eventualo.net/blog/easymail-newsletter-for-developers/) | [forum](https://wordpress.org/support/plugin/alo-easymail) | [news](https://www.eventualo.net/blog/category/alo-easymail-newsletter/)

== Frequently Asked Questions ==

Plugin links: [homepage](https://www.eventualo.net/blog/wp-alo-easymail-newsletter/) | [guide](https://www.eventualo.net/blog/wp-alo-easymail-newsletter-guide/) | [faq](https://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/) | [for developers](https://www.eventualo.net/blog/easymail-newsletter-for-developers/) | [forum](https://wordpress.org/support/plugin/alo-easymail) | [news](https://www.eventualo.net/blog/category/alo-easymail-newsletter/)

== Screenshots ==

1. The subscription option on registration form
2. The widget for registered (left side) and not-registered (right side) users
3. You can add recipients to sending queue or you can send newsletter immediately
4. The ajax engine to generate list of recipients
5. The list of subscribers in administration
5. The newsletter report

== Changelog ==

= 2.12.3
* Added: labels for radio buttons in form for registered users
* Added: recipients info in newsletter report
* Added: css classes in subscriber table
* Added: best sample of unsubscribe link in theme preview
* Added: a filter 'alo_easymail_enable_re_permission' to disable re-permission campaigns

= 2.12.2 =
* Tested with WP 5.2

= 2.12.1 =
* Fixed: replace [] declarations with old array() to be compliant with php 5.2
* Fixed: force width of recipient list modal

= 2.12.0 =
* Added: a filter ('alo_easymail_subscription_form_is_enabled') to disable the subscription form from page and widget at all, useful if using other tools to collect subscribers
* Updated: the newsletter report is loaded as a page, not as a modal
* Updated: the tracking pixel is loaded from a REST url (WP >= 4.4), not from the php file inside plugin
* Fixed: to include files use ABSPATH instead of relative path
* Fixed: hide the modal of recipient list on page load to avoid that it appears for some seconds
* Fixed: remove a php warning

= 2.11.1 =
* Fixed: remove a php warning

= 2.11.0 =
* Added: [GDPR] added a new "re-permission" newsletter type: each recipient is deactivated and he/she must click the confirmation-link to reactivate his/her subscription
* Added: [GDPR] added confirmation-link placeholders for "re-permission" newsletter
* Added: [GDPR] new placeholders for Privacy Page
* Added: [GDPR] add Privacy Page link in all bundled newsletter themes
* Updated: some newsletter theme functions are pluggable

= 2.10.1 =
* Fixed: remove error on php < 5.4

= 2.10.0 =
* Added: [GDPR] the privacy policy text inside subscription form now has a checkbox
* Added: [GDPR] added a draft of suggesting text for the site privacy policy
* Added: [GDPR] now the unsubscribed emails are stored always as MD5 string
* Added: [GDPR] add newsletter data to personal data export/eraser
* Fixed: better WPML integration - thanks to [dgwatkins](https://github.com/groucho75/alo-easymail/pull/16)

= 2.9.8 =
* Added: 2 text options for subscription page: "click to unsubscribe" and "subscription deleted" messages
* Added: a filter for recipient number in newsletter report
* Added: a couple of sample functions in attachment mu-plugins
* Updated: move the "preview in newsletter theme" button inside theme metabox
* Fixed: a warning on plugin activation/deactivation
* Fixed: a warning in options page

= 2.9.7 =
* Fixed: the subscribe button now is properly escaped
* Fixed: now the title attribute in [CUSTOM-LINK] placeholder is properly replaced

= 2.9.6 =
* Added: new newsletter themes based on [Cerberus responsive template](https://github.com/TedGoas/Cerberus)
* Added: new [SITE-LOGO] placeholder
* Updated: dashboard widget that loads news from developer site
* Updated: in newsletter edit now it's possible to not select a post for placeholder
* Deleted: a admin help pointer about bounces

= 2.9.5 =
* Updated: now encrypting the unsubscribed emails is an option, default is clear text

*The full changelog is in changelog.txt inside plugin folder*

== Upgrade Notice ==

= 2.12.2 =
* Tested with WP 5.2
