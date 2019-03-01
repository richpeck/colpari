<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NF_FU_Integrations_NinjaForms_MergeTags {

	/**
	 * NF_FU_Integrations_NinjaForms_MergeTags constructor.
	 */
	public function __construct() {
		add_filter( 'ninja_forms_merge_tag_value_' . NF_FU_File_Uploads::TYPE, array( $this, 'merge_tag_value' ), 10, 2 );
		add_filter( 'ninja_forms_submission_actions', array( $this, 'update_all_mergetags' ), 10, 3 );
		add_action( 'ninja_forms_uploads_external_action_post_process', array( $this, 'update_mergetags_for_external' ), 10, 2 );
	}

	/**
	 * Update all mergetags with cleaned values.
	 *
	 * @param array $actions
	 *
	 * @return array
	 */
	public function update_all_mergetags( $actions, $form_cache, $form_data ) {
		if ( ! isset( $form_data['fields'] ) ) {
			return $actions;
		}

		foreach ( $form_data['fields'] as $field ) {
			if ( NF_FU_File_Uploads::TYPE !== $field['type'] ) {
				continue;
			}

			if ( ! isset( $field['files'] ) || empty( $field['files'] ) ) {
				continue;
			}

			// Update Mergetags
			$this->update_mergetags( $field, $this->get_default_tags() );
		}

		return $actions;
	}

	/**
	 * Format the file URLs to links using the filename as link text
	 *
	 * @param string $value
	 * @param array  $field
	 *
	 * @return string
	 */
	public function merge_tag_value( $value, $field ) {
		if ( is_null( $value ) ) {
			return $value;
		}

		if ( ! isset( $field['files'] ) || empty( $field['files'] ) ) {
			return '';
		}

		$values = $this->get_values( $field );

		return $values['html'];
	}

	/**
	 * @return array
	 */
	protected function get_default_tags() {
		return array(
			'default' => 'html',
			'plain'   => 'plain',
			'embed'   => 'embed',
			'link'    => 'link',
			'url'     => 'url',
		);
	}
	/**
	 * Update mergetag(s) value
	 *
	 * @param array $field
	 * @param array $tags Array keyed on field suffix ('default' for normal field), and value as the type of value, eg.
	 *                    html or plain
	 */
	protected function update_mergetags( $field, $tags = array() ) {
		$all_merge_tags = Ninja_Forms()->merge_tags;

		if ( ! isset( $all_merge_tags['fields'] ) ) {
			return;
		}

		$values = $this->get_values( $field );

		$field['value'] = $values['html'];
		$all_merge_tags['fields']->add_field( $field );

		foreach ( $tags as $type => $value_type ) {
			$tag    = '_' . $type;
			$suffix = ':' . $type;
			if ( 'default' === $type ) {
				$tag    = '';
				$suffix = '';
			}

			$value = isset( $values[ $value_type ] ) ? $values[ $value_type ] : $values['plain'];
			$all_merge_tags['fields']->add( 'field_' . $field['key'] . $tag, $field['key'], "{field:{$field['key']}{$suffix}}", $value );
		}

		// Save merge tags
		Ninja_Forms()->merge_tags = $all_merge_tags;
	}

	/**
	 * Get the formatted value sets for the mergetag value.
	 *
	 * @param array $field
	 *
	 * @return array
	 */
	protected function get_values( $field ) {
		$values = array();
		foreach ( $field['files'] as $file ) {
			$upload = NF_File_Uploads()->controllers->uploads->get( $file['data']['upload_id'] );

			if ( false === $upload ) {
				continue;
			}

			$file_url = NF_File_Uploads()->controllers->uploads->get_file_url( $upload->file_url, $upload->data );

			$values['html'][]  = sprintf( '<a href="%s" target="_blank">%s</a>', $file_url, $upload->file_name );
			$values['link'][]  = sprintf( '<a href="%s" target="_blank">%s</a>', $file_url, $upload->file_name );
			$values['embed'][] = sprintf( '<img src="%s">', $file_url );
			$values['url'][]   = $file_url;
			$values['plain'][] = $file_url;
		}

		if ( isset( $values['html'] ) ) {
			$values['html'] = implode( '<br>', $values['html'] );
		}
		if ( isset( $values['link'] ) ) {
			$values['link'] = implode( '<br>', $values['link'] );
		}
		if ( isset( $values['embed'] ) ) {
			$values['embed'] = implode( '<br>', $values['embed'] );
		}

		if ( isset( $values['plain'] ) ) {
			$values['plain'] = implode( ',', $values['plain'] );
		}
		if ( isset( $values['url'] ) ) {
			$values['url'] = implode( ',', $values['url'] );
		}

		return $values;
	}

	/**
	 * Update mergetags with external service URL values
	 *
	 * @param array  $field
	 * @param string $service
	 */
	public function update_mergetags_for_external( $field, $service ) {
		$tags = array(
			$service            => 'html',
			$service . '_plain' => 'plain',
		);

		$tags = array_merge( $tags, $this->get_default_tags() );

		$this->update_mergetags( $field, $tags );
	}
}