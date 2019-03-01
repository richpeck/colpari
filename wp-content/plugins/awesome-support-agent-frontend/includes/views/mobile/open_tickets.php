<?php defined('ABSPATH') or exit; 

$this->loadTemplate( 'mobile/tickets_list', [ 
    'custom_fields' => $this->getCustomFieldsBySection( 'ticket_list' ),
    'tickets'       => $this->getOpenTickets( 10 ) 
] ); 
