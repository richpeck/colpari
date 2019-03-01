=== Ninja Forms - File Uploads Extension ===
Contributors: kstover, jameslaws
Donate link: http://ninjaforms.com
Tags: form, forms
Requires at least: 4.6
Tested up to: 4.9.5
Stable tag: 3.0.19

License: GPLv2 or later

== Description ==
The Ninja Forms File Uploads Extension allows users to upload files. These files are stored in a database that can be browsed or searched by an administrator. Files can be downloaded or deleted by administrators as well.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the `ninja-forms-uploads` directory to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Visit the 'Forms' menu item in your admin sidebar
4. When you create a form, you can now add upload fields on the field edit page.
5. A "File Uploads" link will now appear underneath the main "Forms" admin menu.

== Use ==

For help and video tutorials, please visit our website: [NinjaForms.com](http://ninjaforms.com)

== Changelog ==

= 3.0.19 (26 April 2018) =

*Changes:*

* Compatibility with form templates in Ninja Forms 3.2.23
* Use file extension whitelist to restrict the file upload select box

*Bugs:*

* upload_max_filesize php.ini config defined in units other than MB not working
* Dropbox connection removed if site cannot access Dropbox temporarily

= 3.0.18 (27 March 2018) =

*Changes:*

* Compatibility with Auto complete support in Ninja Forms 3.2.17

*Bugs:*

* Fatal error when Amazon Web Services plugin installed but not activated
* Timeout when adding new forms on certain installs

= 3.0.17 (18 November 2017) =

*Bugs:*

* Dropbox connection redirect 404ing on some installs
* Only the first 'Select Files' button text is used for multiple fields

*Changes:*

* Filter added for the cron time when deleting the temp file
* Filter added for the default 'Select Files' button text

= 3.0.16 (13 October 2017) =

*Bugs:*

* Fixed a bug that was preventing uploads from being re-saved when using the Save Progress add-on.

= 3.0.15 (1 Aug 2017) =

*Bugs:*

* Fix external URLs not sent to Zapier when using Zapier addon
* Fix field max upload size bigger than server limit

= 3.0.14 (11 Aug 2017) =

*Bugs:*

* Fix Dropbox file uploaded with full server path when having no custom upload path

= 3.0.13 (10 Aug 2017) =

*Features:*

* German translation file, thanks @christophrado!

*Changes:*

* Added original filename to `ninja_forms_uploads_[external service]_filename` filter
* Added more information to the help bubble for file renames to describe creating directories

*Bugs:*

* Fix isset weirdness when checking for old NF format of file value
* Fix method not exists when creating table if using older version of NF core.
* Fix custom upload path not replacing %field_ shortcodes
* Fix custom upload path not working for external (Dropbox/S3) file paths
* Fix %formtitle% not being replaced in field rename if no custom upload path

= 3.0.12 (28 July 2017) =

*Bugs:*

* Dropbox uploads failing when custom path defined

= 3.0.11 (25 July 2017) =

*Bugs:*

* File Uploads table not created on fresh installation
* jQuery File Upload JavaScript files clashed with Calendarize plugin

= 3.0.10 (12 July 2017) =

*Bugs:*

* File Uploads should work properly with non-standard WordPress databse prefixes.

= 3.0.9 (06 July 2017) =

*Bugs:*

* File Uploads should now work properly with Multi-Part Forms.
* Fixed a bug with non-English characters and file name encoding.

= 3.0.8 ( 26 June 2017) =

*Changes:*

* Supports Dropbox API v2
* Custom name of file now supports changing path with field specific data

*Bugs:*

* File appeared even if upload failed

= 3.0.7 ( 5 June 2017) =

*Bugs:*

* Amazon S3 uploads unable to use bucket in newer regions
* File not deleted from server when File Upload deleted in admin
* All File Uploads fields on form used the same nonce
* NF 2.9 submissions not displaying in admin
* File Upload field CSS too generic causing clashes with themes
* PHP notice on uploads table when submission from non-logged in user
* All Fields mergetag not using external URL
* Missing mergetag variations used for Post Creation
* Similar file extensions allowed even when not on file type whitelist for field

= 3.0.6 (12 April 2017) =

*Bugs:*

* Fixed the description text for custom file upload paths.
* Fixed PHP warnings related to uploading a file to a remote server.
* Links to uploaded files should now always show properly.
* Fixed a bug that could cause unexpected output when displaying a form.

= 3.0.5 (07 December 2016) =

*Bugs:*

* Fixed a bug that could cause file upload fields to fail with Ninja Forms versions > 3.0.17.

= 3.0.4 (3 November 2016) =

*Bugs:*

* Fixed a bug with the Max File Upload Size setting.
* Whitelisting file types should now work as explained in the help text.
* File names can now be based upon the values of other fields using merge tags.

*Changes:*

* Added missing help text to the admin.

= 3.0.3 (28 September 2016) =

*Bugs:*

* File Uploads should now show in Conditional Logic conditions.

= 3.0.2 (09 September 2016) =

* Update to 3.0.2

*Bugs:*

* Fixed SQL format that breaks dbdelta.
* Fixed Dropbox case sensitive issues.
* Fixed Multiple file selection bug.
* Fixed a bug with uploading .jpg files.

= 3.0.1 (06 September 2016) =

* Updated with Ninja Forms v3.x compatibility

= 3.0 (06 September 2016) =

* Updated with Ninja Forms v3.x compatibility
* Deprecated Ninja Forms v2.9.x compatible code

= 1.4.9 (09 May 2016) =

*Bugs:*

* Fixed a bug where duplicate file names could cause the extension to be changed. Credit to "fruh of citadelo" for reporting the security vulnerability.

= 1.4.8 (29 March 2016) =

*Bugs:*

* Fixed a bug with Dropbox that could cause uploading to Dropbox to fail.

= 1.4.7 (20 September 2015) =

*Bugs:*

* Fixed a bug related to buckets in Amazon S3.
* Improved how URLs are handled when saving submissions.

= 1.4.6 (24 August 2015) =

*Bugs:*

* Fixed an issue with connecting to Amazon accounts.
* Fixed several PHP notices that appeared on the uploads settings page.

= 1.4.5 (12 May 2015) =

*Bugs:*

* Featured images in the Post Creation extension should now function properly.
* Save Progress extension tables should now show File Upload fields properly.

= 1.4.4 (26 March 2015) =

*Bugs:*

* Multiple file uploads should work properly with external services.
* Fixed several PHP notices.

= 1.4.3 (12 January 2015) =

*Bugs:*

* Fixed a bug that could cause Dropbox to disconnect.
* Fixed a bug with multi-file uploads that could cause the wrong URL to be stored in the file uploads table.
* Fixed a PHP notice.

= 1.4.2 (9 December 2014) =

*Bugs:*

* Fixed a bug with PHP v5.6 and Dropbox uploads.
* Fixed a bug that caused file renaming to work incorrectly.

*Changes:*

* Added a new upload location of none, where files get removed after upload.

= 1.4.1 (17 November 2014) =

*Bugs*

* Fixed a bug caused by a bad commit in the previous version.

= 1.4 (17 November 2014) =

*Bugs:*

* Fixed two PHP notices.

*Changes:*

* Added filter for filename $file_name = apply_filters( 'nf_fu_filename' , $filename );
* The maximum file upload size can now not exceed the server PHP setting for max file uploads.

= 1.3.8 (15 September 2014 ) =

*Changes:*

* File Uploads should now be compatible with Ninja Forms version 2.8 and the new notification system.
* Performance should be noticeably increased.

= 1.3.7 (12 August 2014 ) =

*Bugs:*

* Fixed a bug with viewing files in the edit sub page.

= 1.3.6 (12 August 2014) =

*Bugs:*

* Fixing a bug with file exports and version 2.7+ of Ninja Forms.

* Fixed translation issues.

*Changes:*

* Added new .pot file.

= 1.3.5 (24 July 2014) =

*Changes:*

* Compatibility with Ninja Forms 2.7.

= 1.3.4 =

*Bugs:*

* Making sure the external upload doesn't fire if there is no file uploaded

= 1.3.3 =

*Bugs:*

* Fixed a bug with Dropbox that could cause file uploads to be sluggish.
* is_dir() and mkdir() warnings should be cleared up.
* Multi-file upload fields should now clear correctly when a form is submitted.

= 1.3.2 =

*Bugs:*

* Fixed a bug that could cause the plugin not to activate on some systems.

= 1.3.1 =

*Bugs:*

* The extension should now properly activate on all PHP versions.

= 1.3 =

*Features:*

* You can now store uploaded files in Dropbox or Amazon S3! Simply select the storage location on a per-upload-field basis.

*Bugs:*

* Fixed a PHP notice.
* Fixed a bug that could cause some installations to lose the ninja-forms/tmp/ directory.

= 1.2 =

*Bugs:*

* Fixed a bug that prevented required file uploads from being validated when using AJAX submissions.
* Fixed some php notices.

*Changes:*

* Added support for the new Ninja Forms loading class.
* Editing a submission from the wp-admin that includes a file will now show a link to that file instead of just the filename.

= 1.1 =

*Changes:*

* The format of date searching in the Browse Files tab will now be based upon the date format in Plugin Settings. i.e. you can now search dd/mm/yyyy.
* Added the option to name a directory/file with %userid%.

*Bugs:*

* Fixed a bug that caused file upload fields to load multiple instances or open with pre-filled, incorrect data.

= 1.0.11 =

*Changes:*

* Added a filter so that when a user uploads a file, they don't see the directory to which it was uploaded in their email.

= 1.0.10 =

*Changes:*

* Changed the license and auto-update system to the one available in Ninja Forms 2.2.47.

= 1.0.9 =

*Bugs:*

* Fixed a bug that could cause files to be added to the media library twice when used with the Post Creation extension.

= 1.0.8 =

*Changes:*

* Changed references to wpninjas.com to the new ninjaforms.com.

= 1.0.7 =

*Bugs:*

* Fixed a bug that prevented files from being emailed as attachments in multi-part forms.

= 1.0.6 =

*Changes:*

* Updates for compatibility with WordPress 3.6

= 1.0.5 =

*Bugs:*

* Fixed a bug that prevented Uploads from working properly with AJAX forms.
* Fixed a bug that prevented Uploads from working properly when they were set to required.

= 1.0.4 =

*Changes:*

* Added a filter so that File Uploads will work properly with the confirmation page option of Multi-Part Forms.

= 1.0.3 =

*Changes:*

* Changed the way that file uploads with duplicate names are handled. In previous versions, the new file would simply replace the older file with the same name; now, if a file already exists with the same name as an upload, the upload is renamed with a sequential number. e.g. my-file.jpg -> my-file-001.jpg -> my-file-002.jpg -> etc.

* Added an option to add files to the WordPress Media Library. On each file upload field, you'll find this new option.

* Added three new file renaming options: %displayname%, %firstname%, %lastname%. Each of these will be replaced with the appropriate user information.

* Added a new filter named: ninja_forms_uploads_dir. This filter can be used to modify the location Ninja Forms uploads files.

*Bugs:*

* Fixed a bug that could cause some files from uploading properly.

= 1.0.2 =

*Changes:*

* Added a new option to the [ninja_forms_field id=] shortcode. You can now use [ninja_forms_field id=2 method=url]. This will return just the url of the file. For example, you can now do something like this: <img src="[ninja_forms_field id=2 method=url]">.

= 1.0.1 =

*Changes:*

* Modified the way that the pre-processing is handled for more effeciency.

= 1.0 =

*Bugs:*

* Fixed a bug that prevented files from being replaced on the backend.

= 0.9 =

*Bugs:*

* Fixed a bug that prevented files from being replaced when editing user submissions.

= 0.8 =

*Features:*

* Added the ability to search for file uploads by User ID, User email, or User login.

= 0.7 =

* Updated code formatting.

= 0.6 =

* Fixed a bug that was causing the new [ninja_forms_field id=3] shortcode to fail when used in conjunction with the Uploads Extension.

= 0.5 =

* Changed the upload directory to the built-in WordPress uploads directory. This should help limit the cases of users not being able to upload files because of directory restrictions. Old files have not been moved over because it would be impossible to correctly fix links to new locations.
* Fixed a bug that was causing some users to lose their upload record when they deactivated and reactivated the plugin.
* Errors should now show properly for files that are over the file size limit set in the plugin settings.

= 0.4 =

* Various bug fixes including:
* A bug that prevented files from being moved to the proper directory.
* A bug that prevented the "update file" link from working on pages that already had a file uploaded.
* A bug that prevented the "featured image" functionality from working properly.

* Added a new setting to the upload settings page for file upload URLs.

= 0.3 =

* Various bug fixes.

= 0.2 =

* Various bug fixes.
* Changed the way that javascript and css files are loaded in extensions.