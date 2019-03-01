<?php

namespace TeamBooking\Admin\Framework;

class OnOffSlider implements Element
{
    protected $id;
    protected $checked = FALSE;
    protected $checked_callback = '';
    protected $unchecked_callback = '';
    protected $hidden = FALSE;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function setHidden($bool)
    {
        $this->hidden = (bool)$bool;
    }

    public function setChecked($bool)
    {
        $this->checked = (bool)$bool;
    }

    public function addCheckedCallback($javascript)
    {
        $this->checked_callback = $javascript;
    }

    public function addUncheckedCallback($javascript)
    {
        $this->unchecked_callback = $javascript;
    }

    public function render()
    {
        echo '<div class="ui slider checkbox" id="' . $this->id . '"';
        if ($this->hidden) echo ' style="display:none;"';
        echo '>';
        echo '<input type="checkbox" name="' . $this->id . '"';
        if ($this->checked) echo ' checked="checked"';
        echo '></div>';
        ?>
        <!-- toggle script -->
        <script>
            jQuery(document).ready(function () {
                jQuery('#<?= $this->id ?>')
                    .checkbox({
                        fireOnInit : false,
                        onChecked  : function () {
                            <?= $this->checked_callback ?>
                        },
                        onUnchecked: function () {
                            <?= $this->unchecked_callback ?>
                        }
                    });
            });
        </script>
        <?php
    }
}