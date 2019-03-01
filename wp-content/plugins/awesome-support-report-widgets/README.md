[![Scrutinizer Code Quality](https://scrutinizer-ci.com/b/awesomesupport/report-widgets/badges/quality-score.png?b=master&s=80a94318f5e9f73672a950140932a2744727d3b6)](https://scrutinizer-ci.com/b/awesomesupport/report-widgets/?branch=master)

Display a series of reporting widgets on the WordPress Admin Dashboard.

## Getting Started

Installation is straightforward using the usual WordPress Plugins -> Add New procedure.

- Download [The AS Custom Fields addon](awesome-support-report-widgets.zip).
- Within WordPress Dashboard, click `Plugins` -> `Add New`
- Click the `Upload` button and select the ZIP file you just downloaded.
- Click the `Install` button

## Prerequisites

Version 4.0.0 of Awesome Support or later.

## Configuration

Please go to the TICKETS->SETTINGS->REPORT WIDGETS tab.  From there you can select which reports to show on the screen, chart types, colors and a host of other options.
To tempoarily add/remove a widget from the admin screen simply use the standard WordPress SCREEN OPTIONS drop-down at the top of the Admin dashboard.
To permanently report it uncheck the appropriate option in the TICKETS->SETTINGS->REPORT WIDGETS tab.

## Usage

You can view usage documentation on the https://getawesomesupport.com/documentation/admin-report-widgets/installation-admin-report-widgets/ website.

### Change Log
-----------------------------------------------------------------------------------------
###### Version 2.0.4
- Fix: Many widgets weren't being restricted by agents when a standard agent was logged in.

###### Version 2.0.3
- Fix: The count for open tickets was wrong because it was using the last modified time of the ticket instead of the date the ticket post was created/published.

###### Version 2.0.2
- Tweak: Changed some widget titles
- Fix: Version number.

###### Version 2.0.1
- ENH: Added chart types and color options for the OPEN TICKETS aging report (forgot to add it in version 2.0.0!)
- Fix: Open tickets chart was not passing a chart title.
- Fix: Default data point width was not being read properly.

###### Version 2.0.0
- ENH: Added reports by priority
- ENH: Added reports by channel
- ENH: Added reports by department
- ENH: Added settings tab - control which widgets are enabled (and therefore consume CPU resources when viewing the dashboard).
- ENH: Added options to allow admin to set line colors for reports
- ENH: Added option to change the default chart type - now includes 9 chart types
- ENH: Added option to configure the line width 
- ENH: Added option to configure the data element width
- ENH: Added option to select chart type for PRODUCT, PRIORITY, DEPARMENT and AGENT reports which overrides the default chart type
- ENH: Tooltips
- ENH: Chart by status for open tickets
- ENH: Added new chart reports - summary charts for PRODUCT, PRIORITY, DEPARTMENT, AGENT, STATUS. Each report has about a dozen options to control the look of the widget.
- ENH: Added default options for all charts that don't have specific options
- Tweak: Split the configuration tab into 3 new tabs in order to make all the options manageable.
- Tweak: user with administer_awesome_support capability now allowed to view all widgets
- Tweak: Do not show statuses with zero open ticket count in the OPEN TICKETS BY STATUS text widget.  Zero-counts just takes up precious real-estate.
- BugFix: Return the product name even if product syncing is turned on.  Apparently get_term doesn't do that so have to use get_term_by_id.

###### Version 1.0.1
- BugFix: Closed ticket query
- BugFix: Inefficient timestamp comparison function

###### Version 1.0.0
- New: Initial Release

-----------------------------------------------------------------------------------------