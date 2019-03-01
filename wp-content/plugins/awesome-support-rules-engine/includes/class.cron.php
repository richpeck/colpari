<?php
namespace AsRulesEngine;
use WP_Query;
use DateTime;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class: RE_Conditions handle rules engine condition related work.
 */
if ( ! class_exists( 'RE_Cron' ) ) {

	class RE_Cron {

		/**
		 * $cron_intervals Array The different interval schedules allowed.
		 */
		private $cron_intervals;

		private $rules_engine;  // @todo: put header for this variable.

		/**
		 * RE_cron Constructor.
		 */
		public function __construct(  ) {

			// Create array of allowed cron intervals
			$this->set_cron_intervals() ;

			// Setup hooks and filters...
			add_filter( 'cron_schedules', 									array( $this, 'register_cron_schedules' ) );	// Register a set of intervals with WordPress
			add_action( 'awesome_support_rules_engine_cron_action_hook',  	array( $this, 'execute_cron_process' ) );		// Add an action so that the cron process can be fired from inside a class.

			// Setup function to fire cron
			$this->schedule_cron_process() ;

		}


	    /***
		 * Set up variable to hold the various cron intervals allowed
		 *
		 * @param void
		 * @return void
		 *
		 */
		private function set_cron_intervals() {

			$this->cron_intervals = array(
				'every1min'    => array(
					'interval' => 1 * 60,
					'display'  => __( 'Every 1 Minute (use for testing only!)', 'as-rules-engine' ),
				),
				'every5min'    => array(
					'interval' => 5 * 60,
					'display'  => __( 'Every 5 Minutes', 'as-rules-engine' ),
				),
				'every10min'   => array(
					'interval' => 10 * 60,
					'display'  => __( 'Every 10 Minutes', 'as-rules-engine' ),
				),
				'every20min'   => array(
					'interval' => 20 * 60,
					'display'  => __( 'Every 20 Minutes', 'as-rules-engine' ),
				),
				'every30min'   => array(
					'interval' => 30 * 60,
					'display'  => __( 'Every 30 Minutes', 'as-rules-engine' ),
				),
				'hourly'       => array(
					'interval' => 3600,
					'display'  => __( 'Once Hourly', 'as-rules-engine' ),
				),
				'every2ndhour' => array(
					'interval' => 2 * 3600,
					'display'  => __( 'Every 2nd Hour', 'as-rules-engine' ),
				),
				'every4thhour' => array(
					'interval' => 4 * 3600,
					'display'  => __( 'Every 4th Hour', 'as-rules-engine' ),
				),
				'every6thhour' => array(
					'interval' => 6 * 3600,
					'display'  => __( 'Every 6th Hour', 'as-rules-engine' ),
				),
				'twicedaily'   => array(
					'interval' => 12 * 3600,
					'display'  => __( 'Twice Daily', 'as-rules-engine' ),
				),
				'daily'        => array(
					'interval' => 1 * 86400,
					'display'  => __( 'Once Daily', 'as-rules-engine' ),
				),
			);

		}

	    /***
		 * Set up and initialize cron schedules in WP.
		 *
		 * Filter hook: cron_schedules (core wordpress hook)
		 *
         * @param array $schedules
         * @return array
		 *
		 */
		public function register_cron_schedules( $schedules ) {

            foreach ( $this->cron_intervals as $cr_opt_key => $cr_opt ) {

                if(!in_array($cr_opt_key, array_keys($schedules))) {
                    $schedules[ $cr_opt_key ] = $cr_opt;
                }
            }

            return $schedules;

		}

		/***
		*
		* Retrieves the Cron interval value for a specific ruleset
		*
		* @param int $ruleset_id Ruleset ID
		*
		* @return int $interval_value result of conditional statement for action.
		*
		*/
		public function retrieve_ruleset_interval( $ruleset_id ) {

			//Check that the ID is a valid ruleset, if not then return empty array
			$ruleset = get_post( $ruleset_id );

			if( $ruleset != null ) {
				if( $ruleset->post_type != 'ruleset' ) {
					return false;
				}
			} else {
				return false;
			}

			$interval_value = false;

			$ruleset_interval_meta = get_post_meta( $ruleset_id, 'trigger_cron_intervals-rb', true );

			if( $ruleset_interval_meta != '' ) {
				$interval_value = $this->cron_intervals[$ruleset_interval_meta]['interval'];
			}

			return $interval_value;

		}

		/***
		*
		* Filter the rulesets based on their interval setting
		*
		* @param array $rule_sets Array of ruleset objects
		*
		* @return array $filtered_rules
		*
		*/
		public function filter_rulesets( $rule_sets ) {

			$filtered_rules = array();

			foreach( $rule_sets as $ruleset ) {

				$previous_cron_time = get_post_meta( $ruleset->ID, 'ruleset_cron_last_executed', true );

				if( $previous_cron_time == '' ) {

					$filtered_rules[] = $ruleset;

				} else {

					$ruleset_interval = $this->retrieve_ruleset_interval( $ruleset->ID );

					//Logic if interval is 1min, 5min, 10min, 20min or 30min
					if(
						$ruleset_interval == 60 ||
						$ruleset_interval == 300 ||
						$ruleset_interval == 600 ||
						$ruleset_interval == 1200 ||
						$ruleset_interval == 1800
					) {

						$time_difference = time() - $previous_cron_time;

						if( $time_difference >= $ruleset_interval ) {

							$filtered_rules[] = $ruleset;

						}

					} elseif( $ruleset_interval == 3600 ) { //Hourly logic

						$current_hour = date( "d-m-Y H:00:00" );
						$hour_in_seconds = strtotime( $current_hour );
						$next_hour = $hour_in_seconds + 3600;

						if( !(($hour_in_seconds <= $previous_cron_time) && ($previous_cron_time <= $next_hour)) ) {

						  $filtered_rules[] = $ruleset;

						}

					} elseif(
						$ruleset_interval == 7200 || //Every 2nd, 4th, 6th hour, twice a day and once a day logic
						$ruleset_interval == 14400 ||
						$ruleset_interval == 21600 ||
						$ruleset_interval == 43200 ||
						$ruleset_interval == 86400
						) {

							$middnight_today = strtotime( 'today midnight' );
							$interval_rate = 0;
							$loop_amount = 0;

							switch( $ruleset_interval ) {

								case 7200:

									$loop_amount = 24 / 2; //2 hours
									$interval_rate = 2;
									break;

								case 14400:

									$loop_amount = 24 / 4; //4 hours
									$interval_rate = 4;
									break;

								case 21600:

									$loop_amount = 24 / 6; //6 hours
									$interval_rate = 6;
									break;

								case 43200:

									$loop_amount = 2; //Twice a day
									$interval_rate = 12;
									break;

								case 86400:

									$loop_amount = 1; //Once a day
									$interval_rate = 24;
									break;

							}

							$interval_timeframes = array();
							$start_time = $middnight_today;
							$end_time = 0;

							//Groups the time invervals for the current day then checks if previous task was run in that window
							for( $i=0; $i<$loop_amount; $i++ ) {

								$time_diff = 3600 * $interval_rate;
								$end_time = $start_time + $time_diff;

								$interval_timeframes[] = array(
									'start' => $start_time,
									'end' => $end_time
									);

								$start_time = $end_time;

							}

							$current_time_range = -1; //Unused array key just for initializing

							foreach( $interval_timeframes as $key => $time ) {

								if( ($time['start'] <= time()) && (time() <= $time['end']) ) {

									$current_time_range = $key;
									break;

								}
							}

							if( !(($interval_timeframes[$current_time_range]['start'] <= $previous_cron_time) && ($previous_cron_time <= $interval_timeframes[$current_time_range]['end'])) ) {

							  $filtered_rules[] = $ruleset;

							}

						}	

				}

			}

			return $filtered_rules;

		}

	    /***
		 *
		 * This is the heart of the cron process when it runs.
		 *
		 */
		public function execute_cron_process() {

			$do_debug = true ;

			if ($do_debug) {
				error_log( 'Firing Awesome Support Rules Engine Cron Action...' ) ;
			}

			/* Instantiate the rules engine...*/
			require_once( AS_RE_PATH . 'includes/class.rulesengine.php' );
			require_once( AS_RE_PATH . 'includes/class.cron.php' );
			$this->rules_engine = new Rules_Engine();

			/* Get all cron related rulesets and filter the ones which should be checked */
			$rule_sets = $this->rules_engine->implementation->get_trigger( 'cron' );
			$rule_sets = $this->filter_rulesets( $rule_sets );

			foreach( $rule_sets as $ruleset ) {

				$ruleset_interval = $this->retrieve_ruleset_interval( $ruleset->ID );

				if ($do_debug) {
					error_log('The ruleset being fired is: ' . (string) $ruleset->ID);
				}

				/* Get the tickets that match the ruleset conditions */
				$tickets = $this->rules_engine->implementation->get_condition()->tickets_matching_ruleset($ruleset->ID);

				if ($do_debug) {
					error_log( 'Number of tickets matching ruleset is: ' . (string) count($tickets) ) ;
				}

				/* Now, execute the rule-set action for each ticket that matches the ruleset */
				foreach( $tickets as $ticket ) {

					if ($do_debug) {
						error_log('Handling actions for ticket#: ' . (string) $ticket->ID);
					}

					$this->rules_engine->implementation->get_action()->do_action( true, $ticket->ID, $ticket->ID, $ruleset->ID, '' );
				}

				//Save the time this cron executed
				update_post_meta( $ruleset->ID, 'ruleset_cron_last_executed', time() );

			}

		}

	    /***
		 *
		 * Get a list of ruleset items that are cron actions
		 *
		 * @param none
		 *
		 * @return $array array of rulesets that have cron action enabled
		 *
		 * @TODO: restrict the rulesets being returned to only those whose next action time is greater than that stamped on the ruleset.
		 *
		 */
		 public function get_cron_related_rulesets() {

			$args = array(
				'post_type'              => AS_RE_RULESET_CPT,
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'no_found_rows'          => true,
				'cache_results'          => false,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'meta_query' => array(
					array(
						'key' => 'trigger_cron-cb',
						'value' => 'on',
						)
					)
				);

			$query = new WP_Query( $args );

			if ( empty( $query->posts ) ) {
				return array();
			}

			return $query->posts;

		 }


	    /***
		 *
		 * Schedule the real function that will be called when cron fires...
		 *
		 * @todo - figure out why this fires a lot!
		 *
		 */
		public function schedule_cron_process() {

      if (!wp_next_scheduled('awesome_support_rules_engine_cron_action_hook')) {

          $scheduled = wp_schedule_event( time(), 'every5min', 'awesome_support_rules_engine_cron_action_hook' );

      }

		}
	}

}
