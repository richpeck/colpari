<?php

namespace TeamBooking\Admin\Framework;

class PanelSettingRadios extends PanelSetting implements Element
{
    protected $dividers = FALSE;
    protected $options = array();
    protected $legend = FALSE;

    /**
     * The option array's structure:
     *
     * $option['label']         --> the label's text OR an Element object
     * $option['label_title']   --> the option's title for accessibility purpose
     * $option['value']         --> the option's value
     * $option['checked']       --> 1 for checked, 0 otherwise
     * $option['label_classes'] --> additional classes for the label (i.e. "class_1 class_2")
     * $option['description']   --> optional description (text OR an Element object)
     * $option['disabled']      --> 1 for disabled, 0 otherwise
     *
     * @param array $option
     */
    public function addOption(array $option)
    {
        $empty = array(
            'label'       => '',
            'label_title' => '',
            'value'       => '',
            'checked'     => 0,
            'disabled'    => 0,
            'escape'      => TRUE
        );
        $this->options[] = array_merge($empty, $option);
    }

    public function addLegend($text)
    {
        $this->legend = esc_html($text);
    }

    public function setDividers($bool)
    {
        if ($bool) {
            $this->dividers = TRUE;
        } else {
            $this->dividers = FALSE;
        }
    }

    public function render()
    {
        if (!empty($this->title)) echo '<h4>' . $this->title . '</h4>';
        if (!empty($this->description)) echo '<p>' . $this->description . '</p>';
        echo '<fieldset>';
        if ($this->legend) echo '<legend>' . $this->legend . '</legend>';
        foreach ($this->options as $option) {
            if ($this->dividers) echo '<div class="tbk-light-divider"></div>';
            echo '<label title="' . esc_attr((empty($option['label_title']) && !($option['label'] instanceof Element)) ? $option['label'] : $option['label_title']) . '">';
            echo '<table><tbody><tr>';
            echo '<td style="vertical-align: baseline;padding: 0">';
            echo '<input type="radio" name="' . $this->fieldname . '" value="' . $option['value'] . '"';
            if ($option['checked']) echo ' checked="checked"';
            if ($option['disabled']) echo ' disabled="disabled"';
            echo '>';
            echo '</td>';
            echo '<td style="vertical-align: baseline;padding: 0;font-size: 13px;">';
            if ($option['label'] instanceof Element) {
                echo '<div>';
                $option['label']->render();
                echo '</div>';
            } else {
                if (isset($option['label_classes'])) {
                    echo '<div class="' . $option['label_classes'] . '">';
                } else {
                    echo '<div>';
                }
                if ($option['escape']) {
                    echo esc_html($option['label']);
                } else {
                    echo $option['label'];
                }
                echo '</div>';
            }
            if (isset($option['description'])) {
                if ($option['description'] instanceof Element) {
                    $option['description']->render();
                } else {
                    echo '<p>' . $option['description'] . '</p>';
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