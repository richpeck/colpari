<?php

/**
 * @package   Awesome Support Front-end agents addon
 * @author    Ante Laca <ante.laca@gmail.com>
 * @link      https://antelaca.xyz
 * @copyright 2018 Awesome Support
 */

class AS_Frontend_Agents {

    protected static $instance;  // instance
    public $shortcode = 'front-end-agent';  // shortcode
    public $dir_path;  // addon directory path
    public $url_path;  // addon url path

	/**
     * Get instance of AS_Frontend_Agents
     *
     * @return AS_Frontend_Agents
     */
	public static function get_instance () {

		if ( is_null( static::$instance ) ) {
			static::$instance = new self();
		}

		return static::$instance;
    }
    

    private function __construct () {
        // set vars
        $loader          = AS_Frontend_Agents_Loader::get_instance();
        $this->dir_path  = $loader->getAddonPath();
        $this->url_path  = $loader->getAddonUrl();

        // include functions file to get wpas_is_reply_needed function 
        if ( ! function_exists('wpas_is_reply_needed') ) {
            require_once ( WPAS_PATH . 'includes/admin/functions-misc.php' );
        }

    }


	/**
	 * Run the plugin
	 *
	 * @return void
	 */
	public function run () {
		return is_admin() ? $this->backend() : $this->frontend();
    }
    

	/**
	 * Frontend part
	 */
	private function frontend () {

        // Check if Front-end Agent is enabled
        if ( boolval( wpas_get_option( 'use_frontend_agents' ) ) ) {       
            // load required assets
            add_action( 'wp_enqueue_scripts', [ $this, 'loadAssets' ] );

            // check device
            if ( wp_is_mobile() or isset( $_GET['fa-mobile']) ) {

                if ( boolval( wpas_get_option( 'clean_template_mobile' ) ) ) {
                    // hook up
                    add_filter('template_include', [ $this, 'cleanTemplate'], 100000); 
    
                } 

            } else {

                if ( boolval( wpas_get_option( 'clean_template_desktop' ) ) ) {
                    // hook up
                    add_filter('template_include', [ $this, 'cleanTemplate'], 100000); 
                }

            }

            // register shortcode
            add_shortcode( $this->shortcode, [ $this, 'frontendAgent' ] );

        } else {
            // remove shortcode from content if add-on is not used
            add_filter( 'the_content', function ($content) {
                return str_replace( '[' . $this->shortcode . ']', '', $content );
            } );
        }

    }
    

	/**
	 * Backend part
	 */
	private function backend () {

		if ( current_user_can( 'administrator' ) or wpas_is_agent() ) {

            // load admin css
            add_action( 'admin_init', [ $this, 'loadAssetsAdmin' ] );
			// add tab Front-end Agent to AS settings options
            add_filter( 'wpas_plugin_settings', [ $this, 'addSettingsOptions' ] );
            //  ajax view ticket
            add_action( 'wp_ajax_view_ticket', [ $this, 'ajaxViewTicket' ] );
            // ajax view closed tickets
            add_action( 'wp_ajax_view_closed_tickets', [ $this, 'ajaxViewClosedTickets' ] );
            // ajax open ticket 
            add_action( 'wp_ajax_open_ticket', [ $this, 'ajaxOpenTicket' ] );
            // ajax close  ticket 
            add_action( 'wp_ajax_close_ticket', [ $this, 'ajaxCloseTicket' ] );
            // ajax open ticket mobile 
            add_action( 'wp_ajax_open_ticket_mobile', [ $this, 'ajaxOpenTicketMobile' ] );
            // ajax close ticket mobile 
            add_action( 'wp_ajax_close_ticket_mobile', [ $this, 'ajaxCloseTicketMobile' ] );
            // ajax update ticket status 
            add_action( 'wp_ajax_update_ticket_status', [ $this, 'ajaxUpdateStatus' ] );
            // ajax update ticket status mobile
            add_action( 'wp_ajax_update_ticket_status_mobile', [ $this, 'ajaxUpdateStatusMobile' ] );
            // ajax add reply to ticket
            add_action( 'wp_ajax_ticket_reply', [ $this, 'ajaxAddReply' ] );
            // ajax add reply and close ticket
            add_action( 'wp_ajax_ticket_reply_close', [ $this, 'ajaxAddReply' ] );
            // ajax add reply to ticket mobile
            add_action( 'wp_ajax_ticket_reply_mobile', [ $this, 'ajaxAddReplyMobile' ] );
            // ajax add reply and close ticket mobile
            add_action( 'wp_ajax_ticket_reply_close_mobile', [ $this, 'ajaxAddReplyMobile' ] );
            // ajax add reply and close ticket
            add_action( 'wp_ajax_wpas_fa_ajax_logout', [ $this, 'ajaxUserLogout' ] );
            // ajax load view action
            add_action( 'wp_ajax_load_view', [ $this, 'ajaxLoadView' ] );
            //  ajax view ticket
            add_action( 'wp_ajax_view_ticket_mobile', [ $this, 'ajaxViewTicketMobile' ] );

            // Titan framework init
            add_action( 'tf_init', [ $this, 'titanFrameworkInit' ]  );

        } else {

            // ajax user login action
            add_action( 'wp_ajax_nopriv_wpas_fa_ajax_login', [ $this, 'ajaxUserLogin' ] );

        }
        
	}
    

    /**
     * Add tab to AS Settings
     * 
     * @param  array $defaults Array of existing settings
     *
     * @return array Updated settings
     */
    public function addSettingsOptions ( $defaults ) {

        $settings      = array();
        $custom_fields = $this->getCustomFields();

        $settings['frontend-agents'] = array(
            'name'    => __( 'Front-end Agents', 'awesome-support-frontend-agents' ),
            'options' => array(
                array(
                    'name'    => __( 'Enable Front-end Agents', 'awesome-support-frontend-agents' ),
                    'id'      => 'use_frontend_agents',
                    'type'    => 'checkbox',
                    'default' => true,
                    'desc'    => __( 'Enable Front-end Agents add-on', 'awesome-support-frontend-agents' ),
                ),
                array(
                    'name'    => __( 'Shortcode', 'awesome-support-frontend-agents' ),
                    'type'    => 'custom',
                    'custom'  => '<input type="text" value="[' . $this->shortcode . ']" />'
                ),
                array(
                    'name' => __( 'Clean Theme Templates', 'awesome-support-frontend-agents' ),
                    'type' => 'heading',
					'desc' => __( 'You can choose to remove most theme elements when viewing the agent frontend. This can maximize theme compatibility.  We strongly recommend that you at least turn on the option to clean the theme templates for the mobile view.', 'awesome-support-frontend-agents' ),
                ),
                array(
                    'name'    => __( 'Desktop', 'awesome-support-frontend-agents' ),
                    'id'      => 'clean_template_desktop',
                    'type'    => 'checkbox',
					'desc'    => __( 'Remove theme elements when viewing the agent front-end on a desktop sized browser', 'awesome-support-frontend-agents' ),
                    'default' => false
                ),
                array(
                    'name'    => __( 'Mobile', 'awesome-support-frontend-agents' ),
                    'id'      => 'clean_template_mobile',
                    'type'    => 'checkbox',
					'desc'    => __( 'Remove theme elements when viewing the agent front-end on a mobile sized browser', 'awesome-support-frontend-agents' ),
                    'default' => true
                ),
                array(
                    'name' => __( 'Display custom fields', 'awesome-support-frontend-agents' ),
                    'type' => 'heading'
                )
            ),
        );

        // custom fields
        foreach ( $custom_fields as $field => $data ) {

            $settings['frontend-agents']['options'][] = array(
                'name'    => $data['args']['title'],
                'id'      => 'field_section_' . $field,
                'type'    => 'multi-checkbox-options',
                'field'   => $field,
                'options' => array( 
                    'views' => array(
                        'mobile'  => __( 'Mobile View Options', 'awesome-support-frontend-agents' ),
                        'desktop' => __( 'Desktop View Options', 'awesome-support-frontend-agents' )
                    ),
                    'positions' => array(

                        'mobile' => array(                            
                            'ticket_list_2nd_row'    => __( 'Show in ticket list 2nd row', 'awesome-support-frontend-agents' ), 
                            'ticket_list_status_row' => __( 'Show in ticket list status row', 'awesome-support-frontend-agents' ), 
                            'ticket_list_3nd_row'    => __( 'Show in ticket list 3rd row', 'awesome-support-frontend-agents' ),
                            'ticket_top'             => __( 'Show at the top of the ticket', 'awesome-support-frontend-agents' ),
                            'ticket_sidebar'         => __( 'Show in the ticket side-bar', 'awesome-support-frontend-agents' ),
                            'ticket_rolls_edit'      => __( 'Roles allowed to edit', 'awesome-support-frontend-agents' ),
                        ),
                     
                        'desktop' => array(                            
                            'ticket_list'    => __( 'Show in ticket list', 'awesome-support-frontend-agents' ), 
                            'ticket_top'     => __( 'Show at the top of the ticket', 'awesome-support-frontend-agents' ), 
                            'ticket_sidebar' => __( 'Show in the ticket side-bar', 'awesome-support-frontend-agents' ),
                        )
                    )
                ),
                'default' => $this->getFieldDefaultSection( $field )
            );

        }

        return array_merge ( $defaults, $settings );

    }


    /**
     * Get custom fields
     * 
     * @return array Custom fields
     */
    public function getCustomFields() {

        $fields = array();

        // custom fields to skip
        // this fields are used in the admin part only
        $skip = array(
            'id',
            'status',
            'assignee',
            'wpas-client',
            'time_adjustments_pos_or_neg',
            'wpas-activity',
            'ttl_replies_by_agent',
            'ttl_calculated_time_spent_on_ticket',
            'ttl_adjustments_to_time_spent_on_ticket',
            'final_time_spent_on_ticket',
            'first_addl_interested_party_name',
            'first_addl_interested_party_email',
            'second_addl_interested_party_name',
            'second_addl_interested_party_email'
        );

        $skip_fields   = apply_filters( 'as-frontend-skip-admin-custom-fields', $skip );
        $custom_fields = WPAS()->custom_fields->get_custom_fields(); 

        foreach ( $custom_fields as $field => $data ) {

            // check field
            if ( in_array( $field, $skip_fields ) ) {
                continue;
            }

            $fields[ $field ] = $data;

        }

        return $fields;

    }
    
    /**
     * Get custom fields by section
     *
     * @param string $view
     * @param string $section
     * @return array Custom fields
     */
    public function getCustomFieldsBySection( $view, $section ) {

        $fields        = array();
        $custom_fields = $this->getCustomFields();

        foreach( $custom_fields as $field => $data ) {

            $field_section = wpas_get_option( 'field_section_' . $field, false );

            // field section is not set
            if ( ! $field_section ) {

                // check default
                $default = $this->getFieldDefaultSection( $field );
 
                if ( ! isset( $default[ $field ][ $view ][ $section ] ) ) {
                    continue;
                }

            } else {

                // check field section
                if ( ! isset( $field_section[ $field ][ $view ][ $section ] ) ) {
                    continue;
                }
            }

            $fields[ $field ] = $data;

        }

        return $fields;

    }


    public function displayCustomFieldsBySection( $view, $section, $ticket_id, $open_tag = '<div>', $close_tag = '</div>', $display_field_name = true) {

        $custom_fields = $this->getCustomFieldsBySection( $view, $section );

        foreach( $custom_fields as $field => $data ) {

            echo $open_tag;

            if ( $display_field_name ) {
                echo ( ! empty( $data[ 'args' ][ 'label' ] ) ) ? $data[ 'args' ][ 'label' ] : $data[ 'args' ][ 'title' ];
                echo ': ';
            }

            if ( function_exists( $data[ 'args' ][ 'column_callback' ] ) ) {
                call_user_func( $data[ 'args' ][ 'column_callback' ], $data[ 'name' ], $ticket_id );
            } 
            else {
                wpas_cf_value( $data[ 'name' ], $ticket_id );
            }

            echo $close_tag;

        }

    }


    /**
     * Get custom field default section
     *
     * @param string $field
     * @return string Field section
     */
    public function getFieldDefaultSection( $field ) {

        $defaults = array(
            'department' => array(
                'mobile' => array(
                    'ticket_list_2nd_row' => 1
                )
            ),
            'product' => array(
                'mobile' => array(
                    'ticket_list_2nd_row' => 1
                )
            )
        );

        $fields = apply_filters( 'as-frontend-field-defaults', $defaults );


        if ( isset( $fields[ $field ] ) ) {
            return array( 
                $field => $fields[ $field ] 
            );
        }

        /*
        return array(
            $field => array(
                'mobile' => array(
                    'ticket_list_3nd_row' => 1
                )
            ),
        );
        */

    }

    /**
     * Proccess shortcode
     *
     */
    public function frontendAgent () {

        // check if user is logged in
        if ( is_user_logged_in() ) {
            // check if user is admin or agent
            if ( current_user_can( 'administrator' ) or wpas_is_agent() ) {
                // load agent interface
                return $this->loadTemplate( 'container' );
            }
        }

        // user is not logged-in show login form
        return $this->loadTemplate( 'login_form' );

    }

    
	/**
	 * Load assets 	 
	 */
	public function loadAssets() {

        // Datatables
        wp_enqueue_style( 'fa-css-dt', $this->url_path  . 'assets/css/datatables.css' );
        wp_enqueue_script( 'fa-js-dt', $this->url_path . 'assets/js/datatables.js', [ 'jquery' ] );

        // Plugin
        wp_enqueue_style( 'fa-css',  $this->url_path . 'assets/css/main.css' );
        wp_enqueue_style( 'fa-css-mobile',  $this->url_path . 'assets/css/mobile.css' );
        wp_enqueue_script( 'fa-js',  $this->url_path . 'assets/js/main.js' );       
        // Tinymce
        wp_enqueue_script( 'tinymce_js', includes_url( 'js/tinymce/' ) . 'wp-tinymce.php', [ 'jquery' ], false, true );
        wp_enqueue_style( 'tinymce_css', includes_url( 'css/' ) . ( is_rtl() ? 'editor.rtl.css' : 'editor.css' ) );

        // Dashicons
        wp_enqueue_style( 'dashicons' );

        // JS variables used by the plugin
        wp_localize_script( 'fa-js', 'ASFA', [
            'nonce'        => wp_create_nonce( 'ticket_action' ),
            'ajax_url'     => admin_url( 'admin-ajax.php' ),
            'admin_url'    => admin_url(),
            'plugin_url'   => $this->url_path, 
            'Login'        => __( 'Login', 'awesome-support-frontend-agents' ),
            'reply_empty'  => __( "You can't submit an empty reply", 'awesome-support' ),
            'lengthMenu'   =>  __( 'Display _MENU_ records per page',  'awesome-support-frontend-agents' ),
            'zeroRecords'  =>  __( 'No data available in table',  'awesome-support-frontend-agents' ),
            'info'         =>  __( 'Showing _START_ to _END_ of _TOTAL_ entries',  'awesome-support-frontend-agents' ),
            'infoEmpty'    =>  __( 'No data available in table',  'awesome-support-frontend-agents' ),
            "infoFiltered" =>  __( '(filtered from _MAX_ total records)',  'awesome-support-frontend-agents' ),
            "lengthMenu"   =>  __( 'Show _MENU_ entries',  'awesome-support-frontend-agents' ),
            "search"       =>  __( 'Search', 'awesome-support-frontend-agents' ),
            "first"        =>  __( 'First', 'awesome-support-frontend-agents' ),
            "last"         =>  __( 'Last', 'awesome-support-frontend-agents' ),
            "next"         =>  __( 'Next', 'awesome-support-frontend-agents' ),
            "previous"     =>  __( 'Previous', 'awesome-support-frontend-agents' )
        ] );
        
    }

    /**
	 * Load admin assets 	 
	 */
	public function loadAssetsAdmin() {

        // CSS
        wp_enqueue_style( 'fa-css', $this->url_path  . 'assets/css/admin.css' );
        
    }

    /**
     * Load template
     *
     * @param string $name template name without extension located in include/views directory
     * @param array $vars
     * @param boolean $echo
     * @return string|false
     */
    public function loadTemplate( $name, $vars = [], $echo = true ) {

        if ( file_exists($template = $this->dir_path . '/includes/views/' . $name . '.php') ) {

            extract( $vars );

            ob_start();

            include $template;

            $content = ob_get_clean();

            if ( ! $echo ) {
                return $content;
            }

            echo $content;

        } else {

            return false;

        }

    }

    
    /**
     * Get template
     *
     * @param string $name template name without extension located in include/views directory
     * @param array $vars
     * @return string|false
     */
    public function getTemplate( $name, $vars = [] ) {

        return $this->loadTemplate( $name, $vars, false);

    }


    public function cleanTemplate( $template ) {

        // fallback
        if ( ! file_exists( $template ) ) {
            return $template;
        }

        // start output buffering 
        ob_start();

        // include template
        include $template;

        // get html content
        $content = ob_get_clean(); 

        // include dom library
        include $this->dir_path . '/includes/classes/htmldom.php';

        $html = as_str_get_html( $content, false, false, DEFAULT_TARGET_CHARSET, false );

        $body = $html->find('body', 0);

        // get frontend agent container and login form
        $container  = $body->find('#as-fa-container', 0);
        $login_form = $body->find('#as-fa-login-form', 0);

        // if container or login form is in the html, clean the template
        if ( $container || $login_form ) {
            
            // get all scripts in body
            $scripts = $body->find('script');

            // get all css
            $styles      = $html->find('style');
            $stylesheets = $html->find('link[rel=stylesheet]');

            // get admin bar
            $adminbar = $body->find('#wpadminbar', 0);

            $includes_url = includes_url(); 

            // rebuild the template
            
            // insert container or login form depends on what is visible
            $body->innertext = ( $container ) ? $container : $login_form;


            // Leave only stylesheets from wp-includes dir and awesome-support plugin
            foreach ( $stylesheets as $stylesheet ) {
                if ( ! stristr( $stylesheet->href, $this->url_path ) && ! stristr( $stylesheet->href, $includes_url ) ) {
                    $stylesheet->outertext = '';
                }
            }

            // If plugin scripts are loaded in the body of the page, they will be removed, so re-insert them
            foreach ( $scripts as $script ) {
                // Allow Awesome-Support JS, inline JS and JS loaded from wp-includes directory
                if ( stristr( $script->src, $this->url_path ) || $script->src == false || stristr( $script->src, $includes_url ) ) {
                    $body->innertext .= $script->outertext .  PHP_EOL;
                }
            }

            if ( current_user_can( 'administrator' ) ) {
                $body->innertext .= $adminbar;
            }

        }

        // output template
        exit( $html );

    }

    /**
     * Get list of tickets by status assigned to current logged-in user 
     *
     * @param string $status
     * @return array of WP_Post objects
     */
    private function getTicketsByStatus( $status, $perpage = 0 ) {

        // number of tickets per page
        $num = ( $perpage ==  0 ) ? -1 : intval( $perpage );

        $statuses = apply_filters( 'frontend-agent-statuses-query', [ 'open', 'closed' ] );

        if ( in_array( $status, $statuses ) ) {

            $args = [
                'post_type'  => 'ticket',
				'posts_per_page' => $num,
                'meta_query' => [
                    'relation' => 'AND',
                    [
                        'key'   => '_wpas_assignee',
                        'value' => get_current_user_id()
                    ],
                    [
                        'key'   => '_wpas_status',
                        'value' => $status
                    ]
                ]
            ];

            $query = new WP_Query( $args );
    
            return $query->posts;
            
        }

        return [];
    }


    /**
     * Get list of open tickets assigned to current logged-in user 
     * 
     * @param int perpage limit number of tickets
     * @return array of WP_Post objects
     */
    public function getOpenTickets( $perpage = 0 ) {
        return $this->getTicketsByStatus( 'open', $perpage );
    }

    /**
     * Get list of closed tickets assigned current logged-in user
     *
     * @param int perpage limit number of tickets
     * @return array of WP_Post objects
     */
    public function getClosedTickets( $perpage = 0 ) {
        return $this->getTicketsByStatus( 'closed', $perpage = 0  );
    }


    /**
     * Get ticket assigned to current logged-in user
     * 
     *
     * @param int $id
     * @return WP_Post|false WP_Post object or false
     */
    public function getTicket( $id ) {

        $args = [
            'p'          => $id,
            'post_type'  => 'ticket',
            'meta_query' => [
                [
                    'key'   => '_wpas_assignee',
                    'value' => get_current_user_id()
                ]
            ]
        ];

        $query = new WP_Query( $args );

        if ( empty( $query->posts ) ) {
            return false;
        }

        return $query->posts[0];

    }


    /**
     * Get ticket meta values 
     * 
     * Name prefixes (_wpas) are removed
     *
     * @param int $id ticket id
     * @return stdClass 
     */
    public function getTicketMeta( $id ) {

        $data = new stdClass;

        if ( empty( $meta = get_post_meta($id) ) ) {
            return $data;
        }

        foreach ($meta as $key => $values ) {
            // cleanup keys
            $key = ltrim( str_replace( '_wpas_', '', $key ), '_' );
            $data->$key = $values[0];

        }

        return $data;

    }

    
    /**
     * Get ticket replies, and ticket history
     *
     * @param int $ticket_id
     * @param boolean $include_history
     * @return array|false WP_Post objects or false
     */
    public function getTicketReplies( $ticket_id, $include_history = true ) {

        $post_types = array( 'ticket_reply' );

        if ( $include_history ) {
            array_push( $post_types, 'ticket_history' );
        }
        
        $args = array(
            'posts_per_page' => - 1,
            'orderby'        => 'post_date',
            'order'          => wpas_get_option( 'replies_order', 'ASC' ),
            'post_type'      => apply_filters( 'wpas_replies_post_type', $post_types ),
            'post_parent'    => $ticket_id,
            'post_status'    => apply_filters( 'wpas_replies_post_status', array(
                'publish',
                'inherit',
                'private',
                'trash',
                'read',
                'unread'
            ) )
        );

        $query = new WP_Query( $args );


        if ( empty( $query->posts ) ) {
            return false;
        }

        return $query->posts;

    }



    /**
     * Get all data related to an ticket including replies and metadata
     *
     * @param int $id ticket id
     * @return stdClass
     */
    public function getAllTicketData( $id ) {

        $data = new stdClass;

        $data->ticket  = $this->getTicket( $id );
        $data->meta    = $this->getTicketMeta( $id );
        $data->replies = $this->getTicketReplies( $id );

        return $data;

    }

    /**
     * Format date
     *
     * @param string $date
     * @return string 
     */
    public function formatTicketDate( $date ) {
        return date( get_option( 'date_format' ), strtotime( $date ) ) . ' ' . date( get_option( 'time_format' ), strtotime( $date ) );
    }

    /**
     * Get ticket status label
     * used to refresh the label in table after changing the status or closing/re-opening the ticket
     *
     * @param WP_Post object $ticket
     * @return string
     */
    private function getTicketStatusLabel( $ticket ) {

        $status = wpas_get_ticket_status( $ticket->ID );

        if ( 'closed' === $status ) {

            return '<span class="wpas-label wpas-label-status" style="background-color:' . wpas_get_option( 'color_' . $status, '#dd3333' ) . ';">' . __( 'Closed', 'awesome-support' ) . '</span>';
        
        } else {

            $custom_status = wpas_get_post_status();

            if ( ! array_key_exists( $ticket->post_status, $custom_status ) ) {

                return '<span class="wpas-label wpas-label-status" style="background-color:' . wpas_get_option( 'color_' . $status, '#169baa' ) . ';">' . __( 'Open', 'awesome-support' ) . '</span>';
                
			} else {

				$defaults = array(
					'queued'     => '#1e73be',
					'processing' => '#a01497',
					'hold'       => '#b56629',
                );
                
				$label  = $custom_status[ $ticket->post_status ];
				$color  = wpas_get_option( 'color_' . $ticket->post_status, false );

				if ( false === $color ) {
					if ( isset( $defaults[ $ticket->post_status ] ) ) {
						$color = $defaults[ $ticket->post_status ];
					} else {
						$color = '#169baa';
					}
                }
                
                return '<span class="wpas-label wpas-label-status" style="background-color:' . $color . ';">' . $label . '</span>';

			}

        }

    }


    /**
     * View ticket 
     * 
     * @return void
     */
    public function ajaxViewTicket() {

        check_ajax_referer( 'ticket_action', 'nonce' );

        // check if we have a ticket
        if ( $ticket = $this->getTicket( $_POST['id'] ) ) {

            // before hook
            do_action( 'as-frontend-before-ticket-load', $ticket );

            $ticket_meta     = $this->getTicketMeta( $ticket->ID );
            $ticket_template = ( $ticket_meta->status == 'closed' ) ? 'view_closed_ticket' : 'view_open_ticket';
            $ticket_replies  = $this->getTicketReplies( $ticket->ID );
            $fields_top      = $this->getCustomFieldsBySection( 'desktop', 'ticket_top' );
            $fields_sidebar  = $this->getCustomFieldsBySection( 'desktop', 'ticket_sidebar' );

            // load template
            $this->loadTemplate( $ticket_template, [ 
                'ticket'         => $ticket,
                'ticket_meta'    => $ticket_meta,
                'ticket_replies' => $ticket_replies,
                'fields_top'     => $fields_top,
                'fields_sidebar' => $fields_sidebar
            ] );

        } else {

            // ticket not found
            do_action( 'as-frontend-error-ticket-load', $_POST['id'] );

            // load template
            $this->loadTemplate( 'view_ticket_error' );

        }

        exit;
    }


    /**
     * View closed tickets
     *
     * @return void
     */
    public function ajaxViewClosedTickets() {

        check_ajax_referer( 'ticket_action', 'nonce' );

        $this->loadTemplate( 'tickets_table', [ 
            'table_id'      => 'fa-closed-tickets', 
            'custom_fields' => $this->getCustomFieldsBySection( 'desktop', 'ticket_list' ),
            'tickets'       => $this->getClosedTickets() 
        ] ); 


        exit;

    }

    /**
     * Open ticket action
     * 
     * @return void
     */
    public function ajaxOpenTicket() {

        check_ajax_referer( 'ticket_action', 'nonce' );

        // before hook
        do_action( 'as-frontend-before-ticket-reopen', $_POST['id'] );

        if ( wpas_reopen_ticket( $_POST['id'] ) ) {
            
            $data           = $this->getAllTicketData( $_POST['id'] );
            $fields_top     = $this->getCustomFieldsBySection( 'desktop', 'ticket_top' );
            $fields_sidebar = $this->getCustomFieldsBySection( 'desktop', 'ticket_sidebar' );

            $content = $this->getTemplate( 'view_open_ticket', [ 
                'ticket'         => $data->ticket,
                'ticket_meta'    => $data->meta,
                'ticket_replies' => $data->replies,
                'fields_top'     => $fields_top,
                'fields_sidebar' => $fields_sidebar
            ] );

            $label = $this->getTicketStatusLabel( $data->ticket );

            wp_send_json( [
                'html'  => $content,
                'label' => $label 
            ] );

        } else {

            // ticket not found
            do_action( 'as-frontend-error-ticket-load', $_POST['id'] );

            // load template
            $content = $this->getTemplate( 'view_ticket_error' );

            wp_send_json( [
                'html'  => $content,
                'label' => '' 
            ] );

        }

        exit;

    }

    /**
     * Close ticket action
     * 
     * @return void
     */
    public function ajaxCloseTicket() {

        check_ajax_referer( 'ticket_action', 'nonce' );

        // before hook
        do_action( 'as-frontend-before-ticket-close', $_POST['id'] );

        if ( wpas_close_ticket( $_POST['id'] ) ) {

            $data           = $this->getAllTicketData( $_POST['id'] );
            $fields_top     = $this->getCustomFieldsBySection( 'desktop', 'ticket_top' );
            $fields_sidebar = $this->getCustomFieldsBySection( 'desktop', 'ticket_sidebar' );

            $content = $this->getTemplate( 'view_closed_ticket', [ 
                'ticket'         => $data->ticket,
                'ticket_meta'    => $data->meta,
                'ticket_replies' => $data->replies,
                'fields_top'     => $fields_top,
                'fields_sidebar' => $fields_sidebar
            ] );

            $label = $this->getTicketStatusLabel( $data->ticket );

            wp_send_json( [
                'html'  => $content,
                'label' => $label 
            ] );

        } else {
            // ticket not found
            do_action( 'as-frontend-error-ticket-load', $_POST['id'] );

            // load template
            $content = $this->getTemplate( 'view_ticket_error' );

            wp_send_json( [
                'html'  => $content,
                'label' => '' 
            ] );

        }

        exit;

    }


    
    /**
     * Close ticket mobile action
     * 
     * @return void
     */
    public function ajaxCloseTicketMobile() {

        check_ajax_referer( 'ticket_action', 'nonce' );

        // before hook
        do_action( 'as-frontend-before-ticket-close', $_POST['id'] );

        if ( wpas_close_ticket( $_POST['id'] ) ) {

            $data = $this->getAllTicketData( $_POST['id'] );

            $content = $this->getTemplate( 'mobile/view_closed_ticket', [ 
                'ticket'         => $data->ticket,
                'ticket_meta'    => $data->meta,
                'ticket_replies' => $data->replies,
            ] );

            $item = $this->getTemplate( 'mobile/ticket_list_item', [ 
                'ticket'         => $data->ticket,
                'ticket_meta'    => $data->meta
            ] );

            wp_send_json( [
                'status' => 1,
                'content' => $content,
                'item'    => $item
            ] );

        } else {
            // ticket not found
            do_action( 'as-frontend-error-ticket-load', $_POST['id'] );

            // load template
            $content = $this->getTemplate( 'view_ticket_error' );

            wp_send_json( [
                'status'  => 0,
                'content' => $content,
                'item'    => ''
            ] );

        }

        exit;

    }


    /**
     * Re-open ticket mobile action
     * 
     * @return void
     */
    public function ajaxOpenTicketMobile() {

        check_ajax_referer( 'ticket_action', 'nonce' );

        // before hook
        do_action( 'as-frontend-before-ticket-reopen', $_POST['id'] );

        if ( wpas_reopen_ticket( $_POST['id'] ) ) {
            
            $data  = $this->getAllTicketData( $_POST['id'] );

            $content = $this->getTemplate( 'mobile/view_open_ticket', [ 
                'ticket'         => $data->ticket,
                'ticket_meta'    => $data->meta,
                'ticket_replies' => $data->replies,
            ] );

            $item = $this->getTemplate( 'mobile/ticket_list_item', [ 
                'ticket'         => $data->ticket,
                'ticket_meta'    => $data->meta
            ] );

            wp_send_json( [
                'status'  => 1,
                'content' => $content,
                'item'    => $item
            ] );

        } else {

            // ticket not found
            do_action( 'as-frontend-error-ticket-load', $_POST['id'] );

            // load template
            $content = $this->getTemplate( 'view_ticket_error' );

            wp_send_json( [
                'status'  => 0,
                'content' => $content,
                'item'    => ''
            ] );


        }

        exit;

    }


    /**
     * Update ticket status
     * 
     * @return void
     */
    public function ajaxUpdateStatus() {

        check_ajax_referer( 'ticket_action', 'nonce' );

        // before hook
        do_action( 'as-frontend-before-ticket-status-change', $_POST['id'] );

        if ( wpas_update_ticket_status( $_POST['id'], $_POST['status'] ) ) {

            $data           = $this->getAllTicketData( $_POST['id'] );
            $template       = ( $_POST['status'] === 'close' ) ? 'view_closed_ticket' : 'view_open_ticket';
            $fields_top     = $this->getCustomFieldsBySection( 'desktop', 'ticket_top' );
            $fields_sidebar = $this->getCustomFieldsBySection( 'desktop', 'ticket_sidebar' );

            $content = $this->getTemplate( $template, [ 
                'ticket'         => $data->ticket,
                'ticket_meta'    => $data->meta,
                'ticket_replies' => $data->replies,
                'fields_top'     => $fields_top,
                'fields_sidebar' => $fields_sidebar
            ] );

            $label = $this->getTicketStatusLabel( $data->ticket );

            wp_send_json( [
                'html'  => $content,
                'label' => $label 
            ] );

        } else {

            // ticket not found
            do_action( 'as-frontend-error-ticket-load', $_POST['id'] );

            // load template
            $content = $this->getTemplate( 'view_ticket_error' );

            wp_send_json( [
                'html'  => $content,
                'label' => '' 
            ] );

        }

        exit;

    }

    
    /**
     * Update ticket status mobile
     * 
     * @return void
     */
    public function ajaxUpdateStatusMobile() {

        check_ajax_referer( 'ticket_action', 'nonce' );

        // before hook
        do_action( 'as-frontend-before-ticket-status-change', $_POST['id'] );

        if ( wpas_update_ticket_status( $_POST['id'], $_POST['status'] ) ) {

            $template    = ( $_POST['status'] === 'close' ) ? 'view_closed_ticket' : 'view_open_ticket';
            $data        = $this->getAllTicketData( $_POST['id'] );

            $content = $this->getTemplate( 'mobile/' . $template, [ 
                'ticket'         => $data->ticket,
                'ticket_meta'    => $data->meta,
                'ticket_replies' => $data->replies
            ] );

            $label = $this->getTicketStatusLabel( $data->ticket );


            wp_send_json( [
                'status' => 1,
                'content' => $content,
                'label'   => $label 
            ] );


        } else {

            // ticket not found
            do_action( 'as-frontend-error-ticket-load', $_POST['id'] );

            // load template
            $content = $this->getTemplate( 'view_ticket_error' );

            wp_send_json( [
                'status' => 0,
                'content' => $content,
                'label'   => '' 
            ] );
        }

        exit;

    }


    /**
     * Add reply
     *
     * @return void
     */
    public function ajaxAddReply() {

        check_ajax_referer( 'ticket_action', 'nonce' );

        // before hook
        do_action( 'as-frontend-before-ticket-reply', $_POST['id'] );

        $uploader  = WPAS_File_Upload::get_instance();
        $filetypes = $uploader->get_allowed_filetypes();
        $filetypes = explode( ',', $filetypes );
        $max_files = wpas_get_option( 'attachments_max' ); 


        if ( isset( $_FILES['wpas_files'] ) ) {

            // check file count
            if ( count( $_FILES['wpas_files']['name'] ) > $max_files ) {

                wp_send_json( [
                    'status'  => 0,
                    'message' => sprintf( __( 'You can only upload a maximum of %s files', 'awesome-support-frontend-agents' ), $max_files )
                ] );

            } 

            // check file extension
            foreach ( $_FILES['wpas_files']['name'] as $i => $file ) {

                // skip empty
                if ( empty( $_FILES['wpas_files']['name'][$i] ) ) break;

                $parts     = explode( '.', $_FILES['wpas_files']['name'][$i]);
                $extension = end($parts);

                if ( ! in_array( $extension, $filetypes) ) {
                    wp_send_json( [
                        'status'  => 0,
                        'message' => sprintf( __( 'File extension %s is not allowed', 'awesome-support-frontend-agents' ), $extension )
                    ] );
                }

            }

        }

        $data = [
            'post_content' => $_POST['reply']
        ];

        if ( $reply_id = wpas_add_reply( $data, $_POST['id'] ) ) {

            $attachments = [];

            if ( isset( $_FILES['wpas_files'] ) ) {

                foreach ( $_FILES['wpas_files']['name'] as $i => $file ) {

                    // skip if empty
                    if ( empty( $_FILES['wpas_files']['name'][$i] ) ) break;

                    $attachments[] = [
                        'filename' => $_FILES['wpas_files']['name'][$i],
                        'data' => file_get_contents($_FILES['wpas_files']['tmp_name'][$i])
                    ];

                }

            }

            // upload attachments
            if ( ! empty( $attachments ) ) {

                $upload_dir = wp_upload_dir();

                $dir = $upload_dir['basedir'] . '/awesome-support/ticket_' . $_POST['id'] ;

                if ( ! is_dir( $dir ) ) {
                    $uploader->create_upload_dir( $dir );
                }

                $uploader->process_attachments( $reply_id, $attachments );

            }
            

            // reply and close ticket?
            if ( $_POST['action'] == 'ticket_reply_close' ) {
                // close the ticket
                wpas_close_ticket( $_POST['id'] );
                $template = 'view_closed_ticket';

            } else {

                $template = 'view_open_ticket';

            }

            $data           = $this->getAllTicketData( $_POST['id'] );
            $fields_top     = $this->getCustomFieldsBySection( 'desktop', 'ticket_top' );
            $fields_sidebar = $this->getCustomFieldsBySection( 'desktop', 'ticket_sidebar' );

            $content = $this->getTemplate( $template, [ 
                'ticket'         => $data->ticket,
                'ticket_meta'    => $data->meta,
                'ticket_replies' => $data->replies,
                'fields_top'     => $fields_top,
                'fields_sidebar' => $fields_sidebar
            ] );

            $label = $this->getTicketStatusLabel( $data->ticket );

            wp_send_json( [
                'status' => 1,
                'html'   => $content,
                'label'  => $label 
            ] );

        } else {

            // ticket not found
            do_action( 'as-frontend-error-ticket-load', $_POST['id'] );

            // load template
            $content = $this->getTemplate( 'view_ticket_error' );

            wp_send_json( [
                'status' => 1,
                'html'  => $content,
                'label' => '' 
            ] );

        }

        exit;
    }



    
    /**
     * Add reply mobile
     *
     * @return void
     */
    public function ajaxAddReplyMobile() {

        check_ajax_referer( 'ticket_action', 'nonce' );

        // before hook
        do_action( 'as-frontend-before-ticket-reply', $_POST['id'] );

        $uploader  = WPAS_File_Upload::get_instance();
        $filetypes = $uploader->get_allowed_filetypes();
        $filetypes = explode( ',', $filetypes );
        $max_files = wpas_get_option( 'attachments_max' ); 


        // check file count
        if ( count( $_FILES['wpas_files']['name'] ) > $max_files ) {

            wp_send_json( [
                'status'  => 0,
                'message' => sprintf( __( 'You can only upload a maximum of %s files', 'awesome-support-frontend-agents' ), $max_files )
            ] );

        } 

        // check file extension
        foreach ( $_FILES['wpas_files']['name'] as $i => $file ) {

            // skip empty
            if ( empty( $_FILES['wpas_files']['name'][$i] ) ) break;

            $parts     = explode( '.', $_FILES['wpas_files']['name'][$i]);
            $extension = end($parts);

            if ( ! in_array( $extension, $filetypes) ) {
                wp_send_json( [
                    'status'  => 0,
                    'message' => sprintf( __( 'File extension %s is not allowed', 'awesome-support-frontend-agents' ), $extension )
                ] );
            }

        }

        $data = [
            'post_content' => $_POST['reply']
        ];

        if ( $reply_id = wpas_add_reply( $data, $_POST['id'] ) ) {

            $attachments = [];

            foreach ( $_FILES['wpas_files']['name'] as $i => $file ) {

                // skip if empty
                if ( empty( $_FILES['wpas_files']['name'][$i] ) ) break;

                $attachments[] = [
                    'filename' => $_FILES['wpas_files']['name'][$i],
                    'data' => file_get_contents($_FILES['wpas_files']['tmp_name'][$i])
                ];

            }

            // upload attachments
            if ( ! empty( $attachments ) ) {

                $upload_dir = wp_upload_dir();

                $dir = $upload_dir['basedir'] . '/awesome-support/ticket_' . $_POST['id'] ;

                if ( ! is_dir( $dir ) ) {
                    $uploader->create_upload_dir( $dir );
                }

                $uploader->process_attachments( $reply_id, $attachments );

            }
            

            // reply and close ticket?
            if ( $_POST['action'] == 'ticket_reply_close_mobile' ) {
                // close the ticket
                wpas_close_ticket( $_POST['id'] );
                $template = 'mobile/view_closed_ticket';

            } else {

                $template = 'mobile/view_open_ticket';

            }

            $data = $this->getAllTicketData( $_POST['id'] );

            $content = $this->getTemplate( $template, [ 
                'ticket'         => $data->ticket,
                'ticket_meta'    => $data->meta,
                'ticket_replies' => $data->replies,
            ] );

            $item = $this->getTemplate( 'mobile/ticket_list_item', [ 
                'ticket'         => $data->ticket,
                'ticket_meta'    => $data->meta
            ] );

            wp_send_json( [
                'status' => 1,
                'content' => $content,
                'item'    => $item
            ] );


        } else {

            // ticket not found
            do_action( 'as-frontend-error-ticket-load', $_POST['id'] );

            // load template
            $content = $this->getTemplate( 'view_ticket_error' );

            wp_send_json( [
                'status' => 1,
                'content' => $content,
            ] );

        }

        exit;
    }


    /**
     * Ajax User Login action
     *
     * @return void
     */
    public function ajaxUserLogin() {

        check_ajax_referer( 'ticket_action', 'nonce' );

        parse_str($_POST['data'], $data);

        $info = [];

        $info['user_login']    = $data['username'];
        $info['user_password'] = $data['password'];
        $info['remember']      = isset( $data['remember'] ) ? true : false;

        $signon = wp_signon( $info, false );

        if ( is_wp_error($signon) ) {

            wp_send_json( [
                'status' => 0,
                'message'=> __( 'Wrong username or password', 'awesome-support-frontend-agents' )
            ] );

        } else {

            // check user role
            $access = false;

            foreach ( $signon->roles as $i => $role ) {

                if ( in_array( $role, [ 'wpas_agent', 'administrator' ] ) ) {
                    $access = true;
                    break;
                }

            }

            if ( $access ) {
                // ok
                wp_send_json( [
                    'status' => 1,
                    'message'=> __( 'Success', 'awesome-support-frontend-agents' )
                ] );

            } else {

                // user doesnt have right permissions to view this page

                //logout user
                wp_logout(); 

                // send response
                wp_send_json( [
                    'status' => 0,
                    'message'=> __( "You don't have permission to access this page", 'awesome-support-frontend-agents' )
                ] );

            }



        }

        exit;

    }

    
    /**
     * Ajax User Log-out action
     *
     * @return void
     */
    public function ajaxUserLogout() {

        check_ajax_referer( 'ticket_action', 'nonce' );

        wp_logout(); 

        exit;

    }




        /**
     * Ajax load user interface
     *
     */
    public function ajaxLoadView() {

        check_ajax_referer( 'ticket_action', 'nonce' );

        $id   = isset( $_POST[ 'id' ] ) ? intval( $_POST[ 'id' ] ) : false;
        $view = isset( $_POST[ 'view' ] ) ? $_POST[ 'view' ] : false;

        if ( $view ) {

            $this->loadTemplate( $view, [ 'id' => $id ] );

        } else {

            $this->loadTemplate( 'error' );

        }

        exit;

    }

        /**
     * View ticket 
     * 
     * @return void
     */
    public function ajaxViewTicketMobile() {

        check_ajax_referer( 'ticket_action', 'nonce' );

        // check if we have a ticket
        if ( $ticket = $this->getTicket( $_POST['id'] ) ) {

            // before hook
            do_action( 'as-frontend-before-mobile-ticket-load', $ticket );

            $ticket_meta     = $this->getTicketMeta( $ticket->ID );
            $ticket_template = ( $ticket_meta->status == 'closed' ) ? 'view_closed_ticket' : 'view_open_ticket';
            $ticket_replies  = $this->getTicketReplies( $ticket->ID );

            // load template
            $content = $this->getTemplate( 'mobile/' . $ticket_template, [ 
                'ticket'         => $ticket,
                'ticket_meta'    => $ticket_meta,
                'ticket_replies' => $ticket_replies,
            ] );

            wp_send_json( array(
                'status'  => 1,
                'title'   => $ticket->post_title,
                'content' => $content
            ) );

        } else {

            // ticket not found
            do_action( 'as-frontend-error-mobile-ticket-load', $_POST['id'] );

            
            // load template
            $content = $this->getTemplate( 'view_ticket_mobile_error' );

            wp_send_json( array(
                'status'  => 0,
                'content' => $content
            ) );


        }

        exit;
    }


    /**
     * Titan framework init
     *
     * @return void
     */
    public function titanFrameworkInit() {
        require_once $this->dir_path  . 'includes/titan_fields/class-option-multi-checkbox-options.php';
    }

}
