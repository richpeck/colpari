<?php defined('ABSPATH') or exit; ?>

<div class="as-fa-content-padding">

    <div class="as-fa-mobile-author">
        <?php echo $ticket->post_title; ?>
    </div>

    <div class="as-fa-mobile-ticket-status">

        <a href="#" class="as-fa-mobile-button-right as-fa-mobile-write-reply"  data-id="<?php echo $ticket->ID; ?>">
            <i class="dashicons dashicons-undo"></i>
            <?php _e( 'Reply', 'awesome-support-frontend-agents' ); ?>
        </a>

        <?php

            wpas_cf_display_status( 'status', $ticket->ID );
        
            if ( true === wpas_is_reply_needed( $ticket->ID, $ticket_replies ) ) {

                $color = ( false !== ( $c = wpas_get_option( 'color_awaiting_reply', false ) ) ) ? $c : '#0074a2';
                echo " <span class='wpas-label wpas-label-status' style='background-color:$color;'>" . __( 'Awaiting Support Reply', 'awesome-support' ) . "</span>";

            }

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

                <a href="#" class="as-fa-mobile-button-round as-fa-mobile-write-reply" data-options="1" data-id="<?php echo $ticket->ID; ?>">
                    <i class="dashicons dashicons-undo"></i>
                </a>
                <?php _e( 'Write a reply', 'awesome-support-frontend-agents' ); ?>
            </td>
            <td>
                <a href="#" class="as-fa-mobile-button-round ticket-action-mobile" data-action="close_ticket" data-id="<?php echo $ticket->ID; ?>">
                    <i class="dashicons dashicons-lock"></i>
                </a>
                <?php _e( 'Close Ticket', 'awesome-support-frontend-agents' ); ?>
            </td>
        </tr>
        <tr>
            <td>                
                <?php _e( 'Change Status', 'awesome-support' ); ?>
            </td>
            <td>
                <select name="change_status_mobile" class="as-fa-mobile-change-status" data-id="<?php echo $ticket->ID; ?>">
                    <?php foreach ( wpas_get_post_status() as $id => $value ): ?>
                        <option value="<?php echo $id; ?>" <?php if ( $id == $ticket->post_status ) echo 'selected'; ?>>
                            <?php  _e( $value, 'awesome-support' ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
    </table>

    <?php $this->displayCustomFieldsBySection( 'mobile', 'ticket_sidebar', $ticket->ID ); ?>

</div>