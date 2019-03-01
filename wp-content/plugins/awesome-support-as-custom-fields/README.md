[![Scrutinizer Code Quality](https://scrutinizer-ci.com/b/awesomesupport/as-custom-fields/badges/quality-score.png?b=master&s=b076f48cbb7df5c739c6fadd3901a26ec6badc17)](https://scrutinizer-ci.com/b/awesomesupport/as-custom-fields/?branch=master)

Allow the user to create custom fields that are added to the ticket entry front-end form.

## Getting Started

Installation is straightforward using the usual WordPress Plugins -> Add New procedure.

- Download [The AS Custom Fields addon](awesome-support-as-custom-fields.zip).
- Within WordPress Dashboard, click `Plugins` -> `Add New`
- Click the `Upload` button and select the ZIP file you just downloaded.
- Click the `Install` button

## Prerequisites

Version 3.3.2 of Awesome Support or later.

## Configuration

This addon can be configured from the `Tickets` -> `Settings` -> `Custom Fields` tab. There is no default configuration - the tab  will be empty until you add your first custom field.

## Usage

You can view usage documentation on the https://getawesomesupport.com/documentation-new/documentation-extensions-all/ website.

### Change Log

-----------------------------------------------------------------------------------------
###### Version 1.0.9
- New Feature: Updated for new 4.1 custom field attributes - show_frontend_list and show_frontend_detail

###### Version 1.0.8
- New Feature: Updated for new 4.0 fields (backend_only and read_only)
- Tweak: Minimum version of Awesome Support is now 4.0
- Tweak: Minimum version of php is now 5.6.

###### Version 1.0.7
- New Feature: Hide field and re-ordering option

###### Version 1.0.6
- Bugfix:  Remove <legend> wrapper for checkbox group labels. Use <label> instead for consistency with core AS' LESS/CSS.

###### Version 1.0.5
- Bugfix: 'wpas_plugin_admin_pages' should return values to avoid in_array() warning
- Bugfix: Remove the options visible on WYSIWYG

###### Version 1.0.4
- Bugfix: Restore missing attachment button in ticket reply page

###### Version 1.0.3
- Bugfix: WYSIWYG missing markup after ticket submission
- Bugfix: Include missing checkbox title on both front-end and edit ticket page
- Enhancement: Hide upload field type button on ticket edit page

###### Version 1.0.2
- Bugfix: PHP notices when activating through bulk action( in a sense that main plugin is missing )
- Bugfix: PHP notice on undefined variable field_type
- Enhancement: Avoid using wp_die() on activation to avoid dead page.

###### Version 1.0.1
- New: Add date field types as an option
- Tweak: translations
- Tweak: License notice

###### Version 1.0.0
- New: Initial Release

-----------------------------------------------------------------------------------------