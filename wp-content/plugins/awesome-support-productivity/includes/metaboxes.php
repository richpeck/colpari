<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


add_action( 'add_meta_boxes', 'wpas_pf_metaboxes' );

/**
 * Register the metaboxes.
 * 
 */
function wpas_pf_metaboxes() {

	add_meta_box( 'wpas-pf-mb-ticket-merge-lock', __( 'Merge & Lock', 'wpas_productivity' ), 'wpas_pf_merge_lock_metabox', 'ticket', 'side', 'default' );
	add_meta_box( 'wpas-pf-mb-ticket-navigation', __( 'Navigate', 'wpas_productivity' ), 'wpas_pf_navigate_metabox', 'ticket', 'side', 'default' );	
	
	$support_notes = WPAS_PF_Support_Note::get_instance();
	
	if( $support_notes->user_can_add_note() ) {
		add_meta_box( 'wpas-pf-mb-support-notes',     __( 'Customer Support Notes', 'wpas_productivity' ), 'wpas_pf_support_notes_metabox', 'ticket', 'normal', 'default' );
		add_filter( "postbox_classes_ticket_wpas-pf-mb-support-notes", 'wpas_pf_metabox_add_tabs_class', 11, 1 );
	}
	
	$personal_notes = WPAS_PF_Personal_Note::get_instance();
	
	if( $personal_notes->have_access() ) {
		add_meta_box( 'wpas-pf-mb-personal-notes',     __( 'Personal Notes', 'wpas_productivity' ), 'wpas_pf_personal_notes_metabox', 'ticket', 'normal', 'default' );
		add_filter( "postbox_classes_ticket_wpas-pf-mb-personal-notes", 'wpas_pf_metabox_add_tabs_class', 11, 1 );
	}
	
	$personal_todo = WPAS_PF_Personal_Todo::get_instance();
	
	if( $personal_todo->have_access() ) {
		add_meta_box( 'wpas-pf-mb-personal-todo',     __( 'Personal Todo Lists', 'wpas_productivity' ), 'wpas_pf_personal_todo_metabox', 'ticket', 'normal', 'default' );
		add_filter( "postbox_classes_ticket_wpas-pf-mb-personal-todo", 'wpas_pf_metabox_add_tabs_class', 11, 1 );
	}
	
	if( WPAS_PF_Recently_Closed::should_display() ) {
		add_meta_box( 'wpas-pf-mb-recently-closed',     __( 'Recently Closed Tickets', 'wpas_productivity' ), 'wpas_pf_recently_closed_metabox', 'ticket', 'normal', 'default' );
		add_filter( "postbox_classes_ticket_wpas-pf-mb-recently-closed", 'wpas_pf_metabox_add_tabs_class', 11, 1 );
	}
	
	if( WPAS_PF_Favorite_Ticket::should_display() ) {
		add_meta_box( 'wpas-pf-mb-favorites',     __( 'Favorite Tickets', 'wpas_productivity' ), 'wpas_pf_favorites_metabox', 'ticket', 'normal', 'default' );
		add_filter( "postbox_classes_ticket_wpas-pf-mb-favorites", 'wpas_pf_metabox_add_tabs_class', 11, 1 );
	}
	
	if( WPAS_PF_Agent_Signature::should_display() ) {
		add_meta_box( 'wpas-pf-mb-signatures',     __( 'Signatures', 'wpas_productivity' ), 'wpas_pf_signatures_metabox', 'ticket', 'normal', 'default' );
		add_filter( "postbox_classes_ticket_wpas-pf-mb-signatures", 'wpas_pf_metabox_add_tabs_class', 11, 1 );
	}
	
	if( WPAS_PF_Ticket_Notification_Email::should_display() ) {
		add_meta_box( 'wpas-pf-mb-agent_email_addrs',     __( 'Email Addresses', 'wpas_productivity' ), 'wpas_pf_agent_email_addresses_metabox', 'ticket', 'normal', 'default' );
		add_filter( "postbox_classes_ticket_wpas-pf-mb-agent_email_addrs", 'wpas_pf_metabox_add_tabs_class', 11, 1 );
	}
	
	if( WPAS_PF_Ticket_User_Contact::should_display() ) {
		add_meta_box( 'wpas-pf-mb-ticket_user_contacts',     __( 'User Contacts', 'wpas_productivity' ), 'wpas_pf_ticket_user_contacts_metabox', 'ticket', 'normal', 'default' );
		add_filter( "postbox_classes_ticket_wpas-pf-mb-ticket_user_contacts", 'wpas_pf_metabox_add_tabs_class', 11, 1 );
	}
	
	if( WPAS_PF_Global_Favorite_Ticket::should_display() ) {
		add_meta_box( 'wpas-pf-mb-global-favorites',     __( 'Hotlist', 'wpas_productivity' ), 'wpas_pf_global_favorites_metabox', 'ticket', 'normal', 'default' );
		add_filter( "postbox_classes_ticket_wpas-pf-mb-global-favorites", 'wpas_pf_metabox_add_tabs_class', 11, 1 );
	}
}

/**
 * Merge and Lock metabox callback function
 * @param object $post
 */
function wpas_pf_merge_lock_metabox( $post ) {
	
	$merge = WPAS_PF_Ticket_Merge::get_instance();
	$lock = WPAS_PF_Ticket_Lock::get_instance();
	
	$merge->add_field_html( $post->ID );
	$lock->add_field_html( $post->ID );
	
}

/**
 * Next, Previous ticket navigation metabox callback function
 * @param object $post
 */
function wpas_pf_navigate_metabox( $post ) {
	
	$nav = WPAS_PF_Ticket_Navigate::get_instance();
	$nav->add_buttons( $post->ID );
	
}

/**
 * Support notes metabox callback function
 * @param object $post
 */
function wpas_pf_support_notes_metabox( $post ) {
	
	$support_notes = WPAS_PF_Support_Note::get_instance();
	$support_notes->display( $post->ID );
	
}


/**
 * Personal notes metabox callback function
 * @param object $post
 */
function wpas_pf_personal_notes_metabox( $post ) {
	
	$personal_notes = WPAS_PF_Personal_Note::get_instance();
	$personal_notes->display( $post->ID );
}

/**
 * Personal todo metabox callback function
 * @param object $post
 */
function wpas_pf_personal_todo_metabox( $post ) {
	$personal_todo = WPAS_PF_Personal_Todo::get_instance();
	$personal_todo->display( $post->ID );
}

/**
 * Recently closed tickets metabox callback function
 * 
 * @param object $post
 */
function wpas_pf_recently_closed_metabox( $post ) {
	
	$recently_closed = WPAS_PF_Recently_Closed::get_instance();
	$recently_closed->display( $post->ID );
}

/**
 * Favorite tickets metabox callback function
 * 
 * @param object $post
 */
function wpas_pf_favorites_metabox( $post ) {
	
	$favorite = WPAS_PF_Favorite_Ticket::get_instance();
	$favorite->display( $post->ID );
}

/**
 * Signatures metabox callback function
 * 
 * @param object $post
 */
function wpas_pf_signatures_metabox( $post ) {
	
	$signature = WPAS_PF_Agent_Signature::get_instance();
	$signature->display( $post->ID );
	
}

/**
 * Agent emails metabox callback function
 * 
 * @param object $post
 */
function wpas_pf_agent_email_addresses_metabox( $post ) {
	
	$email = WPAS_PF_Ticket_Notification_Email::get_instance();
	$email->display( $post->ID );
}

/**
 * User contacts metabox callback function
 * 
 * @param object $post
 */
function wpas_pf_ticket_user_contacts_metabox( $post ) {
	$contact = WPAS_PF_Ticket_User_Contact::get_instance();
	$contact->display( $post->ID );
}

/**
 * Global favorite tickets metabox callback function
 * 
 * @param object $post
 */
function wpas_pf_global_favorites_metabox( $post ) {
	$favorite = WPAS_PF_Global_Favorite_Ticket::get_instance();
	$favorite->display( $post->id );
}


/**
 * 
 * @param array $classes
 * @return array
 */
function wpas_pf_metabox_add_tabs_class( $classes ) {
	$classes[] = 'wpas_pf_mb_tab';
	return $classes;
}