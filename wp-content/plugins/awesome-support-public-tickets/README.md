[![Scrutinizer Code Quality](https://scrutinizer-ci.com/b/awesomesupport/public-tickets/badges/quality-score.png?b=master&s=5510bbea0eab47c9efe5e69c50780b6b8c0a7be1)](https://scrutinizer-ci.com/b/awesomesupport/public-tickets/?branch=master)

This add-on for the Awesome Support Help Desk WordPress plugin allows certain tickets and replies to be tagged as "public" or "private".  The public ticket flag can then be used to select tickets that can be displayed and searched without forcing a user to login and without restricting access to the ticket (or reply) to just the owner of the ticket.

The add-on includes three public presentation types that can be chosen via a short code - list, grid and accordion.

## Getting Started

Installation is straightforward using the usual WordPress Plugins -> Add New procedure.

- Download [The AS PUBLIC TICKETS addon] from your account (awesome-support-public-tickets.zip).
- Within WordPress Dashboard, click `Plugins` -> `Add New`
- Click the `Upload` button and select the ZIP file you just downloaded.
- Click the `Install` button

## Prerequisites

Version 4.0.0 of Awesome Support or later.

## Configuration

Once activated, please go to the TICKETS->SETTGINS->Public Tickets configuration tab.  There, you will find two options that govern who can set the public/private tags on tickets and replies.  Additionally, you will see the parameters for the shortcode that you can use - you will need to create at least one page with this shortcode in order to view public tickets without logging in an Awesome Support account.

## Usage

You can view usage documentation on the https://getawesomesupport.com/documentation-new/documentation-extensions-all/ website.

### Change Log

-----------------------------------------------------------------------------------------
###### Version 1.1.1
- Fix: The show public/private flag option was not being respected for replies on the front end.
- Fix: The show public/private flag option would turn off the field on the back-end as well if it was turned off in settings.  It should only be turned off on the front-end.
- Fix: Show public/private flag in ticket list wasn't being respected.

###### Version 1.1.0
- ENH: If user is logged in then display their name if they have an interest in the ticket/reply even if name is set to be hidden.
- ENH: If agent is logged show their name on the ticket even if name is set to be hidden.
- ENH: If admin is logged in always show all names even if name is set to be hidden.

###### Version 1.0.4
- Tweak: Renamed some functions so that they have the proper awesome support prefixes to prevent conflicts with other plugins.

###### Version 1.0.3
- Fix: Prevent scripts from loading on pages that are not related to Awesome Support.

###### Version 1.0.2
- Fix: Incorrect constant name.

###### Version 1.0.1
- Fix: Minor code clean up in main plugin file

###### Version 1.0.0
- New: Initial Release

-----------------------------------------------------------------------------------------