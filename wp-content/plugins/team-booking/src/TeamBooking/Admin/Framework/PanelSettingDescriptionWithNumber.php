<?php

namespace TeamBooking\Admin\Framework;

class PanelSettingDescriptionWithNumber extends PanelSetting implements Element
{
    protected $disabled = FALSE;
    protected $default_value = 0;
    protected $min = NULL;
    protected $max = NULL;
    protected $step = NULL;

    public function setDisabled($bool)
    {
        $this->disabled = (bool)$bool;
    }

    public function setDefaultValue($int)
    {
        $this->default_value = (int)$int;
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

    public function render()
    {
        if (!empty($this->description)) {
            echo '<p>' . $this->description . '</p>';
        }
        echo '<p><input type="number" class="small-text" name="' . $this->fieldname . '"';
        if ($this->disabled) echo ' disabled';
        if (NULL !== $this->min) echo ' min="' . $this->min . '"';
        if (NULL !== $this->max) echo ' max="' . $this->max . '"';
        if (NULL !== $this->step) echo ' step="' . $this->step . '"';
        echo ' value="' . esc_attr($this->default_value) . '"';
        echo '></p>';
        ?>
        <!-- script to disable/enable the textfield -->
        <script>
            jQuery(document).ready(function () {
                var $field = jQuery('input[name="<?= $this->fieldname ?>"]');
                var radio_value = $field.closest('label').find(':radio').val();
                $field.closest('fieldset').find(':radio')
                    .on('click', function () {
                        if (this.value === radio_value) {
                            $field.prop('disabled', false);
                        } else {
                            $field.prop('disabled', 'disabled');
                        }
                    });
            });
        </script>
        <?php
    }
}