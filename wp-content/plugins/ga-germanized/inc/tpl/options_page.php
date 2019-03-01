<div class="wrap">
	<div class="google-analytics-germanized">
		<h1><span class="dashicons dashicons-chart-area"></span> <?php esc_html_e('Google Analytics Germanized', 'ga-germanized') ?></h1>

        <hr class="wp-header-end" />


		<form action="<?php echo esc_url( rest_url( gag_settings_handler::$namespace . '/' . gag_settings_handler::$version . '/save-settings') ) ?>" class="google-analytics-germanized-form" id="google-analytics-germanized-form" novalidate>

			<nav class="gag-settings-buttons">
				<ul>
					<li><a href="#tab1" class="active"><?php esc_html_e('General', 'ga-germanized') ?></a></li>
					<li><a href="#tab2"><?php esc_html_e('Tracking Settings', 'ga-germanized') ?></a></li>
                    <li><a href="#tab3"><?php esc_html_e('Cookie Notice', 'ga-germanized') ?></a></li>
                    <li><a href="#tab4"><?php esc_html_e('Tracking Codes', 'ga-germanized') ?></a></li>
					<li><a href="#tab5"><?php esc_html_e('How it works', 'ga-germanized') ?></a></li>
                    <?php if( get_locale() == 'de_DE_formal' || get_locale() == 'de_DE' ): ?>
					<li><a href="#tab6"><?php esc_html_e('GDPR Special', 'ga-germanized') ?><span class="new"><?php esc_html_e('NEW', 'ga-germanized') ?></span></a></li>
                    <?php endif; ?>
				</ul>
			</nav>

            <div class="gag-settings-wrapper-outer">

                <div class="logotype">
                    <img src="<?php echo plugins_url(dirname(PBGAG_BASE)) ?>/assets/img/google-analytics-germanized.png" title="<?php esc_attr_e('Google Analytics Germanized', 'ga-germanized') ?>" alt="<?php esc_attr_e('Google Analytics Germanized', 'ga-germanized') ?>" />
                </div>

                <div class="gag-settings-wrapper tab-item" id="tab1">

                    <div class="gag-analytics-id gag-settings-item onegagbox">
                        <label for="analytics-id"><?php esc_html_e('Google Analytics ID', 'ga-germanized') ?></label>
                        <input type="text" name="analytics-id" id="analytics-id" placeholder="<?php esc_attr_e('Google Analytics ID: UA-XXXXXXXX-XX', 'ga-germanized') ?>" value="<?php echo esc_attr( $settings['analytics-id'] ) ?>" required />
                    </div>

                    <div class="gag-analytics-mode gag-settings-item twogagbox">
                        <label for="analytics-mode"><?php esc_html_e('Google Analytics Mode', 'ga-germanized') ?></label>

                        <select name="analytics-mode" id="analytics-mode">
                            <option value="gst" <?php selected($settings['analytics-mode'], 'gst') ?>><?php esc_html_e('Global site tag (gtag.js)', 'ga-germanized') ?> - <?php esc_html_e('recommended', 'ga-germanized') ?></option>
                            <option value="ua" <?php selected($settings['analytics-mode'], 'ua') ?>><?php esc_html_e('Universal Analytics (analytics.js)', 'ga-germanized') ?></option>
                        </select>
                    </div>

                    <div class="gag-analytics-optout gag-settings-item thirdgagbox">
                        <label for="analytics-mode"><?php esc_html_e('Google Analytics Opt-out', 'ga-germanized') ?></label>
	                    <p><?php echo sprintf(
	                                __('Use the following shortcode to integrate a Google Analytics Opt-out link, for example in <a href="%s" target="_blank">your privacy policy</a>.', 'ga-germanized'),
                                    esc_url( admin_url('privacy.php') )
                                );
                            ?></p>

                        <code>[ga-optout text="<?php esc_html_e('Disable Google Analytics', 'ga-germanized') ?>"]</code>
                    </div>

                    <div class="gag-analytics-sendmebutton">
                        <button type="submit" class="gag-sendme-up"><?php esc_html_e('Save Changes', 'ga-germanized') ?></button>
                    </div>
                </div><!--end of general -->


                <div class="gag-settings-wrapper tab-item" id="tab2" style="display: none;">


                    <div class="gag-settings-item clear" id="disable-analytics-wrapper">

                        <div class="oneline_field checkboxarea clear">
                            <div class="leftbox">
                                <strong><?php esc_html_e('Disable Analytics', 'ga-germanized') ?></strong>
                            </div>

                            <div class="rightbox">
                                <input type="checkbox" name="disable-analytics-integration" id="disable-analytics-integration" value="1" <?php checked(1, $settings['disable-analytics-integration']) ?> />

                                <label for="disable-analytics-integration" class="checkboxlabel"><?php esc_html_e('Disable Google Analytics integration', 'ga-germanized') ?></label>

                                <p class="mini-description"><?php esc_html_e('If you choose this option the Google Analytics integration will be disabled as well as all following features including "anonymize_ip". However, you can still use the Opt-out Shortcode. This is useful if you already have integrated another plugin and only need the Opt-out feature.', 'ga-germanized') ?></p>
                            </div>
                        </div>

                    </div>

                    <div class="gag-settings-item clear">

                        <div class="oneline_field checkboxarea clear">
                            <div class="leftbox">
                                <strong><?php esc_html_e('Anonymize IP', 'ga-germanized') ?></strong>
                            </div>

                            <div class="rightbox">
                                <input type="checkbox" name="anonymize_ip" id="anonymize_ip" value="1" <?php checked(1, $settings['anonymize_ip']) ?> />

                                <label for="anonymize_ip" class="checkboxlabel"><?php esc_html_e('Activate Anonymize IP', 'ga-germanized') ?></label>

                                <p class="mini-description"><?php esc_html_e('This parameter is required by European Union laws, we recommend to leave it activated.', 'ga-germanized') ?></p>
                            </div>
                        </div>

                    </div>

                    <div class="gag-settings-item clear">

                        <div class="oneline_field checkboxarea clear">
                            <div class="leftbox">
                                <strong><?php esc_html_e('Demographics', 'ga-germanized') ?></strong>
                            </div>

                            <div class="rightbox">
                                <input type="checkbox" name="displayfeatures" id="displayfeatures" value="1" <?php checked(1, $settings['displayfeatures']) ?> />

                                <label for="displayfeatures" class="checkboxlabel"><?php esc_html_e('Enable Demographics and Interests Reports', 'ga-germanized') ?></label>

                                <p class="mini-description"><?php printf( __('This setting will add the Demographics and Remarketing features to your Google Analytics tracking code. Make sure to enable Demographics and Remarketing in your Google Analaytics account. For further information about Remarketing, we refer <a href="%s" target="_blank">Google\'s documentation</a>.', 'ga-germanized'), 'https://support.google.com/analytics/answer/2444872?hl='.get_locale() ) ?></p>
                            </div>
                        </div>

                    </div>

                    <div class="gag-settings-item clear">

                        <div class="oneline_field checkboxarea clear">
                            <div class="leftbox">
                                <strong><?php esc_html_e('Link Tracking', 'ga-germanized') ?></strong>
                            </div>

                            <div class="rightbox">
                                <input type="checkbox" name="link-tracking" id="link-tracking" value="1" <?php checked(1, $settings['link-tracking']) ?> />

                                <label for="link-tracking" class="checkboxlabel"><?php esc_html_e('Enable Outbound Link Tracking', 'ga-germanized') ?></label>

                                <p class="mini-description"><?php esc_html_e('Track outbound link clicks as Analytics event.', 'ga-germanized') ?> <span class="warning-text"><?php esc_html_e('This feature can cause problems with some links. Please check your site and disable this in case of problems.', 'ga-germanized') ?></span></p>

                            </div>
                        </div>

                    </div>

                    <div class="gag-settings-item clear">

                        <div class="oneline_field checkboxarea clear">
                            <div class="leftbox">
                                <strong><?php esc_html_e('Enhanced Link Attribution', 'ga-germanized') ?></strong>
                            </div>

                            <div class="rightbox">
                                <input type="checkbox" name="linkid" id="linkid" value="1" <?php checked(1, $settings['linkid']) ?> />

                                <label for="linkid" class="checkboxlabel"><?php esc_html_e('Enable Enhanced Link Attribution', 'ga-germanized') ?></label>

                                <p class="mini-description"><?php esc_html_e('Enhanced Link Attribution improves the accuracy of your In-Page Analytics report. It automatically differentiates between multiple links of the same URL on a single page by using link element IDs.', 'ga-germanized') ?></p>
                            </div>
                        </div>

                    </div>

                    <div class="gag-settings-item subheadline clear">

                        <span><?php esc_html_e('Advanced Compatibility', 'ga-germanized') ?></span>

                        <div class="oneline_field clear">
                            <div class="leftbox">
                                <label for="domain"><?php esc_html_e('Domain to track', 'ga-germanized') ?></label>
                            </div>

                            <div class="rightbox">
                                <input type="text" name="domain" id="domain" placeholder="<?php echo esc_attr( parse_url(home_url(), PHP_URL_HOST) ) ?>" value="<?php echo esc_attr( $settings['domain'] ) ?>" />


                                <p class="mini-description"><?php esc_html_e('Leave blank to use the "auto" mode (recommended).', 'ga-germanized') ?></p>
                            </div>
                        </div>

                        <div class="oneline_field clear">
                            <div class="leftbox">
                                <label for="custom-code"><?php esc_html_e('Custom Code', 'ga-germanized') ?></label>
                            </div>

                            <div class="rightbox">
                                <textarea name="custom-code" id="custom-code"><?php echo esc_html( $settings['custom-code'] ) ?></textarea>

                                <p class="mini-description"><?php esc_html_e('Put Custom Code inside the Google Analytics Tracker function.', 'ga-germanized') ?></p>
                            </div>
                        </div>

                    </div>

                    <div class="gag-settings-item subheadline clear">

                        <span><?php esc_html_e('Do Not Track', 'ga-germanized') ?></span>

                        <div class="oneline_field checkboxarea clear">
                            <div class="leftbox">
                                <strong><?php esc_html_e('Do Not Track', 'ga-germanized') ?></strong>
                            </div>

                            <div class="rightbox">
                                <input type="checkbox" name="ga-dnt" id="ga-dnt" value="1" <?php checked(1, $settings['ga-dnt']) ?> />

                                <label for="ga-dnt" class="checkboxlabel"><?php esc_html_e('Respect "Do Not Track" Header', 'ga-germanized') ?></label>

                                <p class="mini-description"><?php _e('Disable Tracking Codes completely if the users browser send a <a href="https://en.wikipedia.org/wiki/Do_Not_Track" target="_blank">"Do Not Track" header</a>.', 'ga-germanized') ?> <span class="warning-text"><?php esc_html_e('Caching Plugins could disrupt this behaviour!', 'ga-germanized') ?></span></p>
                            </div>
                        </div>

                    </div>

                    <div class="gag-analytics-sendmebutton">
                        <button type="submit" class="gag-sendme-up"><?php esc_html_e('Save Changes', 'ga-germanized') ?></button>
                    </div>

                </div><!--end of advanced settings -->


                <div class="gag-settings-wrapper tab-item" id="tab3" style="display: none;">


                    <div class="gag-settings-item clear" id="disable-cookie-notice-wrapper">

                        <div class="oneline_field checkboxarea clear">
                            <div class="leftbox">
                                <strong><?php esc_html_e('Disable Cookie Notice', 'ga-germanized') ?></strong>
                            </div>

                            <div class="rightbox">
                                <input type="checkbox" name="disable-cookie-notice" id="disable-cookie-notice" value="1" <?php checked(1, $settings['disable-cookie-notice']) ?> />

                                <label for="disable-cookie-notice" class="checkboxlabel"><?php esc_html_e('Disable Cookie Notice', 'ga-germanized') ?></label>
                                <p class="mini-description"><?php esc_html_e('This will disable the Cookie Notice / Cookie Consent Integration.', 'ga-germanized') ?></p>
                            </div>
                        </div>

                    </div>

                    <div class="gag-settings-item clear">

                        <div class="oneline_field clear">
                            <div class="leftbox">
                                <strong><?php esc_html_e('Compliance Type', 'ga-germanized') ?></strong>
                            </div>

                            <div class="rightbox">
                                <select name="compliance-type" id="compliance-type">
                                    <option value="note" <?php selected($settings['compliance-type'], 'note') ?>><?php esc_html_e('Notification (just tell users that we use cookies)', 'ga-germanized') ?></option>
                                    <option value="opt-in" <?php selected($settings['compliance-type'], 'opt-in') ?>><?php esc_html_e('Opt-in', 'ga-germanized') ?></option>
                                    <option value="opt-out" <?php selected($settings['compliance-type'], 'opt-out') ?>><?php esc_html_e('Opt-out', 'ga-germanized') ?></option>
                                </select>
                                <p class="mini-description"><?php _e('<strong>Opt-in:</strong> Google Analytics tracking code will not fired <strong>before</strong> the user has <strong>confirmed</strong> your Cookie Consent banner.', 'ga-germanized') ?></p>
                                <p class="mini-description"><?php _e('<strong>Opt-out:</strong> Google Analytics tracking code will not fired <strong>after</strong> the user has <strong>declined</strong> your Cookie Consent banner.', 'ga-germanized') ?></p>

                            </div>
                        </div>

                    </div>

                    <div class="gag-settings-item clear">

                        <div class="oneline_field clear">
                            <div class="leftbox">
                                <strong><?php esc_html_e('Position', 'ga-germanized') ?></strong>
                            </div>

                            <div class="rightbox">
                                <select name="cc-position" id="cc-position">
                                    <option value="bottom" <?php selected($settings['cc-position'], 'bottom') ?>><?php esc_html_e('Banner bottom', 'ga-germanized') ?></option>
                                    <option value="top" <?php selected($settings['cc-position'], 'top') ?>><?php esc_html_e('Banner top', 'ga-germanized') ?></option>
                                    <option value="top-pushed" <?php selected($settings['cc-position'], 'top-pushed') ?>><?php esc_html_e('Banner top (pushdown)', 'ga-germanized') ?></option>
                                    <option value="bottom-left" <?php selected($settings['cc-position'], 'bottom-left') ?>><?php esc_html_e('Floating left', 'ga-germanized') ?></option>
                                    <option value="bottom-right" <?php selected($settings['cc-position'], 'bottom-right') ?>><?php esc_html_e('Floating right', 'ga-germanized') ?></option>
                                </select>
                            </div>
                        </div>

                    </div>

                    <div class="gag-settings-item clear">

                        <div class="oneline_field clear">
                            <div class="leftbox">
                                <strong><?php esc_html_e('Layout', 'ga-germanized') ?></strong>
                            </div>

                            <div class="rightbox">
                                <select name="cc-layout" id="cc-layout">
                                    <option value="block" <?php selected($settings['cc-layout'], 'block') ?>><?php esc_html_e('Block', 'ga-germanized') ?></option>
                                    <option value="edgeless" <?php selected($settings['cc-layout'], 'edgeless') ?>><?php esc_html_e('Edgeless', 'ga-germanized') ?></option>
                                    <option value="classic" <?php selected($settings['cc-layout'], 'block') ?>><?php esc_html_e('Classic', 'ga-germanized') ?></option>
                                </select>
                            </div>
                        </div>

                    </div>

                    <div class="gag-settings-item clear">

                        <div class="oneline_field clear">
                            <div class="leftbox">
                                <label for="cc-banner-text"><?php esc_html_e('Message', 'ga-germanized') ?></label>
                            </div>

                            <div class="rightbox">
                                <textarea name="cc-banner-text" id="cc-banner-text" placeholder="<?php esc_attr_e('This website uses cookies to ensure you get the best experience on our website.', 'ga-germanized') ?>"><?php echo esc_html( $settings['cc-banner-text'] ) ?></textarea>
                            </div>
                        </div>

                        <div class="oneline_field clear">
                            <div class="leftbox">
                                <label for="cc-button-text"><?php esc_html_e('Dismiss Button Text', 'ga-germanized') ?></label>
                            </div>

                            <div class="rightbox">
                                <input type="text" name="cc-button-text" id="cc-button-text" placeholder="<?php esc_attr_e('Got it!', 'ga-germanized') ?>" value="<?php echo esc_attr( $settings['cc-button-text'] ) ?>" />
                            </div>
                        </div>

                        <div class="oneline_field clear" id="accept-button-wrapper">
                            <div class="leftbox">
                                <label for="cc-accept-button-text"><?php esc_html_e('Allow Button Text', 'ga-germanized') ?></label>
                            </div>

                            <div class="rightbox">
                                <input type="text" name="cc-accept-button-text" id="cc-accept-button-text" placeholder="<?php esc_attr_e('Allow Cookies', 'ga-germanized') ?>" value="<?php echo esc_attr( $settings['cc-accept-button-text'] ) ?>" />
                            </div>
                        </div>

                        <div class="oneline_field clear" id="deny-button-wrapper">
                            <div class="leftbox">
                                <label for="cc-deny-button-text"><?php esc_html_e('Deny Button Text', 'ga-germanized') ?></label>
                            </div>

                            <div class="rightbox">
                                <input type="text" name="cc-deny-button-text" id="cc-deny-button-text" placeholder="<?php esc_attr_e('Refuse Cookies', 'ga-germanized') ?>" value="<?php echo esc_attr( $settings['cc-deny-button-text'] ) ?>" />
                            </div>
                        </div>

                        <hr style="margin-bottom: 30px; margin-top: 30px;" />

                        <div class="oneline_field checkboxarea clear">
                            <div class="leftbox">
                                <strong><?php esc_html_e('Enable Policy link', 'ga-germanized') ?></strong>
                            </div>

                            <div class="rightbox">
                                <input type="checkbox" name="enable-policy-link" id="enable-policy-link" value="1" <?php checked(1, $settings['enable-policy-link']) ?> />

                                <label for="enable-policy-link" class="checkboxlabel"><?php esc_html_e('Enable Policy link', 'ga-germanized') ?></label>
                                <p class="mini-description"><?php esc_html_e('Enable a policy link after your message, this could be linked to you privacy policy for example.', 'ga-germanized') ?></p>
                            </div>
                        </div>

                        <div id="policy-link-area" style="<?php echo (($settings['enable-policy-link'] != 1)?'display: none;':''); ?>">
                            <div class="oneline_field clear">
                                <div class="leftbox">
                                    <label for="cc-policy-link-text"><?php esc_html_e('Policy link text', 'ga-germanized') ?></label>
                                </div>

                                <div class="rightbox">
                                    <input type="text" name="cc-policy-link-text" id="cc-policy-link-text" placeholder="<?php esc_attr_e('Learn more', 'ga-germanized') ?>" value="<?php echo esc_attr( $settings['cc-policy-link-text'] ) ?>" />
                                </div>
                            </div>

                            <div class="oneline_field clear">
                                <div class="leftbox">
                                    <label for="cc-policy-link"><?php esc_html_e('Policy link', 'ga-germanized') ?></label>
                                </div>

                                <div class="rightbox">
                                    <input type="text" name="cc-policy-link" id="cc-policy-link" placeholder="<?php esc_attr_e('https://cookiesandyou.com/', 'ga-germanized') ?>" value="<?php echo esc_attr( $settings['cc-policy-link'] ) ?>" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="gag-settings-item clear">

                        <div class="oneline_field clear">
                            <div class="leftbox">
                                <label for="cc-banner-background-color"><?php esc_html_e('Pick a layout', 'ga-germanized') ?></label>
                            </div>

                            <div class="rightbox">

                                <div class="ga-cookie-notice-layout ga-lay1" data-banner-background="#ffffff" data-banner-text="#333232" data-button-background="#37d6d3" data-button-text="#ffffff"></div>
                                <div class="ga-cookie-notice-layout ga-lay2" data-banner-background="#37d6d3" data-banner-text="#ffffff" data-button-background="#ffffff" data-button-text="#37d6d3"></div>
                                <div class="ga-cookie-notice-layout ga-lay3" data-banner-background="#eeeef8" data-banner-text="#333232" data-button-background="#009ed4" data-button-text="#ffffff"></div>
                                <div class="ga-cookie-notice-layout ga-lay4" data-banner-background="#2c2c2c" data-banner-text="#636262" data-button-background="#f25416" data-button-text="#ffffff"></div>
                                <div class="ga-cookie-notice-layout ga-lay5" data-banner-background="#275f47" data-banner-text="#ffffff" data-button-background="#01cc76" data-button-text="#ffffff"></div>
                                <div class="ga-cookie-notice-layout ga-lay6" data-banner-background="#ff6c4f" data-banner-text="#333232" data-button-background="#fcab4c" data-button-text="#333232"></div>
                                <div class="ga-cookie-notice-layout ga-lay7" data-banner-background="#e0e0e0" data-banner-text="#333232" data-button-background="#e5025d" data-button-text="#ffffff"></div>
                                <div class="ga-cookie-notice-layout ga-lay8" data-banner-background="#000000" data-banner-text="#ffffff" data-button-background="#ffffff" data-button-text="#000000"></div>

                                <p class="mini-description"><?php esc_html_e('Choose a layout we already made for you.', 'ga-germanized') ?></p>
                            </div>
                        </div>




                        <div class="oneline_field clear">
                            <div class="leftbox">
                                <label for="cc-banner-background-color"><?php esc_html_e('Banner Background', 'ga-germanized') ?></label>
                            </div>

                            <div class="rightbox">
                                <input type="text" name="cc-banner-background-color" id="cc-banner-background-color" placeholder="<?php esc_attr_e('Color as Hex Color Code', 'ga-germanized') ?>" value="<?php echo esc_attr( $settings['cc-banner-background-color'] ) ?>" />
                                <p class="mini-description"><?php esc_html_e('Color as Hex Color Code, for example #ff0000 = red', 'ga-germanized') ?></p>
                            </div>
                        </div>

                        <div class="oneline_field clear">
                            <div class="leftbox">
                                <label for="cc-banner-text-color"><?php esc_html_e('Banner Text', 'ga-germanized') ?></label>
                            </div>

                            <div class="rightbox">
                                <input type="text" name="cc-banner-text-color" id="cc-banner-text-color" placeholder="<?php esc_attr_e('Color as Hex Color Code', 'ga-germanized') ?>" value="<?php echo esc_attr( $settings['cc-banner-text-color'] ) ?>" />
                                <p class="mini-description"><?php esc_html_e('Color as Hex Color Code, for example #ff0000 = red', 'ga-germanized') ?></p>
                            </div>
                        </div>

                        <hr style="margin-bottom: 30px;" />

                        <div class="oneline_field clear">
                            <div class="leftbox">
                                <label for="cc-button-background-color"><?php esc_html_e('Button Background', 'ga-germanized') ?></label>
                            </div>

                            <div class="rightbox">
                                <input type="text" name="cc-button-background-color" id="cc-button-background-color" placeholder="<?php esc_attr_e('Color as Hex Color Code', 'ga-germanized') ?>" value="<?php echo esc_attr( $settings['cc-button-background-color'] ) ?>" />
                                <p class="mini-description"><?php esc_html_e('Color as Hex Color Code, for example #ff0000 = red', 'ga-germanized') ?></p>
                            </div>
                        </div>

                        <div class="oneline_field clear">
                            <div class="leftbox">
                                <label for="cc-button-text-color"><?php esc_html_e('Button Text', 'ga-germanized') ?></label>
                            </div>

                            <div class="rightbox">
                                <input type="text" name="cc-button-text-color" id="cc-button-text-color" placeholder="<?php esc_attr_e('Color as Hex Color Code', 'ga-germanized') ?>" value="<?php echo esc_attr( $settings['cc-button-text-color'] ) ?>" />
                                <p class="mini-description"><?php esc_html_e('Color as Hex Color Code, for example #ff0000 = red', 'ga-germanized') ?></p>
                            </div>
                        </div>

                    </div>

                    <div class="gag-analytics-sendmebutton">
                        <button type="submit" class="gag-sendme-up"><?php esc_html_e('Save Changes', 'ga-germanized') ?></button>
                    </div>

                </div><!--end of cookie notice -->

                <div class="gag-settings-wrapper tab-item" id="tab4" style="display: none;">
                    <div class="gag-settings-item subheadline clear">

                        <span><?php esc_html_e('Other Tracking Codes', 'ga-germanized') ?></span>

                        <div class="oneline_field clear">
                            <div class="leftbox">
                                <label for="custom-tracker-head"><?php esc_html_e('Code (head)', 'ga-germanized') ?></label>
                            </div>

                            <div class="rightbox">
                                <textarea name="custom-tracker-head" id="custom-tracker-head"><?php echo esc_html( $settings['custom-tracker-head'] ) ?></textarea>

                                <p class="mini-description"><?php esc_html_e('Custom Tracker or other Code placed inside head-tag', 'ga-germanized') ?></p>
                            </div>
                        </div>

                        <div class="oneline_field clear">
                            <div class="leftbox">
                                <label for="custom-tracker-footer"><?php esc_html_e('Code (footer)', 'ga-germanized') ?></label>
                            </div>

                            <div class="rightbox">
                                <textarea name="custom-tracker-footer" id="custom-tracker-footer"><?php echo esc_html( $settings['custom-tracker-footer'] ) ?></textarea>

                                <p class="mini-description"><?php esc_html_e('Custom Tracker or other Code placed in the footer', 'ga-germanized') ?></p>
                            </div>
                        </div>

                    </div>

                    <div class="gag-settings-item clear" id="other-tracking-compliance-wrapper">

                        <div class="oneline_field checkboxarea clear">
                            <div class="leftbox">
                                <strong><?php esc_html_e('Compliance Type', 'ga-germanized') ?></strong>
                            </div>

                            <div class="rightbox">
                                <input type="checkbox" name="other-tracking-compliance" id="other-tracking-compliance" value="1" <?php checked(1, $settings['other-tracking-compliance']) ?> />

                                <label for="other-tracking-compliance" class="checkboxlabel"><?php esc_html_e('Cookie Consent Compliance Setting', 'ga-germanized') ?></label>
                                <p class="mini-description"><?php esc_html_e('Enable this checkbox to reflect the Cookie Consent compliance type setting.', 'ga-germanized') ?></p>
                            </div>
                        </div>

                    </div>

                    <div class="gag-settings-item subheadline clear">

                        <span><?php esc_html_e('Do Not Track', 'ga-germanized') ?></span>

                        <div class="oneline_field checkboxarea clear">
                            <div class="leftbox">
                                <strong><?php esc_html_e('Do Not Track', 'ga-germanized') ?></strong>
                            </div>

                            <div class="rightbox">
                                <input type="checkbox" name="custom-tracker-dnt" id="custom-tracker-dnt" value="1" <?php checked(1, $settings['custom-tracker-dnt']) ?> />

                                <label for="custom-tracker-dnt" class="checkboxlabel"><?php esc_html_e('Respect "Do Not Track" Header', 'ga-germanized') ?></label>

                                <p class="mini-description"><?php _e('Disable Tracking Codes completely if the users browser send a <a href="https://en.wikipedia.org/wiki/Do_Not_Track" target="_blank">"Do Not Track" header</a>.', 'ga-germanized') ?> <span class="warning-text"><?php esc_html_e('Caching Plugins could disrupt this behaviour!', 'ga-germanized') ?></span></p>
                            </div>
                        </div>

                    </div>

                    <div class="gag-analytics-sendmebutton">
                        <button type="submit" class="gag-sendme-up"><?php esc_html_e('Save Changes', 'ga-germanized') ?></button>
                    </div>
                </div><!-- end of other tracking codes -->

                <div class="gag-settings-wrapper tab-item" id="tab5" style="display: none;">

                    <div class="gag-analytics-id gag-settings-item">
                        <p><strong><?php esc_html_e('Disclaimer', 'ga-germanized') ?>:</strong> <?php esc_html_e('The plugin was developed to the best of our knowledge and belief. However, there will be no guarantee for the legal certainty of the implementation. The following statements are no legal advice.', 'ga-germanized') ?></p>
                    </div>

                    <div class="gag-analytics-id gag-settings-item ">

                        <p><strong><?php esc_html_e('Google Analytics Germanized is very easy and convenient.', 'ga-germanized') ?></strong></p>

                        <p style="text-align: center"><iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/rP_4ak9bCRs?rel=0&amp;showinfo=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe></p>

                        <p><?php esc_html_e('You can use this plugin to integrate Google Analytics conform to EU law in compliance with data protection. For this you only need to put your Google Analytics ID into the general settings. Other settings (e.g. AnonymizeIP) are preconfigured accordingly and need to be changed only when needed or when extensions are desired.', 'ga-germanized') ?></p>

                        <p><?php esc_html_e('For a privacy-compliant integration, it is necessary that you clarify the use of Google Analytics in your privacy policy. Additionally, a possibility for an opt-out of Google Analytics must be created. For the opt-out you can use the shortcode described in the tab general, point 3.', 'ga-germanized') ?></p>

                        <p><?php printf(__('A good generator for a privacy policy is provided by eRecht24. <a href="%s" target="_blank">Go to eRecht24 Generator</a>', 'ga-germanized'), 'http://go.pbajorat.174027.digistore24.com/GA-Germanized') ?></p>


                        <p><?php esc_html_e('In the advanced settings features of Google Analytics can be activated. Corresponding information can be found in the settings.', 'ga-germanized') ?></p>

                    </div>

                    <div class="gag-settings-item clear">
                        <h3><a href="https://www.webdesign-podcast.de/2018/02/10/google-analytics-datenschutzkonform-und-rechtssicher-integrieren/" target="_blank">Google Analytics datenschutzkonform und rechtssicher integrieren</a></h3>

                        <p>Dank diverser Regelungen und Vorgaben ist es mittlerweile gar nicht mehr so einfach Tools wie Google Analytics datenschutzkonform und rechtssicher in Webseiten zu integrieren.</p>

                        <p>Neben technischen Anpassungen am Google Analytics Code sind auch gewisse vertragliche Zusatzvereinbarungen mit Google notwendig, um hier deutschem bzw. europäischem Datenschutzrecht zu entsprechen.</p>

                        <p>Die technischen Anpassungen am Google Analytics Code übernimmt dieses Plugin für dich. Du musst dich jedoch um die notwendigen Verträge und eine passende Datenschutzerklärung kümmern.</p>
                    </div>

                    <div class="gag-settings-item onegagbox">
                        <h3>1. Vertrag zur Auftragsdatenverarbeitung</h3>

                        <p>Es mag für viele nun vielleicht absurd klingen, aber es ist leider notwendig mit Google einen schriftlichen Vertrag zur Auftragsdatenverarbeitung zu schließen.</p>

                        <p>Nach Meinung der Aufsichtsbehörden sind Betreiber von Webseiten bzw. konkret die Nutzer von Google Analytics Auftraggeber von Datenverarbeitungsleistungen, welche durch Google als Auftragnehmer erfolgen.</p>

                        <p>In diesem Kontext ist daher ein schriftlicher Vertrag über die Auftragsdatenverarbeitung zwischen dem Betreiber der Webseite und Google notwendig.</p>

                        <p>Seitens Google wird zu diesem Zweck eine entsprechende Vertragsvorlage zur Verfügung gestellt.</p>

                        <p><strong>Download als PDF:</strong> <a href="http://www.google.com/analytics/terms/de.pdf" target="_blank">Vertrag zur Auftragsdatenverarbeitung</a></p>

                        <p>Der Vertrag muss ausgefüllt via Post an die Europa-Zentrale von Google gesendet werden, welche ihren Sitz in Dublin (Irland) hat.</p>

                        <p>Auf der ersten Seite der PDF-Datei werden alle relevanten Informationen zum Ausfüllen des Dokumentes erklärt.</p>
                    </div>

                    <div class="gag-settings-item twogagbox">
                        <h3>2. Zusatz zur Datenverarbeitung</h3>

                        <p>Neben dem schriftlichen Vertrag sollte auch im Google Analytics Konto, in der Kontoverwaltung, dem Punkt „Zusatz zur Datenverarbeitung“ zugestimmt werden.</p>

                        <p>Bei neuen Google Analytics Konten wird diese Zustimmung in der Regel mit der Kontoeröffnung erteilt. Bei älteren Analytics Konten muss dieser Zusatzvereinbarung  noch gesondert zugestimmt werden.</p>

                        <p>Zu finden ist der Bereich in Google Analytics unter:<br> <em>Verwaltung</em> (links unten im Hauptmenü) &gt; <em>Kontoeinstellungen</em> (linke Spalte)<br> In den Kontoeinstellungen ist am Ende dann der Bereich "Zusatz zur Datenverarbeitung" zu finden.</p>
                    </div>

                    <div class="gag-settings-item thirdgagbox">
                        <h3>3. Widerspruchsrecht und Datenschutzerklärung</h3>

                        <p>Wer Google Analytics verwendet, muss darauf zum einen in der Datenschutzerklärung hinweisen und darüberhinaus auch Möglichkeiten für einen Widerspruch (Opt-out) gegen die Erfassung mittels Google Analytics bieten.</p>

                        <p><strong>Zur Generierung einer DSGVO konformen Datenschutzerklärung empfehlen wir den <a href="http://go.pbajorat.174027.digistore24.com/GA-Germanized" target="_blank">Premium DSGVO-Datenschutzgenerator von eRecht24</a>.</strong></p>

                        <p><strong>Wichtig:</strong> Denke daran den Shortcode <code>[ga-optout text="Disable Google Analytics"]</code> in der <a href="<?php echo esc_url( admin_url('privacy.php') ) ?>">Datenschutzerklärung</a> zu setzen. Details dazu findest du im obigen Video.</p>

                        <p>Quelle: <a href="https://www.webdesign-podcast.de/2018/02/10/google-analytics-datenschutzkonform-und-rechtssicher-integrieren/" target="_blank">https://www.webdesign-podcast.de/2018/02/10/google-analytics-datenschutzkonform-und-rechtssicher-integrieren/</a></p>
                    </div>

	                <?php
                    if( get_locale() == 'de_DE_formal' || get_locale() == 'de_DE' ) {
	                    require 'gdpr-special.php';
                    }
                    ?>
                </div>

	            <?php if( get_locale() == 'de_DE_formal' || get_locale() == 'de_DE' ): ?>
                <div class="gag-settings-wrapper tab-item" id="tab6" style="display: none;">
	                <?php require 'gdpr-special.php' ?>
                </div>
                <?php endif; ?>
            </div>

		</form>

	</div>

    <p class="ga-copyright"><?php echo sprintf( esc_html__('developed and maintained by %s.'), '<a href="https://www.pascal-bajorat.com/">'.esc_html__('Bajorat Media - WordPress Agency').'</a>' ) ?></p>

    <p class="ga-copyright"><a href="https://www.pascal-bajorat.com/" target="_blank"><img src="<?php echo plugins_url(dirname(PBGAG_BASE)) ?>/assets/img/bajorat-media.png" width="250" height="59" alt="Bajorat Media" /></a></p>

</div>