<?php

namespace TeamBooking\Admin\Framework;

class PanelSettingNumber extends PanelSetting implements Element
{
    protected $min = NULL;
    protected $max = NULL;
    protected $step = NULL;
    protected $value;
    protected $field_description = '';

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function setMin($number)
    {
        $this->min = $number;
    }

    public function setMax($number)
    {
        $this->max = $number;
    }

    public function setStep($number)
    {
        $this->step = $number;
    }

    public function setFieldDescription($text)
    {
        $this->field_description = $text;
    }

    public function render()
    {
        echo '<h4>' . $this->title . '</h4>';
        if (!empty($this->description)) {
            echo '<p>' . $this->description . '</p>';
        }
        echo '<p><input type="number" class="small-text"';
        if (!is_null($this->min)) echo ' min="' . $this->min . '"';
        if (!is_null($this->max)) echo ' max="' . $this->max . '"';
        if (!is_null($this->step)) echo ' step="' . $this->step . '"';
        echo ' name="' . $this->fieldname . '" value="' . $this->value . '">';
        if (!empty($this->field_description)) echo ' <span class="description">' . esc_html($this->field_description) . '</span>';
        echo '</p>';
        $this->renderAlerts();
    }
}