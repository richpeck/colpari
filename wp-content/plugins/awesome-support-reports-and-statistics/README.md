[![Scrutinizer Code Quality](https://scrutinizer-ci.com/b/awesomesupport/reports-and-statistics/badges/quality-score.png?b=master&s=f30231757ea1e6bcc7ba8d9d07646834f73eddc2)](https://scrutinizer-ci.com/b/awesomesupport/reports-and-statistics/?branch=master)

# README #

This project is an extension designed to run on the AWESOME SUPPORT Wordpress Plugin Platform.  

### How do I get set up? ###

Installation is straightforward using the usual WordPress Plugins -> Add New procedure.

- Download the file from your receipt or from your dashboard(awesome-support-reports-and-statistics.zip).
- Within WordPress Dashboard, click `Plugins` -> `Add New`
- Click the `Upload` button and select the ZIP file you just downloaded.
- Click the `Install` button


### Change Log  ###
1.2.0
-----
* New: Updated CANVAS.js to version 2.0
* Tweak: Replaced get_the_author_meta function calls with get_user_option function calls for better multi-site compatibility.

1.1.5
-----
* Fix: Multiple fixes related to issues when running on WP Multisite.

1.1.4
-----
* Fix: Possible security issue with the use of an unsanitized var from $_SERVER
* Fix: Possible security issue with the use of a double quote instead of a single quote
* Fix: Workaround for a known issue with filtering INPUT_SERVER
* Fix: Retrieve ALL reports for a user instead of just 10
* Tweak: Some user facing messages were updated to be more informative

1.1.3
-----
* Add: CLIENT as a 2nd dimension option.
* Add: Cancel button to the SAVE REPORT screen
* Add: Option to drop zero rows and zero columns from the reports
* Tweak: Axis labels on certain reports to be clear (Number of Tickets instead of Tickets for example)
* Tweak: Relabeled the APPLY FILTERS button to RUN REPORT
* Tweak: Added a bit of margin around the CLEAR link under the client filter to make it look a bit better
* Tweak: Show the user friendly custom field name in the 2nd dimension boxes.
* Tweak: When viewing a saved report, place the name of the report on the screen.

1.1.2
------
* New: Implemented basic client filter for all other reports
* Tweak: Additional code cleanup

1.1.1
-----
* New: Implemented basic client filter for Ticket Count and Productivity Analysis Reports
* Fix: New installs will have default custom fields properly activated for reports-and-statistics

1.1.0
-----
* Numerous bug fixes
* Screen clean up
* Code clean up
* Revamped landing page
* Usability issues
* Help screens

1.0.0
-----
Initial Version