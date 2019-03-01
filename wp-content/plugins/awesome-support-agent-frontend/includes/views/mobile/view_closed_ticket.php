<?php defined('ABSPATH') or exit; ?>

<div class="as-fa-content-padding">

    <div class="as-fa-mobile-author">
        <?php echo $ticket->post_title; ?>
    </div>

    <div class="as-fa-mobile-ticket-status">
        
        <?php 
            printf( '<span class="wpas-label wpas-label-status" style="background-color:%s;">%s</span>', 
                wpas_get_option( 'color_closed', '#dd3333' ),
                __( 'Closed', 'awesome-support' )
            );
        ?>

        <?php $this->displayCustomFieldsBySection( 'mobile', 'ticket_list_status_row', $ticket->ID ); ?>

    </div>

    <div class="as-fa-mobile-replies">

        <div class="as-fa-mobile-ticket-title">

            <?php $author = get_user_by( 'id', $ticket->post_author ); ?>

            <strong><?php echo $author->display_name; ?></strong>

            <div class="as-fa-mobile-ticket-date">
                <?php echo $this->formatTicketDate( $ticket->post_date ); ?>
            </div>

        </div>

        <div class="as-fa-mobile-ticket-content break-words">
            <?php echo apply_filters( 'the_content', $ticket->post_content ); ?>
        </div>

    </div>

    <?php if ( $ticket_replies ): ?>

        <?php foreach ($ticket_replies as $reply): ?>

            <?php 
            
            // Set the author data (if author is known)
            if ( $reply->post_author != 0 ) {
                $user_data = get_userdata( $reply->post_author );
                $user_id   = $user_data->data->ID;
                $user_name = $user_data->data->display_name;
            }

            // In case the post author is unknown, we set this as an anonymous post
            else {
                $user_name = __( 'Anonymous', 'awesome-support' );
                $user_id   = 0;
            }

            ?>

            <?php if ( $reply->post_type == 'ticket_history' ): ?>

                <div class="as-fa-mobile-history">

                    <?php

                    do_action( 'wpas_backend_history_content_before', $reply->ID );

                    $content = apply_filters( 'the_content', $reply->post_content );

                    do_action( 'wpas_backend_history_content_after', $reply->ID ); ?>

                    <div><?php echo $user_name; ?>, <?php echo $this->formatTicketDate( $reply->post_date );  ?></div>
                    <div class="break-words"><?php echo $content; ?></div>
                                    
                </div>

            <?php else: ?>

                <div class="as-fa-mobile-replies">

                    <div class="as-fa-mobile-ticket-title">

                        <strong><?php echo $user_name; ?></strong>

                        <div class="as-fa-mobile-ticket-date">
                            <?php echo $this->formatTicketDate( $reply->post_date ); ?>
                        </div>

                    </div>

                    <div class="as-fa-mobile-ticket-content break-words">

                        <?php

                            $content = apply_filters( 'the_content', $reply->post_content );

                            do_action( 'wpas_backend_reply_content_before', $reply->ID );

                            echo wp_kses( $content, wp_kses_allowed_html( 'post' ) );

                            do_action( 'wpas_backend_reply_content_after', $reply->ID );

                        ?>

                    </div>

                </div>
                
            <?php endif; ?>

        <?php endforeach; ?>

    <?php else: ?>
        <br /><br />
        <?php echo _x( 'No reply yet.', 'No last reply', 'awesome-support' ); ?>

    <?php endif; ?>


</div>

<div class="as-fa-mobile-ticket-options-content" data-options-id="<?php echo $ticket->ID; ?>">

    <a href="#" class="as-fa-mobile-close-options">
        <i class="dashicons dashicons-no"></i>
    </a>

    <table>
        <tr>
            <td>
                <a href="#" class="as-fa-mobile-button-round ticket-action-mobile" data-action="open_ticket" data-id="<?php echo $ticket->ID; ?>">
                    <i class="dashicons dashicons-lock"></i>
                </a>
                <?php _e( 'Re-open Ticket', 'awesome-support-frontend-agents' ); ?>
            </td>
        </tr>
    </table>

    
    <?php $this->displayCustomFieldsBySection( 'mobile', 'ticket_sidebar', $ticket->ID ); ?>


</div>