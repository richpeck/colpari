<?php defined('ABSPATH') or exit; ?>

<div class="as-fa-mobile-container">

    <div class="as-fa-mobile-header">

        <ul>
            
            <li id="as-fa-mobile-menu-left">

                <a href="#" class="as-fa-toggle-mobile-menu">
                    <i class="dashicons dashicons-menu"></i>
                </a>

                <a href="#" class="as-fa-mobile-back" data-back="open_tickets">
                    <i class="dashicons dashicons-arrow-left-alt2"></i>
                </a>

            </li>

            <li id="as-fa-mobile-menu-center">
                <?php _e( 'Open tickets', 'awesome-support-frontend-agents' ); ?>
            </li>

            <li id="as-fa-mobile-menu-right">
                
                <a href="#" class="as-fa-mobile-ticket-options">
                    <i class="dashicons dashicons-screenoptions"></i>
                </a>

            </li>
        </ul>

        <div class="as-fa-mobile-menu-options">

            <div>
                <a href="#" data-id="open_tickets" data-view="mobile/open_tickets" data-title="<?php _e( 'Open tickets', 'awesome-support-frontend-agents' ); ?>" >
                    <?php _e( 'Open tickets', 'awesome-support-frontend-agents' ); ?>
                </a>
            </div>
            <div>
                <a href="#" data-id="closed_tickets" data-view="mobile/closed_tickets" data-title="<?php _e( 'Closed tickets', 'awesome-support-frontend-agents' ); ?>" >
                    <?php _e( 'Closed tickets', 'awesome-support-frontend-agents' ); ?>
                </a>
            </div>
            <div>
                <a href="#" id="wpas-fa-logout" data-mobile="true">
                    <?php _e( 'Logout', 'awesome-support-frontend-agents' ); ?>
                </a>
            </div>
        </div>

    </div>

    <div id="as-fa-tab-mobile-content">

        <div class="as-fa-mobile-content-page active" data-window-content="open_tickets">

            <?php 

            $this->loadTemplate( 'mobile/tickets_list', [ 
                'tickets' => $this->getOpenTickets( 10 ) 
            ] );  

            ?>  

        </div>

    </div>

    <div id="as-fa-tab-mobile-reply-content"></div>

</div>
