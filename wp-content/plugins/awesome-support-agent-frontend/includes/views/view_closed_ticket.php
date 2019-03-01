<?php defined('ABSPATH') or exit; ?>

<table class="wpas-fa-ticket-info-table">

    <thead>
        <tr>

            <?php if ( count( $fields_sidebar ) ): ?>
                <th></th>
            <?php endif; ?>

            <th>ID</th>
            <th><?php _e( 'Status', 'awesome-support' ); ?></th>
            <th><?php _e( 'Date', 'awesome-support' ); ?></th>
            <?php foreach( $fields_top as $field => $data ): ?>
                <th><?php _e( $data[ 'args' ][ 'title' ], 'awesome-support' ); ?></th>
            <?php endforeach; ?>
            <th><?php _e( 'Re-open Ticket', 'awesome-support-frontend-agents' ); ?></th>
        </tr>
    </thead>

    <tbody>
        <tr>

            <?php if ( count( $fields_sidebar ) ): ?>
                <td>
                    <a href="#" class="wpas-hide-on-mobile wpas-fa-arrow-down" data-id="<?php echo $ticket->ID; ?>"></a>
                </td>
            <?php endif; ?>

            <td>
                <span class="wpas-fa-mobile-info">ID</span> 
                #<?php echo $ticket->ID; ?>
            </td>
            <td class="wpas-row-ticket-status-<?php echo $ticket->ID; ?>">
                <span class="wpas-fa-mobile-info"><?php _e( 'Status', 'awesome-support' ); ?></span> 
                <?php 
                    printf( '<span class="wpas-label wpas-label-status" style="background-color:%s;">%s</span>', 
                        wpas_get_option( 'color_closed', '#dd3333' ),
                        __( 'Closed', 'awesome-support' )
                    );
                ?>
            </td>
            <td>
                <span class="wpas-fa-mobile-info"><?php _e( 'Date', 'awesome-support' ); ?></span> 
                <time datetime="<?php echo date( 'Y-m-d\TH:i:s', strtotime( wpas_get_offset_html5() ) ); ?>">
                    <?php echo $this->formatTicketDate( $ticket->post_date ); ?>
                </time>
            </td>
            <?php foreach( $fields_top as $field => $data ): ?>
                <td>
                    <?php 

                        if ( function_exists( $data[ 'args' ][ 'column_callback' ] ) ) {
                            call_user_func( $data[ 'args' ][ 'column_callback' ], $data[ 'name' ], $ticket->ID );
                        }
                        else {
                            wpas_cf_value( $data[ 'name' ], $ticket->ID );
                        }
                        
                    ?>
                </td>
            <?php endforeach; ?>
            <td>
                <span class="wpas-fa-mobile-info"><?php _e( 'Re-open Ticket', 'awesome-support' ); ?></span> 
                <a href="#" class="wpas-fa-btn btn-close wpas-fa-ticket-action" data-id="<?php echo $ticket->ID; ?>" data-action="open_ticket"><?php _e( 'Re-open ticket', 'awesome-support-frontend-agents' ); ?></a>
            </td>
        </tr>
    </tbody>
</table>

<?php if ( count( $fields_sidebar ) ): ?>

<table class="wpas-fa-sidebar-table" id="wpas-fa-sidebar-content-table-<?php echo $ticket->ID; ?>">

    <?php foreach( $fields_sidebar as $field => $data ): ?>
        <tr>
            <td>
                <strong><?php _e( $data[ 'args' ][ 'title' ], 'awesome-support' ); ?></strong>
            </td>
            <td>
                <?php 

                    if ( function_exists( $data[ 'args' ][ 'column_callback' ] ) ) {
                        call_user_func( $data[ 'args' ][ 'column_callback' ], $data[ 'name' ], $ticket->ID );
                    }
                    else {
                        wpas_cf_value( $data[ 'name' ], $ticket->ID );
                    }
                    
                ?>
            </td>
        </tr>

    <?php endforeach; ?>

</table>

<?php endif; ?>

<table class="wpas-fa-reply-table">
    <tr>
        <td rowspan="3" class="wpas-fa-ticket-avatar">
            <?php echo get_avatar( $ticket->post_author, '64', get_option( 'avatar_default' ) ); ?>
        </td>
    </tr>
    <tr>
        <td>

            <?php $author = get_user_by( 'id', $ticket->post_author ); ?>

            <strong><?php echo $author->display_name; ?></strong>

        </td>
        <td class="wpas-fa-ticket-date">

            <?php $default_date = sprintf( __( '%s ago', 'awesome-support' ), human_time_diff( get_the_time( 'U', $ticket->ID ), current_time( 'timestamp' ) ) ); ?>

            <span class="wpas-fa-replay-date-display" data-default="<?php echo $default_date; ?>" data-hover="<?php echo $this->formatTicketDate( $ticket->post_date ); ?> ">
                <?php echo $default_date; ?>
            </span>         

        </td>
    </tr>
    <tr>
        <td colspan="2" class="wpas-fa-ticket-post-content break-words">
            <?php echo apply_filters( 'the_content', $ticket->post_content ); ?>
        </td>
    </tr>
</table>

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
        $date = sprintf( __( '%s ago', 'awesome-support' ), human_time_diff( get_the_time( 'U', $reply->ID ), current_time( 'timestamp' ) ) );

        ?>

        <?php if ( $reply->post_type == 'ticket_history' ): ?>

            <div class="wpas-fa-history">

                <?php

                do_action( 'wpas_backend_history_content_before', $reply->ID );

                $content = apply_filters( 'the_content', $reply->post_content );

                do_action( 'wpas_backend_history_content_after', $reply->ID ); ?>

                <div class="wpas-fa-history-content">
                    <div><?php echo $user_name; ?>, <em class='wpas-time'><?php echo $date; ?></em></div>
                    <div class="break-words"><?php echo $content; ?></div>
                </div>
                                
            </div>

        <?php else: ?>

            <table class="wpas-fa-reply-table wpas-fa-reply-background">
                <tr>
                    <td rowspan="3" class="wpas-fa-ticket-avatar">
                        <?php echo get_avatar( $user_id, '64', get_option( 'avatar_default' ) );  ?>
                    </td>
                </tr>
                <tr>
                    <td>

                        <strong>
                            <?php echo $user_name; ?>
                        </strong>

                    </td>
                    <td class="wpas-fa-ticket-date">

                        <span class="wpas-fa-replay-date-display" data-default="<?php echo $date; ?>" data-hover="<?php echo $this->formatTicketDate( $reply->post_date ); ?> ">
                            <?php echo $date; ?>
                        </span>         

                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="wpas-fa-ticket-post-content break-words">
                        
                        <?php

                            $content = apply_filters( 'the_content', $reply->post_content );

                            do_action( 'wpas_backend_reply_content_before', $reply->ID );

                            echo wp_kses( $content, wp_kses_allowed_html( 'post' ) );

                            do_action( 'wpas_backend_reply_content_after', $reply->ID );

                        ?>

                    </td>
                </tr>

            </table>
            
        <?php endif; ?>

    <?php endforeach; ?>

<?php else: ?>
    <br /><br />
    <?php echo _x( 'No reply yet.', 'No last reply', 'awesome-support' ); ?>

<?php endif; ?>


<br />


<h3><?php _e( 'Write a reply', 'awesome-support-frontend-agents' ); ?></h3>

<div>
    <?php _e( 'The ticket has been closed', 'awesome-support-frontend-agents' ); ?>.
    <strong>
        <a href="#" class="wpas-fa-ticket-action" data-id="<?php echo $ticket->ID; ?>" data-action="open_ticket">
            <?php _e( 'Re-open ticket', 'awesome-support-frontend-agents' ); ?>
        </a>
    </strong>
</div>
