<?php defined('ABSPATH') or exit; 

$this->loadTemplate( 'mobile/tickets_list', [ 
    'tickets' => $this->getClosedTickets( 10 ) 
] ); 
