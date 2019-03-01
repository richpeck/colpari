<?php 

if( ! class_exists( 'Product_Tickets_Report_Widget' ) ) {

	require_once( 'base/tickets-report-base-widget.php' );

	class Product_Tickets_Report_Widget extends Tickets_Report_Base_Widget {

		private $user_id;

		/**
		 * Register the widget
		 *
		 * @param $ticket_status
		 * @param $widget_slug
		 * @param $widget_title
		 *
		 * @return void
		 * @since 0.1.0
		*/
		public function __construct( $ticket_status, $widget_slug, $widget_title ) {
			$support_products = (bool)wpas_get_option( 'support_products' );

			if( $this->should_show_widget() && $support_products === true ) {
				parent::__construct( $ticket_status, $widget_slug, $widget_title );
				$this->user_id = get_current_user_id();

				if ( 'product_open_tickets_report' === $widget_slug || 'product_closed_tickets_report' === $widget_slug  ) {
					wp_add_dashboard_widget(
						$widget_slug,
						'<i class="fa fa-ticket" aria-hidden="true"></i> ' . $widget_title,
						array( $this, 'register_product_tickets_report_widget' )
					);
				}
				
				if  ( 'product_open_tickets_chart_report' === $widget_slug ) {
					// Add a product summary chart dashboard widget....
					wp_add_dashboard_widget(
						$widget_slug ,
						'<i class="fa fa-ticket" aria-hidden="true"></i> ' . $widget_title,
						array( $this, 'register_product_tickets_summary_chart_widget' )
					);				
				}
					
			}
		}
		
		/**
		 * Get the ids of products
		 *
		 * @return array
		 * @since 0.1.0
		*/
		private function get_products_ids() {
			$products_ids = array();
			if( taxonomy_exists( 'product' ) ) {
				$args = array(
					'fields' => 'ids',
				);
				$products_ids = get_terms( 'product', $args );
			}
			return $products_ids;
		}

		/**
		 * Get tickets for single product
		 *
		 * @param $product_id - the id of the product to get tickets for
		 *
		 * @return array
		 * @since 0.1.0
		*/
		private function get_product_tickets( $product_id ) {
			$args = array(
				'tax_query' => array(
					array(	
						'taxonomy' => 'product',
						'field' => 'id',
						'terms' => $product_id,
					),
				),
			);
			return $this->get_tickets_by_agent_helper( $args ) ;
		}

		/**
		 * Get report for single product
		 *
		 * @param $product_tickets - Array of the tickets for prduct
		 *
		 * @return array
		 * @since 0.1.0
		*/
		private function get_product_tickets_report( $product_tickets ) {
			return $this->ticket_status == 'open'
					? $this->get_tickets_report_by_date_short( $product_tickets ) 
					: $this->get_tickets_report_by_date_long( $product_tickets );
		}

		/**
		 * Get tickets for each currently existing product, that hasn't been deleted.
		 *
		 * @param $products_ids - the ids of the products to get tickets for
		 *
		 * @return array
		 * @since 0.1.0
		*/
		private function get_existing_products_tickets( $products_ids ) {
			$tickets = array();
			if( ! empty( $products_ids ) ) {	
				foreach( $products_ids as $product_id ){
					$product_tickets = $this->get_product_tickets( $product_id );
					$product_tickets_report = $this->get_product_tickets_report( $product_tickets );
					
					$product = get_term_by( 'id', $product_id, 'product' );  // Should return the the product term object regardless of whether syncing is turned on or not.
					if ( empty( $product ) ) {
						$product = get_term( $product_id );		// Just in case nothing was returned in the prior call, try again...
					}				
					
					$tickets[$product->name] = $product_tickets_report;
				}
			}
			return $tickets;
		}

		/**
		 * Get tickets for each deleted product
		 *
		 * @return array
		 * @since 0.1.0
		*/
		private function get_deleted_products() {
			$tickets = array();
			$deleted_products = wpas_get_option( '_deleted_products', array() );

			if( ! empty( $deleted_products ) ) {
				foreach ( $deleted_products as $deleted_product ) {
					if( ! empty( $deleted_product['ticket_ids'] ) ) {
						$products = get_terms( 'product', array( 'fields' => 'ids'  ) );
						$args = array(
							'post__in' => $deleted_product['ticket_ids'],
							'tax_query' => array(
								array(
									'taxonomy' => 'product',
									'terms'    => $products,
									'operator' => 'NOT IN'
								),
							),
						);
						$product_tickets = wpas_get_tickets( $this->ticket_status, $args );
						if( ! empty( $product_tickets ) ) {
							$product_tickets_report = $this->get_product_tickets_report( $product_tickets );
							$tickets[$deleted_product['name']] = $product_tickets_report;
						}	
					}	
				}
			}

			return $tickets;
		}

		/**
		 * Get tickets for each product.
		 *
		 * @param $products_ids - the ids of the products to get tickets for
		 *
		 * @return array
		 * @since 0.1.0
		*/
		private function get_products_tickets( $products_ids ) {
			$tickets = $this->get_existing_products_tickets( $products_ids );
			$deleted_products = $this->get_deleted_products();
			$tickets = wp_parse_args( $deleted_products, $tickets );
			return $tickets;
		}

		/**
		 * The callback for the dashboard widget - individual panes for each product 
		 *
		 * @return void
		 * @since 0.1.0
		*/
		public function register_product_tickets_report_widget() {
			$ids = $this->get_products_ids();
			$tickets = $this->get_products_tickets( $ids );
			$this->ticket_status == 'open' 
				? $this->get_template( 'open-tickets', $tickets, false, 'product' ) 
				: $this->get_template( 'closed-tickets', $tickets );
		}

				
		/**
		 * The callback for the dashboard widget - summary chart pane for products on open tickets only
		 *
		 * @return void
		 * @since 2.0.0
		*/
		public function register_product_tickets_summary_chart_widget() {

			if ('open' === $this->ticket_status ) {
				$ids = $this->get_products_ids();
				$tickets = $this->get_products_tickets( $ids );			
				$this->get_template( 'single-chart', $tickets, false, 'product-summary-chart' );
			}
		}
		
	}
}