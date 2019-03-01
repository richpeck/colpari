<?php

namespace TeamBooking\Admin\Framework;

class PanelForList extends Panel
{
    public function render()
    {
        echo '<div class="tbk-panel">';
        echo '<div class="tbk-content">';
        echo '<h2>' . $this->title . ' ';
        foreach ($this->title_extra_content as $item) {
            if ($item instanceof Element) $item->render();
        }
        echo '</h2>';
        foreach ($this->elements as $element) {
            $element->render();
        }
        echo '</div></div>';
    }
}