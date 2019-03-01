<?php

namespace TeamBooking\Admin\Framework;

class PanelSettingColoredRadios extends PanelSetting implements Element
{
    protected $default_radio = array();
    protected $colored_radios = array();
    protected $selected = NULL;

    public function setDefaultRadio($value, $text)
    {
        $this->default_radio['value'] = $value;
        $this->default_radio['text'] = $text;
    }

    public function addRadio($value, $hex_color)
    {
        $this->colored_radios[ $value ] = $hex_color;
    }

    public function setSelected($value)
    {
        $this->selected = $value;
    }

    public function render()
    {
        echo '<h4>' . $this->title . '</h4>';
        if (!empty($this->description)) {
            echo '<p>' . $this->description . '</p>';
        }
        echo '<fieldset>';
        echo '<label title="' . esc_attr($this->default_radio['text']) . '">';
        echo '<input type="radio" name="' . $this->fieldname . '" value="' . $this->default_radio['value'] . '"';
        if ($this->selected == $this->default_radio['value']) echo ' checked="checked"';
        echo '></input>';
        echo '<span>' . esc_html($this->default_radio['text']) . '</span>';
        echo '</label><br><br>';
        // Moz hack
        echo '<style type="text/css">';
        echo '@-moz-document url-prefix(){';
        foreach ($this->colored_radios as $color) {
            echo "input[type='radio']._" . $color . '{
        ';
            echo 'outline: 2px solid #' . $color . '}';
        }
        echo '}';
        echo '</style>';
        foreach ($this->colored_radios as $value => $color) {
            echo '<input type="radio" style="background:#' . $color . ';" class="_' . $color . '"';
            echo ' value="' . $value . '" name="' . $this->fieldname . '"';
            if ($this->selected == $value) echo ' checked="checked"';
            echo '>';
        }
        echo '</fieldset>';
    }
}