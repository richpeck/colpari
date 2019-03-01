<?php

namespace TeamBooking\Admin\Framework;

class ElementFrom implements Element
{
    protected $content;

    public function __construct($content)
    {
        $this->content = $content;
    }

    static function content($content)
    {
        return new ElementFrom($content);
    }

    public function render()
    {
        echo $this->content;
    }
}