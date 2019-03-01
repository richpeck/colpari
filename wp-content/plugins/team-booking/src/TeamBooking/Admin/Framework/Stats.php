<?php

namespace TeamBooking\Admin\Framework;

class Stats implements Element
{
    protected $text;
    protected $value;

    public function __construct($text, $value)
    {
        $this->text = $text;
        $this->value = $value;
    }

    public static function get($text, $value)
    {
        return new Stats($text, $value);
    }

    public function render()
    {
        echo '<a class="tbk-stats">';
        echo '<div class="tbk-content">';
        echo '<div class="tbk-stats-num">';
        echo $this->value;
        echo '</div>';
        echo '<span>';
        echo esc_html($this->text);
        echo '</span>';
        echo '</div>';
        echo '</a>';
    }
}