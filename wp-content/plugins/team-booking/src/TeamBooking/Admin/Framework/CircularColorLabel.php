<?php

namespace TeamBooking\Admin\Framework;

class CircularColorLabel implements Element
{
    protected $color;

    public function __construct($color)
    {
        $this->color = $color;
    }

    public static function ofColor($color)
    {
        return new CircularColorLabel($color);
    }

    public function render()
    {
        echo '<div class="ui circular empty label" style="background-color:' . $this->color . ';vertical-align: middle;"></div>';
    }
}