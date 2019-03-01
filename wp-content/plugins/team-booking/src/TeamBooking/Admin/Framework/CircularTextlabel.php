<?php

namespace TeamBooking\Admin\Framework;

class CircularTextlabel extends TextLabel
{
    public function render()
    {
        echo '<div class="ui circular mini label ' . $this->color . '"';
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