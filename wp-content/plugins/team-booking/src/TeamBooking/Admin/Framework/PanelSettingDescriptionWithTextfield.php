<?php

namespace TeamBooking\Admin\Framework;

class PanelSettingDescriptionWithTextfield extends PanelSetting implements Element
{
    protected $disabled = FALSE;
    protected $default_text = NULL;
    protected $placeholder = '';

    public function setDisabled($bool)
    {
        $this->disabled = (bool) $bool;
    }

    public function setDefaultText($text)
    {
        $this->default_text = $text;
    }

    public function setPlaceholder($text)
    {
        $this->placeholder = $text;
    }

    public function render()
    {
        if (!empty($this->description)) {
            echo '<p>' . $this->description . '</p>';
        }
        echo '<p><input type="text" class="regular-text" name="' . $this->fieldname . '"';
        if (!empty($this->placeholder)) echo ' placeholder="' . esc_attr($this->placeholder) . '"';
        if ($this->disabled) echo ' disabled';
        echo ' value="' . esc_attr($this->default_text) . '"';
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