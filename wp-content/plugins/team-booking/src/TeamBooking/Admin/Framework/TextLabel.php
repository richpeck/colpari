<?php

namespace TeamBooking\Admin\Framework;

class TextLabel implements Element
{
    protected $text;
    protected $color = '';
    protected $id = '';
    protected $data = array();
    protected $hidden = FALSE;

    public function __construct($text)
    {
        $this->text = $text;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setClass($class)
    {
        $this->color .= ' ' . $class;
    }

    public function setColor($color)
    {
        if (empty($this->color)) {
            $this->color = $color;
        } else {
            $this->color .= ' ' . $color;
        }
    }

    public function setHidden($bool)
    {
        $this->hidden = (bool)$bool;
    }

    public function addData(array $array)
    {
        foreach ($array as $data => $value) {
            $this->data[ $data ] = $value;
        }
    }

    public static function basic($text)
    {
        return TextLabel::getOfColor($text, '');
    }

    public static function blue($text)
    {
        return TextLabel::getOfColor($text, 'blue');
    }

    public static function yellow($text)
    {
        return TextLabel::getOfColor($text, 'yellow');
    }

    public static function red($text)
    {
        return TextLabel::getOfColor($text, 'red');
    }

    public static function green($text)
    {
        return TextLabel::getOfColor($text, 'green');
    }


    public static function black($text)
    {
        return TextLabel::getOfColor($text, 'black');
    }

    private static function getOfColor($text, $color)
    {
        $label = new TextLabel($text);
        $label->setColor($color);

        return $label;
    }

    public function get()
    {
        ob_start();
        $this->render();

        return ob_get_clean();
    }

    public function render()
    {
        echo '<div class="ui mini label ' . $this->color . '"';
        if (!empty($this->id)) echo ' id="' . $this->id . '"';
        if (!empty($this->data)) {
            foreach ($this->data as $data => $value) {
                echo ' data-' . $data . '="' . $value . '"';
            }
        }
        if ($this->hidden) echo ' style="display:none;"';
        echo '>' . esc_html($this->text) . '</div>';
    }
}