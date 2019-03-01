# README #

This project is an extension designed to run on the AWESOME SUPPORT Wordpress Plugin Platform.  

### How do I get set up? ###

Installation is straightforward using the usual WordPress Plugins -> Add New procedure.

- Download the file from your receipt or from your dashboard(Awesome-Support-Filepicker.zip).
- Within WordPress Dashboard, click `Plugins` -> `Add New`
- Click the `Upload` button and select the ZIP file you just downloaded.
- Click the `Install` button


### Change Log  ###
1.0.8 (0.1.8)
-----
New: Version of Awesome Support Core required is now at least 4.2.
New: Version of PHP required is now at least 5.6
Tweak: Use new tabs available in latest versions of Awesome Support in the reply area
Tweak: Change version number to use semver convention so instead of 0.1.8 this is now 1.0.8.
Fix: Force use of HTTPS when FORCE_SSL_ADMIN is set to true
Fix: Update license message to include the name of the add-on

0.1.6
-----
Tweak: Update translation catalog
Fix: Update textdomain to the correct string
Fix: Load translations
Fix: Correct a typo.

0.1.6
-----
Tweak: use new filestack API/URL
Tweak: update plugin name and description in header
Fix: Remove wrong parameters from some function calls
Fix: Incorrect link to license page
Fix: Fallback to read policy type
Fix: Use URL encode the only before generating the URL
Fix: Display attachments only during the AJAX process
