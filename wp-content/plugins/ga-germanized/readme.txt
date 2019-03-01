=== Google Analytics Germanized (GDPR / DSGVO) ===
Contributors: pascalbajorat, sascharudolph
Donate link: https://www.pascal-bajorat.com/spenden/
Tags: google, analytics, gaoptout, german, anonymize_ip, gdpr, dsgvo, gtag, universal, germanized, tracking, privacy, eu, law, settings, outbound
Requires at least: 4.7
Tested up to: 4.9.7
Stable tag: 1.4.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Google Analytics preconfigured to respect EU law and with lots of advanced analytics settings for extensive tracking possibilities.

== Description ==

> Google Analytics preconfigured to respect EU law (GDPR / DSGVO) and with lots of advanced analytics settings for extensive tracking possibilities.

You can use this plugin to integrate Google Analytics conform to EU law in compliance with data protection. For this you only need to put your Google Analytics ID into the general settings. Other settings (e.g. AnonymizeIP) are preconfigured accordingly and need to be changed only when needed or when extensions are desired.

For a privacy-compliant integration, it is necessary that you clarify the use of Google Analytics in your privacy policy. Additionally, a possibility for an opt-out of Google Analytics must be created. For the opt-out you can use the shortcode described in the tab general, point 3.

In the advanced settings features of Google Analytics can be activated. Corresponding information can be found in the settings.

#### Features of Google Analytics Germanized
- Easy Google Analytics integration only the UA ID is required, everything else is preconfigured
- Preconfigured to respect EU law
- Cookie Consent Integration for EU cookie law
- Compatible to Google Site Tag and Universal Analytics Code (it's your choice)
- Google Analytics Opt-out link Shortcode for your privacy policy
- Google Analytics integration could be disabled and you can use the Opt-out as standalone feature (compatible to other Google Analytics plugins)
- Compatible to eRecht24 generated Opt-out links
- Anonymize IP is integrated and enabled by default
- Demographics and Interests Reports
- Outbound Link Tracking
- Enhanced Link Attribution
- Custom Code integration
- "Do Not Track" header support
- WPML support

If you have any questions or problems, contact me: [Pascal Bajorat - Webdesigner and WordPress Developer from Berlin](https://www.pascal-bajorat.com/ "Pascal Bajorat - Webdesigner and WordPress Developer from Berlin")

> The plugin was developed to the best of our knowledge and belief. However, there will be no guarantee for the legal certainty of the implementation.

== Installation ==

1.	Upload the complete directory to /wp-content/plugins/
2.	Activate the plugin using "Plugins > Installed Plugins" in your WordPress Backend
3.	Go to "Settings" and "Google Analytics" to configure the plugin


== Screenshots ==

1.	Plugin Main Window
2.	Advanced Settings
3.	Cookie Consent Settings
4.	Other Tracking Codes

== Changelog ==

= 1.4.0 =
* General optimization and improved debugging
* improved implementation of cookie notice
* Fixed a bug with the link tracking function

= 1.3.1 =
* General optimization and improved debugging
* improved implementation of the new filters

= 1.3.0 =
* General optimization
* Fixed line-break bug in Cookie Consent text
* Optimized Opt-in / Opt-out - force a reload after Opt-in / Opt-out to fire or remove the tracker immediately
* New: Added filters and hooks for other developers (please check the code, it's currently not documented)
* New: Added wpml-config.xml for a better WPML support
* New: Option to respect "Do Not Track" header

= 1.2.1 =
* Optimized Link-Tracking function [thank you @webwart](https://wordpress.org/support/topic/ausgehendes-link-tracking-verhindert-target_blank/ "thank you @webwart")
* General optimization

= 1.2.0 =
* General optimizations
* Optimized Interface
* New: Custom Tracker e.g. for Facebook pixel
* New: Cookie Consent Opt-in and Opt-out
* New: GDPR Special / DSGVO Special (german only)

= 1.1.0 =
* WP Version check for WordPress 4.9.5
* Fixed a bug with the link tracking function
* New: Cookie Consent Integration for EU cookie law (IMPORTANT: It is enabled by default, please check your settings after updating to this version)

= 1.0.2 =
* Text changes

= 1.0.1 =
* Text changes

= 1.0.0 =
* Initial release.

== License ==

GNU General Public License v.3 - http://www.gnu.org/licenses/gpl-3.0.html