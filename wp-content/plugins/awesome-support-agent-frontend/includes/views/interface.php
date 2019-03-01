<?php defined('ABSPATH') or exit; ?>

<div class="as-fa-desktop-container">
    
    <ul id="wpas-fa-tabs-menu">
        <li>
            <a href="#" class="active" data-page-id="open-tickets">
                <?php _e( 'Open tickets', 'awesome-support-frontend-agents' ); ?>
            </a>
        </li>
        <li>
            <a href="#" data-page-id="closed-tickets">
                <?php _e( 'Closed tickets', 'awesome-support-frontend-agents' ); ?>
            </a>
        </li>

        <?php do_action( 'as-frontend-agent-tabs' ); ?>

        <li class="wpas-fa-setting-menu-tab">

            <a href="#">
                <span class="dashicons dashicons-admin-generic"></span>
            </a>

            <div class="wpas-fa-settings-menu">

                <div class="wpas-fa-arrow-up"></div>

                <a href="#" id="wpas-fa-logout" class="wpas-fa-setting-menu-link"><?php _e( 'Logout', 'awesome-support-frontend-agents'  ); ?></a>

                <?php do_action( 'as-frontend-settings-menu' ); ?>

            </div>

        </li>

    </ul>

    <div id="wpas-fa-tab-content">

        <div class="wpas-fa-tab-page active" data-page="open-tickets">   
        
            <?php 
            
                $this->loadTemplate( 'tickets_table', [ 
                    'table_id'      => 'fa-open-tickets', 
                    'custom_fields' => $this->getCustomFieldsBySection( 'desktop', 'ticket_list' ),
                    'tickets'       => $this->getOpenTickets() 
                ] ); 
            
            ?>  
        </div>


        <div class="wpas-fa-tab-page" data-page="closed-tickets"></div>

        <?php do_action( 'as-frontend-agent-tabs-content' ); ?>

    </div>
</div>

