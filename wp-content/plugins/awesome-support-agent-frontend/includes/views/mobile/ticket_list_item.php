<?php defined('ABSPATH') or exit; ?>

<div class="as-fa-mobile-ticket-card" data-title="<?php echo $ticket->post_title; ?>" data-id="<?php echo $ticket->ID; ?>">

    <div class="as-fa-mobile-author">

        <div class="as-fa-mobile-ticket-date">
            <?php echo $this->formatTicketDate( $ticket->post_date ); ?>
        </div>

        <?php echo $ticket->post_title; ?>

        <div class="as-fa-mobile-ticket-title">

            #<?php echo $ticket->ID; ?> 
            <?php $user = get_userdata( $ticket->post_author ); ?>

            <?php echo $user->display_name; ?>
    
        </div>

    </div>

    <?php $this->displayCustomFieldsBySection( 'mobile', 'ticket_list_2nd_row', $ticket->ID ); ?>

    <?php $this->displayCustomFieldsBySection( 'mobile', 'ticket_list_3nd_row', $ticket->ID ); ?>

    <div class="as-fa-mobile-ticket-content break-words">
        <?php

            $words = explode(' ', $ticket->post_content );

            if ( count( $words ) > 1 ) {

                array_splice($words, 14);
                echo implode(' ', $words ) . '...'; 

            } else {
                echo substr( $words[0], 0, 50 ) . '...';
            }

        ?>
    </div>

    <div class="as-fa-mobile-ticket-reply-info">

        <?php 
        
        if ( $replies = $this->getTicketReplies( $ticket->ID, false ) ) {

            $last_reply = $replies[ count( $replies ) - 1 ];
            $last_user  = get_user_by( 'id', $last_reply->post_author );

            echo _x( sprintf( _n( '%s reply.', '%s replies.', count( $replies ), 'awesome-support' ), count( $replies ) ), 'Number of replies to a ticket', 'awesome-support' ) . ' ';
            printf( _x( 'Last replied %s ago by %s', 'Last reply ago', 'awesome-support' ), human_time_diff( strtotime( $last_reply->post_date ), current_time( 'timestamp' ) ), $last_user->user_nicename );

        } else {

            echo _x( 'No reply yet.', 'No last reply', 'awesome-support' );

        }

        ?>
    </div>

    <div class="as-fa-mobile-ticket-status" data-id="<?php echo $ticket->ID; ?>">
    
        <?php 

            if ( $ticket_meta->status == 'closed' ) {

                printf( '<span class="wpas-label wpas-label-status" style="background-color:%s;">%s</span>', 
                    wpas_get_option( 'color_closed', '#dd3333' ),
                    __( 'Closed', 'awesome-support' )
                );

            } else {
                wpas_cf_display_status( 'status', $ticket->ID );
            }

            if ( true === wpas_is_reply_needed( $ticket->ID, $replies ) ) {

                $color = ( false !== ( $c = wpas_get_option( 'color_awaiting_reply', false ) ) ) ? $c : '#0074a2';
                echo " <span class='wpas-label wpas-label-status' style='background-color:$color;'>" . __( 'Awaiting Support Reply', 'awesome-support' ) . "</span>";

            }

        ?>

        <?php if ( $ticket_meta->status != 'closed' ): ?>
            <a href="#" class="as-fa-mobile-write-reply as-fa-mobile-button-right"  data-id="<?php echo $ticket->ID; ?>">
                <i class="dashicons dashicons-undo"></i>
            </a>
        <?php endif; ?>

        <?php $this->displayCustomFieldsBySection( 'mobile', 'ticket_list_status_row', $ticket->ID ); ?>

    </div>



    <div class="as-fa-mobile-ticket-custom-fields" style="display:none;" data-fields-id="<?php echo $ticket->ID; ?>">

        <div class="as-fa-ticket-option-buttons">
                            
            <?php 

                if ( $ticket_meta->status == 'closed' ) {

                    $action = 'open_ticket';
                    $text   =  __( 'Open', 'awesome-support-frontend-agents' );

                } else {
                    $action = 'close_ticket';
                    $text   = __( 'Close', 'awesome-support-frontend-agents' );
                }

            ?>

            <a href="#" class="as-fa-mobile-button as-fa-ticket-quick-action-mobile" data-action="<?php echo $action; ?>" data-id="<?php echo $ticket->ID; ?>"><?php echo $text; ?></a>

            <?php if ( $ticket_meta->status != 'closed' ): ?>
                <a href="#" class="as-fa-mobile-button as-fa-mobile-write-reply"  data-id="<?php echo $ticket->ID; ?>"><?php _e( 'Reply', 'awesome-support-frontend-agents' ); ?></a>
            <?php endif; ?>

        </div>

    </div>

</div>

