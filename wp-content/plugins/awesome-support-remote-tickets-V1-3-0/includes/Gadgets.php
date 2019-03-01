<?php

namespace WPAS_Remote_Tickets;

class Gadgets {

	/**
	 * @var
	 */
	protected static $_instance;

	/**
	 * @var string
	 */
	public static $_prefix = '_wpas_';

	/**
	 * Only make one instance of \RCP_Avatax\Gadgets
	 *
	 * @return Gadgets
	 */
	public static function get_instance() {
		if ( ! self::$_instance instanceof Gadgets ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	protected function __construct() {
		$this->add_hooks();
	}

	/**
	 * Add various hooks.
	 */
	public function add_hooks() {
		add_action( 'init',            array( $this, 'gadget_post_type' ) );
		add_action( 'cmb2_admin_init', array( $this, 'metabox_router' ) );
		add_action( 'cmb2_render_code', array( $this, 'cb_for_code' ), 10, 5 );
	}

	/** Actions **************************************/

	/**
	 * Register Gadget post type
	 */
	public function gadget_post_type() {

		/* Post type labels */
		$labels = apply_filters( 'wpas_remote_tickets_labels', array(
			'name'               => _x( 'Remote Tickets', 'post type general name', 'awesome-support-remote-tickets' ),
			'singular_name'      => _x( 'Remote Ticket', 'post type singular name', 'awesome-support-remote-tickets' ),
			'menu_name'          => _x( 'Remote Tickets', 'admin menu', 'awesome-support-remote-tickets' ),
			'name_admin_bar'     => _x( 'Remote Ticket', 'add new on admin bar', 'awesome-support-remote-tickets' ),
			'add_new'            => _x( 'Add New', 'gadget', 'awesome-support-remote-tickets' ),
			'add_new_item'       => __( 'Add New Remote Ticket', 'awesome-support-remote-tickets' ),
			'new_item'           => __( 'New Remote Ticket', 'awesome-support-remote-tickets' ),
			'edit_item'          => __( 'Edit Remote Ticket', 'awesome-support-remote-tickets' ),
			'view_item'          => __( 'View Remote Ticket', 'awesome-support-remote-tickets' ),
			'all_items'          => __( 'Remote Tickets', 'awesome-support-remote-tickets' ),
			'search_items'       => __( 'Search Remote Tickets', 'awesome-support-remote-tickets' ),
			'parent_item_colon'  => __( 'Parent Remote Ticket:', 'awesome-support-remote-tickets' ),
			'not_found'          => __( 'No Remote Tickets found.', 'awesome-support-remote-tickets' ),
			'not_found_in_trash' => __( 'No Remote Tickets found in Trash.', 'awesome-support-remote-tickets' ),
		) );

		/* Post type capabilities */
		$cap = apply_filters( 'wpas_remote_tickets_type_cap', array(
			'read'                   => 'view_ticket',
			'read_post'              => 'view_ticket',
			'read_private_posts'     => 'view_private_ticket',
			'edit_post'              => 'edit_ticket',
			'edit_posts'             => 'edit_ticket',
			'edit_others_posts'      => 'edit_other_ticket',
			'edit_private_posts'     => 'edit_private_ticket',
			'edit_published_posts'   => 'edit_ticket',
			'publish_posts'          => 'create_ticket',
			'delete_post'            => 'delete_ticket',
			'delete_posts'           => 'delete_ticket',
			'delete_private_posts'   => 'delete_private_ticket',
			'delete_published_posts' => 'delete_ticket',
			'delete_others_posts'    => 'delete_other_ticket'
		) );

		/* Post type arguments */
		$args = apply_filters( 'wpas_remote_tickets_type_args', array(
			'labels'              => $labels,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=ticket',
			'capability_type'     => 'administer_awesome_support',
			'capabilities'        => $cap,
			'hierarchical'        => false,
			'menu_position'       => null,
			'supports'            => array( 'title' ),
			'show_in_rest'        => true,
			'rest_base'           => 'remote-tickets',
			'rest_controller_class' =>  'WPAS_Remote_Tickets\API\Gadgets'
		) );

		register_post_type( 'wpas_gadget', $args );
	}

	/**
	 * Router for metaboxes
	 */
	public function metabox_router() {
		$this->general_settings();
		$this->code();
		$this->button_settings();
		$this->form_settings();
		$this->labels();
	}

	/**
	 * Build the data array
	 *
	 * @param null $id
	 *
	 *
	 * @since  1.0.1
	 *
	 * @return array
	 */
	public static function get_data( $id = null ) {
		$options = maybe_unserialize( get_option( 'wpas_options', array() ) );
		$data    = array();

		if ( ! $id && isset( $_GET['post'] ) ) {
			$id = $_GET['post'];
		} elseif ( ! $id ) {
			return $data;
		}

		$meta = get_post_meta( $id );

		if ( empty( $options[ 'support_products' ] ) || true !== boolval( $options[ 'support_products' ] ) || isset( $meta[ self::$_prefix . 'hide_ticket_product'] ) ) {
			unset( $meta[ self::$_prefix . 'label_product'] );
		}

		if ( empty( $options['departments'] ) || true !== boolval( $options['departments'] ) || isset( $meta[ self::$_prefix . 'hide_ticket_department'] ) ) {
			unset( $meta[ self::$_prefix . 'label_department'] );
		}

		if ( empty( $options['support_priority'] ) || true !== boolval( $options['support_priority'] ) || isset( $meta[ self::$_prefix . 'hide_ticket_priority'] ) ) {
			unset( $meta[ self::$_prefix . 'label_ticket_priority'] );
		}
	
		if ( empty( $meta[ self::$_prefix . 'disable_authentication'] ) ) {
			unset( $meta[ self::$_prefix . 'label_first_name'] );
			unset( $meta[ self::$_prefix . 'label_last_name'] );
		} else {
			unset( $meta[ self::$_prefix . 'label_password'] );
		}

		unset( $meta[ self::$_prefix . 'disable_authentication'] );
		
		// this shouldn't ever happen, but it's good to be safe.
		if ( empty( $meta ) || ! is_array( $meta ) ) {
			return $data;
		}

		foreach( $meta as $key => $item ) {
			$item = array_shift( $item );

			if ( strpos( $key, self::$_prefix ) !== 0 ) {
				continue;
			}

			if ( strpos( $key, '_label_' ) ) {
				$key = str_replace( self::$_prefix . 'label_', '', $key );
				$data['labels'][ $key ] = $item;

				continue;
			}

			if ( self::$_prefix . 'pageMatches' == $key ) {
				$item = array_filter( array_map( 'trim', explode( PHP_EOL, $item ) ) );
			}

			$key          = str_replace( self::$_prefix, '', $key );
			$data[ $key ] = $item;
		}

		$data['gadgetID']  = $id;
		$data['errorText'] = __( '%s is a required field.', 'awesome-support-remote-tickets' );
		$data['url']       = get_rest_url();
		$data['cssURL']    = apply_filters( 'wpas_remote_tickets_css_url', AS_RT_URL . 'assets/public/css/public.css', $id );

		return $data;
	}

	/**
	 * Return the script to copy/paste
	 *
	 * @param null $id
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	public static function get_code( $id = null ) {

		if ( ! $id && isset( $_GET['post'] ) ) {
			$id = $_GET['post'];
		} elseif ( ! $id ) {
			return false;
		}

		$data = self::get_data( $id );

		if ( empty( $data ) ) {
			return false;
		}

		if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG  ) {
			$script_url        = apply_filters( 'wpas_remote_tickets_js_url', AS_RT_URL . 'assets/public/js/gadget.js', $id );
		} else {
			$script_url        = apply_filters( 'wpas_remote_tickets_js_url', AS_RT_URL . 'assets/public/js/gadget-dist.js', $id );
		}

		ob_start(); ?>
<!-- begin awesome support code -->
<script type="text/javascript" async>;(function (a, b, c, e, f, g) {if (a.wpasGadget) {return};e = 'script';g = b.createElement(e);e = b.getElementsByTagName(e)[0];g.async = 1;g.src = c;e.parentNode.insertBefore(g, e);})(window, document, '<?php echo $script_url; ?>');var wpasData = <?php echo json_encode( array( 'gadgetID' => $data['gadgetID'], 'url' => $data['url'] ) ); ?></script>
<!-- end awesome support code -->
		<?php

		return ob_get_clean();
	}

	/** Metaboxes ***********************************/
	
	protected function general_settings() {
		$cmb = new_cmb2_box( array(
			'id'           => 'general_settings',
			'title'        => __( 'General Form Settings', 'awesome-support-remote-tickets' ),
			'object_types' => array( 'wpas_gadget', ), // Post type
			'context'      => 'normal',
			'priority'     => 'high',
			'show_names'   => true, // Show field names on the left
		) );
	
		$cmb->add_field( array(
			'name'       => __( 'Disable', 'awesome-support-remote-tickets' ),
			'desc'       => __( 'Check this button to disable the Javascript from executing on the remote client.  The script will still load on the page but will not execute.', 'awesome-support-remote-tickets' ),
			'id'         => self::$_prefix . 'disable',
			'type'       => 'checkbox',
		) );			
	
	}

	protected function code() {

		if ( ! $code = self::get_code() ) {
			return;
		}

		$cmb = new_cmb2_box( array(
			'id'           => 'form_code',
			'title'        => __( 'Code', 'awesome-support-remote-tickets' ),
			'object_types' => array( 'wpas_gadget', ), // Post type
			'context'      => 'normal',
			'priority'     => 'high',
			'show_names'   => true, // Show field names on the left
		) );

		$cmb->add_field( array(
			'name'    => __( 'Form Code', 'awesome-support-remote-tickets' ),
			'desc'    => __( 'Copy and past this at the bottom of your page\'s <body> tag', 'awesome-support-remote-tickets' ),
			'id'      => self::$_prefix . 'code',
			'type'    => 'code',
			'default' => '#cccccc',
		) );
	}

	protected function button_settings() {
		/**
		 * Initiate the metabox
		 */
		$cmb = new_cmb2_box( array(
			'id'           => 'button_settings',
			'title'        => __( 'Button Settings', 'awesome-support-remote-tickets' ),
			'object_types' => array( 'wpas_gadget', ), // Post type
			'context'      => 'normal',
			'priority'     => 'high',
			'show_names'   => true, // Show field names on the left
		) );

		// Regular text field
		$cmb->add_field( array(
			'name'       => __( 'Button Color', 'awesome-support-remote-tickets' ),
			'desc'       => __( 'The color of the form buttons', 'awesome-support-remote-tickets' ),
			'id'         => self::$_prefix . 'buttonColor',
			'type'       => 'colorpicker',
			'default'    => '#1e73be',
		) );

		$cmb->add_field( array(
			'name'       => __( 'Button Text Color', 'awesome-support-remote-tickets' ),
			'desc'       => __( 'The color of the form buttons', 'awesome-support-remote-tickets' ),
			'id'         => self::$_prefix . 'buttonTextColor',
			'type'       => 'colorpicker',
			'default'    => '#ffffff',
		) );

		$cmb->add_field( array(
			'name'       => __( 'Button Position', 'awesome-support-remote-tickets' ),
			'desc'       => __( 'The color of the form buttons', 'awesome-support-remote-tickets' ),
			'id'         => self::$_prefix . 'buttonPosition',
			'type'       => 'radio',
			'default'    => 'right',
			'options'    => array(
				'right' => __( 'Right', 'awesome-support-remote-tickets' ),
				'left'  => __( 'Left', 'awesome-support-remote-tickets' )
			),
		) );

		$cmb->add_field( array(
			'name'       => __( 'Button Text', 'awesome-support-remote-tickets' ),
			'desc'       => __( 'The text to use for the call to action button.', 'awesome-support-remote-tickets' ),
			'id'         => self::$_prefix . 'buttonText',
			'type'       => 'text',
			'default'    => __('Submit Ticket', 'awesome-support-remote-tickets' ),
		) );

		$cmb->add_field( array(
			'name'       => __( 'Button Cancel Text', 'awesome-support-remote-tickets' ),
			'desc'       => __( 'The text to use for the call to action button when the form is active.', 'awesome-support-remote-tickets' ),
			'id'         => self::$_prefix . 'buttonCancelText',
			'type'       => 'text',
			'default'    => __('Cancel Ticket', 'awesome-support-remote-tickets' ),
		) );
		
		$cmb->add_field( array(
			'name'       => __( 'Invisible Button', 'awesome-support-remote-tickets' ),
			'desc'       => __( 'Do not show the button - form will be loaded via some other method.', 'awesome-support-remote-tickets' ),
			'id'         => self::$_prefix . 'invisibleButton',
			'type'       => 'checkbox',
		) );		


	}

	protected function form_settings() {
		/**
		 * Initiate the metabox
		 */
		$cmb = new_cmb2_box( array(
			'id'           => 'form_settings',
			'title'        => __( 'Form Settings', 'awesome-support-remote-tickets' ),
			'object_types' => array( 'wpas_gadget', ), // Post type
			'context'      => 'normal',
			'priority'     => 'high',
			'show_names'   => true, // Show field names on the left
		) );

		// Regular text field
		$cmb->add_field( array(
			'name'       => __( 'Background Color', 'awesome-support-remote-tickets' ),
			'desc'       => __( 'The color of the form', 'awesome-support-remote-tickets' ),
			'id'         => self::$_prefix . 'formBackgroundColor',
			'type'       => 'colorpicker',
			'default'    => '#cccccc',
		) );

		$cmb->add_field( array(
			'name'       => __( 'Success Text', 'awesome-support-remote-tickets' ),
			'desc'       => __( 'The text to show when the ticket has been created successfully, use [ticket-url] to show a link to the newly created ticket.', 'awesome-support-remote-tickets' ),
			'id'         => self::$_prefix . 'successText',
			'type'       => 'textarea',
			'default'    => __('Your ticket was submitted successfully! You can view it here: [ticket-url].', 'awesome-support-remote-tickets' ),
		) );

		$cmb->add_field( array(
			'name'       => __( 'Success Button Text', 'awesome-support-remote-tickets' ),
			'desc'       => __( 'The text to show in the button when the ticket has been created successfully.', 'awesome-support-remote-tickets' ),
			'id'         => self::$_prefix . 'successButtonText',
			'type'       => 'text',
			'default'    => 'Close',
		) );

		$cmb->add_field( array(
			'name'       => __( 'Processing Text', 'awesome-support-remote-tickets' ),
			'desc'       => __( 'The text to show when the form is processing', 'awesome-support-remote-tickets' ),
			'id'         => self::$_prefix . 'processingText',
			'type'       => 'text',
			'default'    => __('Processing ...', 'awesome-support-remote-tickets' ),
		) );

		$cmb->add_field( array(
			'name'       => __( 'Uploading Text', 'awesome-support-remote-tickets' ),
			'desc'       => __( 'The text to show when uploading an image', 'awesome-support-remote-tickets' ),
			'id'         => self::$_prefix . 'uploadingText',
			'type'       => 'text',
			'default'    => __('Uploading image ...', 'awesome-support-remote-tickets' ),
		) );

		$cmb->add_field( array(
			'name'       => __( 'Disable authentication', 'awesome-support-remote-tickets' ),
			'desc'       => __( 'Does the user need to provide his password? If not any new users will automatically be created.', 'awesome-support-remote-tickets' ),
			'id'         => self::$_prefix . 'disable_authentication',
			'type'       => 'checkbox',
		) );

		$cmb->add_field( array(
			'name'       => __( 'Page Display Matches', 'awesome-support-remote-tickets' ),
			'desc'       => __( 'Specify the page paths that should include/exclude this form. Enter one match per line. Matches do not need to be full urls.', 'awesome-support-remote-tickets' ),
			'id'         => self::$_prefix . 'pageMatches',
			'type'       => 'textarea',
		) );

		$cmb->add_field( array(
			'name'       => __( 'Page Display Option', 'awesome-support-remote-tickets' ),
			'desc'       => __( 'Should the form be included or excluded on the matches defined above?', 'awesome-support-remote-tickets' ),
			'id'         => self::$_prefix . 'pageMatchesSetting',
			'type'       => 'radio',
			'default'    => 'include',
			'options'    => array(
				'include' => __( 'Include (show only on the pages listed above)', 'awesome-support-remote-tickets' ),
				'exclude' => __( 'Exclude (show on all pages except the ones listed above)', 'awesome-support-remote-tickets' )
			),
		) );
		
		$cmb->add_field( array(
			'name'       => __( 'Left Form Header Text', 'awesome-support-remote-tickets' ),
			'desc'       => __( 'Specify the text that should go at the top of the first column of the form.  This is optional.  For example you can you can use this for instructions or logos.', 'awesome-support-remote-tickets' ),
			'id'         => self::$_prefix . 'leftHeaderText',
			'type'       => 'wysiwyg',
		) );
		
		$cmb->add_field( array(
			'name'       => __( 'Right Form Header Text', 'awesome-support-remote-tickets' ),
			'desc'       => __( 'Specify the text that should go at the top of the second column of the form.  This is optional.  For example you can you can use this for instructions or logos.', 'awesome-support-remote-tickets' ),
			'id'         => self::$_prefix . 'rightHeaderText',
			'type'       => 'wysiwyg',
		) );		
		
		$cmb->add_field( array(
			'name'       => __( 'Form Pre-Footer Text', 'awesome-support-remote-tickets' ),
			'desc'       => __( 'Specify the text that should go at the bottom of the form - before the submit/help button.  This is optional.  For example you can you can use this to let the user know what to expect next.', 'awesome-support-remote-tickets' ),
			'id'         => self::$_prefix . 'preFooterText',
			'type'       => 'wysiwyg',
		) );
		
		$cmb->add_field( array(
			'name'       => __( 'Form Footer Text', 'awesome-support-remote-tickets' ),
			'desc'       => __( 'Specify the text that should go at the bottom of the form AFTER the submit/help button.  This is optional.  For example you can you can use this to let the user know what to expect next.', 'awesome-support-remote-tickets' ),
			'id'         => self::$_prefix . 'footerText',
			'type'       => 'wysiwyg',
		) );
		
		$cmb->add_field( array(
			'name'       => __( 'Show Terms of Service Checkbox', 'awesome-support-remote-tickets' ),
			'desc'       => __( 'If you would like the user to agree to your terms of service before submitting a ticket then enable this option.', 'awesome-support-remote-tickets' ),
			'id'         => self::$_prefix . 'enableTos',
			'type'       => 'checkbox',
		) );
		
		$cmb->add_field( array(
			'name'       => __( 'Terms of Service Text', 'awesome-support-remote-tickets' ),
			'desc'       => __( 'The text to show when the terms of service checkbox above is enabled. Keep it brief to avoid spilling over to multiple lines!', 'awesome-support-remote-tickets' ),
			'id'         => self::$_prefix . 'tosText',
			'type'       => 'text',
			'default'    => __( 'Yes, I agree to your terms of service', 'awesome-support-remote-tickets' ),
		) );		
		
	}

	protected function labels() {
		$options = maybe_unserialize( get_option( 'wpas_options', array() ) );

		/**
		 * Initiate the metabox
		 */
		$cmb = new_cmb2_box( array(
			'id'           => 'labels',
			'title'        => __( 'Field Labels', 'awesome-support-remote-tickets' ),
			'object_types' => array( 'wpas_gadget', ), // Post type
			'context'      => 'normal',
			'priority'     => 'high',
			'show_names'   => true, // Show field names on the left
		) );

		$cmb->add_field( array(
			'name'       => __( 'First Name', 'awesome-support-remote-tickets' ),
			'desc'       => __( 'The label for the First Name field. Used if authentication is not required.', 'awesome-support-remote-tickets' ),
			'id'         => self::$_prefix . 'label_first_name',
			'type'       => 'text',
			'default'    => 'First Name',
		) );

		$cmb->add_field( array(
			'name'       => __( 'Last Name', 'awesome-support-remote-tickets' ),
			'desc'       => __( 'The label for the Last Name field. Used if authentication is not required.', 'awesome-support-remote-tickets' ),
			'id'         => self::$_prefix . 'label_last_name',
			'type'       => 'text',
			'default'    => 'Last Name',
		) );

		$cmb->add_field( array(
			'name'       => __( 'Email', 'awesome-support-remote-tickets' ),
			'desc'       => __( 'The label for the Email field', 'awesome-support-remote-tickets' ),
			'id'         => self::$_prefix . 'label_email',
			'type'       => 'text',
			'default'    => 'Email',
		) );

		$cmb->add_field( array(
			'name'       => __( 'Password', 'awesome-support-remote-tickets' ),
			'desc'       => __( 'The label for the Password field. Used if authentication is required.', 'awesome-support-remote-tickets' ),
			'id'         => self::$_prefix . 'label_password',
			'type'       => 'text',
			'default'    => 'Password',
		) );

		$cmb->add_field( array(
			'name'       => __( 'Subject', 'awesome-support-remote-tickets' ),
			'desc'       => __( 'The label for the ticket Subject field.', 'awesome-support-remote-tickets' ),
			'id'         => self::$_prefix . 'label_title',
			'type'       => 'text',
			'default'    => 'Subject',
		) );

		if ( isset( $options[ 'support_products' ] ) && true === boolval( $options[ 'support_products' ] ) ) {
			$cmb->add_field( array(
				'name'    => __( 'Product', 'awesome-support-remote-tickets' ),
				'desc'    => __( 'The label for the ticket Product field.', 'awesome-support-remote-tickets' ),
				'id'      => self::$_prefix . 'label_product',
				'type'    => 'text',
				'default' => 'Product',
			) );

			$cmb->add_field( array(
				'name'    => __( 'Disable Product', 'awesome-support-remote-tickets' ),
				'desc'    => __( 'Do not include the Product field.', 'awesome-support-remote-tickets' ),
				'id'      => self::$_prefix . 'hide_ticket_product',
				'type'    => 'checkbox',
			) );
		}

		if ( isset( $options['departments'] ) && true === boolval( $options['departments'] ) ) {
			$cmb->add_field( array(
				'name'    => __( 'Department', 'awesome-support-remote-tickets' ),
				'desc'    => __( 'The label for the ticket Department field.', 'awesome-support-remote-tickets' ),
				'id'      => self::$_prefix . 'label_department',
				'type'    => 'text',
				'default' => 'Department',
			) );

			$cmb->add_field( array(
				'name'    => __( 'Disable Department', 'awesome-support-remote-tickets' ),
				'desc'    => __( 'Do not include the Department field.', 'awesome-support-remote-tickets' ),
				'id'      => self::$_prefix . 'hide_ticket_department',
				'type'    => 'checkbox',
			) );
		}

		if ( isset( $options['support_priority'] ) && true === boolval( $options['support_priority'] ) ) {
			$cmb->add_field( array(
				'name'    => __( 'Priority', 'awesome-support-remote-tickets' ),
				'desc'    => __( 'The label for the ticket Priority field.', 'awesome-support-remote-tickets' ),
				'id'      => self::$_prefix . 'label_ticket_priority',
				'type'    => 'text',
				'default' => 'Priority',
			) );

			$cmb->add_field( array(
				'name'    => __( 'Disable Priority', 'awesome-support-remote-tickets' ),
				'desc'    => __( 'Do not include the Priority field.', 'awesome-support-remote-tickets' ),
				'id'      => self::$_prefix . 'hide_ticket_priority',
				'type'    => 'checkbox',
			) );
		}

		$cmb->add_field( array(
			'name'       => __( 'Description', 'awesome-support-remote-tickets' ),
			'desc'       => __( 'The label for the ticket Description field.', 'awesome-support-remote-tickets' ),
			'id'         => self::$_prefix . 'label_content',
			'type'       => 'text',
			'default'    => 'Description',
		) );

		$cmb->add_field( array(
			'name'       => __( 'Attachment', 'awesome-support-remote-tickets' ),
			'desc'       => __( 'The label for the ticket Attachment field.', 'awesome-support-remote-tickets' ),
			'id'         => self::$_prefix . 'label_file',
			'type'       => 'text',
			'default'    => 'Attachment',
		) );

	}

	public function cb_for_code( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
		if ( ! $code = self::get_code() ) {
			return;
		}

		ob_start();
		?>
		<textarea onclick="this.select()" rows="10"><?php echo $code; ?></textarea>
		<?php
		echo ob_get_clean();
	}

}