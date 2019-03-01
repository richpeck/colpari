<?php

defined('ABSPATH') or die("No script kiddies please!");
use TeamBooking\Toolkit;

#TeamBookingFormTextField
#TeamBooking_Components_Form_TextField

/**
 * @deprecated 2.2.0 No longer used by internal code
 * @see        \TeamBooking\FormElements\TextService
 *
 * Class TeamBookingFormTextField
 */
class TeamBookingFormTextField
{
    protected $hook;
    protected $label;
    protected $active;
    protected $required;
    protected $value;
    protected $validation_regex;
    protected $validation_rule;

    //------------------------------------------------------------

    public function setOn()
    {
        $this->active = TRUE;
    }

    public function setOff()
    {
        $this->active = FALSE;
    }

    public function getIsActive()
    {
        return $this->active;
    }

    //------------------------------------------------------------

    public function setRequiredOn()
    {
        $this->required = TRUE;
    }

    public function setRequiredOff()
    {
        $this->required = FALSE;
    }

    public function getIsRequired()
    {
        return $this->required;
    }

    //------------------------------------------------------------

    public function getHook()
    {
        return $this->hook;
    }

    public function setHook($hook)
    {
        $this->hook = Toolkit\filterInput($hook, TRUE);
    }

    //------------------------------------------------------------

    /**
     * @return TeamBooking_Components_Form_Option|mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    //------------------------------------------------------------

    public function getMarkup($input_size = '')
    {
        if ($this->getValidationRegex()) {
            $validation_regex = 'data-validation="' . base64_encode($this->getValidationRegex()) . '"';
        } else {
            $validation_regex = '';
        }
        $random_append = substr(md5(rand()), 0, 8);
        ?>
        <div class="tbk-field <?= $this->getRequiredFieldClass() ?>">
            <label><?= esc_html($this->wrapStringForTranslations($this->getLabel())) ?></label>
            <input id="tbk-<?= esc_attr($this->hook) ?>-<?= $random_append ?>" type="text"
                   name="form_fields[<?= esc_attr($this->hook) ?>]" value="<?= esc_attr($this->value) ?>"
                   style="height:inherit;max-width: none;" <?= $this->required ? "required='required'" : "" ?> <?= $validation_regex ?>>
            <?php $this->getValidationMessageLabel() ?>
        </div>
        <?php if ($this->hook == 'address' && !is_null(TeamBooking\Functions\getSettings()->getGmapsApiKey())) { ?>
        <!-- GeoComplete -->
        <script>
            if (typeof google !== 'undefined' && typeof google.maps === 'object' && typeof google.maps.places === 'object') {
                jQuery("#tbk-<?= $this->hook . '-' . $random_append ?>").geocomplete();
            }
        </script>
    <?php } ?>
        <?php
    }

    //------------------------------------------------------------

    public function getValidationRegex($custom_directly = FALSE)
    {
        if (isset($this->validation_regex)) {
            if ($custom_directly) {
                return $this->validation_regex;
            } else {
                if ($this->getValidationRule() == 'email') {
                    return '^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$';
                } elseif ($this->getValidationRule() == 'alphanumeric') {
                    return '^[a-zA-Z0-9]+$';
                } elseif ($this->getValidationRule() == 'phone') {
                    return '^(1\s*[-\/\.]?\s*)?(\((\d{3})\)|(\d{3}))\s*[-\/\.]?\s*(\d{3})\s*[-\/\.]?\s*(\d{4})\s*(([xX]|[eE][xX][tT]?)\.?\s*([#*\d]+))*$';
                } elseif ($this->getValidationRule() == 'custom') {
                    return $this->validation_regex;
                } else {
                    return FALSE;
                }
            }
        } else {
            return FALSE;
        }
    }

    public function setValidationRegex($expression)
    {
        $this->validation_regex = $expression;
    }

    //------------------------------------------------------------

    public function getValidationRule()
    {
        return $this->validation_rule;
    }

    public function setValidationRule($rule)
    {
        $this->validation_rule = $rule;
    }

    //------------------------------------------------------------

    protected function getRequiredFieldClass()
    {
        if ($this->required) {
            return 'tbk-required';
        } else {
            return '';
        }
    }

    //------------------------------------------------------------

    protected function wrapStringForTranslations($text)
    {
        $builtin_labels = array(
            'first_name'  => __('First name', 'team-booking'),
            'second_name' => __('Last name', 'team-booking'),
            'email'       => __('Email', 'team-booking'),
            'address'     => __('Address', 'team-booking'),
            'phone'       => __('Phone number', 'team-booking'),
            'url'         => __('Website', 'team-booking'),
        );

        if (isset($builtin_labels[ $this->hook ])) {
            return $builtin_labels[ $this->hook ];
        } else {
            // TODO
            return $text;
        }
    }

    //------------------------------------------------------------

    public function getLabel()
    {
        return htmlspecialchars($this->wrapStringForTranslations(Toolkit\unfilterInput($this->label)), ENT_QUOTES, 'UTF-8');
    }

    public function setLabel($label)
    {
        $this->label = Toolkit\filterInput($label);
    }

    //------------------------------------------------------------

    // HTML Semantic UI mapper

    protected function getValidationMessageLabel()
    {
        ?>
        <div class="tbk-reservation-form-pointing-error" style="display:none;">
            <?= esc_html__('Please enter a correct value', 'team-booking') ?>
        </div>
        <?php
    }

    //------------------------------------------------------------

    public function getHiddenMarkup()
    {
        ?>
        <input type="hidden" name="form_fields[<?= esc_attr($this->hook) ?>]" value="<?= esc_attr($this->value) ?>">
        <?php
    }

    //------------------------------------------------------------

    public function getOptions()
    {
        return array();
    }

    public function getProperties()
    {
        $properties = array(
            'hook'        => $this->getHook(),
            'description' => '',
            'required'    => $this->getIsRequired(),
            'visible'     => $this->getIsActive(),
            'title'       => Toolkit\unfilterInput($this->label),
            'data'        => array(
                'validation' => array(
                    'validation_regex' => array(
                        'custom' => $this->getValidationRegex(TRUE)
                    ),
                    'validate'         => $this->getValidationRule() === 'none' ? FALSE : $this->getValidationRule()
                ),
                'value'      => $this->getValue() instanceof TeamBooking_Components_Form_Option ? $this->getValue()->getText() : $this->getValue()
            )
        );

        return $properties;
    }

}
