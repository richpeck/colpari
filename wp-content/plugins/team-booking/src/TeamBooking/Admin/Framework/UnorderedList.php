<?php

namespace TeamBooking\Admin\Framework;

class UnorderedList implements Element
{
    protected $items = array();

    public function addItem($content)
    {
        $this->items[] = $content;
    }

    public function addStringToItem($item_key, $string)
    {
        if ($this->items[ $item_key ] instanceof Element) return;
        $this->items[ $item_key ] .= $string;
    }

    public function render()
    {
        echo '<ul class="tbk-list">';
        foreach ($this->items as $item) {
            echo '<li>';
            if ($item instanceof Element) {
                $item->render();
            } else {
                echo $item;
            }
            echo '</li>';
        }
        echo '</ul>';
    }
}