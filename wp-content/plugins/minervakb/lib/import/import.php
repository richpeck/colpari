<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MinervaKB_DemoImporter {

	public function __constructor() {}

	public static function run_import( $options ) {

		$config = wp_parse_args(
			$options,
			array(
				"set_home_page" => true
			)
		);

		require_once( MINERVA_KB_PLUGIN_DIR . 'lib/import/includes/wordpress-importer.php' );
		$import = new KST_WP_Import();
		$imported_entries = $import->import(MINERVA_KB_PLUGIN_DIR . 'lib/import/demo.xml');
		update_option('mkb_demo_import_completed', true);
		update_option('mkb_demo_imported_entries', $imported_entries);

		$home_page = get_page_by_title( 'Knowledge Base' );

		if ($home_page && isset($home_page->ID) && $config['set_home_page']) {
			MKB_Options::save_option('kb_page', $home_page->ID);
		}

		return $imported_entries;
	}

	public static function skip_import() {
		update_option('mkb_demo_import_skipped', true);
	}

	public static function is_skipped() {
		return (bool)get_option('mkb_demo_import_skipped');
	}

	public static function is_imported() {
		return (bool)get_option('mkb_demo_import_completed');
	}

	public static function entities_html($entries = array()) {
		if (empty($entries)) {
			$entries = get_option('mkb_demo_imported_entries');
		}

		$entries = $entries ? $entries : array();
		?>
		<?php if (isset($entries['articles']) && !empty($entries['articles'])): ?>
			<div class="mkb-import-articles fn-import-articles fn-import-entities-group" data-entity-type="articles">
				<h3><?php _e( 'Articles', 'minerva-kb' ); ?> (<span class="mkb-entities-count fn-mkb-entities-count"><?php echo esc_html(sizeof($entries['articles'])); ?></span>)</h3>
				<table class="mkb-entities-table" cellspacing="0" cellpadding="0">
					<tr class="mkb-entities-table__header">
						<th class="mkb-entities-table__header-item">
							<input name="check_me" type="checkbox" />
						</th>
						<th class="mkb-entities-table__header-item"><?php _e( 'Title', 'minerva-kb' ); ?></th>
						<th class="mkb-entities-table__header-item"><?php _e( 'Open', 'minerva-kb' ); ?></th>
					</tr>
					<?php foreach($entries['articles'] as $article_id): ?>
						<tr class="mkb-entities-table__row">
							<td class="mkb-entities-table__row-item">
								<input name="check_me" type="checkbox" data-id="<?php echo esc_attr($article_id); ?>" />
							</td>
							<td class="mkb-entities-table__row-item"><?php echo esc_html(get_the_title($article_id)); ?></td>
							<td class="mkb-entities-table__row-item">
								<a href="<?php echo esc_attr(get_the_permalink($article_id)); ?>" target="_blank">
									<?php _e( 'Open', 'minerva-kb' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
				<div class="mkb-entities-actions">
					<a href="#" id="mkb-import-articles-remove"
					   class="fn-import-entities-remove mkb-import-entities-remove mkb-action-button mkb-action-danger mkb-disabled"
					   title="<?php esc_attr_e('Remove selected articles', 'minerva-kb'); ?>">
						<i class="fa fa-trash-o"></i>
						<?php echo __( 'Remove selected articles', 'minerva-kb' ); ?></a>
				</div>
			</div>
		<?php endif; ?>
		<?php if (isset($entries['topics']) && !empty($entries['topics'])): ?>
			<div class="mkb-import-topics fn-import-topics fn-import-entities-group" data-entity-type="topics">
				<h3><?php _e( 'Topics', 'minerva-kb' ); ?> (<span class="mkb-entities-count fn-mkb-entities-count"><?php echo esc_html(sizeof($entries['topics'])); ?></span>)</h3>
				<table class="mkb-entities-table" cellspacing="0" cellpadding="0">
					<tr class="mkb-entities-table__header">
						<th class="mkb-entities-table__header-item">
							<input name="check_me" type="checkbox" />
						</th>
						<th class="mkb-entities-table__header-item"><?php _e( 'Title', 'minerva-kb' ); ?></th>
						<th class="mkb-entities-table__header-item"><?php _e( 'Open', 'minerva-kb' ); ?></th>
					</tr>
					<?php foreach($entries['topics'] as $topic_id):
						$term = get_term($topic_id, MKB_Options::option('article_cpt_category'));
						if (!$term) {
							continue;
						}
						?>
						<tr class="mkb-entities-table__row">
							<td class="mkb-entities-table__row-item">
								<input name="check_me" type="checkbox" data-id="<?php echo esc_attr($topic_id); ?>" />
							</td>
							<td class="mkb-entities-table__row-item"><?php echo esc_html($term->name); ?></td>
							<td class="mkb-entities-table__row-item">
								<a href="<?php echo esc_attr(get_term_link($term)); ?>" target="_blank">
									<?php _e( 'Open', 'minerva-kb' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
				<div class="mkb-entities-actions">
					<a href="#" id="mkb-import-topics-remove"
					   class="fn-import-entities-remove mkb-import-entities-remove mkb-action-button mkb-action-danger mkb-disabled"
					   title="<?php esc_attr_e( 'Remove selected topics', 'minerva-kb'); ?>">
						<i class="fa fa-trash-o"></i>
						<?php echo __( 'Remove selected topics', 'minerva-kb' ); ?></a>
				</div>
			</div>
		<?php endif; ?>
		<?php if (isset($entries['tags']) && !empty($entries['tags'])): ?>
			<div class="mkb-import-tags fn-import-tags fn-import-entities-group" data-entity-type="tags">
				<h3><?php _e( 'Tags', 'minerva-kb' ); ?> (<span class="mkb-entities-count fn-mkb-entities-count"><?php echo esc_html(sizeof($entries['tags'])); ?></span>)</h3>
				<table class="mkb-entities-table" cellspacing="0" cellpadding="0">
					<tr class="mkb-entities-table__header">
						<th class="mkb-entities-table__header-item">
							<input name="check_me" type="checkbox" />
						</th>
						<th class="mkb-entities-table__header-item"><?php _e( 'Title', 'minerva-kb' ); ?></th>
						<th class="mkb-entities-table__header-item"><?php _e( 'Open', 'minerva-kb' ); ?></th>
					</tr>
					<?php foreach($entries['tags'] as $tag_id):
						$term = get_term($tag_id, MKB_Options::option('article_cpt_tag'));
						if (!$term) {
							continue;
						}
						?>
						<tr class="mkb-entities-table__row">
							<td class="mkb-entities-table__row-item">
								<input name="check_me" type="checkbox" data-id="<?php echo esc_attr($tag_id); ?>"/>
							</td>
							<td class="mkb-entities-table__row-item"><?php echo esc_html($term->name); ?></td>
							<td class="mkb-entities-table__row-item">
								<a href="<?php echo esc_attr(get_term_link($term)); ?>" target="_blank">
									<?php _e( 'Open', 'minerva-kb' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
				<div class="mkb-entities-actions">
					<a href="#" id="mkb-import-tags-remove"
					   class="fn-import-entities-remove mkb-import-entities-remove mkb-action-button mkb-action-danger mkb-disabled"
					   title="<?php esc_attr_e('Remove selected tags', 'minerva-kb'); ?>">
						<i class="fa fa-trash-o"></i>
						<?php echo __( 'Remove selected tags', 'minerva-kb' ); ?></a>
				</div>
			</div>
		<?php endif; ?>
		<?php if (isset($entries['pages']) && !empty($entries['pages'])): ?>
			<div class="mkb-import-pages fn-import-pages fn-import-entities-group" data-entity-type="pages">
				<h3><?php _e( 'Pages', 'minerva-kb' ); ?> (<span class="mkb-entities-count fn-mkb-entities-count"><?php echo esc_html(sizeof($entries['pages'])); ?></span>)</h3>
				<table class="mkb-entities-table" cellspacing="0" cellpadding="0">
					<tr class="mkb-entities-table__header">
						<th class="mkb-entities-table__header-item">
							<input name="check_me" type="checkbox" />
						</th>
						<th class="mkb-entities-table__header-item"><?php _e( 'Title', 'minerva-kb' ); ?></th>
						<th class="mkb-entities-table__header-item"><?php _e( 'Open', 'minerva-kb' ); ?></th>
					</tr>
					<?php foreach($entries['pages'] as $page_id): ?>
						<tr class="mkb-entities-table__row">
							<td class="mkb-entities-table__row-item">
								<input name="check_me" type="checkbox" data-id="<?php echo esc_attr($page_id); ?>" />
							</td>
							<td class="mkb-entities-table__row-item"><?php echo esc_html(get_the_title($page_id)); ?></td>
							<td class="mkb-entities-table__row-item">
								<a href="<?php echo esc_attr(get_the_permalink($page_id)); ?>" target="_blank">
									<?php _e( 'Open', 'minerva-kb' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
				<div class="mkb-entities-actions">
					<a href="#" id="mkb-import-pages-remove"
					   class="fn-import-entities-remove mkb-import-entities-remove mkb-action-button mkb-action-danger mkb-disabled"
					   title="<?php esc_attr_e('Remove selected pages', 'minerva-kb'); ?>">
						<i class="fa fa-trash-o"></i>
						<?php echo __( 'Remove selected pages', 'minerva-kb' ); ?></a>
				</div>
			</div>
		<?php endif; ?>
		<?php if (isset($entries['attachments']) && !empty($entries['attachments'])): ?>
			<div class="mkb-import-attachments fn-import-attachments fn-import-entities-group" data-entity-type="attachments">
				<h3><?php _e( 'Attachments', 'minerva-kb' ); ?> (<span class="mkb-entities-count fn-mkb-entities-count"><?php echo esc_html(sizeof($entries['attachments'])); ?></span>)</h3>
				<table class="mkb-entities-table" cellspacing="0" cellpadding="0">
					<tr class="mkb-entities-table__header">
						<th class="mkb-entities-table__header-item">
							<input name="check_me" type="checkbox" />
						</th>
						<th class="mkb-entities-table__header-item"><?php _e( 'Title', 'minerva-kb' ); ?></th>
						<th class="mkb-entities-table__header-item"><?php _e( 'Open', 'minerva-kb' ); ?></th>
					</tr>
					<?php foreach($entries['attachments'] as $attachment_id): ?>
						<tr class="mkb-entities-table__row">
							<td class="mkb-entities-table__row-item">
								<input name="check_me" type="checkbox" data-id="<?php echo esc_attr($attachment_id); ?>" />
							</td>
							<td class="mkb-entities-table__row-item"><?php echo esc_html(get_the_title($attachment_id)); ?></td>
							<td class="mkb-entities-table__row-item">
								<a href="<?php echo esc_attr(get_the_permalink($attachment_id)); ?>" target="_blank">
									<?php _e( 'Open', 'minerva-kb' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
				<div class="mkb-entities-actions">
					<a href="#" id="mkb-import-attachments-remove"
					   class="fn-import-entities-remove mkb-import-entities-remove mkb-action-button mkb-action-danger mkb-disabled"
					   title="<?php esc_attr_e('Remove selected attachments', 'minerva-kb'); ?>">
						<i class="fa fa-trash-o"></i>
						<?php echo __( 'Remove selected attachments', 'minerva-kb' ); ?></a>
				</div>
			</div>
		<?php endif; ?>
		<?php if (!MKB_Options::option('disable_faq') && isset($entries['faq_items']) && !empty($entries['faq_items'])): ?>
			<div class="mkb-import-faq-items fn-import-faq-items fn-import-entities-group" data-entity-type="faq_items">
				<h3><?php _e( 'FAQ questions', 'minerva-kb' ); ?> (<span class="mkb-entities-count fn-mkb-entities-count"><?php echo esc_html(sizeof($entries['faq_items'])); ?></span>)</h3>
				<table class="mkb-entities-table" cellspacing="0" cellpadding="0">
					<tr class="mkb-entities-table__header">
						<th class="mkb-entities-table__header-item">
							<input name="check_me" type="checkbox" />
						</th>
						<th class="mkb-entities-table__header-item"><?php _e( 'Title', 'minerva-kb' ); ?></th>
						<th class="mkb-entities-table__header-item"><?php _e( 'Open', 'minerva-kb' ); ?></th>
					</tr>
					<?php foreach($entries['faq_items'] as $faq_id): ?>
						<tr class="mkb-entities-table__row">
							<td class="mkb-entities-table__row-item">
								<input name="check_me" type="checkbox" data-id="<?php echo esc_attr($faq_id); ?>" />
							</td>
							<td class="mkb-entities-table__row-item"><?php echo esc_html(get_the_title($faq_id)); ?></td>
							<td class="mkb-entities-table__row-item">
								<a href="<?php echo esc_attr(get_the_permalink($faq_id)); ?>" target="_blank">
									<?php _e( 'Open', 'minerva-kb' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
				<div class="mkb-entities-actions">
					<a href="#" id="mkb-import-faq-items-remove"
					   class="fn-import-entities-remove mkb-import-entities-remove mkb-action-button mkb-action-danger mkb-disabled"
					   title="<?php esc_attr_e('Remove selected questions', 'minerva-kb'); ?>">
						<i class="fa fa-trash-o"></i>
						<?php echo __( 'Remove selected questions', 'minerva-kb' ); ?></a>
				</div>
			</div>
		<?php endif; ?>
		<?php if (!MKB_Options::option('disable_faq') && isset($entries['faq_categories']) && !empty($entries['faq_categories'])): ?>
			<div class="mkb-import-faq-categories fn-import-faq-categories fn-import-entities-group" data-entity-type="faq_categories">
				<h3><?php _e( 'FAQ categories', 'minerva-kb' ); ?> (<span class="mkb-entities-count fn-mkb-entities-count"><?php echo esc_html(sizeof($entries['faq_categories'])); ?></span>)</h3>
				<table class="mkb-entities-table" cellspacing="0" cellpadding="0">
					<tr class="mkb-entities-table__header">
						<th class="mkb-entities-table__header-item">
							<input name="check_me" type="checkbox" />
						</th>
						<th class="mkb-entities-table__header-item"><?php _e( 'Title', 'minerva-kb' ); ?></th>
						<th class="mkb-entities-table__header-item"><?php _e( 'Open', 'minerva-kb' ); ?></th>
					</tr>
					<?php foreach($entries['faq_categories'] as $faq_category_id):
						$term = get_term($faq_category_id, 'mkb_faq_category');
						if (!$term) {
							continue;
						}
						?>
						<tr class="mkb-entities-table__row">
							<td class="mkb-entities-table__row-item">
								<input name="check_me" type="checkbox" data-id="<?php echo esc_attr($faq_category_id); ?>" />
							</td>
							<td class="mkb-entities-table__row-item"><?php echo esc_html($term->name); ?></td>
							<td class="mkb-entities-table__row-item">
								<a href="<?php echo esc_attr(get_term_link($term)); ?>" target="_blank">
									<?php _e( 'Open', 'minerva-kb' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
				<div class="mkb-entities-actions">
					<a href="#" id="mkb-import-faq-categories-remove"
					   class="fn-import-entities-remove mkb-import-entities-remove mkb-action-button mkb-action-danger mkb-disabled"
					   title="<?php esc_attr_e('Remove selected FAQ categories', 'minerva-kb'); ?>">
						<i class="fa fa-trash-o"></i>
						<?php echo __( 'Remove selected FAQ categories', 'minerva-kb' ); ?></a>
				</div>
			</div>
		<?php endif; ?>
	<?php
	}

	public static function get_entities_html($entries = array()) {
		if (empty($entries)) {
			$entries = get_option('mkb_demo_imported_entries');
		}
		ob_start();
		self::entities_html($entries);
		return ob_get_clean();
	}

	public static function remove_import_entities($type, $ids) {
		$entries = get_option('mkb_demo_imported_entries');

		if (!$entries || !$type || empty($ids) || !isset($entries[$type])) {
			return 1;
		}

		foreach ($ids as $id) {
			if (($key = array_search($id, $entries[$type])) !== false) {

				switch($type) {
					case 'articles':
						if (get_post_type($id) === MKB_Options::option('article_cpt')) { // only remove KB posts
							wp_delete_post($id);
						}
						break;

					case 'pages':
						if (get_post_type($id) === 'page') { // only remove pages
							wp_delete_post($id, true);
						}
						break;

					case 'attachments':
						if (get_post_type($id) === 'attachment') { // only remove attachments
							wp_delete_post($id);
						}
						break;

					case 'topics':
						wp_delete_term( $id, MKB_Options::option('article_cpt_category'));
						break;

					case 'tags':
						wp_delete_term( $id, MKB_Options::option('article_cpt_tag'));
						break;

					case 'faq_items':
						if (get_post_type($id) === 'mkb_faq') { // only remove FAQ posts
							wp_delete_post($id);
						}
						break;

					case 'faq_categories':
						wp_delete_term( $id, 'mkb_faq_category');
						break;

					default:
						break;
				}
				// delete here
				unset($entries[$type][$key]);
			}
		}

		update_option('mkb_demo_imported_entries', $entries);

		return 0;
	}

	/**
	 * Removes all imported entries
	 * @return int
	 */
	public static function remove_all_import_entities() {
		$entries = get_option('mkb_demo_imported_entries');

		if (!$entries) {
			return 1;
		}

		if (isset($entries['articles']) && !empty($entries['articles'])) {
			foreach($entries['articles'] as $id) {
				if (get_post_type($id) === MKB_Options::option('article_cpt')) { // only remove KB posts
					wp_delete_post($id);
				}
			}
		}

		if (isset($entries['pages']) && !empty($entries['pages'])) {
			foreach($entries['pages'] as $id) {
				if (get_post_type($id) === 'page') { // only remove pages
					wp_delete_post($id, true);
				}
			}
		}

		if (isset($entries['attachments']) && !empty($entries['attachments'])) {
			foreach($entries['attachments'] as $id) {
				if (get_post_type($id) === 'attachment') { // only remove attachments
					wp_delete_post($id);
				}
			}
		}

		if (isset($entries['topics']) && !empty($entries['topics'])) {
			foreach($entries['topics'] as $id) {
				wp_delete_term( $id, MKB_Options::option('article_cpt_category'));
			}
		}

		if (isset($entries['tags']) && !empty($entries['tags'])) {
			foreach($entries['tags'] as $id) {
				wp_delete_term( $id, MKB_Options::option('article_cpt_tag'));
			}
		}

		if (!MKB_Options::option('disable_faq') && isset($entries['faq_items']) && !empty($entries['faq_items'])) {
			foreach($entries['faq_items'] as $id) {
				if (get_post_type($id) === 'mkb_faq') { // only remove FAQ posts
					wp_delete_post($id);
				}
			}
		}

		if (!MKB_Options::option('disable_faq') && isset($entries['faq_categories']) && !empty($entries['faq_categories'])) {
			foreach($entries['faq_categories'] as $id) {
				wp_delete_term( $id, 'mkb_faq_category');
			}
		}

		update_option('mkb_demo_imported_entries', '');

		return 0;
	}

	public static function get_entities_total() {
		$entries = get_option('mkb_demo_imported_entries');

		if (!$entries) {
			return 0;
		}

		return sizeof($entries['articles']) +
		       sizeof($entries['pages']) +
		       sizeof($entries['attachments']) +
		       sizeof($entries['topics']) +
		       sizeof($entries['tags']) +
		       (isset($entries['faq_items']) ? sizeof($entries['faq_items']) : 0) +
		       (isset($entries['faq_categories']) ? sizeof($entries['faq_categories']) : 0);
	}

	public static function remove_data() {
		self::remove_all_import_entities();

		delete_option('mkb_demo_imported_entries');
		delete_option('mkb_demo_import_completed');
		delete_option('mkb_demo_import_skipped');
	}
}
