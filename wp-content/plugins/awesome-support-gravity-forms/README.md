# Awesome Support: Gravity Forms Addon

[![Scrutinizer Code Quality]([![Scrutinizer Code Quality](https://scrutinizer-ci.com/b/awesomesupport/gravity-forms/badges/quality-score.png?b=develop-tabbed&s=06a06c90d1042ad1ca0b82c1b4f210350d6bddda)](https://scrutinizer-ci.com/b/awesomesupport/gravity-forms/?branch=develop-tabbed)

Map Gravity Forms form fields to the Awesome Support ticket fields.

## Getting Started

Installation is straightforward using the usual WordPress Plugins -> Add New procedure.

- Download [Gravity Forms addon](Awesome-Support-Gravity-Forms.zip).
- Within WordPress Dashboard, click `Plugins` -> `Add New`
- Click the `Upload` button and select the ZIP file you just downloaded.
- Click the `Install` button

## Prerequisites

The Awesome Support: Gravity Forms addon requires The Gravity Forms plugin from RocketGenius. 

- Download [Gravity Forms plugin](gf.zip) by RocketGenius.
- Create a new Gravity Forms form by clicking `Forms` -> `Add New`
- Awesome Support: Gravity Forms addon requires a Subject, Content and Email field. Optionally you can create Ticket ID, Department and Product fields.

> Do NOT proceed until you have verified that your Gravity Forms plugin is working correctly!

## Configuration

Gravity Forms addon can be configured from the `Tickets` -> `Settings` -> `Gravity Forms` tab.

On the settings tab you will find many options that control the appearance and functionality of the addon.

##### Form

Select a Gravity Form from the dropdown list.

##### Field Mappings

- Email *
- Subject *
- Content *
- Ticket ID
- Product
- Department

##### Form Options

- Allow Create User - When enabled a new user is created automatically if the specified email address does not already exist as a WordPress User. (Ticket ID must also be left blank).
- Include Unmapped Fields - When enabled all Gravity Forms fields that are submitted will be included in ticket and ticket replies as key/value pairs.
- Include WPAS GF Details - Output form submission details in tickets and replies.

##### Attributes

- Required - the field will be required regardless of form configuration
- Validate - validates that input data is valid. For Email it ensures the email address exists in the WordPress users' table. For Ticket ID this option ensures the specified ticket id exists.
- Hide - hides submitted data from the ticket or reply content.
- Populate - Populates a Department and Product dropdowns using Awesome Support configurations.

## Example Forms

We've put together some example Gravity Forms configurations and exported them to .XML format. You can download these forms and import them to your Gravity Forms configurations.
Downloads and documentation on how to configure them is located here: https://getawesomesupport.com/documentation-new/documentation-extensions-all/#sample-gravity-forms

Here is a list of forms that you can find there:

- WPAS GF - #1 Reply to Ticket
- WPAS GF - #2 Reply to Ticket (Authenticated)
- WPAS GF - #3 New Ticket
- WPAS GF - #4 New Ticket with Department
- WPAS GF - #5 New Ticket with Product
- WPAS GF - #6 New Ticket with Status & State
- WPAS GF - #7 TBD
- WPAS GF - #8 Ticket Update for Status & State Changes
- WPAS GF - #9 Ticket Update for Status & State Changes (Authenticated)
- WPAS GF - #10 TDB
- WPAS GF - #11 TBD
- WPAS GF - #12 TBD

## Usage

You can view usage documentation here: https://getawesomesupport.com/documentation-new/documentation-extensions-all/#gravity-forms

### Change Log
-----------------------------------------------------------------------------------------
###### Version 1.5.0
- NEW: Added option to hide the gravity form field ids of unmapped items added to the body of the ticket.

###### Version 1.4.0
- NEW: Added option to hide blank fields in ticket body.

###### Version 1.3.0
  ****Warning:  Please test this version in a staging site BEFORE updating!***
- NEW: Revamped entire process of adding tickets so that other add-ons have an easier time accessing and using the data.

###### Version 1.2.0
- New: Added options in the ticket mapping screen to control new user emails that are sent out by when a new user is created.

###### Version 1.1.2
- New: Add a filter so developers can hook into unmapped fields as the fields are being processed.  The filter name is gf_unmapped_field.
- Fix: A potential PHP notice could be thrown if an email tag didn't have a value and we're trying to log that value to our log files.  

###### Version 1.1.1
- Fix: When mapping an agent directly, email assignment alerts were sent to the incorrect agent.

###### Version 1.1.0

- New: Register a new custom field called gf_close_ticket.  If a value of "1" is mapped to this field when submitting a ticket-reply form, the ticket will be closed. (Thanks to Jamie from wcvendors.com for this enhancement)
       Note that this duplicates the function provided by mapping to the STATE field.  Either way would allow you to close a ticket now.
	   This is a demonstration of how to use custom fields to trigger additional functions inside of the Gravity Forms bridge and Awesome Support.
- New: Added action hook gf_wpas_after_custom_fields_update
- New: Added action hook gf_wpas_after_attachments_update
- New: Added filter gf_wpas_save_form
- Tweak: Modified filter gf_mapped_field_data to include additional parameters (Thanks to Jamie from wcvendors.com for this suggestion)
- Fix: When using a ticket id (reply) and the option to validate an email address was unchecked and the user was not logged in, the email 
       address was ignored completely, always resulting in an "invalid ticket id" message.
- Fix: Some strings were not being translated
- Fix: An attempt was made to access an object element without checking to see if the object was valid.
- Fix: When an attachment was added to a REPLY TICKET form, it was attached to the TICKET instead of the REPLY.  (Thanks to Jamie from wcvendors.com for identifying and fixing this issue)
- Fix: Check to make sure that there is a value set on a field before attempting to write it to the database to prevent an array index error (issue only showed up with ticket reply forms).
- Fix: If the Ticket State field is mapped but mapped value is empty do not attempt to update the ticket state
- Fix: If the Ticket Status field is mapped but mapped value is empty do not attempt to update the ticket status

-----------------------------------------------------------------------------------------
###### Version 1.0.7

- Tweak: Prevent scripts from loading on pages that are not related to Awesome Support.
- Fix: Taxonomy Term IDs were not being set on tickets properly

-----------------------------------------------------------------------------------------
###### Version 1.0.6

- Tweak: Show the term name instead of the id
- Tweak: Added two do_action hooks when a ticket is created
			- do_action( 'gf_wpas_after_ticket_insert_success', $this->ticket_id, $this->data, $args  );
			- do_action( 'gf_wpas_after_ticket_insert_failed', $this->data, $args  );
- Tweak: Updated language string file			
			
-----------------------------------------------------------------------------------------
###### Version 1.0.5

- Enh: All taxonomy fields can now be "popuplated"
- Enh: Separate log files for each form
- Enh: Option to auto-populate email mapped field if user is logged in
- Fix: Various bug fixes
- Tweak: Code refactoring
- Tweak: Minimum version of AS is now 4.0.0
- Tweak: Minimum version of PHP is now 5.6


###### Version 1.0.4 (Internal Release Only)

- Fix: Incorrect object reference causing nulls to be saved to user-defined custom fields.
- Tweak: Update language files
- Tweak: Lots of code clean up
-----------------------------------------------------------------------------------------
###### Version 1.0.3 - December 27, 2016

- Add: Enforce core AS File Upload settings on form uploads.

-----------------------------------------------------------------------------------------
###### Version 1.0.2 (Internal Testing) - December 2, 2016

- Add: File uploads via form submission to ticket attachments.

-----------------------------------------------------------------------------------------
###### Version 1.0.1 (2nd public Beta) - November 27 2016

- Second beta release
- Fix: Custom fields mapping logic
- Fix: Custom fields not updating postmeta

-----------------------------------------------------------------------------------------
###### Version 0.2.4 internal version - November 21, 2016

- fix: Remove testing code that created a couple of AS custom fields.

-----------------------------------------------------------------------------------------
###### Version 1.0.0 (First public Beta) - November 2016

- Initial beta release

-----------------------------------------------------------------------------------------
###### Version 0.0.10 - October 16, 2016

- initial: Initial Alpha-Only Release

-----------------------------------------------------------------------------------------