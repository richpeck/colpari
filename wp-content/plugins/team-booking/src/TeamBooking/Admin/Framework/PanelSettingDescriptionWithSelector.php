<?php

namespace TeamBooking\Admin\Framework;

class PanelSettingDescriptionWithSelector extends PanelSetting implements Element
{

    protected $options = array();
    protected $disabled = FALSE;
    protected $selected_option = NULL;

    /**
     * Array element structure:
     *
     * [value] => [text]
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    public function addOption($value, $text)
    {
        $this->options[ $value ] = $text;
    }

    public function setSelectedOption($value)
    {
        $this->selected_option = $value;
    }

    public function setDisabled($bool)
    {
        if ($bool) {
            $this->disabled = TRUE;
        } else {
            $this->disabled = FALSE;
        }
    }

    public function render()
    {
        if (!empty($this->description)) {
            echo '<p>' . $this->description . '</p>';
        }
        echo '<p><select name="' . $this->fieldname . '"';
        if ($this->disabled) echo ' disabled';
        echo '>';
        foreach ($this->options as $value => $text) {
            echo '<option value="' . $value . '"';
            if ($this->selected_option == $value) echo ' selected="selected"';
            echo '>' . esc_html($text) . '</option>';
        }
        echo '</select></p>';
        ?>
        <!-- script to disable/enable the dropdown -->
        <script>
            jQuery(document).ready(function () {
                var $select = jQuery('select[name="<?= $this->fieldname ?>"]');
                var radio_value = $select.closest('label').find(':radio').val();
                $select.closest('fieldset').find(':radio')
                    .on('click', function () {
                        if (this.value === radio_value) {
                            $select.prop('disabled', false);
                        } else {
                            $select.prop('disabled', 'disabled');
                        }
                    });
            });
        </script>
        <?php
    }
}