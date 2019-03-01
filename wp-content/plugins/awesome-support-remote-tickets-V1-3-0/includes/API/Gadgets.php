<?php

namespace WPAS_Remote_Tickets\API;

use WPAS_Remote_Tickets\Gadgets as Gadget;
use WP_REST_Posts_Controller;
use WP_REST_Post_Meta_Fields;
use WP_REST_Request;

class Gadgets extends WP_REST_Posts_Controller {


	public function __construct( $post_type ) {
		parent::__construct( $post_type );

		$this->meta = new WP_REST_Post_Meta_Fields( $this->post_type );
		$this->namespace = wpas_api()->get_api_namespace();
	}

	/**
	 * Adds the schema from additional fields to a schema array.
	 *
	 * The type of object is inferred from the passed schema.
	 *
	 * @param array $schema Schema array.
	 * @return array Modified Schema array.
	 */
	protected function add_additional_fields_schema( $schema ) {
		$schema['properties']['settings'] = array(
			'description' => __( 'The settings for this remote ticket form.', 'awesome-support-remote-tickets' ),
			'type'        => 'array',
			'context'     => array( 'view', 'edit', 'embed' ),
		);

		return parent::add_additional_fields_schema( $schema );
	}

	/**
	 * Adds the values from additional fields to a data object.
	 *
	 * @param array           $data  Data object.
	 * @param WP_REST_Request $request Full details about the request.
	 * @return array Modified data object with additional fields.
	 */
	protected function add_additional_fields_to_object( $data, $request ) {
		$data['settings'] = Gadget::get_data( $data['id'] );
		$data = parent::add_additional_fields_to_object( $data, $request );
		return $data;
	}

}