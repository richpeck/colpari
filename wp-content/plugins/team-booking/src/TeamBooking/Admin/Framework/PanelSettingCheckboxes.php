<?php

namespace TeamBooking\Admin\Framework;

class PanelSettingCheckboxes extends PanelSetting implements Element
{
    protected $dividers = FALSE;
    protected $checkboxes = array();

    /**
     * The checkbox array's structure:
     *
     * $checkbox['fieldname']     --> the checkbox fieldname
     * $checkbox['label']         --> the label's text OR an Element object
     * $checkbox['label_title']   --> the checkbox title for accessibility purpose
     * $checkbox['value']         --> the checkbox value
     * $checkbox['checked']       --> 1 for checked, 0 otherwise
     * $checkbox['label_classes'] --> additional classes for the label (i.e. "class_1 class_2")
     * $checkbox['description']   --> optional description (text OR an Element object)
     *
     * @param array $checkbox
     */
    public function addCheckbox(array $checkbox)
    {
        $empty = array(
            'label'       => '',
            'label_title' => '',
            'value'       => '',
            'checked'     => 0,
            'fieldname'   => $this->fieldname . '[' . (count($this->checkboxes) + 1) . ']',
        );
        $this->checkboxes[] = array_merge($empty, $checkbox);
    }

    public function setDividers($bool)
    {
        $this->dividers = (bool)$bool;
    }

    public function render()
    {
        echo '<h4>' . $this->title . '</h4>';
        if (!empty($this->description)) {
            echo '<p>' . $this->description . '</p>';
        }
        echo '<fieldset>';
        foreach ($this->checkboxes as $checkbox) {
            if ($this->dividers) echo '<div class="tbk-light-divider"></div>';
            echo '<label title="' . esc_attr((empty($checkbox['label_title']) && (!$checkbox['label'] instanceof Element)) ? $checkbox['label'] : $checkbox['label_title']) . '">';
            echo '<table><tbody><tr>';
            echo '<td style="vertical-align: baseline;padding: 0">';
            echo '<input type="checkbox" style="vertical-align: sub;" name="' . $checkbox['fieldname'] . '" value="' . $checkbox['value'] . '"';
            if ($checkbox['checked']) echo ' checked="checked"';
            echo '>';
            echo '</td>';
            echo '<td style="vertical-align: baseline;padding: 0;font-size: 13px;">';
            if ($checkbox['label'] instanceof Element) {
                echo '<div>';
                $checkbox['label']->render();
                echo '</div>';
            } else {
                if (isset($checkbox['label_classes'])) {
                    echo '<div class="' . $checkbox['label_classes'] . '">';
                } else {
                    echo '<div>';
                }
                echo esc_html($checkbox['label']);
                echo '</div>';
            }
            if (isset($checkbox['description'])) {
                if ($checkbox['description'] instanceof Element) {
                    $checkbox['description']->render();
                } else {
                    echo '<p>' . $checkbox['description'] . '</p>';
                }
            }
            echo '</td>';
            echo '</tr></tbody></table>';
            echo '</label>';
        }
        echo '</fieldset>';
        $this->renderAlerts();
    }
}