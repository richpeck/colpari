# Awesome Support: Custom Status

Allows admins and users to create and use any number of statuses on tickets. 

## Getting Started

Installation is straightforward using the usual WordPress Plugins -> Add New procedure.

- Download the add-on.
- Within WordPress Dashboard, click `Plugins` -> `Add New`
- Click the `Upload` button and select the ZIP file you just downloaded.
- Click the `Install` button

## Prerequisites

Just the CORE AS plugin is required to use this add-on. 

## Configuration

You can create new statuses by going to Tickets->Custom Status.

## Usage

You can view usage documentation at the following link: https://getawesomesupport.com/documentation/custom-status-add/creatingadding-custom-statuses/

### Change Log
-----------------------------------------------------------------------------------------
###### Version 1.1.0
- New: Added options for sorting the statuses when shown in the status drop-down on tickets

###### Version 1.0.4
- Tweak: Updated capabilities so only users with administer_awesome_support can see the menu
- Tweak: Changed menu label to "Status an Labels"

###### Version 1.0.3
- Tweak: Updated capabilities so only certain roles can see the menu option
- Fix: Custom statuses with length greater than 20 now works.  
		(Make sure you delete old statuses with greater than 20 chars and recreate them!)
- Fix: Synchronize the color settings between AS Core's TICKETS->SETTINGS->STYLES tab and 
		this plugin's setting.  Requires AS 3.3.5 or later.
- Tweak: Ensure that a version greater than 3.3.4 of Awesome Support core is installed since
		a couple of fixes were made with support from AS core files.

###### Version 1.0.2 - November 3, 2016
- Force use of HTTPS when FORCE_SSL_ADMIN is set to true

###### Version 1.0.1 - October 19th, 2016
- Remove inadvertent limit of only 5 statuses

-----------------------------------------------------------------------------------------
###### Version 1.0.0 - August, 2016
- Initial: Public Release

-----------------------------------------------------------------------------------------