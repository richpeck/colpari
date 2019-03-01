<?php

namespace TeamBooking\Admin\Framework;

class Row implements Element
{
    /** @var Element[] */
    private $elements = array();

    public function addElement(Element $element)
    {
        $this->elements[] = $element;
    }

    public function render()
    {
        echo '<div class="tbk-row">';
        foreach ($this->elements as $element) {
            $element->render();
        }
        echo '</div>';
    }
}