<?php

namespace TeamBooking\Admin\Framework;

class PanelSettingRichTextarea extends PanelSetting implements Element
{
    protected $text;

    public function setText($text)
    {
        $this->text = $text;
    }

    public function render()
    {
        echo '<a class="ui mini circular label tb-toggle-email-editor" style="float: right">';
        echo '<span class="dashicons dashicons-welcome-write-blog"></span></a>';
        echo '<h4>' . $this->title . '</h4>';
        if (!empty($this->description)) {
            echo '<p>' . $this->description . '</p>';
        }
        echo '<div class="tb-email-editor">';
        wp_editor(
            $this->text,
            str_replace(array('[', ']'), '_', $this->fieldname),
            array(
                'media_buttons' => FALSE,
                'textarea_name' => $this->fieldname,
                'tinymce'       => FALSE,
                'textarea_rows' => 8,
            )
        );
        echo \TeamBooking\Actions\backend_panel_setting_after_content('', $this->fieldname, $this->service_id);
        echo '</div>';
    }
}