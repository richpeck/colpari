<?php defined('ABSPATH') or exit; ?>

<?php if ( ! empty( $tickets ) ): ?>

    <?php 
    
        foreach ( $tickets as $ticket ) {

            $ticket_meta = $this->getTicketMeta( $ticket->ID ); 

            include 'ticket_list_item.php';

        }

    ?>

<?php else: ?>

    <div class="as-fa-content-padding">
        <?php _e( 'There is no tickets at this time', 'awesome-support-frontend-agents' ); ?>
    </div>

<?php endif; ?>
