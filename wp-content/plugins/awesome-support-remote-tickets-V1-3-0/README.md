Awesome Support: Remote Tickets
==================

Awesome Support: Remote Tickets is an add-on for Awesome Support that allows you to accept tickets on sites outside of your Awesome Support base site.

## Requirements

- Awesome Support 4.0.4 +
- WordPress 4.0+
- PHP 5.6+

## Installation

Installation is straightforward using the usual WordPress Plugins -> Add New procedure.

- Download the add-on.
- Within WordPress Dashboard, click `Plugins` -> `Add New`
- Click the `Upload` button and select the ZIP file you just downloaded.
- Click the `Install` button

### Prerequisites

- Core Awesome Support plugin installed and activated
- The Awesome Support REST API plugin installed and activated.

## Usage

You can view usage documentation at the following link: https://getawesomesupport.com/documentation/remote-tickets/overview/.  

### Change Log

-----------------------------------------------------------------------------------------
###### Version 1.3.0
New: Added an optional terms of service checkbox.

###### Version 1.2.0
New: Added ability to disable the javascript snippet on the remote site without having to physically remove it from the site.

###### Version 1.1.0
New: Added ability to customize ticket form by including custom header and footer elements on the form.
New: Option to make help button invisible so that the javascript can be called from another element.

!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
! Warning: Breaking Change - If you are upgrading from 1.0.2 or earlier you must regenerate your Javascript and re-insert into your 
!          client sites!
!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

###### Version 1.0.4
Fix: check if the form should be displayed after the settings API call.

###### Version 1.0.3
Tweak: Update the form settings to pull from the base site so that remote ticket forms are always in sync.

###### Version 1.0.2
Tweak: User flow tweaked to be clearer after ticket is submitted.

###### Version 1.0.1
Fix: Form layout
Fix: Flow after user completes form and attempted to start a new one.
Fix: Attachments need to be moved to the proper location
Tweak: Uses the REST API plugin version 1.0.2
Tweak: Removed vendor folder - used the build process with composer and grunt to create this folder
Tweak: Updated this readme.md file

###### Version 1.0.0
Initial Release