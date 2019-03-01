# README #

# Awesome Support: Easy Digital Downloads

This project is an extenstion designed to run on the AWESOME SUPPORT Wordpress Plugin Platform.  It Integrates Awesome Support with EDD and EDD Software Licensing

### How do I get set up? ###

Installation is straightforward using the usual WordPress Plugins -> Add New procedure.

- Download the file from your receipt or from your dashboard(awesome-support-easy-digital-downloads.zip).
- Within WordPress Dashboard, click `Plugins` -> `Add New`
- Click the `Upload` button and select the ZIP file you just downloaded.
- Click the `Install` button


### Change Log  ###
1.0.7 
-----
Enh: Added customer lifetime value to ticket EDD metabox
Enh: Added a link to the EDD customer profile in the ticket EDD metabox
Enh: When software licensing is in use, added the license status next to the license key in the license key dropdown.
Enh: Selecting a product on the ticket page will automatically restrict the ORDER NUMBER and LICENSE drop-down to values that contain that product.
Enh: Selecting an order number on the ticket page will automatically restrict the the LICENSE drop-down to values that apply to that order
Enh: Selecting a license (when EDD Licensing is enabled) will automatically populate the ORDER NUMBER and PRODUCT fields
Enh: Added options to treat inactive and expired licenses as being valid for support
Enh: Added option for admin to change titles and description of edd order number and description fields.
Fix: Handle unknown license validation error
Fix: Change license verification logic to handle single product case
Fix: Refund policy was not being displayed correctly - it always showed as expired.
Dev: Added new debug option in TICKETS->SETTINGS to show/not show the term ids and post ids used in the license dropdown field.


1.0.6 - February 1st, 2017
----
Updates: Clean up translation catalog, textdomains and add to POEDITOR.com
Tweak: Update texdomain for more consistent translations
Tweak: Update translation catalog
Tweak: Move translation catalog to poeditor.com
Fix: Force use of HTTPS when FORCE_SSL_ADMIN is set to true

1.0.5
-----
Not used - no public release

1.0.4
-----
Fix: Compatibility issue with EDD SL.

1.0.3 - May 27, 2016
-----
Update deprecated method for admin notice dismissal
Fix issue with "Don't use other users orders" error
Fix issue with EDD FES where vendors couldn't access the dashboard anymore
Fix wrong link to license page

1.0.2 - January 26, 2016
-----
Fix wrong item price in the EDD metabox in admin view and add order total amount

1.0.1 - October 22, 2015
-----
Couple of stability improvements

1.0.0
-----
First release