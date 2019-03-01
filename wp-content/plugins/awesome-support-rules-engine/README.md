This add-on to the Awesome Support WordPress Helpdesk Plugin allow users and administrators to automate ticket and notification actions based on user-defined rules.  Automated actions can be any of the following:
- Send emails
- Send data to Zapier
- Call a webhook
- Add a reply to a ticket
- Add a note to a ticket
- Change the status of a ticket
- Change the state of a ticket open/close
- Change the agent on a ticket

## Getting Started

Installation is straightforward using the usual WordPress Plugins -> Add New procedure.

- Download [The RULES ENGINE addon] from your account(awesome-support-rules-engine.zip).
- Within WordPress Dashboard, click `Plugins` -> `Add New`
- Click the `Upload` button and select the ZIP file you just downloaded.
- Click the `Install` button
- Click the `Activate` button

## Prerequisites

- Version 4.1.0 of Awesome Support or later.
- PHP 5.6 or later
- WordPress 4.7 or later

## Configuration

Please see the documentation located here: https://getawesomesupport.com/documentation/rules-engine/introduction-rules-engine/

## Usage

You can view usage documentation on our website here: https://getawesomesupport.com/documentation/rules-engine/introduction-rules-engine/

### Change Log

-----------------------------------------------------------------------------------------
###### Version 2.0.1
- Fix: If user closed ticket on front-end, the close rule was not firing because the permission check was for edit-ticket instead of close-ticket.

###### Version 2.0.0
- New: CRON process allows rules to be run on a regular basis on open tickets, closed tickets or both.  You can use this to do thins like send alerts on tickets that haven't been updated in a while.
- New: Added the ticket contents field as a CONDITION item.
- Fix: Issue with conditions for custom field taxonomies
- Fix: Issue with conditions for date custom fields

###### Version 1.1.2
- New: Ruleset ID as a new email template tag
- Fix: Template tags are now replaced properly in email template subject lines.

###### Version 1.1.1
- Tweak: Disable actions for DEPT and PRIORITY if not enabled in TICKETS->SETTINGS
- Tweak: Refactor a large function into three smaller ones
- Fix: The ADD NOTE action was adding the note under the end-user's name instead of an agent's name.  Now have option to use an agent's name/id
- Fix: Corrected some labels in the ACTION tabs section of a ruleset.

###### Version 1.1.0
- New: Add option to change priority as an action
- New: Add option to change department as an action
- New: Add option to change channel as an action
- New: Add option to change secondary and tertiary agents as an action
- New: Add option to change first and second additional interested user email addresses as an action
- Tweak: Added some color to the main ruleset definition screen.  Let us know if you hate it and we'll revert back.
- Fix: Some strings were not being translated

###### Version 1.0.3
- Fix: Not all roles were showing on the security settings screen
- Fix: The Close Ticket checkbox was not respecting the security role option on the settings screen.
- Fix: Not all elements of the SEND EMAIL action tab were respecting the security role option on the settings screen.

-----------------------------------------------------------------------------------------
###### Version 1.0.2
- Tweak: Easier to edit items in fields when editing a ruleset
- Fix: Permissions issue prevented a number of items from showing when used on multi-site
- Fix: Permissions issue when user with administer_awesome_support capability is logged in - they weren't being treated as an AS administer_awesome_support

###### Version 1.0.1
- New: Send TICKET ID and REPLY ID to zapier and make available for use in WEBHOOKS as well
- Tweak: Grammar (Changed TICKET REPLY RECEIVED trigger label to CLIENT REPLIED TO TICKET)
- Tweak: Include language files for translation
- Tweak: Ticket ID and Reply ID are now sent to Zapier and accessible by WEBHOOKS

Known issues that are not fixed in this release:
- Conflict with NOTIFICATIONS add-on.  Notifications will use the ASSIGNEE on the ticket as the source of replies when sending notifications instead of the ASSIGNEE on the reply

###### Version 1.0.0
- New: Initial Release.   

Known issues that are not fixed in this release:
- TICKET ID and REPLY ID are not sent to Zapier

-----------------------------------------------------------------------------------------