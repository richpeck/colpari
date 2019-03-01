<?php

namespace TeamBooking\Admin\Framework;

class PanelSettingColorPicker extends PanelSetting implements Element
{
    protected $value;

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function render()
    {
        echo '<h4>' . $this->title . '</h4>';
        echo '<p><input type="text" class="tb-color-field" name="' . $this->fieldname . '" value="' . $this->value . '" ></p>';
    }
}