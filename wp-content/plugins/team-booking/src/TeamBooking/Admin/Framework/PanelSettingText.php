<?php

namespace TeamBooking\Admin\Framework;

class PanelSettingText extends PanelSetting implements Element
{
    protected $field_classes = array('regular-text');
    protected $required = FALSE;
    protected $placeholder = '';
    protected $default_value = '';
    protected $read_only = FALSE;


    public function addFieldClass($class)
    {
        $this->field_classes[] = $class;
    }

    public function setRequired($bool)
    {
        if (!$bool) {
            $this->required = FALSE;
        } else {
            $this->required = TRUE;
        }
    }

    public function setReadOnly($bool)
    {
        if (!$bool) {
            $this->read_only = FALSE;
        } else {
            $this->read_only = TRUE;
        }
    }

    public function addDefaultValue($value)
    {
        $this->default_value = $value;
    }

    public function addPlaceholder($text)
    {
        $this->placeholder = esc_attr($text);
    }

    public function render()
    {
        echo '<h4>' . $this->title . '</h4>';
        if (!empty($this->description)) echo '<p>' . $this->description . '</p>';
        echo '<p>';
        echo '<input type="text"';
        if (!empty($this->default_value)) echo ' value="' . $this->default_value . '"';
        if (!empty($this->placeholder)) echo ' placeholder="' . $this->placeholder . '"';
        if ($this->read_only) echo ' readonly="readonly"';
        echo ' class="' . implode(" ", $this->field_classes) . '"';
        echo ' name="' . $this->fieldname . '"';
        if ($this->required) echo ' required="required"';
        echo '>';
        echo \TeamBooking\Actions\backend_panel_setting_after_content('', $this->fieldname, $this->service_id);
        echo '</p>';
        $this->renderAlerts();
    }
}