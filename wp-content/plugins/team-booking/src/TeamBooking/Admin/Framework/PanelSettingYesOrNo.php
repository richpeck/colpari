<?php

namespace TeamBooking\Admin\Framework;

class PanelSettingYesOrNo extends PanelSetting implements Element
{
    protected $default = TRUE;
    protected $disabled = FALSE;

    public function setState($bool)
    {
        if ($bool) {
            $this->default = TRUE;
        } else {
            $this->default = FALSE;
        }
    }

    public function setDisabled($bool)
    {
        $this->disabled = (bool)$bool;
    }

    public function render()
    {
        echo '<h4>' . $this->title . '</h4>';
        if (!empty($this->description)) echo '<p>' . $this->description . '</p>';
        if ($this->disabled) {
            echo '<fieldset disabled="disabled">';
        } else {
            echo '<fieldset>';
        }
        echo '<label title="yes"><input type="radio" value="1"';
        echo ' name="' . $this->fieldname . '"';
        if ($this->default) echo ' checked="checked"';
        echo '></input>';
        echo '<div style="display: inline">' . esc_html__('Yes', 'team-booking') . '</div>';
        echo '</label>';
        echo '</br>';
        echo '<label title="no"><input type="radio" value="0"';
        echo ' name="' . $this->fieldname . '"';
        if (!$this->default) echo ' checked="checked"';
        echo '></input>';
        echo '<div style="display: inline">' . esc_html__('No', 'team-booking') . '</div>';
        echo '</label>';
        echo '</fieldset>';
        $this->renderAlerts();
    }
}