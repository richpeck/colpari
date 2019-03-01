<?php

namespace TeamBooking\Admin\Framework;

class PanelSettingTimespanSelector extends PanelSetting implements Element
{
    protected $max_hours = 23;
    protected $max_mins = 59;
    protected $max_days = 90;
    protected $selected_hours;
    protected $selected_mins;
    protected $selected_days;
    protected $hours_label = 'hours';
    protected $mins_label = 'minutes';
    protected $days_label = 'days';
    protected $show_days = FALSE;
    protected $nested = FALSE;

    public function setShowDays($bool)
    {
        if ($bool) {
            $this->show_days = TRUE;
        } else {
            $this->show_days = FALSE;
        }
    }

    public function isNested($bool)
    {
        if ($bool) {
            $this->nested = TRUE;
        } else {
            $this->nested = FALSE;
        }
    }

    public function setMaxHours($int)
    {
        $this->max_hours = (int)$int;
    }

    public function setMaxMins($int)
    {
        $this->max_mins = (int)$int;
    }

    public function setMaxDays($int)
    {
        $this->max_days = (int)$int;
    }

    public function setSelectedHours($int)
    {
        $this->selected_hours = (int)$int;
    }

    public function setSelectedMins($int)
    {
        $this->selected_mins = (int)$int;
    }

    public function setSelectedDays($int)
    {
        $this->selected_days = (int)$int;
    }

    public function setHoursLabel($text)
    {
        $this->hours_label = $text;
    }

    public function setMinsLabel($text)
    {
        $this->mins_label = $text;
    }

    public function setDaysLabel($text)
    {
        $this->days_label = $text;
    }

    public function render()
    {
        if ($this->nested) {
            echo '<div>' . $this->title . '</div>';
        } else {
            echo '<h4>' . $this->title . '</h4>';
        }
        if (!empty($this->description)) {
            echo '<p>' . $this->description . '</p>';
        }
        echo '<div style="display:inline-block;vertical-align:top;">';
        if ($this->show_days) {
            echo '<select name="' . $this->fieldname . '[days]">';
            for ($i = 0; $i <= $this->max_days; $i++) {
                echo '<option value="' . $i . '"';
                if ($this->selected_days == $i) echo 'selected="selected"';
                echo '>' . $i . '</option>';
            }
            echo '</select>';
            echo '<span> ' . esc_html($this->days_label) . '</span>';
            echo '<br>';
        }
        echo '<select name="' . $this->fieldname . '[hours]">';
        for ($i = 0; $i <= $this->max_hours; $i++) {
            echo '<option value="' . $i . '"';
            if ($this->selected_hours == $i) echo ' selected="selected"';
            echo '>' . $i . '</option>';
        }
        echo '</select>';
        echo '<span> ' . esc_html($this->hours_label) . '</span>';
        echo '<br>';
        echo '<select name="' . $this->fieldname . '[minutes]">';
        for ($i = 0; $i <= $this->max_mins; $i++) {
            echo '<option value="' . $i . '"';
            if ($this->selected_mins == $i) echo ' selected="selected"';
            echo '>' . $i . '</option>';
        }
        echo '</select>';
        echo ' <span> ' . esc_html($this->mins_label) . '</span>';
        echo '</div>';
        if ($this->nested) {
            ?>
            <!-- script to disable/enable the selectors -->
            <script>
                jQuery(document).ready(function () {
                    var $select_h = jQuery('select[name="<?= $this->fieldname . '[hours]'?>"]');
                    var $select_m = jQuery('select[name="<?= $this->fieldname . '[minutes]'?>"]');
                    var radio_value = $select_h.closest('label').find(':radio').val();
                    $select_h.closest('fieldset').find(':radio')
                        .on('click', function () {
                            if (this.value === radio_value) {
                                $select_h.prop('disabled', false);
                                $select_m.prop('disabled', false);
                            } else {
                                $select_h.prop('disabled', 'disabled');
                                $select_m.prop('disabled', 'disabled');
                            }
                        });
                });
            </script>
            <?php
        }
    }
}