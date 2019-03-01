# Awesome Support: Satisfaction Survey Addon

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/b/awesomesupport/satisfaction-survey/badges/quality-score.png?b=master&s=2a569c0c444284ed1531e0603701a86124a81656)](https://scrutinizer-ci.com/b/awesomesupport/satisfaction-survey/?branch=master)

Automatically sends a satisfaction survey invitation to client via e-mail (SMTP) when a ticket is closed.

## Getting Started

Installation is straightforward using the usual WordPress Plugins -> Add New procedure.

- Download [Satisfaction Survey addon](Awesome-Support-Satisfaction-Survey.zip).
- Within WordPress Dashboard, click `Plugins` -> `Add New`
- Click the `Upload` button and select the ZIP file you just downloaded.
- Click the `Install` button

## Prerequisites

The Awesome Support: Satisfaction Survey addon requires a working SMTP mail configuration. Good plugins to help configure SMTP for your WordPress website are:

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

Email invitations are scheduled to be sent using the WordPress wp_cron() facility. It is very important that wp_cron() is enabled and working correctly prior to using this addon.

- Edit `wp-config.php` and add the following line just before the `/* That's all, ... ` line.

```php
define('DISABLE_WP_CRON', false);

/* That's all, stop editing! Happy blogging. */
```

> This addon will not operate as expected unless wp_cron() is enabled and working correctly.

## Configuration

Satisfaction Survey addon can be configured from the `Tickets` -> `Settings` -> `Satisfaction Survey` tab. The default delay for sending a survey invitation email after closing a ticket is 24 hours or 1440 minutes. We recommend setting this value to 1 or 2 minutes while you are testing installation.

On the settings tab you will find many options that control the appearance and functionality of the addon.

## Usage

You can view usage documentation on the https://www.getawesomesupport.com/ website.

### Change Log
-----------------------------------------------------------------------------------------
###### Version 1.0.8 - June 2017
- Fix: Error thrown when ticket is opened on front-end.  (Emergency Fix).

-----------------------------------------------------------------------------------------
###### Version 1.0.7 - June 2017
- Enh: Add option to add quick-close and rate links in agent reply emails.

-----------------------------------------------------------------------------------------
###### Version 1.0.6 - June 2017
- Enh: Add option to control if survey pops up immediately after a ticket is closed or just sent via email.
- Enh: Add template tags for quick 'thumbs up' or 'thumbs down' that can be used in survey emails. 
		These tags will immediately record the users choice and show an thank you page instead of requiring another step to complete the survey.
- Enh: Added ability to customize the thank you message taht shows up after a survey is completed.		
- Enh: Sorting on rating column in ticket list
- Fix: License meta field wasn't being updated
- Fix: Disable if AS core isn't active

-----------------------------------------------------------------------------------------
###### Version 1.0.5 - November 27, 2016
- Fix: SS notifications occasionally being sent at incorrect times. 

-----------------------------------------------------------------------------------------
###### Version 1.0.4 - November 3rd, 2016
- Updates: Clean up translation catalog, textdomains and add to POEDITOR.net
- Fix: Force use of HTTPS when FORCE_SSL_ADMIN is set to true

-----------------------------------------------------------------------------------------
###### Version 1.0.3 - October 5th, 2016
- Additional Fixes for issue with standard wp emails not sending if this add-on is installed.
- Now requires version 3.3.3 of Awesome Support Core

-----------------------------------------------------------------------------------------
###### Version 1.0.2 - October 4th, 2016
- Fixed issue with standard wp emails not sending if this add-on is installed.
- Fixed licensing issues

-----------------------------------------------------------------------------------------
###### Version 1.0.1 - September 28, 2016
- Minor fixes

-----------------------------------------------------------------------------------------
###### Version 1.0.0 - September 23, 2016
- Initial: Public Release

###### Version 0.1.0 - September 5, 2016

- initial: Initial Alpha-Only Release

-----------------------------------------------------------------------------------------