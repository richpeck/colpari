<?php
namespace AsRulesEngine;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'RE_Implementation' ) ) {

	class RE_Implementation {
		/**
		 * An array of all condition and filters fields objects
		 *
		 * @var object $conditions values.
		 */
		private $conditions;

		/**
		 * Rules engine applied action array.
		 * @var object $action values.
		 */
		private $action;

		/**
		 * Rules engine applied condition array.
		 * @var array $condition values.
		 */
		private $condition;

		/**
		 * Rules engine email template setting class object.
		 *
		 * @var object $email email template object.
		 */
		private $email;

		/**
		 * Rules engine  current reply_id
		 * @var int reply_id
		 */
		private $reply_id;

		/**
		 * Rules engine  current ruleset_id
		 * @var int ruleset_id
		 */
		private $ruleset_id;

		/**
		 * Rules engine  current trigger check
		 * @var int ruleset_trigger_check
		 */
		private $ruleset_trigger_check;

		/**
		 * check assinee has changed or not. help in new ticket trigger when no user is assigned to ticket.
		 * @var boolean $change_assignee agent is assignee to ticket or not.
		 */
		private $change_assignee = false;

		/**
		 * RE_Implementation class constructor.
		 */
		public function __construct( $instances = '' ) {
			if ( ! empty( $instances ) && ( is_array( $instances ) || is_object( $instances ) ) ) {
				foreach ( $instances as $key => $value ) {
					$this->$key = $value;
				}
			}
			$this->init();
		}

		/**
		 * Function file on class object creation.
		 */
		private function init() {
			$this->hook();
			$this->setObject();
		}

		/**
		 * Set class object for action condition and email templates.
		 */
		private function setObject() {
			if ( empty( $this->action )  ) {
				require_once( AS_RE_PATH . 'includes/class.actions.php' );
				$this->action = new RE_Action();
			}

			if ( empty( $this->condition )  ) {
				require_once( AS_RE_PATH . 'includes/class.conditions.php' );
				$this->condition = new RE_Conditions( array( 'conditions' => $this->conditions ) );
			}

			if (  empty( $this->email ) ) {
				require_once( AS_RE_PATH . 'includes/class.email.template.php' );
				$this->email = new RE_Email_Template( );
			}
		}

		/**
		 * Bind all trigger with word press actions using code.
		 */
		public function hook() {
			/**
			 * New Ticket trigger hook
			*/
			add_action( 'wpas_open_ticket_after', array( $this, 'trigger_new_ticket' ), 100, 2 );

			/**
			 * Since on new ticket submission default assignee get assign to ticket.
			 * So it can create problem when ruleset action get set to change Agent on new ticket submission.
			 * on this hook. I am going to check if action defined in ruleset plugin to change Agent on new ticket submission.
			 * then this hook will use that ruleset assignee to ticket assignee otherwise it will continue default working.
			 */
			add_filter( 'wpas_new_ticket_agent_id', array( $this, 'wpas_new_ticket_agent_callback' ),10, 3 );

			/**
			 * Client replied to ticket (Ticket reply received) trigger hook
			*/
			add_action( 'wpas_add_reply_complete', array( $this, 'trigger_reply_received' ), 10, 2 );

			/**
			 * Agent replied to ticket trigger hook
			 * We can use same action hook. The difference is,
			 * the reply data should have 'post_author' value
			*/
			add_action( 'wpas_add_reply_complete', array( $this, 'trigger_agent_replied' ), 10, 2 );

			/**
			 * Status changed trigger
			*/
			add_action( 'save_post', array( $this, 'trigger_status_changed' ), 10, 3 );
			add_filter( 'wp_insert_post_data', array( $this, 'save_trigger_status_changed' ), 10, 2 );
			/**
			 * Ticket closed trigger
			*/
			add_action( 'wpas_after_close_ticket', array( $this, 'trigger_ticket_closed' ), 10, 3 );

			/**
			 * Ticket updated trigger
			*/
			add_action( 'save_post', array( $this, 'trigger_ticket_updated' ), 10, 3 );

			/**
			 * Ticket trashed trigger
			*/
			add_action( 'wp_trash_post', array( $this, 'trigger_ticket_trashed' ), 10, 1 );

			add_action( 'trashed_post', array( $this, 'clear_data_on_ticket_trashed' ), 10, 1 );

		}

		/**
		 * Function get fire on  New Ticket trigger hook
		 *
		 * @param int $ticket_id Ticket ID
		 * @param array $data Ticket data.
		 */
		public function trigger_new_ticket( $ticket_id, $data ) {
			$rulesets = $this->get_trigger( 'new_ticket' );
			if ( ! empty( $rulesets ) ) {
				foreach ( $rulesets as $ruleset ) {
					$statement = $this->condition->check( $ruleset->ID, $ticket_id, $this->reply_id );
					if ( ! empty( $statement ) ) {
						$this->ruleset_id = $ruleset->ID;
						$this->action->do_action( $statement, $ticket_id, $this->reply_id, $ruleset->ID, 'new_ticket' );
					}
				}
			}
		}
		/**
		 * wpas_new_ticket_agent_callback wpas_new_ticket_agent_id filter callback function.
		 *
		 * @param  int $agent_id     Agent ID.
		 * @param  int $ticket_id    Ticket ID.
		 * @param  int $pre_agent_id Agent ID.
		 * @return int $agent_id Agent Id.
		 */
		function wpas_new_ticket_agent_callback( $agent_id, $ticket_id, $pre_agent_id ) {
			$rulesets = $this->get_trigger( 'new_ticket' );
			if ( ! empty( $rulesets ) ) {
				foreach ( $rulesets as $ruleset ) {
					if ( isset( $ruleset->ID ) && ! empty( $ruleset->ID ) ) {
						$statement = $this->condition->check( $ruleset->ID, $ticket_id, $this->reply_id );
						if ( ! empty( $statement ) ) {
							$change_agent = get_post_meta( $ruleset->ID, AS_RE_ACTION_META_PREFIX . 'change_agent', true );
							if ( isset( $change_agent['value'] ) && ! empty( $change_agent['value'] ) && 'default' !== $change_agent['value'] ) {
								// If Rule set action is defined to change Agent on New ticket submission then we will do the required work here.
								$agent_id = $change_agent['value'];
							}
						}
					}
				}
			}
			return $agent_id;
		}

		/**
		 * Client replied to ticket (Ticket reply received) trigger hook
		 * @param  int $replyid reply id.
		 * @param  array $data    post data
		 */
		public function trigger_reply_received( $replyid, $data ) {
			$this->reply_id = $replyid;
			$rulesets = $this->get_trigger( 'ticket_reply_received' );
			if ( ! empty( $rulesets ) ) {

				/**
				 * Get ticket ID by reply ID
				*/
				$ticket_id = wp_get_post_parent_id( $replyid );
				$action_check = get_post_meta( $ticket_id, 'ticket_reply_action', true );
				if ( $action_check ) {
					return false;
				}
				/**
				 * Check if the current reply author is wpas_user
				*/
				$reply = get_post( $this->reply_id );

				if ( ! empty( $reply ) ) {
					$post = get_post( $ticket_id );
					$reply_author = get_userdata( $reply->post_author );
					// If reply Author is equal to ticket author and author have create_ticket capability.
					if ( isset( $post->post_author ) && ! empty( $post )  && isset( $reply_author->ID ) && ! empty( $reply_author ) &&  intval( $post->post_author ) === intval( $reply_author->ID ) && user_can( $reply_author->ID , 'create_ticket' ) ) {
						foreach ( $rulesets as $ruleset ) {
							$statement = $this->condition->check( $ruleset->ID, $ticket_id, $this->reply_id );
							if ( ! empty( $statement ) ) {
								$this->action->do_action( $statement, $ticket_id, $this->reply_id, $ruleset->ID, 'add_reply' );
								// Update current trigger fire  add_reply in this call.
								$this->ruleset_trigger_check = array(
												$ruleset->ID => array( 'add_reply' ),
											);
							}
						}
					}
				}
			}
		}

		/**
		 * Agent reply received on ticket trigger hook
		 *
		 * @param  int $replyid reply id.
		 * @param  array $data    post data
		 */
		public function trigger_agent_replied( $reply_id, $data ) {
			$rulesets = $this->get_trigger( 'agent_replied_ticket' );
			if ( ! empty( $rulesets ) ) {

				/**
				 * Get ticket ID by reply ID
				*/
				$ticket_id = wp_get_post_parent_id( $reply_id );

				/**
				 * Check if the current reply author is wpas_agent
				*/
				$reply = get_post( $reply_id );

				if ( ! empty( $reply ) ) {

					$author = get_userdata( $reply->post_author );
					if ( isset( $author->ID ) && ! empty( $author ) ) {

						if ( user_can( $author->ID , 'assign_ticket' ) ) {
							$action_check = get_post_meta( $ticket_id, 'ticket_reply_action', true );
							if ( $action_check ) {
								return false;
							}

							foreach ( $rulesets as $ruleset ) {
								$statement = $this->condition->check( $ruleset->ID, $ticket_id, $this->reply_id );
								if ( ! empty( $statement ) ) {
									if ( ! isset( $this->ruleset_trigger_check[ $ruleset->ID ] ) || ! in_array( 'add_reply', $this->ruleset_trigger_check[ $ruleset->ID ] ) ) {

										$this->action->do_action( $statement, $ticket_id, $this->reply_id, $ruleset->ID, 'add_reply' );
										// Update current trigger fire  agent add_reply in this call.
										$this->ruleset_trigger_check = array(
													$ruleset->ID => array( 'add_reply' ),
												);
										wpas_write_log( 'as-rules-engine', 'Fire Agent reply trigger' );
									} else {
										wpas_write_log( 'as-rules-engine', "Don't fire Agent reply trigger" );
									}
								}
							}
						}
					}
				}
			}
		}

		/**
		 * Function to save  ticket previous status on before save_post.
		 * @param  array $post  post data array.
		 * @param  array $org_post current post data.
		 *
		 * @return  $post  post data array.
		 */
		function save_trigger_status_changed( $post, $org_post ) {
			$post_id = $org_post['ID'];
			$status = get_post_status( $post_id );
			update_post_meta( $post_id,'previous_ticket_status', $status );
			return $post;
		}

		/**
		 * Status changed trigger
		 * Check if 'post_status_override' request exists
		 * Compare current/original status, if they don't match
		 * The agent update the status
		*/
		public function trigger_status_changed( $post_id, $post, $update ) {

			$post_type = get_post_type( $post_id );

			if ( 'ticket' !== $post_type || wp_is_post_revision( $post_id ) ) {
				return;
			}

			if ( isset( $_REQUEST['post_status_override'] ) ) {
				$original_status = get_post_meta( $post_id, 'previous_ticket_status', true );

				if ( $original_status !== $_REQUEST['post_status_override'] ) {
					$rulesets = $this->get_trigger( 'status_changed' );

					if ( ! empty( $rulesets ) ) {
						foreach ( $rulesets as $ruleset ) {
							$statement = $this->condition->check( $ruleset->ID, $post_id, $this->reply_id );
							if ( ! empty( $statement ) ) {
								/**
								 * Don't Fire status change trigger and run action again, When add_reply trigger is already fire.
								 *
								 * So I am going to allow call of do_action here. when
								 ** $this->ruleset_trigger_check array is empty.
								 ** OR It contain trigger other than add_reply.
								 ** OR ruleset_Id of  not $this->ruleset_trigger_check have current Rule set ID.
								 *
								 */
								// IF no trigger fire before this trigger.
								if ( ! isset( $this->ruleset_trigger_check[ $ruleset->ID ] ) || ( ! in_array( 'add_reply', $this->ruleset_trigger_check[ $ruleset->ID ] ) && ! in_array( 'change_state', $this->ruleset_trigger_check[ $ruleset->ID ] ) ) ) {

									$this->action->do_action( $statement, $post_id, $this->reply_id, $ruleset->ID, 'change_status_action' );
									$this->ruleset_trigger_check[ $ruleset->ID ][] = 'change_status_action';
									wpas_write_log( 'as-rules-engine',  'Fire status change trigger' );
								} else {
									wpas_write_log( 'as-rules-engine', 'Do not fire status change trigger' );
								}
							}
						}
					}
				}
			}

		}

		/**
		 * Use save_post hook on ticket updated trigger
		 * Just check the post_type, then do the rest
		*/
		public function trigger_ticket_updated( $post_id, $post, $update ) {
			$ticket_data = get_post( $post_id );
			$post_type = $ticket_data->post_type;

			// If it is not post update then dont execute further.
			if ( 'ticket' !== $post_type || wp_is_post_revision( $post_id ) || 'auto-draft' === $ticket_data->post_status ) {
				return;
			}
			if ( isset( $ticket_data->post_modified ) && ! empty( $ticket_data->post_modified )  && isset( $ticket_data->post_date ) && ! empty( $ticket_data->post_date ) ) {
				if ( $ticket_data->post_modified !== $ticket_data->post_date ) {
					//Updated Post
					$trash_moved_status = get_post_meta( $post_id, 'trash_moved_status', true );
					if ( $trash_moved_status ) {
						return false;
					}
					if ( isset( $_POST['post_title'] ) && ! empty( $_POST['post_title'] ) ) {
						$rulesets = $this->get_trigger( 'ticket_updated' );
						if ( ! empty( $rulesets ) ) {
							foreach ( $rulesets as $ruleset ) {
								$statement = $this->condition->check( $ruleset->ID, $post_id, $this->reply_id );
								if ( ! empty( $statement ) ) {
									// IF no trigger fire before this trigger.
									if ( ! isset( $this->ruleset_trigger_check[ $ruleset->ID ] ) || ( ! in_array( 'add_reply', $this->ruleset_trigger_check[ $ruleset->ID ] ) && ! in_array( 'change_status_action', $this->ruleset_trigger_check[ $ruleset->ID ] ) && ! in_array( 'change_state', $this->ruleset_trigger_check[ $ruleset->ID ] ) ) ) {

										$this->action->do_action( $statement, $post_id, $this->reply_id, $ruleset->ID, 'ticket_updated' );
										wpas_write_log( 'as-rules-engine', 'Allow Update trigger execution' );
									} else {
										wpas_write_log( 'as-rules-engine', 'Do no fire Update trigger' );
									}
								}
							}
						}
					}
				} else {
					$rulesets = $this->get_trigger( 'new_ticket' );
					$prev_new_ticket_check = get_post_meta( $post_id, 'prev_new_ticket_check' );
					if ( ! $prev_new_ticket_check ) {
						if ( ! empty( $rulesets ) ) {
							if ( isset( $_POST['post_title'] ) && ! empty( $_POST['post_title'] ) ) {
								foreach ( $rulesets as $ruleset ) {
									$statement = $this->condition->check( $ruleset->ID, $post_id, $this->reply_id );
									if ( ! empty( $statement ) ) {
										$this->ruleset_id = $ruleset->ID;
										$this->action->do_action( $statement, $post_id, $this->reply_id, $ruleset->ID, 'new_ticket' );
									}
								}
							}
						}
						update_post_meta( $post_id, 'prev_new_ticket_check', true );
					}
				}
			}
		}

		/**
		 * Ticket get closed
		 *
		 * @param  int $ticket_id ticket Id
		 * @param  array $update  updated data array
		 * @param  int $user_id   current user id
		 */
		public function trigger_ticket_closed( $ticket_id, $update, $user_id ) {
			$action_check = get_post_meta( $ticket_id, 'ticket_close_action', true );
			if ( $action_check ) {
				return false;
			}

			$rulesets = $this->get_trigger( 'ticket_closed' );
			if ( ! empty( $rulesets )  && user_can( $user_id, 'close_ticket' ) ) {
				foreach ( $rulesets as $ruleset ) {
					$statement = $this->condition->check( $ruleset->ID, $ticket_id, $this->reply_id );
					if ( ! empty( $statement ) ) {
						if ( ! isset( $this->ruleset_trigger_check[ $ruleset->ID ] ) || ! in_array( 'add_reply', $this->ruleset_trigger_check[ $ruleset->ID ] ) ) {
							$this->action->do_action( $statement, $ticket_id, $this->reply_id, $ruleset->ID, 'change_state' );
							$this->ruleset_trigger_check[ $ruleset->ID ][] = 'change_state';
							wpas_write_log( 'as-rules-engine', 'Fire close ticket trigger' );
						} else {
							wpas_write_log( 'as-rules-engine', "Don't fire close ticket trigger" );
						}
					}
				}
			}
		}

		/**
		 * Trashed ticket trigger
		 * Check if post type is ticket
		*/
		public function trigger_ticket_trashed( $post_id ) {
			$post_type = get_post_type( $post_id );
			$action_check = get_post_meta( $post_id, 'ticket_trash_action', true );
			if ( 'ticket' !== $post_type || wp_is_post_revision( $post_id ) ) {
				return;
			}

			if ( $action_check ) {
				return false;
			}
			$post_status = get_post_status( $post_id );
			if ( ! empty( $post_status ) ) {
				$rulesets = $this->get_trigger( 'ticket_trashed' );
				if ( ! empty( $rulesets ) ) {
					foreach ( $rulesets as $ruleset ) {
						$statement = $this->condition->check( $ruleset->ID, $post_id, $this->reply_id );
						if ( ! empty( $statement ) ) {
							$this->action->do_action( $statement, $post_id, $this->reply_id, $ruleset->ID, 'trash_ticket' );
						}
					}
				}
			}
		}

		/**
		 * Function to clear ticket data on move trash action.
		 *
		 * @param  int $post_id ticket id.
		 */
		public function clear_data_on_ticket_trashed( $post_id ) {
			$post_type = get_post_type( $post_id );

			if ( 'ticket' !== $post_type || wp_is_post_revision( $post_id ) ) {
				return;
			}
			$post_status = get_post_status( $post_id );
			if ( ! empty( $post_status ) ) {
				update_post_meta( $post_id, 'trash_moved_status', false );
			}
		}

		/**
		 * Function to get ruleset based on given trigger
		 * @param  string $type trigger
		 * @return array   rules data array.
		 */
		public function get_trigger( $type = 'new_ticket' ) {
			$prefix = AS_RE_TRIGGER_META_PREFIX;
			$trigger_type = $prefix . $type . '-cb';

			$args = array(
				'posts_per_page' => -1, // For now, update this on next version
				'post_type' 	=> AS_RE_RULESET_CPT,
				'post_status' 	=> 'publish',
				'meta_query'	=> array(
					array(
						'key' 	=> $trigger_type,
						'value'	=> 'on',
						'compare'	=> '=',
					),
				),
			);
			return get_posts( $args );
		}

		/**
		 * Getter function to get the condition field
		 */
		public function get_condition() {
			return $this->condition;
		}
		
		/**
		 * Getter function to get the action field
		 */
		public function get_action() {
			return $this->action;
		}
		
	}

}
