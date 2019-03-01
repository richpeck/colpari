<?php

namespace TeamBooking\Admin\Framework;

use TeamBooking\Admin;

class PanelWithForm extends Panel
{
    protected $action_value;
    protected $nonce_id;
    protected $form_id = '';

    public function setAction($action)
    {
        $this->action_value = $action;
    }

    public function setNonce($id)
    {
        $this->nonce_id = $id;
    }

    public function setFormId($id)
    {
        $this->form_id = $id;
    }

    public function render()
    {
        echo '<form method="POST" action="' . Admin::add_params_to_admin_url(admin_url('admin-post.php')) . '"';
        if (!empty($this->form_id)) echo ' id="' . $this->form_id . '"';
        echo '>';
        echo '<input type="hidden" name="action" value="' . $this->action_value . '">';
        wp_nonce_field($this->nonce_id);
        echo '<div class="tbk-panel">';
        echo '<div class="tbk-panel-title"><h4>' . $this->title . ' ';
        foreach ($this->title_extra_content as $item) {
            if ($item instanceof Element) $item->render();
        }
        echo '</h4></div>';
        echo '<div class="tbk-content">';
        echo '<ul class="tbk-list">';
        foreach ($this->elements as $element) {
            echo '<li>';
            $element->render();
            echo '</li>';
        }
        echo '</ul></div></div>';
        echo '</form>';
    }
}