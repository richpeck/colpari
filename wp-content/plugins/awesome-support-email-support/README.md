# README #

This project is an extension designed to run on the AWESOME SUPPORT WordPress Plugin Platform.  It creates tickets or updates tickets via email instead of forcing users to login and update their tickets.  

### How do I get set up? ###

Installation is straightforward using the usual WordPress Plugins -> Add New procedure.

- Download the file from your receipt or from your dashboard(Awesome-Support-Email-Support.zip).
- Within WordPress Dashboard, click `Plugins` -> `Add New`
- Click the `Upload` button and select the ZIP file you just downloaded.
- Click the `Install` button

Further Configuration and setup instructions located at: https://getawesomesupport.com/documentation/email-support/

### Change Log  ###
5.0.2
-----
!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
! Warning: Breaking Change - You must update your email inbox configuration to explicitly choose the security protocol.
!          Go to TICKETS->SETTINGS->EMAIL PIPING and verify the SECURE PORT option. Generally it should be set to SSL or NONE.
!
!		   If using more than one mailbox then you also need to go to TICKETS->INBOX CONFIGURATIONS and update your mailboxes there as well.
!
! Additionally, you must use Awesome Support 4.0.6 or later!
!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

- New: Option to send notifications for unassigned emails/tickets
- New: Added options in rules to UPDATE and CLOSE ticket in one action
- New: Experimental support for TLS
- New: Option to not import duplicate emails
- Tweak: Run update rules earlier in the ticket insert process by calling an earlier filter.  This allows auto agent assignment to get data it needs since the filters/hooks it uses runs later.
- Fix: Encoding issue - when email arrived with mixed encoded characters an error was thrown when the TICKETS->SETTINGS->EMAIL PIPING screen encoding settings were blank.
- Tweak: Includes new version of ZENDFRAMEWORK library (as of 5.0.2 RC02)
- Tweak: Requires 4.0.6 of Awesome Support Core.
- Tweak: Add option to turn off email rejection notifications

5.0.1
-----
- ENH: New options to control the user name when a user has to be created.  Requires AS 4.0.3 or later.
- ENH: Added support for emails where the entire email is encoded in base64 without providing an additional part with ascii/plain or html encoding.
- ENH: Option to allow certain html tags to be retained (BETA version - and possibly insecure if enabled since users can retain javascript embedded in emails!)
- ENH: Added an option to specify a character set for all emails.  This allows for the character set to be matched to the MYSQL server but could result in the loss of data for strings that cannot be converted.
- Fix: Styling issues on inbox rules and multi-inbox config pages
- Tweak: Better logging of rejected emails to the log files.
- Fix: Various bug fixes, removing unused code and a bit of code cleanup.
- Fix: Mailbox rules were unnecessarily overwriting the mailbox defaults for things like priority.  
- Fix: Handle emails where no transfer encoding is specified but the actual encoding is base64.
- Fix: Handle emails where the encoding is base64
- Fix: Email rules could not handle spaces in the "rule" field (aka the $rule_contents variable) because it was being sanitized using FILTER_SANITIZE_URL instead of FILTER_SANITIZE_STRING

5.0.0
-----
- New: Changed library used to talk to mailboxes from FLOURISH to ZEND
- Tweak: Changed version numbers to move the primary version to the first digit in the version number (from the second digit)
- New: Min PHP version is 5.6
- New: Multiple Mailboxes
- New: Rules engine for incoming emails
	- Prevent emails from being added
	- Prevent emails from being deleted
	- Update priority/dept/product/etc based on rules for newly added tickets/replies
	- Create custom commands that can be sent via email

**NOTE:** Requires 4.0.0 of the core Awesome Support plugin!	


0.4.0 (internal build)
-----
- Enhancement: Easier to re-assign unassigned emails
  - Shows both the agent and ticket # field so user can decide if the message is a new ticket or a reply to an existing ticket.  Before, the system tried to be the one that figured it out but didn't always do a good - job of that.
  - Allows user to save agent and/or ticket number without exiting the unassigned ticket.  Before, simply choosing one or the other automatically exited the unassigned ticket and could sometimes lead to the user not - realizing where the message was assigned to.
Enhancement: Always assign replies to a ticket number regardless of which email address the reply arrived from.  This allows for users to send replies from any email address since many users today have multiple email - addresses.
	- NOTE: Unrecognized email addresses will treat the reply as if its coming from the client/ticket creator.  So if an AGENT uses an unrecognized email address it will show up as being from the customer instead.
- Enhancement: Properly attribute emailed replies to the primary agent or the new agents in 3.6.0 of core (secondary or tertiary agents)
- Enhancement: Properly attribute emailed replies to the the new "interested third parties" added in 3.6.0 of core
- New: Uses a new capability to control who can see "unassigned" tickets (view_unassigned_tickets).  Added by default to administrators only in core.
- New: Fills in the channel field in the upcoming 4.0.0 version of Core.
- New: Requires 4.0.0 of core to function
- New: New filter to allow better access to the raw email.
- New: Added support for the ticket lock feature in the forthcoming PRODUCTIVITY add-on.
- New: Added new hook after data is saved - this will help to trigger notifications at the right time (when all data is saved instead of when only some is saved)
- Tweak: Adjusted some logic so that emails can be sent out after all data is saved (including attachments)
- Tweak: Added product name to license warning so user can tell which add-on is generating the warning.
- Tweak: Some grammar changes

**NOTE:** Requires 4.0.0 of the core Awesome Support plugin!

0.3.0 
------
- Add: Final - email attachments support. 
- Add: Choice of "heartbeat" or "wp-cron" method of scheduling checking for emails.  
  - The new default is wp-cron which does NOT show an alert on the dashboard when wp-admin is open.
  - But it fires more often and more consistently.
  - To see the message and to use the FETCH button, change to the jQuery Hearbeat method and save. 
- Add: Option to check for messages every 60 seconds.

0.2.9 (Internal Build)
-----
- Add: Continue developmment of email attachments support. 

0.2.8 (Internal Build)
-----
- Add: Start adding email attachments support. 

0.2.7
-----
- Fix: Obscure Flourish bug that caused the auto-create user functionality to create users with the wrong email address.

0.2.6
-----
- Update: Clean up translation catalog, textdomains and add to POEDITOR.com
- Update: Make the delimiter that shows where a reply starts and end translatable.
- Fix: Force use of HTTPS when FORCE_SSL_ADMIN is set to true

0.2.5
-----
- Better handling of replies to closed tickets. There are options now to reject replies sent to close tickets or to accept the reply and reopen the ticket.

0.2.4
-----
- The email support extension now has three options for handling unrecognized emails:
     1. Leave them in the unassigned folder like its done now
     2. Create a new ticket and, if necessary, a new user based on email address.  Send new user link to reset password
     3. Create a new ticket if the email address is recognized; otherwise leave in the unassigned folder.
- These new options are located in the TICKETS->SETUP->EMAIL PIPING tab

0.2.3
-----
- Add new filters
- Filter the returned user id
- Make sure the sub-menu is defined before working on it
- Update incorrect links in settings page