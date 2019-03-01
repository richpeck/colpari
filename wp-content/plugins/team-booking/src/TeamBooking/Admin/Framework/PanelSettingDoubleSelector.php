<?php

namespace TeamBooking\Admin\Framework;

class PanelSettingDoubleSelector extends PanelSettingSelector
{
    protected $fieldname_2;
    protected $selected_2;
    protected $label_2 = '';

    public function addFieldname2($one, $two)
    {
        $this->fieldname = $one;
        $this->fieldname_2 = $two;
    }

    public function addOption2($value, $text, $to_which)
    {
        $this->options[ $to_which ][ $value ] = $text;
    }

    public function setSelected2($one, $two)
    {
        $this->selected = $one;
        $this->selected_2 = $two;
    }

    public function render()
    {
        echo '<h4>' . $this->title . '</h4>';
        if (!empty($this->description)) {
            echo '<p>' . $this->description . '</p>';
        }
        echo '<p>';
        echo '<select name="' . $this->fieldname . '">';
        foreach ($this->options[ $this->fieldname ] as $value => $text) {
            echo '<option value="' . $value . '"';
            if ($this->selected == $value) echo ' selected="selected"';
            echo '>' . esc_html($text) . '</option>';
        }
        echo '</select>';
        if (!empty($this->label)) echo '<span>' . esc_html($this->label) . '</span>';
        echo '<select name="' . $this->fieldname_2 . '">';
        foreach ($this->options[ $this->fieldname_2 ] as $value => $text) {
            echo '<option value="' . $value . '"';
            if ($this->selected_2 == $value) echo ' selected="selected"';
            echo '>' . esc_html($text) . '</option> ';
        }
        echo '</select>';
        if (!empty($this->label_2)) echo '<span>' . esc_html($this->label_2) . '</span>';
        echo '</p>';
    }
}