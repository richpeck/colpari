# README #

This project is an extension designed to run on the AWESOME SUPPORT Wordpress Plugin Platform.  

### How do I get set up? ###

Installation is straightforward using the usual WordPress Plugins -> Add New procedure.

- Download the file from your receipt or from your dashboard(Awesome-Notifications.zip).
- Within WordPress Dashboard, click `Plugins` -> `Add New`
- Click the `Upload` button and select the ZIP file you just downloaded.
- Click the `Install` button


### Change Log  ###

1.4.0
-----
!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
IMPORTANT: New version of Awesome Support core is required to upgrade to this version! You need Awesome Support 4.0.5 or later!
!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

New: Notifications for 3rd parties related to functions in the POWERPACK PRODUCTIVITY add-on
Tweak: Minor grammar updates
Tweak: Translation Catalog
Tweak: Added major version number since this add-on has been out for a while. Instead of 0.4.0 its now 1.4.0.

0.3.0
-----
Tweak: License warning message to clearly identify that the message is from this add-on
Tweak: Clean up code
Fix:   A fix to ensure that the {message} tag is processed properly in outgoing emails
Tweak: Rearchitect some areas to ensure that custom fields data is saved prior to sending notifications.  
       This allows notifications to access and use custom fields data.
Tweak: Rearchitect some areas to ensure that file attachments are saved prior to sending notifications.  
       This allows notifications to access and use file attachments.
New: Min version of php is now 5.6	 
New: Min version of AS is now 4.0.0.
New: Added option to allow certain notifications related to the EMAIL SUPPORT add-on to be run via AJAX for testing purposes.

0.2.0
-----
Add: New event to send notifications for status changes
Add: New target: send email notifications.  
This means that third parties not directly related to the ticket can now be notified of important ticket events.
It also means that conditions such as tickets being escalated can result in notifications to supervisors and 
other interested 3rd parties.

0.1.8
-----
Fix: Duplicate notifications in slack

0.1.7
-----
Fix: Force use of HTTPS when FORCE_SSL_ADMIN is set to true