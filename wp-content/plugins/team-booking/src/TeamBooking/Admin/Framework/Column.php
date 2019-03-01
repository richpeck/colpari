<?php

namespace TeamBooking\Admin\Framework;

class Column implements Element
{
    /** @var Element[] */
    private $elements = array();
    private $width = 12;

    public function __construct($width = 12)
    {
        $this->width = $width;
    }

    public function addElement(Element $element)
    {
        $this->elements[] = $element;
    }

    public static function fullWidth()
    {
        return new Column();
    }

    public static function ofWidth($int)
    {
        return new Column($int);
    }

    public function appendTo(Row &$row)
    {
        $row->addElement($this);
    }

    public function render()
    {
        echo '<div class="tbk-column tbk-span-' . $this->width . '">';
        foreach ($this->elements as $element) {
            $element->render();
        }
        echo '</div>';
    }
}