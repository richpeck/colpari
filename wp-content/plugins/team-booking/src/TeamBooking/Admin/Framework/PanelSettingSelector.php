<?php

namespace TeamBooking\Admin\Framework;

class PanelSettingSelector extends PanelSetting implements Element
{
    protected $selected;
    protected $label = '';
    protected $options = array();
    protected $warnings = array();

    public function addOption($value, $text)
    {
        $this->options[ $value ] = $text;
    }

    public function addWarning($value, $text)
    {
        $this->warnings[ $value ] = $text;
    }

    public function setSelected($value)
    {
        $this->selected = $value;
    }

    public function render()
    {
        echo '<h4>' . $this->title . ' </h4>';
        if (!empty($this->description)) {
            echo '<p>' . $this->description . '</p>';
        }
        echo '<p>';
        echo '<select name="' . $this->fieldname . '" id="' . $this->fieldname . '">';
        foreach ($this->options as $value => $text) {
            echo '<option value="' . $value . '"';
            if ((string)$this->selected == (string)$value) echo ' selected="selected"';
            if (isset($this->warnings[ $value ])) echo ' data-warning="' . esc_attr($this->warnings[ $value ]) . '"';
            echo '>' . esc_html($text) . '</option>';
        }
        echo '</select>';
        if (!empty($this->label)) echo ' <span>' . esc_html($this->label) . '</span>';
        echo '</p>';
        if (!empty($this->warnings)) {
            echo '<div id="' . $this->fieldname . '_warning" class="' . 'tb-admin-selector-warning" style="display: none;"></div>';
            ?>
            <!-- This script handles the warnings -->
            <script>
                jQuery(document).ready(function () {
                    jQuery('#<?=$this->fieldname ?>').change(function () {
                        var text = jQuery(this).find('option:selected').data('warning');
                        if (text !== undefined && text !== '') {
                            jQuery('#<?=$this->fieldname . '_warning'?>').html(text).show();
                        } else {
                            jQuery('#<?=$this->fieldname . '_warning'?>').empty().hide();
                        }
                    });
                });
            </script>
            <?php
        }
        $this->renderAlerts();
    }
}