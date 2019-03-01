<?php

namespace TeamBooking\Admin\Framework;

class PanelSettingTextWithLock extends PanelSettingText
{
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
        if (!empty($this->default_value)) {
            echo '<div class="ui toggle checkbox" id="' . $this->fieldname . '_lock" style="vertical-align: initial;">';
            echo '<input type="checkbox">';
            echo '<label>' . esc_html__('locked', 'team-booking') . '</label>';
            echo '</div>';
            ?>
            <!-- lock/unlock script -->
            <script>
                jQuery(document).ready(function () {
                    jQuery('#<?=$this->fieldname . '_lock'?>').checkbox({
                        onChecked  : function () {
                            jQuery('#<?=$this->fieldname . '_lock'?>').closest('li').find('input[name="<?=$this->fieldname?>"]').attr("readonly", false);
                            jQuery('#<?=$this->fieldname . '_lock'?>').find('label').html("<?= esc_html__('unlocked', 'team-booking') ?>");
                        },
                        onUnchecked: function () {
                            jQuery('#<?=$this->fieldname . '_lock'?>').closest('li').find('input[name="<?=$this->fieldname?>"]').attr("readonly", true);
                            jQuery('#<?=$this->fieldname . '_lock'?>').find('label').html("<?= esc_html__('locked', 'team-booking') ?>");
                        },
                        fireOnInit : false
                    });
                });
            </script>
            <?php
        }
        echo '</p>';
    }
}