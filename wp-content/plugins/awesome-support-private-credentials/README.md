# Awesome Support: Private Credentials Addon

Integrate capability for both administrators and users to exchange encrypted private credentials.

Saving usernames and passwords in a database always comes with a security risk. It's a terrible idea to be exchanging usernames and passwords in plain text. With Private Credentials addon you are able to exchange this type of information with reasonable security.

## Getting Started

Installation is straightforward using the usual WordPress Plugins -> Add New procedure.

- Download [Private Credentials addon](Awesome-Support-Private-Credentials.zip).
- Within WordPress Dashboard, click `Plugins` -> `Add New`
- Click the `Upload` button and select the ZIP file you just downloaded.
- Click the `Install` button

## Master Encryption Key

For security purposes a new encryption key is generated for each ticket when a private credential is created. Each ticket's private credentials are encrypted with this key prior to being stored in the database. This encryption key is stored in the database along with the credentials.  

As an added security precaution to help foil would-be database hackers we have included the option for a double layer of protection by encrypting all ticket-based encryption keys with a master encryption key that is stored on disk. 

By simply adding a single line of code to your website's wp-config.php file all of your ticket-based encryption keys will be encrypted themselves by the master encryption key stored in wp-config.

Using this method even if a hacker were to compromise your database, the credentials you have saved on tickets are useless without the master encryption key saved on disk.

> A Master Encryption key is HIGHLY recommended, but entirely optional.

To enable a Master Encryption Key simply copy and paste the following line to your wp-config.php file:

```php
    $ define('WPAS_PC_ENCRYPTION_KEY_MASTER',    'master encryption key here');
```

A good place is below your database username and password definitions or just above or below the standard keys included by WordPress.

> NOTE: Use of a Master Encryption Key, or not, should be made prior to creating any private credentials. Changing the encryption method on pre-existing private credentials will cause them to be unreadable.

## Usage

Once installed, a Private Credentials button will appear on both the backend and frontend ticket details pages. If you do not see the Private Credentials metabox on the backend ticket details page, click `Screen Options` in the upper right-hand corner of the ticket details page, then make sure `Private Credentials` is ticked. These allow ticket administrators and users to exchange credentials with each other allowing you to authenticate into systems related to the ticket.

You can add, edit or delete credentials, reset the encryption key and attach notes to each credential. Your ticket admins are able to provide credentials for the user to authenticate into a system.

The System field is used as the tab title so it should be clear what its purpose is and yet be kept as short as possible. For example:

- cPanel
- WordPress
- FTP
- etc

> All credentials are automatically removed from the database when a ticket 
> is closed from the backend or frontend ticket details pages.


### Change Log
-----------------------------------------------------------------------------------------
###### Version 2.0.0
- Update: New User Interface - remove tabs, use full screen and list instead
- Add: Separate field for URL

###### Version 1.2.4
- Tweak: Allow height of PC window to grow to 100% - makes it more compatible with other add-ons that use thickbox

###### Version 1.2.3
- Change: Add a column to the ticket list to display the number of credentials on the ticket
- Tweak: Cleaner CSS to prevent conflicts with other add-ons and plugins.
- Tweak: Moved the PRIVATE CREDENTIALS button to the BUTTON bar on the front end.
- Fix: Close button on back-end asked to confirm save even when nothing had changed.
-----------------------------------------------------------------------------------------
###### Version 1.2.2 - December 23rd, 2016
- Change: Set maximum number of credential tabs to 5 instead of 3.
- Fix: Check if user is logged in before checking view access in private_credentials_form().
- Fix: Stop the plugin from loading resources on pages when not needed.

-----------------------------------------------------------------------------------------
###### Version 1.2.1 - November 16th, 2016
- Fix: WPEngine.com issues

###### Version 1.1.0 - November 15th, 2016
- Fix: Some jquery error conflicts

###### Version 1.0.9 - November 3rd, 2016
- Fix: Force use of HTTPS when FORCE_SSL_ADMIN is set to true

###### Version 1.0.8 - October 13th, 2016

- Misc: Code formatting and documentation.

-----------------------------------------------------------------------------------------
###### Version 1.0.7 - October 13th, 2016

- Fixed: Incorrect error message in status bar when no credentials saved to ticket.

-----------------------------------------------------------------------------------------
###### Version 1.0.6 - October 13th, 2016

- Fixed: Init credential to empty array. Fixes tickets list column count.

-----------------------------------------------------------------------------------------
###### Version 1.0.5 - October 12th, 2016

- Added: Notification in status bar of popup to indicate decryption failure.

-----------------------------------------------------------------------------------------
###### Version 1.0.4 - October 12th, 2016

- Added: Tickets list column showing # of private credentials on each ticket.
- Added: init_credentials() sets initial state and allows calling by ticket id for retrieval of credential count.

-----------------------------------------------------------------------------------------
###### Version 1.0.3 - October 12th, 2016

- Fixed: Reply post was triggering save_hook().

-----------------------------------------------------------------------------------------
###### Version 1.0.2 - October 12th, 2016

- Added: Second check to ensure user has view access to current ticket. 
- Change: Use of new $post_id value in wp_footer() hook rather than get_the_ID() when rendering HTML form. 

-----------------------------------------------------------------------------------------
###### Version 1.0.1 - October 12th, 2016

- Added: $post_id var to save id initially.

-----------------------------------------------------------------------------------------
###### Version 1.0.0 - September 28th, 2016

- updated: A lot of bug fixes and code cleanup.

-----------------------------------------------------------------------------------------
###### Version 0.0.9 - September 8th, 2016

- updated: A lot of bug fixes and code cleanup.

-----------------------------------------------------------------------------------------

-----------------------------------------------------------------------------------------
###### Version 0.0.8 - August 19, 2016

- updated: Included additional installation instructions.
- updated: PHP class file header information.

-----------------------------------------------------------------------------------------
###### Version 0.0.7 - August 18, 2016

- improved: Made Master Encryption Key optional.

-----------------------------------------------------------------------------------------
###### Version 0.0.6 - August 18, 2016

- NEW: Localization of jQuery strings.
- updated: /languages POT and en_US translations.

-----------------------------------------------------------------------------------------
###### Version 0.0.5 - August 17, 2016

- NEW: Add /languages with POT template and en_US translations.
- fixed: Accessibility issues with form field arrays.
- improved: Backend and frontend UI/UX.

-----------------------------------------------------------------------------------------
###### Version 0.0.4 - August 16, 2016

- NEW: _IsSaved flag created to warn user of actions that may result in loss of credential data.

-----------------------------------------------------------------------------------------
###### Version 0.0.3 - August 15, 2016

- NEW: Disk-based Master Encryption Key support to encrypt each ticket's encryption key stored in database.
- improved: All PHP strings localized using L10N.
- NEW: Delete private credentials when ticket is closed by admin or user.
