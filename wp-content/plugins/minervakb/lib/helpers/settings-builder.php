<?php
/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */

require_once(MINERVA_KB_PLUGIN_DIR . 'lib/helpers/icon-options.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/helpers/fonts.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/helpers/layout-editor.php');

class MKB_SettingsBuilder {

	private $no_tabs = false;

	private $vc = false;

	private $topic = false;

	private $post = false;

	public function __construct($args = null) {
		if (!isset($args)) {
			return;
		}

		if (isset($args['no_tabs'])) {
			$this->no_tabs = $args['no_tabs'];
		}

		if (isset($args['vc'])) {
			$this->vc = true;
		}

		if (isset($args['topic'])) {
			$this->topic = true;
		}

		if (isset($args['post'])) {
			$this->post = true;
		}
	}

	protected $tab_open = false;

	public function render_option( $type, $value, $config ) {
		switch ( $type ) {
			case 'checkbox':
				$this->toggle( $value, $config );
				break;

			case 'input':
			case 'input_text':
            case 'range': // TODO: add range for php settings too
				$this->input( $value, $config );
				break;

			case 'textarea':
			case 'textarea_text':
				$this->textarea( $value, $config );
				break;

			case 'media':
				$this->media( $value, $config );
				break;

			case 'color':
				$this->color( $value, $config );
				break;

			case 'select':
				$this->select( $value, $config );
				break;

			case 'page_select':
				$this->page_select( $value, $config );
				break;

			case 'icon_select':
				$this->icon_select( $value, $config );
				break;

			case 'image_select':
				$this->image_select( $value, $config );
				break;

			case 'layout_select':
				$this->layout_select( $value, $config );
				break;

			case 'term_select':
				$this->term_select( $value, $config );
				break;

			case 'tab':
				$this->open_tab_container( $config );
				break;

			case 'title':
				$this->title( $value, $config );
				break;

			case 'code':
				$this->code( $value, $config );
				break;

			case 'layout_editor':
				$this->layout_editor( $value, $config );
				break;

			case 'font':
				$this->font( $value, $config );
				break;

			case 'google_font_weights':
				$this->google_font_weights( $value, $config );
				break;

			case 'google_font_languages':
				$this->google_font_languages( $value, $config );
				break;

			case 'css_size':
				$this->css_size( $value, $config );
				break;

			case 'demo_import':
				$this->demo_import( $value, $config );
				break;

			case 'articles_list':
				$this->articles_list( $value, $config );
				break;

			case 'roles_select':
				$this->roles_select( $value, $config );
				break;

			case 'warning':
				$this->warning( $value, $config );
				break;

			case 'info':
				$this->info( $value, $config );
				break;

			case 'export':
				$this->export( $value, $config );
				break;

			case 'import':
				$this->import( $value, $config );
				break;

			case 'envato_verify':
				$this->envato_verify( $value, $config );
				break;

			default:
				break;
		}
	}

	public function render_tab_links( $options ) {
		$tabs = array_filter( $options, function ( $option ) {
			return $option["type"] === "tab";
		} );
		?>
		<div class="mkb-settings-tabs">
			<ul>
				<?php
				foreach ( $tabs as $tab ):
					?>
					<li class="mkb-settings-tab">
						<a href="#mkb_tab-<?php echo esc_attr( $tab["id"] ); ?>">
							<i class="mkb-settings-tab__icon fa fa-lg <?php echo esc_attr($tab["icon"]); ?>"></i>
							<?php echo esc_html( $tab["label"] ); ?>
						</a>
					</li>
				<?php
				endforeach;
				?>
			</ul>
		</div>
	<?php
	}

	protected function open_tab_container( $config ) {
		$this->close_tab_container();

		$this->tab_open = true;
		?>
		<div id="mkb_tab-<?php echo esc_attr( $config["id"] ); ?>" class="mkb-settings-tab__container">
	<?php
	}

	public function close_tab_container() {
		if ( $this->tab_open ) {
			?></div><?php
		}

		$this->tab_open = false;
	}

	protected function render_label($config) {
		if (!array_key_exists('label', $config)) {
			return;
		}

		?>
		<label class="mkb-setting-label" for="<?php echo esc_attr( $this->get_id_key( $config ) ); ?>">
			<?php echo esc_html( $this->get_label( $config ) ); ?>
			<?php if (isset($config['experimental'])): ?>
				<i class="mkb-setting-experimental-notice fa fa-flask"<?php if(is_string($config['experimental'])) {
					echo ' title="' . esc_attr($config['experimental']) . '"'; }
				?>></i>
			<?php endif; ?>
		</label>
	<?php
	}

	protected function render_description($config) {
		if ($this->topic || !array_key_exists('description', $config)) {
			return;
		}

		?>
		<div class="mkb-setting-description"><?php echo wp_kses_post($config["description"]); ?></div>
	<?php
	}

	protected function maybe_print_dependency_attribute($config) {
		if (!isset($config['dependency'])) {
			return;
		}

		echo ' data-dependency="'. esc_attr(json_encode($config['dependency'])) . '"';
	}

	protected function get_id_key( $config, $postfix = '' ) {
		if ($postfix) {
			$postfix = '_' . $postfix;
		}

		if ($this->topic) {
			return 'term_meta[' . $config["id"] . $postfix . ']';
		}

		return MINERVA_KB_OPTION_PREFIX . $config["id"] . $postfix;
	}

	protected function get_name_key( $config, $postfix = '' ) {

		if ($postfix) {
			$postfix = '_' . $postfix;
		}

		if ($this->vc || $this->post) {
			return $config["id"] . $postfix;
		} else if ($this->topic) {
			return 'term_meta[' . $config["id"] . $postfix . ']';
		} else {
			return MINERVA_KB_OPTION_PREFIX . $config["id"] . $postfix;
		}
	}

	protected function get_label( $config ) {
		return $this->topic ? '' : $config['label'];
	}

	protected function checkbox( $value, $config ) {
		?>
		<div class="mkb-control-wrap fn-control-wrap"
		     data-type="checkbox"
		     data-name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			<?php self::maybe_print_dependency_attribute($config); ?>>
			<?php $this->render_label($config); ?>
			<input class="fn-control"
			       type="checkbox"
			       id="<?php echo esc_attr( $this->get_id_key( $config ) ); ?>"
			       name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
				<?php if ( $value === true || $value === 'true' ) {
					echo 'checked="checked"';
				} ?>
				/>
			<?php $this->render_description($config); ?>
		</div>
	<?php
	}

	public function toggle( $value, $config ) {
		?>
		<div class="mkb-control-wrap fn-control-wrap"
		     data-type="toggle"
		     data-name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			<?php self::maybe_print_dependency_attribute($config); ?>>

			<div class="mkb-toggle-label">
				<?php echo esc_html( $this->get_label( $config ) ); ?>
			</div>

			<div class="mkb-switch">
				<input id="<?php echo esc_attr( $this->get_id_key( $config ) ); ?>"
				       name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
				       class="fn-control mkb-toggle mkb-toggle-round wpb_vc_param_value"
						<?php if (!$this->topic && !$this->post):?>
							value="<?php echo in_array($value, array(true, 'true', 'on'), true) ? 'on' : 'off'; ?>"
						<?php endif; ?>
					<?php if ( in_array($value, array(true, 'true', 'on'), true) ) {
						echo 'checked="checked"';
					} ?>
				       type="checkbox" />
				<label for="<?php echo esc_attr( $this->get_id_key( $config ) ); ?>"></label>
			</div>

			<?php $this->render_description($config); ?>
		</div>
	<?php
	}

	protected function input( $value, $config ) {
		?>
		<div class="mkb-control-wrap fn-control-wrap"
		     data-type="input"
		     data-name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			<?php self::maybe_print_dependency_attribute($config); ?>>
			<?php $this->render_label($config); ?>
			<input class="fn-control"
			       type="text"
			       id="<?php echo esc_attr( $this->get_id_key( $config ) ); ?>"
			       name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			       value="<?php echo esc_attr( $value ); ?>"
				/>
			<?php $this->render_description($config); ?>
		</div>
	<?php
	}

	/**
	 * Media uploader
	 * @param $value
	 * @param $config
	 */
	protected function media( $value, $config ) {

		if (is_string($value)) { // old img format compatibility

			$decoded_value = null;

			try {
				// can be either JSON or url
				$decoded_value = json_decode($value, true);

			} catch(Exception $e) {}

			if (!$decoded_value) {
				$url = $value;
				$value = array(
					"isUrl" => true,
					"img" => $url
				);
			} else {
				$value = $decoded_value;
			}
		}

		$value["isUrl"] = ($value["isUrl"] === "true" || $value["isUrl"] === true) ? true : false;

		$hasImage = !empty($value["img"]);
		$src = "";

		if ($hasImage) {
			if ($value["isUrl"]) {
				$src = trim($value["img"]);
			} else {
				$image = wp_get_attachment_image_src((int) $value["img"], "full");
				$src = $image[0];
			}
		}

		?>
		<div class="mkb-control-wrap fn-control-wrap"
		     data-type="media"
		     data-name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			<?php self::maybe_print_dependency_attribute($config); ?>>

			<?php $this->render_label($config); ?>

			<div class="mkb-media-wrap-outer">

				<input class="fn-control fn-media-store mkb-media-store"
				       type="hidden"
				       id="<?php echo esc_attr( $this->get_id_key( $config ) ); ?>"
				       name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
				       value="<?php echo esc_attr( stripslashes(json_encode($value)) ); ?>"
					/>

				<div class="mkb-media-wrap">
					<div class="mkb-toggle-label">
						<?php _e('Use URL?', 'minerva-kb'); ?>
					</div>
					<div class="mkb-switch">
						<input name="media-value-type"
						       id="<?php echo esc_attr(
							       $this->get_id_key( $config, 'value-type' )
						       ); ?>"
						       class="mkb-toggle mkb-toggle-round fn-media-value-type"
						       value=""
							<?php if ($value['isUrl']): ?> checked <?php endif; ?>
						       type="checkbox" />
						<label for="<?php echo esc_attr(
							$this->get_id_key( $config, 'value-type' )
						); ?>"></label>
					</div>

					<div class="fn-mkb-url-upload<?php if (!$value['isUrl']): ?> hidden<?php endif; ?>">
						<input class="fn-mkb-url-store mkb-media-url"
						       type="text"
						       id="<?php echo esc_attr(
							       $this->get_id_key( $config, 'url' )
						       ); ?>"
						       value="<?php echo esc_attr( $value['isUrl'] ? $src : '' ); ?>"
							/>

						<div class="mkb-media-preview-wrap">
							<div class="fn-mkb-url-preview mkb-media-preview<?php if (!$value['isUrl'] || !$src): ?> hidden<?php endif; ?>">
								<?php if ($value['isUrl'] && $src): ?>
									<img src="<?php echo esc_attr( $src ); ?>" />
								<?php endif; ?>
							</div>
							<a class="fn-mkb-remove-url-img mkb-remove-media-img<?php if (!$value['isUrl'] || !$src): ?> hidden<?php endif; ?>" href="#">
								<i class="fa fa-lg fa-times-circle"></i>
							</a>
						</div>
					</div>

					<div class="fn-mkb-media-upload mkb-media-upload<?php if ($value['isUrl']): ?> hidden<?php endif; ?>">
						<div class="mkb-media-preview-wrap">
							<div class="fn-mkb-media-preview mkb-media-preview<?php if ($value['isUrl'] || !$src): ?> hidden<?php endif; ?>">
								<?php if (!$value['isUrl'] && $src): ?>
									<img src="<?php echo esc_attr( $src ); ?>" />
								<?php endif; ?>
							</div>
							<a class="fn-mkb-remove-media-img mkb-remove-media-img<?php if ($value['isUrl'] || !$src): ?> hidden<?php endif; ?>" href="#">
								<i class="fa fa-lg fa-times-circle"></i>
							</a>
						</div>

						<a class="fn-mkb-add-media-img mkb-add-media-img<?php if (!$value['isUrl'] && $src): ?> hidden<?php endif; ?>" href="#">
							<?php _e('Upload media', 'minerva-kb'); ?>
						</a>

						<input class="fn-media-upload-store" name="<?php echo esc_attr( $this->get_name_key( $config, 'id_store' ) ); ?>" type="hidden"
						       value="<?php echo esc_attr( $value['isUrl'] ? '' : $value["img"] ); ?>" />
					</div>

				</div>

			</div>

			<?php $this->render_description($config); ?>
		</div>
	<?php
	}

	/**
	 * Media url parser
	 * @param $value
	 * @param string $size
	 *
	 * @return mixed
	 */
	public static function media_url($value, $size = 'full') {
		$decoded_value = null;

		if (is_string($value)) {
			try {
				// can be either JSON or url
				$decoded_value = json_decode(stripslashes($value), true);

			} catch(Exception $e) {}

			if (!$decoded_value) {
				$url = $value;
				$value = array(
					"isUrl" => true,
					"img" => $url
				);
			} else {
				$value = $decoded_value;
			}
		}

		$value["isUrl"] = ($value["isUrl"] === "true" || $value["isUrl"] === true) ? true : false;

		if ($value["isUrl"]) {
			return isset($value["url"]) ? $value["url"] : $value["img"]; // legacy format
		}

		$image = wp_get_attachment_image_src((int) $value["img"], $size);
		return $image[0];
	}

	protected function textarea( $value, $config ) {

		$rows = isset($config["height"]) ? $config["height"] : 10;
		$cols = isset($config["width"]) ? $config["width"] : 60;

		?>
		<div class="mkb-control-wrap fn-control-wrap"
		     data-type="textarea"
		     data-name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			<?php self::maybe_print_dependency_attribute($config); ?>>

			<?php $this->render_label($config); ?>

			<textarea class="fn-control"
			          class="mkb-settings-textarea"
			       id="<?php echo esc_attr( $this->get_id_key( $config ) ); ?>"
			       name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			       rows="<?php echo esc_html($rows); ?>"
			       cols="<?php echo esc_html($cols); ?>"
				><?php echo wp_kses_post( $value ); ?></textarea>
			<?php $this->render_description($config); ?>
		</div>
	<?php
	}

	protected function color( $value, $config ) {
		?>
		<div class="mkb-control-wrap fn-control-wrap"
		     data-type="color"
		     data-name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			<?php self::maybe_print_dependency_attribute($config); ?>>

			<?php $this->render_label($config); ?>

			<input type="text"
			       class="mkb-color-picker fn-control"
			       id="<?php echo esc_attr( $this->get_id_key( $config ) ); ?>"
			       name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			       value="<?php echo esc_attr( $value ); ?>"
				/>
			<?php $this->render_description($config); ?>
		</div>
	<?php
	}

	protected function select( $value, $config ) {
		?>
		<div class="mkb-control-wrap fn-control-wrap"
		     data-type="select"
		     data-name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			<?php self::maybe_print_dependency_attribute($config); ?>>

			<?php $this->render_label($config); ?>

			<select class="fn-control"
			        id="<?php echo esc_attr( $this->get_id_key( $config ) ); ?>"
			        name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>">
				<?php
				foreach ( $config["options"] as $key => $label ):
					?><option value="<?php echo esc_attr( $key ); ?>"<?php
					if ($key == $value) {echo ' selected="selected"'; }
					?>><?php echo esc_html( $label ); ?></option><?php
				endforeach;
				?>
			</select>
			<?php $this->render_description($config); ?>
		</div>
	<?php
	}

	protected function page_select( $value, $config ) {
		?>
		<div class="mkb-control-wrap fn-control-wrap"
		     data-type="page_select"
		     data-name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			<?php self::maybe_print_dependency_attribute($config); ?>>

			<?php $this->render_label($config); ?>

			<span class="mkb-page-select-wrap fn-page-select-wrap">
				<select class="fn-control"
				        id="<?php echo esc_attr( $this->get_id_key( $config ) ); ?>"
				        name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>">
					<?php
					foreach ( $config["options"] as $key => $label ):
						?><option value="<?php echo esc_attr( $key ); ?>"<?php
						if ($key == $value) {echo ' selected="selected"'; }
						?> data-link="<?php echo esc_attr(get_the_permalink($key)); ?>"
						   data-edit-link="<?php echo esc_attr( get_edit_post_link($key) ); ?>"><?php echo esc_html( $label ); ?></option><?php
					endforeach;
					?>
				</select>
				<a class="mkb-page-select-link fn-page-select-link mkb-unstyled-link mkb-disabled" href="#" target="_blank">
					<?php _e( 'Open page', 'minerva-kb' ); ?> <i class="fa fa-external-link-square"></i>
				</a>
				<a class="mkb-page-select-link fn-page-select-edit-link mkb-unstyled-link mkb-disabled" href="#" target="_blank">
					<?php _e( 'Edit page', 'minerva-kb' ); ?> <i class="fa fa-pencil-square"></i>
				</a>
			</span>
			<?php $this->render_description($config); ?>
		</div>
	<?php
	}

	/**
	 * Public role parser
	 * @param $value
	 *
	 * @return array
	 */
	public static function get_role($value) {
		return self::maybe_parse_old_role_value($value);
	}

	/**
	 * Convert old role selector format to new
	 * @param $value
	 *
	 * @return array
	 */
	protected static function maybe_parse_old_role_value($value) {

		$value = stripslashes($value);
		$str_value = $value;

		try {
			$value = json_decode($value); // new format or default
		} catch (Exception $e) {
			$value = $str_value;
		}

		// new format, return
		if (is_array($value)) {
			return empty($value) ? array("administrator") : $value;
		}

		if (!$value) {
			$value = $str_value;
		}

		if (is_string($value)) {
			$str_value = $value;

			switch ($str_value) { // parse old string format
				case "none":
					// guest / all roles
					$value = array("guest");
					break;

				case "administrator":
					// none, admins only
					$value = array("administrator");
					break;

				case "editor":
					// editor
					$value = array("editor");
					break;

				case "author":
					// author & editor
					$value = array("author", "editor");
					break;

				case "contributor":
					// contributor, author & editor
					$value = array("contributor", "author", "editor");
					break;

				case "subscriber":
					// subscriber, contributor, author & editor
					$value = array("subscriber", "contributor", "author", "editor");
					break;

				default: // some bad format
					$value = array("guest");
					break;
			}
		} else { // some bad format
			$value = array("guest");
		}

		return $value;
	}

	/**
	 * WP system roles selector
	 * @param $value
	 * @param $config
	 */
	protected function roles_select( $value, $config ) {
		global $wp_roles;
		$roles = $wp_roles->get_names();
		$roles["guest"] = __("Guest (anyone)", "minerva-kb");

		$allowed_roles = $this->maybe_parse_old_role_value($value);

		?>
		<div class="mkb-control-wrap fn-control-wrap"
		     data-type="roles_select"
		     data-name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			<?php self::maybe_print_dependency_attribute($config); ?>>

			<?php $this->render_label($config); ?>

			<div class="mkb-roles-selector-wrap fn-mkb-roles-selector-wrap">

				<span class="mkb-roles-toggle-all fn-roles-toggle-all"><?php esc_html_e('Toggle all', 'minerva-kb'); ?></span>

				<input class="fn-control mkb-roles-selector-hidden-input" type="hidden"
				       id="<?php echo esc_attr( $this->get_id_key( $config ) ); ?>"
				       name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
				       value="<?php echo esc_attr( json_encode($allowed_roles, true) ); ?>"
					/>

				<?php

				foreach ( $roles as $key => $label ):
					if ($key === 'administrator' || $key === 'super') {
						continue;
					}
					?>
					<label>
						<input class="fn-mkb-role-select" data-role="<?php echo esc_attr( $key ); ?>" type="checkbox" <?php
							if(in_array($key, $allowed_roles) || in_array("guest", $allowed_roles)) {
								echo "checked=\"checked\"";
							} ?>/>
						<?php echo esc_html( $label ); ?>
					</label>
				<?php endforeach; ?>
			</div>

			<p><?php
				esc_html_e('Admin and Super Admin roles are not editable and have full access to content.', 'minerva-kb');
				esc_html_e(' Note, that some users may have multiple roles, for example Contributor & BBP Participant. In this case you must restrict both roles.', 'minerva-kb'); ?>
			</p>
				<?php if (isset($config['view_log']) && $config['view_log']): ?>
					<h3><?php esc_html_e('Recent visitors roles', 'minerva-kb'); ?></h3>
					<a href="#" class="mkb-button mkb-unstyled-link fn-roles-log-view">
						<i class="mkb-icon-button__icon fa fa-lg fa-user-circle"></i>
						<span><?php esc_html_e('View recent visitors roles', 'minerva-kb'); ?></span>
					</a>
					<a href="#" class="mkb-button mkb-unstyled-link mkb-button-danger fn-roles-log-clear">
						<i class="mkb-icon-button__icon fa fa-lg fa-user-circle"></i>
						<span><?php esc_html_e('Clear log', 'minerva-kb'); ?></span>
					</a>
					<br/>
					<div class="mkb-hidden mkb-restriction-log-results fn-mkb-restriction-log-results"></div>
					<p><?php esc_html_e('Visitor roles are recorded only for restricted content visits, admins are not included in log.', 'minerva-kb'); ?></p>

				<?php endif;?>

			<?php if (isset($config['flush']) && $config['flush']): ?>
				<h3><?php esc_html_e('Restriction cache', 'minerva-kb'); ?></h3>
				<a href="#" class="mkb-button mkb-unstyled-link fn-roles-selector-flush">
					<i class="mkb-icon-button__icon fa fa-lg fa-refresh"></i>
					<span><?php esc_html_e('Clear restriction cache', 'minerva-kb'); ?></span>
				</a>

				<p>
					<?php esc_html_e('This is optional. Cache is updated every time you edit settings, articles or topics, but you can clear it manually in case you have performed some other actions that may affect it.', 'minerva-kb'); ?>
				</p>
			<?php endif; ?>

			<?php $this->render_description($config); ?>
		</div>
	<?php
	}

	/**
	 * Icon selector
	 * @param $value
	 * @param $config
	 */
	protected function icon_select( $value, $config ) {
		$icon_options = mkb_icon_options();

		?>
		<div class="mkb-control-wrap fn-control-wrap"
		     data-type="icon_select"
		     data-name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			<?php self::maybe_print_dependency_attribute($config); ?>>

			<?php $this->render_label($config); ?>

			<input class="fn-control mkb-icon-hidden-input" type="hidden"
			       id="<?php echo esc_attr( $this->get_id_key( $config ) ); ?>"
			       name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			       value="<?php echo esc_attr( $value ); ?>"
				/>

			<div class="mkb-icon-button">
				<a href="#" class="mkb-icon-button__link mkb-button mkb-unstyled-link">
					<i class="mkb-icon-button__icon fa fa-lg <?php echo esc_attr( $value ); ?>"></i>
					<span class="mkb-icon-button__text"><?php echo esc_html( $value ); ?></span>
				</a>
			</div>
			<div class="mkb-icon-select-filter mkb-hidden">
				<input placeholder="Type keyword to filter" type="text" />
			</div>
			<div class="mkb-icon-select mkb-hidden">
				<?php
				foreach ( $icon_options as $key => $label ):
					?>
					<span data-mkb-icon="<?php echo esc_attr($key); ?>" class="mkb-icon-select__item<?php if ($key == $value) { echo ' mkb-icon-selected'; } ?>">
						<i class="fa fa-lg <?php echo esc_attr($key); ?>"></i>
					</span>
				<?php
				endforeach;
				?>
			</div>
			<?php $this->render_description($config); ?>
		</div>
	<?php
	}

	public function image_select( $value, $config ) {
		?>
		<div class="mkb-control-wrap fn-control-wrap"
		     data-type="image_select"
		     data-name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			<?php self::maybe_print_dependency_attribute($config); ?>>

			<?php $this->render_label($config); ?>

			<input class="fn-control mkb-image-hidden-input wpb_vc_param_value" type="hidden"
			       id="<?php echo esc_attr( $this->get_id_key( $config ) ); ?>"
			       name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			       value="<?php echo esc_attr( $value ); ?>"
				/>

			<div class="mkb-image-select">
				<ul>
					<?php
					foreach ( $config["options"] as $key => $item ):
						?>
						<li data-value="<?php echo esc_attr( $key ); ?>"
						    class="mkb-image-select__item<?php
						if ($key == $value) {echo ' mkb-image-selected'; } ?>">
							<span class="mkb-image-wrap">
								<img src="<?php echo esc_attr($item["img"]); ?>"
							     class="mkb-image-select__image" />
								<span class="mkb-image-selected__checkmark">
									<i class="fa fa-lg fa-check-circle"></i>
								</span>
								</span>
							<span class="mkb-image-select__item-label">
								<?php echo esc_html( $item["label"] ); ?>
							</span>
						</li>
					<?php
					endforeach;
					?>
				</ul>
			</div>

			<?php $this->render_description($config); ?>
		</div>
	<?php
	}

	public function layout_select( $value, $config ) {
		$options = $config['options'];

		$value = isset( $value ) && ! empty( $value ) ?
			array_map( function ( $item ) {
				return $item;
			}, explode( ",", $value ) ) :
			array();

		if (!empty($options)) {
			$available = array_filter($options, function($item) use ($value) {
				return !in_array($item['key'], $value);
			});

			$selected = array_filter($options, function($item) use ($value) {
				return in_array($item['key'], $value);
			});

			if (isset($selected) && !empty($selected)) {
				usort($selected, function($a, $b) use ($value) {
					return array_search($a['key'], $value) < array_search($b['key'], $value) ? -1 : 1;
				});
			}
		}

		?>
		<div class="mkb-control-wrap fn-control-wrap"
		     data-type="layout_select"
		     data-name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			<?php self::maybe_print_dependency_attribute($config); ?>>

			<?php $this->render_label($config); ?>

			<input class="fn-control mkb-layout-hidden-input wpb_vc_param_value" type="hidden"
			       id="<?php echo esc_attr( $this->get_id_key( $config ) ); ?>"
			       name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			       value="<?php echo esc_attr( implode(",", $value) ); ?>" />

			<div class="mkb-layout-select">

				<div class="mkb-layout-select__available mkb-layout-select__container">
					<?php
					if ( isset( $available ) && ! empty( $available ) ):
						foreach ( $available as $item ):
							?>
							<div data-value="<?php echo esc_attr( $item['key'] ); ?>"
							     class="mkb-layout-select__item">
								<?php echo esc_html( $item['label'] ); ?>
							</div>
						<?php
						endforeach;
					endif;
					?>
				</div>

				<div class="mkb-layout-select__selected mkb-layout-select__container">
					<?php
					if ( isset( $selected ) && ! empty( $selected ) ):
						foreach ( $selected as $item ):
							?>
							<div data-value="<?php echo esc_attr( $item['key'] ); ?>"
							     class="mkb-layout-select__item">
								<?php echo esc_html( $item['label'] ); ?>
							</div>
						<?php
						endforeach;
					endif;
					?>
				</div>
			</div>

			<?php $this->render_description($config); ?>
		</div>
	<?php
	}

	/**
	 * Select from a term tree
	 * @param $value
	 * @param $config
	 */
	public function term_select( $value, $config ) {
		$tax = $config['tax'];

		if (!taxonomy_exists($tax)) {
			echo '<p>' . __( 'Error: taxonomy does not exist.', 'minerva-kb' ) . '</p>';
			return;
		}

		$value = isset( $value ) && ! empty( $value ) ?
			array_map( function ( $item ) {
				return $item;
			}, explode( ",", $value ) ) :
			array();

		?>
		<div class="mkb-control-wrap fn-control-wrap"
		     data-type="term_select"
		     data-name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			<?php self::maybe_print_dependency_attribute($config); ?>>

			<?php $this->render_label($config); ?>

			<input class="fn-control fn-terms-select-store mkb-term-select-hidden-input wpb_vc_param_value" type="hidden"
			       id="<?php echo esc_attr( $this->get_id_key( $config ) ); ?>"
			       name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			       value="<?php echo esc_attr( implode(",", $value) ); ?>" />

			<?php

			$terms = get_terms($tax, array(
				"hide_empty" => false
			));

			$terms_tree = $this->build_terms_tree($terms);
			$extra_items = isset($config['extra_items']) ? $config['extra_items'] : array();

			if (!empty($terms_tree)) {
				?>
				<div class="fn-terms-tree mkb-terms-tree">
					<form action="" novalidate>
						<?php
						$this->render_tree($terms_tree, $this->get_id_key( $config ), '', $value, $extra_items);
						?>
					</form>
				</div>

				<div class="fn-terms-selected mkb-terms-selected">
					<ul>
						<?php

						if ($value && !empty($value)) {
							if (!empty($extra_items)) {
								$terms_tree = array_merge($terms_tree, $extra_items);
							}

							$selected_data = $this->get_selected_terms($terms_tree, '', $value, $extra_items);

							foreach($value as $selected):

								$extra_items_keys = !empty($extra_items) ?
									array_keys(self::get_array_by_id($extra_items, 'key')) :
									array();

								if (!in_array($selected, $extra_items_keys)) {
									$term = term_exists( (int)$selected, $tax );
									if ( $term == 0 || $term == null ) {
										continue;
									}
								}

								$item_info = $selected_data['term_' . $selected];
								?>
								<li data-id="<?php echo esc_attr($selected); ?>">
									<span><?php echo esc_html($item_info['path']); ?></span>
									<?php echo esc_html($item_info['name']); ?>
								</li>
							<?php

							endforeach;

						}
						?>
					</ul>
				</div>
			<?php
			}

			?>
			<?php $this->render_description($config); ?>
		</div>
	<?php
	}

	/**
	 * Gets array by id
	 * @param $arr
	 */
	private static function get_array_by_id($arr, $id_field) {
		return array_reduce($arr, function($acc, $item) use ($id_field) {
			$acc[$item[$id_field]] = $item;
			return $acc;
		}, array());
	}

	/**
	 * Renders available terms tree
	 * @param $tree
	 * @param $option_id
	 * @param $path
	 * @param $value
	 */
	private function render_tree($tree, $option_id, $path, $value, $extra_items = array()) {
		?>
		<ul>
			<?php foreach($tree as $branch):
				$term = $branch["term"];
				$children = isset($branch["children"]) ? $branch["children"] : array();
				$branch_path = ($path ? $path . '/' : '') . $term->name;
				$is_selected = in_array($term->term_id, $value);
				?>
				<li>
					<?php self::render_term_item($term->term_id, $term->name, $term->count, $path, $is_selected, $option_id); ?>
					<?php
					if (!empty($children)):
						$this->render_tree($children, $option_id, $branch_path, $value);
					endif;
					?>
				</li>
			<?php endforeach; ?>
			<?php
			/**
			 * Custom extra items
			 */
			if($path === '' && !empty($extra_items)):
				foreach($extra_items as $item):
					$key = $item['key'];
					$label = $item['label'];
					$is_selected = in_array($key, $value);
					?>
					<li class="mkb-custom-term-item">
						<?php self::render_term_item($key, $label, "", $path, $is_selected, $option_id); ?>
					</li>
				<?php endforeach;
				?>
			<?php endif; ?>
		</ul>
	<?php
	}

	/**
	 * Renders term tree item
	 * @param $id
	 * @param $name
	 * @param $count
	 * @param $path
	 * @param $is_selected
	 * @param $option_id
	 */
	private static function render_term_item($id, $name, $count, $path, $is_selected, $option_id) {
		?>
		<span data-id="<?php echo esc_attr($id); ?>"
		      data-count="<?php echo esc_attr($count); ?>"
		      data-path="<?php echo esc_attr($path); ?>"<?php if ($is_selected) {
			?> class="mkb-term-selected"<?php
		} ?>>
						<i class="fa fa-folder"></i>
			<?php echo esc_html($name . ($count ? ' (' . $count . ')' : '')); ?>

			<input type="checkbox"
			       id="term_select_<?php echo esc_attr($id . '_' . $option_id); ?>"
			       name="term_select_<?php echo esc_attr($id . '_' . $option_id); ?>"
				<?php if ( $is_selected ) {
					echo 'checked="checked"';
				} ?>

				/>
			<label for="term_select_<?php echo esc_attr($id . '_' . $option_id); ?>"></label>

		</span>
		<?php
	}

	/**
	 * Renders selected terms recursively
	 * @param $tree
	 * @param $path
	 * @param $value
	 */
	private function get_selected_terms($tree, $path, $value, $extra_items = array()) {
		if (!$value || empty($value)) {
			return array();
		}

		$selected = array();

		$extra_items_by_id = array();
		$extra_items_keys = array();

		if (!empty($extra_items)) {
			$extra_items_by_id = self::get_array_by_id($extra_items, 'key');
			$extra_items_keys = array_keys($extra_items_by_id);
		}

		foreach($tree as $branch):
			$term = isset($branch["term"]) ? $branch["term"] : $branch['key']; // can be real term or hardcoded items
			$term_data = array();

			if (in_array($term, $extra_items_keys)) {
				$term_data = array(
					'id' => $term,
					'name' => $extra_items_by_id[$term]['label'],
					'count' => '',
				);
			} else {
				$term_data = array(
					'id' => $term->term_id,
					'name' => $term->name,
					'count' => $term->count,
				);
			}
			$children = isset($branch["children"]) ? $branch["children"] : array();
			$branch_path = ($path ? $path . '/' : '') . $term_data['name'];

			if (in_array($term_data['id'], $value)) {
				$selected['term_' . $term_data['id']] = array(
					'path' => $path,
					'name' => $term_data['name'] . ($term_data['count'] ? ' (' . $term_data['count'] . ')' : '')
				);
			}

			if (!empty($children)){
				$selected = array_merge($selected, $this->get_selected_terms($children, $branch_path, $value));
			}
		endforeach;

		return $selected;
	}

	/**
	 * Builds hierarchical terms tree
	 * @param $terms
	 *
	 * @return mixed
	 */
	private function build_terms_tree(&$terms) {
		$tree = array(
			'0' => array(
				'term' => null
			)
		);

		while(!empty($terms)) {
			foreach($terms as $term) {
				if ($this->locate_in_term_tree($term, $tree, $terms)) {
					continue;
				}
			}
		}

		return $tree['0']['children'];
	}

	/**
	 * Places term in existing tree
	 * @param $term
	 * @param $tree
	 * @param $terms
	 *
	 * @return bool
	 */
	private function locate_in_term_tree($term, &$tree, &$terms) {
		$is_found = false;

		foreach($tree as $id => $tree_item) {
			if ($term->parent == $id) {
				$is_found = true;

				if (!isset($tree[$id]['children'])) {
					$tree[$id]['children'] = array();
				}

				$this->remove_term_by_id($term->term_id, $terms);

				$tree[$id]['children'][$term->term_id] = array(
					'term' => $term
				);

				break;
			} else {
				if (isset($tree_item['children'])) {
					if ($this->locate_in_term_tree($term, $tree[$id]['children'], $terms)) {
						$is_found = true;
						break;
					}
				}
			}
		}

		return $is_found;
	}

	/**
	 * Removes term given its id
	 * @param $id
	 * @param $terms
	 */
	private function remove_term_by_id($id, &$terms) {
		$found = null;

		foreach($terms as $index => $term) {
			if ($term->term_id == $id) {
				$found = $index;
				break;
			}
		}

		unset($terms[$found]);
	}

	protected function title( $value, $config ) {
		?>
		<div class="mkb-control-wrap fn-control-wrap"
		     data-type="title"
		     data-name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			<?php self::maybe_print_dependency_attribute($config); ?>>
			<div class="mkb-settings-title-wrap">
				<div class="mkb-settings-title"><?php echo esc_html( $this->get_label( $config ) ); ?>
					<?php if(isset($config['preview_image'])): ?>
						<i class="mkb-settings-preview fa fa-eye"></i>
					<?php endif; ?>
				</div>
				<?php if(isset($config['preview_image'])): ?>
				<div class="mkb-setting-preview-image"
				     style="<?php if (isset($config['width'])) { echo esc_attr("width: " . $config['width'] . "px;"); } ?>">
					<img src="<?php echo esc_attr($config['preview_image']); ?>" alt="<?php echo esc_attr( $this->get_label( $config ) ); ?>"/>
				</div>
				<?php endif; ?>
			</div>

			<?php $this->render_description($config); ?>
		</div>
	<?php
	}

	protected function code( $value, $config ) {
		?>
		<div class="mkb-control-wrap fn-control-wrap"
		     data-type="code"
		     data-name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			<?php self::maybe_print_dependency_attribute($config); ?>>
			<div class="mkb-settings-code-wrap">
				<h3 class="mkb-code-title"><?php echo esc_html( $this->get_label( $config ) ); ?></h3>
				<code class="mkb-setting-code">
					<?php echo wp_kses_post($config["default"]); ?>
				</code>
			</div>

			<?php $this->render_description($config); ?>
		</div>
	<?php
	}

	/**
	 * Page builder layout editor
	 * @param $value
	 * @param $config
	 */
	protected function layout_editor( $value, $config ) {
		$layout_editor = new MKB_LayoutEditor($this);

		?>
		<div class="fn-layout-editor-wrap"
		     data-type="layout_editor"
		     data-name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			<?php self::maybe_print_dependency_attribute($config); ?>>

			<input class="fn-control mkb-layout-editor-hidden-input" type="hidden"
			       id="<?php echo esc_attr( $this->get_id_key( $config ) ); ?>"
			       name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			       value="<?php echo esc_attr($value ); ?>" />

			<div class="mkb-settings-layout-editor-wrap">
				<h3 class="mkb-layout-editor-title"><?php echo esc_html( $this->get_label( $config ) ); ?></h3>
				<div class="mkb-settings-layout-editor-container">
					<?php $layout_editor->render(); ?>
				</div>
			</div>

			<?php $this->render_description($config); ?>
		</div>
	<?php
	}

	/**
	 * Font
	 * @param $value
	 * @param $config
	 */
	protected function font( $value, $config ) {
		$fonts_list = mkb_get_all_fonts();
		?>
		<div class="mkb-control-wrap fn-control-wrap"
		     data-type="font"
		     data-name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			<?php self::maybe_print_dependency_attribute($config); ?>>

			<?php $this->render_label($config); ?>

			<select class="fn-control"
			        id="<?php echo esc_attr( $this->get_id_key( $config ) ); ?>"
			        name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>">
				<?php
				foreach ( $fonts_list as $group ):
					?>
					<optgroup label="<?php echo esc_attr( $group["id"] ); ?>"><?php
					foreach ( $group["fonts"] as $key => $label ): ?>
						<option value="<?php echo esc_attr( $key ); ?>"<?php
						if ( $key == $value ) {
							echo ' selected="selected"';
						}
						?>><?php echo esc_html( $label ); ?></option><?php
					endforeach;
					?></optgroup><?php
				endforeach
				?>
			</select>

			<?php $this->render_description($config); ?>
		</div>
	<?php
	}

	/**
	 * Font weights to load
	 * @param $value
	 * @param $config
	 */
	protected function google_font_weights( $value, $config ) {
		$weights_list = mkb_get_all_gf_weights();

		$value = is_array($value) ? $value : array();

		?>
		<div class="mkb-control-wrap fn-control-wrap"
		     data-type="google_font_weights"
		     data-name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			<?php self::maybe_print_dependency_attribute($config); ?>>

			<?php $this->render_label($config); ?>

			<select multiple class="fn-control"
			        id="<?php echo esc_attr( $this->get_id_key( $config ) ); ?>"
			        name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>">
				<?php
					foreach ( $weights_list as $key => $label ): ?>
						<option value="<?php echo esc_attr( $key ); ?>"<?php
						if ( in_array($key, $value) ) {
							echo ' selected="selected"';
						}
						?>><?php echo esc_html( $label ); ?></option><?php
					endforeach;
				?>
			</select>

			<?php $this->render_description($config); ?>
		</div>
	<?php
	}

	/**
	 * Font languages to load
	 * @param $value
	 * @param $config
	 */
	protected function google_font_languages( $value, $config ) {
		$languages_list = mkb_get_all_gf_languages();

		$value = is_array($value) ? $value : array();

		?>
		<div class="mkb-control-wrap fn-control-wrap"
		     data-type="google_font_languages"
		     data-name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			<?php self::maybe_print_dependency_attribute($config); ?>>

			<?php $this->render_label($config); ?>

			<select multiple class="fn-control"
			        id="<?php echo esc_attr( $this->get_id_key( $config ) ); ?>"
			        name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>">
				<?php
				foreach ( $languages_list as $key => $label ): ?>
					<option value="<?php echo esc_attr( $key ); ?>"<?php
					if ( in_array($key, $value) ) {
						echo ' selected="selected"';
					}
					?>><?php echo esc_html( $label ); ?></option><?php
				endforeach;
				?>
			</select>

			<?php $this->render_description($config); ?>
		</div>
	<?php
	}

	/**
	 * Font languages to load
	 * @param $value
	 * @param $config
	 */
	public function css_size( $value, $config ) {
		$units = array(
			'px' => 'px',
			'rem' => 'rem',
			'em' => 'em',
			'%' => '%'
		);

		if (isset($config["units"])) {
			$units = array_filter($units, function($value) use ($config) {
				return in_array($value, $config["units"]);
			});
		}

		$default = $config['default'];

		if (is_string($value)) {
			$unit_value = $default['unit'];
			$size_value = $default['size'];

			foreach($units as $unit) {
				if (strpos($value, $unit) !== false) {
					$unit_value = $unit;
					$size_value = (float) str_replace($unit, '', $value);
					break;
				}
			}

			$value = array("unit" => $unit_value, "size" => $size_value);
		}

		$selected_unit = is_array($value) && isset($value["unit"]) ? $value["unit"] : $default['unit'];
		$selected_size = is_array($value) && isset($value["size"]) ? $value["size"] : $default['size'];

		?>
		<div class="mkb-control-wrap fn-control-wrap"
		     data-type="css_size"
		     data-name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			<?php self::maybe_print_dependency_attribute($config); ?>>

			<?php $this->render_label($config); ?>

			<div class="mkb-css-size">

				<input name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
				       class="fn-css-size-store wpb_vc_param_value" type="hidden" value="<?php echo esc_attr( $value['size'] . $value['unit'] ); ?>" />

				<input class="fn-css-size-value fn-control mkb-css-size__input"
				       type="text"
				       id="<?php echo esc_attr( $this->get_id_key( $config ) ); ?>"
				       name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
				       value="<?php echo esc_attr( $selected_size ); ?>" /><?php
				?><ul class="mkb-css-size__units"><?php
					foreach($units as $unit):
						?><li><a href="#" class="fn-css-unit mkb-unstyled-link mkb-css-unit<?php if ($unit === $selected_unit) {
								echo esc_attr(' mkb-css-unit--selected');
							} ?>" data-unit="<?php echo esc_attr($unit); ?>"><?php echo esc_html($unit); ?></a></li><?php
					endforeach;
					?></ul>
				<input class="fn-css-size-unit-value mkb-css-size__unit-input"
				       type="hidden"
				       name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>_unit"
				       value="<?php echo esc_attr( $selected_unit ); ?>" />
			</div>

			<?php $this->render_description($config); ?>
		</div>
	<?php
	}

	public static function css_size_to_string($value) {
		if (is_string($value)) {
			return $value;
		}

		return $value["size"] . $value["unit"];
	}

	protected function demo_import( $value, $config ) {
		?>
		<div class="mkb-control-wrap fn-control-wrap"
		     data-type="demo_import"
		     data-name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			<?php self::maybe_print_dependency_attribute($config); ?>>
			<div class="mkb-settings-demo-import-wrap">
				<h3 class="mkb-demo-import-title"><?php echo esc_html( $this->get_label( $config ) ); ?></h3>
				<div class="mkb-setting-demo-import">
					<div class="mkb-import-options">
						<div class="mkb-control-wrap">
							<div class="mkb-toggle-label">
								<?php esc_html_e('Set imported page as KB home?', 'minerva-kb'); ?>
							</div>
							<div class="mkb-switch">
								<input id="id_import_set_home_page"
								       name="id_import_set_home_page"
								       class="fn-import-set-home-page mkb-toggle mkb-toggle-round"
									<?php echo 'checked="checked"'; ?>
								       type="checkbox" />
								<label for="id_import_set_home_page"></label>
							</div>
						</div>
					</div>
					<div class="mkb-button-group">
						<a href="#" id="mkb-setting-demo-import-run" class="fn-mkb-demo-import mkb-action-button mkb-action-featured"
						   title="<?php esc_attr_e('Import Demo data', 'minerva-kb'); ?>">
							<i class="fa fa-cloud-download"></i>
							<?php _e( 'Import Demo data', 'minerva-kb' ); ?></a>
						<a href="#" id="mkb-setting-demo-import-remove-all"
						   class="fn-demo-import-remove-all mkb-action-button mkb-action-danger<?php
						   if (!MinervaKB_DemoImporter::is_imported() || MinervaKB_DemoImporter::get_entities_total() < 1) {
							   echo esc_attr(' mkb-hidden');
						   }?>"
						   title="<?php esc_attr_e('Remove all imported Demo data', 'minerva-kb'); ?>">
							<i class="fa fa-trash"></i>
							<?php _e( 'Remove all imported Demo data', 'minerva-kb' ); ?></a>
						<?php if (!MinervaKB_DemoImporter::is_skipped()): ?>
							<a href="#" id="mkb-setting-demo-import-cancel" class="fn-mkb-skip-demo-import mkb-action-button"
							   title="<?php esc_attr_e('Skip this', 'minerva-kb'); ?>">
								<i class="fa fa-close"></i>
								<?php echo __( 'Skip this', 'minerva-kb' ); ?></a>
						<?php endif; ?>
					</div>
					<div class="mkb-import-output mkb-hidden fn-import-output">
						<h3><i class="fa fa-check-circle"></i> <?php _e( 'Success! Import completed', 'minerva-kb' ); ?></h3>
						<p><?php _e( 'Import generated the following output:', 'minerva-kb' ); ?></p>
						<code class="mkb-import-output-content fn-import-output-content"></code>
					</div>
					<div class="mkb-import-entities fn-import-entities">
						<?php if(MinervaKB_DemoImporter::is_imported()):?>
							<?php MinervaKB_DemoImporter::entities_html(); ?>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<?php $this->render_description($config); ?>
		</div>
	<?php
	}

	public function articles_list($value, $config) {

		$related = $value;

		if ($related !== '') {
			$related = explode(',', $related);
		}

		?>
		<div class="mkb-control-wrap fn-control-wrap"
	        data-type="articles_list"
	        data-name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			<?php self::maybe_print_dependency_attribute($config); ?>>
			<div class="mkb-related-articles fn-related-articles">
				<input name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
				       class="fn-related-articles-value wpb_vc_param_value" type="hidden" value="<?php echo esc_attr( $value ); ?>" />
			<?php
				if ($related && is_array($related) && !empty($related)):

					$query_args = array(
						'post_type' => MKB_Options::option( 'article_cpt' ),
						'posts_per_page' => -1
					);

					$articles_loop = new WP_Query( $query_args );

					$articles_list = array();

					if ( $articles_loop->have_posts() ) :
						while ( $articles_loop->have_posts() ) : $articles_loop->the_post();
							array_push( $articles_list, array(
								"title"  => get_the_title(),
								"id"   => get_the_ID()
							) );
						endwhile;
					endif;
					wp_reset_postdata();

					foreach($related as $article_id):
						?>
						<div class="mkb-related-articles__item fn-related-article-item">
							<select class="mkb-related-articles__select fn-related-article-item-select wpb_vc_param_value" name="mkb_related_articles[]">
								<?php foreach($articles_list as $article): ?>
									<option value="<?php echo esc_attr($article["id"]); ?>"<?php if ($article["id"] == $article_id) {
										echo ' selected="selected"';
									}?>><?php echo esc_html($article["title"]); ?></option>
								<?php endforeach; ?>
							</select>
							<a class="mkb-related-articles__item-remove fn-related-remove mkb-unstyled-link" href="#">
								<i class="fa fa-close"></i>
							</a>
						</div>
					<?php
					endforeach;
				else:
					?>
					<div class="fn-no-related-message mkb-no-related-message">
						<p><?php esc_html_e('No related articles selected', 'minerva-kb'); ?></p>
					</div>
				<?php
				endif;
				?>
			</div>
			<div class="mkb-related-actions">
				<a href="#"
				   data-id="<?php echo esc_attr(get_the_ID()); ?>"
				   class="fn-related-article-add button button-primary button-large"
				   title="<?php esc_attr_e('Add article', 'minerva-kb'); ?>">
					<?php esc_html_e('Add article', 'minerva-kb'); ?>
				</a>
			</div>
		</div>
	<?php
	}

	/**
	 * Control warning
	 * @param $value
	 * @param $config
	 */
	protected function warning( $value, $config ) {
		if (isset($config['show_if']) && !$config['show_if']) {
			return;
		}

		?>
		<div class="mkb-control-wrap"
		     data-type="warning"
		     data-name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			<?php self::maybe_print_dependency_attribute($config); ?>>
			<div class="mkb-control-note mkb-control-note--warning">
				<i class="mkb-control-note__icon fa fa-exclamation-triangle"></i>
				<span class="mkb-control-note__label"><?php echo esc_html( $this->get_label( $config ) ); ?></span>
			</div>
		</div>
	<?php
	}


	/**
	 * Control info
	 * @param $value
	 * @param $config
	 */
	protected function info( $value, $config ) {
		if (isset($config['show_if']) && !$config['show_if']) {
			return;
		}

		?>
		<div class="mkb-control-wrap"
		     data-type="info"
		     data-name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			<?php self::maybe_print_dependency_attribute($config); ?>>
			<div class="mkb-control-note mkb-control-note--info">
				<i class="mkb-control-note__icon fa fa-info-circle"></i>
				<span class="mkb-control-note__label"><?php echo $this->get_label( $config ); ?></span>
			</div>
		</div>
	<?php
	}

	/**
	 * Export
	 * @param $value
	 * @param $config
	 */
	protected function export( $value, $config ) {

		?>
		<div class="mkb-control-wrap fn-control-wrap"
		     data-type="export"
		     data-name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			<?php self::maybe_print_dependency_attribute($config); ?>>

			<?php $this->render_label($config); ?>
			<div class="fn-mkb-settings-export-container">
				<textarea class="fn-mkb-export-json-control mkb-json-textarea mkb-json-textarea--export"
				          name="mkb_export_control" cols="30" rows="20" readonly><?php
					echo json_encode(MKB_Options::get(), JSON_PRETTY_PRINT);
					?></textarea>

				<p>
					<a href="data:application/json;charset=utf-8,<?php echo rawurlencode(json_encode(MKB_Options::get(), JSON_PRETTY_PRINT)); ?>"
					   download="minerva-kb-export-<?php esc_attr_e(date('m-d-Y_his')); ?>.json"
					   class="fn-mkb-settings-export-download mkb-action-button"
					   title="<?php esc_attr_e('Download JSON file', 'minerva-kb'); ?>">
						<i class="fa fa-cloud-download"></i>
						<?php echo __('Download JSON file', 'minerva-kb'); ?></a>
				</p>
			</div>

			<?php $this->render_description($config); ?>
		</div>
	<?php
	}

	/**
	 * Import
	 * @param $value
	 * @param $config
	 */
	protected function import( $value, $config ) {

		?>
		<div class="mkb-control-wrap fn-control-wrap"
		     data-type="import"
		     data-name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			<?php self::maybe_print_dependency_attribute($config); ?>>

			<?php $this->render_label($config); ?>

			<div class="fn-mkb-settings-import-container">
				<textarea class="fn-mkb-settings-import-control mkb-json-textarea mkb-json-textarea--import" name="mkb_import_control" cols="30" rows="20"></textarea>

				<p>
					<?php echo __( 'Or select saved JSON file:', 'minerva-kb' ); ?>
					<input type="file"
					       class="fn-mkb-settings-import-upload"
					       title="<?php esc_attr_e('Upload JSON file', 'minerva-kb'); ?>" />
				</p>
				<p>
					<a href="#"
					   class="fn-mkb-settings-import-upload-btn mkb-action-button mkb-disabled"
					   title="<?php esc_attr_e('Apply imported settings', 'minerva-kb'); ?>">
						<i class="fa fa-cloud-upload"></i>
						<?php echo __( 'Apply imported settings', 'minerva-kb' ); ?></a>
				</p>
			</div>

			<?php $this->render_description($config); ?>
		</div>
	<?php
	}

	/**
	 * Envato verify
	 * @param $value
	 * @param $config
	 */
	protected function envato_verify( $value, $config ) {

		?>
		<div class="mkb-control-wrap fn-control-wrap"
		     data-type="envato_verify"
		     data-name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
			<?php self::maybe_print_dependency_attribute($config); ?>>

			<?php $this->render_label($config); ?>
			<div class="fn-mkb-envato-verify-container">
				<input type="text"
				       placeholder="<?php esc_attr_e(__('Purchase Code', 'minerva-kb')); ?>"
				       class="fn-mkb-envato-verify-control fn-control mkb-envato-verify-control"
				       id="<?php echo esc_attr( $this->get_id_key( $config ) ); ?>"
				       name="<?php echo esc_attr( $this->get_name_key( $config ) ); ?>"
				       value="<?php echo esc_attr( $value ); ?>"
					/>

				<p>
					<a href="#"
					   class="fn-mkb-envato-verify-submit mkb-action-button"
					   title="<?php esc_attr_e('Verify Purchase', 'minerva-kb'); ?>">
						<i class="fa fa-registered"></i>
						<?php echo __('Verify Purchase', 'minerva-kb'); ?></a>
				</p>
			</div>

			<?php $this->render_description($config); ?>
		</div>
	<?php
	}
}