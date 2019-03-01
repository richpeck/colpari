# Awesome Support: Automatic Ticket Close

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/b/awesomesupport/automatic-ticket-close/badges/quality-score.png?b=master&s=9486b92e4454dbadb1ae608600f79be6531aec17)](https://scrutinizer-ci.com/b/awesomesupport/automatic-ticket-close/?branch=master)

Automatically send out warning messages about tickets that have not received replies from a customer and, optionally, close them after a set period of time.

## Getting Started

Installation is straightforward using the usual WordPress Plugins -> Add New procedure.

- Download [Automatic Ticket Close addon](Awesome-Support-Automatic-Ticket-Close.zip).
- Within WordPress Dashboard, click `Plugins` -> `Add New`
- Click the `Upload` button and select the ZIP file you just downloaded.
- Click the `Install` button

## Prerequisites

The Awesome Support: Automatic Ticket Close addon requires a working SMTP mail configuration. Good plugins to help configure SMTP for your WordPress website are:

* [Postman SMTP Mailer](https://wordpress.org/plugins/postman-smtp/)
* [Easy WP SMTP](https://wordpress.org/plugins/easy-wp-smtp/)

Although there are many others. 

> Do NOT proceed until you have verified that your SMTP configuration is working correctly!

Additionally, we cannot debug email issues unless you are using a transaction e-mail service such as:

- Mailgun
 
- SendGrid 

- Mandrill 

The reason is simple - these services provide PROOF that an email was sent.  Standard SMTP services do not provide any kind of easy to view proof of service.

## About Wordpress Scheduling ##

Warning messages are scheduled to be sent using the WordPress wp_cron() facility. It is very important that wp_cron() is enabled and working correctly prior to using this addon.

- Edit `wp-config.php` and add the following line just before the `/* That's all, ... ` line.

```php
define('DISABLE_WP_CRON', false);

/* That's all, stop editing! Happy blogging. */
```

> This addon will not operate as expected unless wp_cron() is enabled and working correctly.

## Configuration

This addon can be configured from the `Tickets` -> `Settings` -> `Auto Close` tab. There is no default configuration so you must configure your first warning message and optional auto-closing conditions before this plugin will work.  Note that all time delays are in MINUTES!

## Usage

You can view usage documentation on the https://getawesomesupport.com/documentation-new/documentation-extensions-all/ website.

### Change Log

-----------------------------------------------------------------------------------------
###### Version 1.0.4
- Fix: Minor fix to deactivation code to remove a PHP notice.
- Tweak: Now requires AS version 4.1.0 as the minimum version
- Tweak: Now requires PHP 5.6 as the minimum version since that is what AS 4.x requires
- Tweak: Email receipients to match changes made in notifications api for 4.0.6
- Tweak: Updated warning message custom field internal definition to use new 4.1.0 attributes to prevent display on the front end.
- Tweak: Grammar changes
- Tweak: Update language catalog file
- Tweak: Option to control how many messages per sequence is sent out on each cron cycle.  Now defaults to 1 instead of All
- Tweak: Option to clear and restart the sequence of messages for any tickets where the full message cycle is not yet complete.

###### Version 1.0.3 - November 3rd, 2016
- Update: Clean up translation catalog, textdomains and add to POEDITOR.net
- Fix: Force use of HTTPS when FORCE_SSL_ADMIN is set to true

###### Version 1.0.2 - October 26, 2016
- Added: Show the last date the ticket was evaluated for auto-close in the AutoClose metabox.
- Fix: Additional fixes for potential duplicate emails.

###### Version 1.0.1 - October 20, 2016
- Changes to support reopening ticket
- Fix: prevent duplicate emails from being sent under certain circumstances

###### Version 1.0.0 - October 14th, 2016
- First Release

###### Version 0.9.0 - September 26, 2016
- Initial: First Public Beta

###### Version 0.0.1 - September 20, 2016

- initial: Initial Alpha-Only Release

-----------------------------------------------------------------------------------------