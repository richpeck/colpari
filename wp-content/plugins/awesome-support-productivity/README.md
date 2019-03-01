[![Scrutinizer Code Quality](https://scrutinizer-ci.com/b/awesomesupport/productivity/badges/quality-score.png?b=master&s=15b985a3b96fc2c10b8f1434086526e94d245992)](https://scrutinizer-ci.com/b/awesomesupport/productivity/?branch=master)

Adds a series of productivity enhancing functions to Awesome Support including:
- next/previous links on a ticket
- ticket merge
- ticket lock
and more.

## Getting Started

Installation is straightforward using the usual WordPress Plugins -> Add New procedure.

- Download [The AS PRODUCTIVITY addon] from your account(awesome-support-productivity.zip).
- Within WordPress Dashboard, click `Plugins` -> `Add New`
- Click the `Upload` button and select the ZIP file you just downloaded.
- Click the `Install` button

## Prerequisites

Version 4.0.0 of Awesome Support or later.

## Configuration

Because this add-on incldues various functions that are not necessarily related to each other you should read the user manual on our website in order to learn how to configure the function you're looking to use.

## Usage

You can view usage documentation on the https://getawesomesupport.com/documentation/productivity/ website.

### Change Log

-----------------------------------------------------------------------------------------
###### Version 4.0.0
!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
- New: REQUIRES VERSION 4.0.7 (or later) OF CORE AWESOME SUPPORT 
!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
- New: Google invisible captcha can now be activated on the registration screen
- New: Allow custom CSS to be entered for certain pages; also includes examples.
- New: Bulk Edit - quickly change agent, priority, status, channel on multiple tickets (available on WP 4.7 and later)
- New: Save filter criteria in the ticket list 
- New: Email template tag for {fullticket} - inserts the entire history of the ticket in the outgoing email
- Tweak: Merge tickets will now merge attachments from source tickets as well.
- Tweak: Better styling for merged messages.
- Tweak: Merge tickets will now search on ticket # as well as description when trying to identify the target of the merge
- Tweak: If Running in SAAS mode the CAPABILITIES tab under TOOLS will not be shown
- Tweak: Added options to turn off merge email alerts
- Fix: Security Profiles did not respect the OR option under certain circumstances

###### Version 3.0.0
- New: Agent Signatures
- New: Options to set defaults for SUBJECT and DESCRIPTION field on ticket form
- New: Email template tags for click-to-close (Warning: security and performance issues when turned on!)
- New: Email template tags for click-to-view (Warning: security and performance issues when turned on!)
- New: Option to allow certain users to use a full editor when in admin.  Set this by using the 'edit_ticket_with_full_editor' capability
- New: Allow the ATTACHMENTS label on the front-end ticket form to be changed
- New: Merge multiple tickets into one ticket (requires WP version 4.7 or higher)
- New: Add multiple email addresses to user and agent profiles and optionally allow them to receive notifications
- New: Add multiple email addresses to a ticket and optionally allow them to receive notifications
- New: Add multiple WordPress users to a ticket and optionally allow them to receive notifications
- New: Add new email notification template for tickets being closed because they are being merged. This overrides other standard email notifications (ticket closed, ticket updated etc.) during the merge process.
- New: Add new email notification template for the ticket that is being merged into.  This overrides other standard email notifications (new ticket, new reply etc.) during the merge process.
- Enh: Allow edits before saving split ticket
- Enh: Splitting a single ticket or reply can now be done multiple times
- Enh: Tabs are now responsive

###### Version 2.0.0
- New: SECURITY PROFILES for agents - extremely flexible way to control exactly what an agent can see.
- New: Tabs to help control display of todo lists/notes introduced in 1.1.0.
- New: Ticket Split

###### Version 1.1.0
- New: Agent can add new user from link on ticket page
- New: Show tickets in the user profile page
- New: Labels for key core fields can now be changed without resorting to translations
- New: Agents can create support notes tied to the customer
- New: Agents can create personal notes tied to just their profile
- New: Agents can create personal todo lists
- New: Agents can elect to automatically lock a ticket when its closed
- New: Added options in the TOOLS->CLEANUP menu to LOCK or UNLOCK all closed tickets
- New: Option to set CONTENT before the SUBMIT button on a new ticket.
- New: OPTION to set content before the SUBJECT line on a new ticket.
- New: OPTION to make PRODUCT mandatory
- New: OPTION to make Department mandatory
- New: OPTION to set the maximum number of tickets a user can have open at any time
- NEW: OPTION to set a limit on the maximum number of open and closed tickets in the user profile widget on the ticket back-end screen.



###### Version 1.0.0
- New: Initial Release

-----------------------------------------------------------------------------------------