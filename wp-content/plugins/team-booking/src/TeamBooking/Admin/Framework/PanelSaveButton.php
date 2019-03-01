<?php

namespace TeamBooking\Admin\Framework;

class PanelSaveButton implements Element
{
    protected $name;
    protected $value;
    protected $with_multi_apply;
    protected $attr;

    public function __construct($name, $value, $with_multi_apply = FALSE, array $attr = array())
    {
        $this->name = esc_html($name);
        $this->value = $value;
        $this->with_multi_apply = $with_multi_apply;
        $this->attr = $attr;
    }

    public function render()
    {
        echo '<p class="submit">';
        submit_button($this->name, 'primary', $this->value, FALSE);
        if ($this->with_multi_apply) {
            submit_button($this->with_multi_apply['name'], 'tbk-apply-to-all', $this->with_multi_apply['value'], FALSE, $this->attr);
        }
        echo '</p>';
    }

    public function appendTo(Panel $panel)
    {
        $panel->addElement($this);
    }
}