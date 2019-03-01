Provides automated and manual time tracking and invoicing functions for the Awesome Support WordPress HelpDesk Plugin

## Getting Started

Installation is straightforward using the usual WordPress Plugins -> Add New procedure.

- Download [The AS Time Tracking addon](awesome-support-time-tracking.zip).
- Within WordPress Dashboard, click `Plugins` -> `Add New`
- Click the `Upload` button and select the ZIP file you just downloaded.
- Click the `Install` button
- Click the `Activate` button after the installation is complete

## Prerequisites

Version 4.3.0 of Awesome Support or later.
PHP 5.6 or later, 7.0.x recommended
WordPress 4.4 or later, 4.9 recommended.

## Configuration

Please go to the TICKETS->SETTINGS->TIME TRACKING tab to set up some basic options. And read the documentation on our website for complete instructions on how it all works.

## Usage

You can view usage documentation on our website here: https://getawesomesupport.com/documentation/advanced-time-tracking/overview-advanced-time-tracking/

### Change Log
-----------------------------------------------------------------------------------------
###### Version 2.1.0
- Tweak: Use Awesome Support core sessions instead of PHP sessions
- Fix: 4.9.6 issue with editor when the powerpack add-on is installed along with time tracking.
- Fix: Divi workaround in the latest issue of the DIVI theme

###### Version 2.0.3
- Fix: Removed a debugging call that was inadvertently left in the production code.

###### Version 2.0.2
- Tweak: Give user option to remove the invoice number counter when the plugin is uninstalled
- Fix: Make sure that invoice number is not reset to zero upon deactivation/installation
- Fix: Compatibility styling issue with the powerpack add-on.

###### Version 2.0.1
- Fix: Saving time at the ticket level did not respect the rounding levels in settings
- Fix: Cleaned up text-domain for translations
- Fix: Verified nonces and security in ajax call-back functions
- Fix: Saving time at the ticket level could sometimes result in a blank time-tracking record
- Fix: Long lookup lists on the ADD TIME TRACKING ticket number lookup field had an issue where the agent could not click to select the ticket or ticket reply
- Fix: Conflict with editors - under certain circumstances the editors could not be used in visual mode.
- Tweak: The timer on the ADD TIME TRACKING page is now acting in a more logical manner, automatically updating the time field only when the time field is zero.
- Tweak: Added a close button to the popup used for saving time at the ticket level

###### Version 2.0.0
- New: Time logging is now allowed at just the ticket level instead of always requiring a ticket reply
- New: Multiple simultaneous timers are now supported - tickets / time entries in separate tabs can each have their own timers
- New: Timer values are now shown on the tab title. 
- New: You can now see if a timer is running even if a tab using a timer is not the active tab.
- New: Can now add multiple time entries for a single ticket reply.
- New: An automatic timer is now present on the manual ADD TIME screen.

###### Version 1.0.0 Beta 3 / Version 1.0.0
- Fix: Numerous bug fixes and tweaks

###### Version 1.0.0 Beta 2
- Fix: Numerous bug fixes and tweaks

###### Version 1.0.0 Beta 1
- New: Initial Release

-----------------------------------------------------------------------------------------