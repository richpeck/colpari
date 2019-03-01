<?php


class WPAC_Object {
    /**
     * 
     * @param array $args
     */
    public function __construct($args = array()) {
        
        foreach($args as $key => $val) {
            if(property_exists($this, $key)) {
                $this->{$key} = $val;
            }
        }
    }
}