<?php defined('ABSPATH') or exit; ?>

<table id="<?php echo $table_id; ?>" class="display dt-responsive nowrap wpas-fa-table">

    <thead>
        <th></th>
        <th><?php _e( 'Status', 'awesome-support' ); ?></th>
        <th><?php _e( 'Title',  'awesome-support' ); ?></th>
        <th class="all"><?php _e( 'Ticket ID', 'awesome-support' ); ?></th>

        <?php foreach( $custom_fields as $field => $data ): ?>
            <th><?php _e( $data[ 'args' ][ 'title' ], 'awesome-support' ); ?></th>
        <?php endforeach; ?>

        <th><?php _e( 'Created by', 'awesome-support' ); ?></th>
        <th><?php _e( 'Activity',   'awesome-support' ); ?></th>

        <?php do_action( 'as-frontend-agent-table-columns' ); ?>

    </thead>

    <tbody>

    <?php foreach ( $tickets as $ticket ): ?>

        <?php $ticket_meta = $this->getTicketMeta( $ticket->ID );  ?>

        <tr class="wpas-ticket-table-row-<?php echo $ticket->ID; ?>">
        
            <td></td>

            <td class="wpas-row-ticket-status-<?php echo $ticket->ID; ?>">

                <?php 

                if ( $ticket_meta->status == 'closed' ) {

                    printf( '<span class="wpas-label wpas-label-status" style="background-color:%s;">%s</span>', 
                        wpas_get_option( 'color_closed', '#dd3333' ),
                        __( 'Closed', 'awesome-support' )
                    );

                } else {
                    wpas_cf_display_status( 'status', $ticket->ID );
                }
                
                ?>

            </td>
            <td>
                <a href="#" class="wpas-fa-view-ticket" data-ticket-id="<?php echo $ticket->ID; ?>" data-ticket-title="<?php echo $ticket->post_title; ?>">
                    <?php echo $ticket->post_title; ?>
                </a>
            </td>
            <td>
                <a href="#" class="wpas-fa-view-ticket" data-ticket-id="<?php echo $ticket->ID; ?>" data-ticket-title="<?php echo $ticket->post_title; ?>">
                    #<?php echo $ticket->ID; ?>
                </a>
            </td>

            <?php foreach( $custom_fields as $field => $data ): ?>
                <td>
                    <?php 

                        if ( function_exists( $data[ 'args' ][ 'column_callback' ] ) ) {
                            call_user_func( $data[ 'args' ][ 'column_callback' ], $data[ 'name' ], $ticket->ID );
                        } /* default rendering options */
                        else {
                            wpas_cf_value( $data[ 'name' ], $ticket->ID );
                        }
                        
                    ?>
                </td>
            <?php endforeach; ?>
            
            <td>
                <?php $user = get_userdata( $ticket->post_author ); ?>
                <a href="#" data-fa-search="<?php echo $user->display_name; ?>">
                    <?php echo $user->display_name; ?>
                </a>
            </td>
            <td>
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

            </td>

            <?php do_action( 'as-frontend-agent-table-row', $ticket, $ticket_meta, $replies ); ?>

        </tr>

    <?php endforeach; ?>

    </tbody>

</table>

