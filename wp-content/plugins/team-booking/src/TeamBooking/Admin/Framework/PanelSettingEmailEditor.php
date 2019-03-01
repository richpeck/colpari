<?php

namespace TeamBooking\Admin\Framework;

use TeamBooking\Database\EmailTemplates;

class PanelSettingEmailEditor extends PanelSetting implements Element
{
    protected $placeholders = array();
    protected $subject = '';
    protected $body = '';
    protected $show_send = FALSE;
    protected $send_state = FALSE;
    protected $send_fieldname = '';
    protected $show_repeat_delimiters = FALSE;
    protected $show_templates = TRUE;

    public function setSendState($bool)
    {
        $this->send_state = (bool)$bool;
    }

    public function setShowSend($bool)
    {
        $this->show_send = (bool)$bool;
    }

    public function setShowRepeatDelimiters($bool)
    {
        $this->show_repeat_delimiters = (bool)$bool;
    }

    public function setShowTemplates($bool)
    {
        $this->show_templates = (bool)$bool;
    }

    public function setSendFieldname($fieldname)
    {
        $this->send_fieldname = $fieldname;
    }

    public function setSubject($string)
    {
        $this->subject = esc_html($string);
    }

    public function setBody($string)
    {
        $this->body = $string;
    }

    public function addPlaceholder($text, $is_enclosure = FALSE)
    {
        if ($is_enclosure) {
            $this->placeholders[] = array(
                'label' => '[' . $text . ']',
                'open'  => '[' . $text . ']',
                'close' => '[/' . $text . ']',
                'value' => esc_html__('Insert link text here', 'team-booking')
            );
        } else {
            $this->placeholders[] = array(
                'label' => '[' . $text . ']',
                'value' => '[' . $text . ']'
            );
        }
    }

    public function render()
    {
        echo '<a class="ui mini label tb-toggle-email-editor" style="float: right" tabindex="0">';
        echo '<span class="dashicons dashicons-welcome-write-blog"></span><span style="line-height: 2em">' . esc_html__('edit message', 'team-booking') . '</span></a>';
        echo '<h4>' . $this->title . '</h4>';
        if ($this->show_send) {
            echo '<p style="color:initial"><label for="' . $this->send_fieldname . '">';
            echo '<input name="' . $this->send_fieldname . '" type="checkbox" value="1"' . (($this->send_state) ? ' checked="checked">' : '>');
            esc_html_e('Send', 'team-booking');
            echo '</label></p>';
        }
        if (!empty($this->description)) {
            echo '<p>' . $this->description . '</p>';
        }
        echo '<div class="tb-email-editor">';
        echo '<p style="color:initial">' . esc_html__('Subject', 'team-booking');
        echo ' <input type="text" value="' . $this->subject . '" class="regular-text" name="' . $this->fieldname . '[subject]">';
        echo '</p>';
        if (!empty($this->placeholders)) echo '<p>' . esc_html__('Available form hooks (click to insert at cursor point):', 'team-booking') . '</p>';
        echo '<p class="description">';
        foreach ($this->placeholders as $placeholder) {
            echo '<a class="ui mini tb-hook-placeholder label" tabindex="0"'
                . ' data-value="' . $placeholder['value'] . '"'
                . (isset($placeholder['open']) ? ' data-open="' . $placeholder['open'] . '"' : '')
                . (isset($placeholder['close']) ? ' data-close="' . $placeholder['close'] . '"' : '')
                . '>' . $placeholder['label'] . '</a>';
        }
        echo '</p>';
        if ($this->show_repeat_delimiters) {
            echo '<p class="description">';
            echo sprintf(
                esc_html__('In case of multiple slots order, if single e-mail per service is active, any content wrapped with %s and %s delimiters will be repeated for each slot (the wrapped form hooks data will change accordingly).', 'team-booking'),
                '<code>' . TBK_EMAIL_REPEAT_DELIMITER_OPEN . '</code>', '<code>' . TBK_EMAIL_REPEAT_DELIMITER_CLOSE . '</code>');
            echo '</p>';
        }
        echo \TeamBooking\Actions\backend_email_editor_after_content('', $this->fieldname, $this->service_id);
        wp_editor(
            $this->body,
            str_replace(array('[', ']'), '_', $this->fieldname . '_body'),
            array(
                'media_buttons' => FALSE,
                'textarea_name' => $this->fieldname . '[body]',
                'tinymce'       => FALSE,
            )
        );
        if ($this->show_templates) {
            $modal_id = 'tbk-' . \TeamBooking\Toolkit\randomNumber(10);
            $modal_id_save = 'tbk-save-' . \TeamBooking\Toolkit\randomNumber(10);
            $modal = new Modal($modal_id);
            echo '<p class="tbk-email-template-section">';
            echo '<a class="ui mini label tbk-load-email-template" data-modal-id="' . $modal_id . '" tabindex="0">';
            echo '<span class="dashicons dashicons-schedule"></span>';
            echo ' <span style="line-height: 2em">' . esc_html__('import from template', 'team-booking') . '</span></a>';
            if (\TeamBooking\Functions\isAdmin()) {
                echo '<a class="ui mini label tbk-save-email-template" data-modal-id="' . $modal_id_save . '" tabindex="0">';
                echo '<span class="dashicons dashicons-external"></span>';
                echo ' <span style="line-height: 2em">' . esc_html__('export as template', 'team-booking') . '</span></a>';
            }
            echo '</p>';
            $modal->setWide();
            $modal->addContent('<ul class="tbk-email-templates">');
            foreach (EmailTemplates::get() as $template) {
                $modal->addContent('<li>');
                $modal->addContent('<div class="tbk-email-template-thumbnail"><div class="tbk-scaled">');
                $modal->addContent($template->getContent());
                $modal->addContent('</div></div>');
                $modal->addContent('<div class="tbk-email-template-description"><div class="tbk-email-template-name">');
                $modal->addContent($template->getName());
                $modal->addContent('</div>');
                $modal->addContent('<p class="description">');
                $modal->addContent($template->getDescription());
                $modal->addContent('</p>');
                $modal->addContent('</div>');
                $modal->addContent('</li>');
            }
            $modal->addContent('</ul>');
            $modal->setHeaderText(array('main' => __('Import the content from a template', 'team-booking')));
            $modal->addClass('tbk-email-templates-modal');
            $modal->closeOnly(TRUE);
            $modal->render();

            if (\TeamBooking\Functions\isAdmin()) {
                $modal = new Modal($modal_id_save);
                $modal->setHeaderText(array('main' => ucfirst(__('export as template', 'team-booking'))));
                $modal->addClass('tbk-email-templates-save-modal');
                $modal->addContent('<div class="tbk-email-template-thumbnail-big"><div class="tbk-scaled">');
                $modal->addContent('</div></div>');
                $modal->addContent('<div class="tbk-email-template-attributes">');
                $modal->addContent(Html::label(__('Template name', 'team-booking')));
                $modal->addContent(Html::textfield(array(
                    'class' => 'tbk-email-template-name',
                    'name'  => 'tbk-email-template-name'
                )));
                $modal->addContent(Html::label(__('Template description', 'team-booking')));
                $modal->addContent(Html::textarea(array(
                    'class' => 'tbk-email-template-description',
                    'name'  => 'tbk-email-template-description',
                    'cols'  => 50
                )));
                $modal->addContent('</div>');
                $modal->addErrorText(__('Error while saving the template.', 'team-booking'));
                $modal->render();
            }
        }
        echo '</div>';
    }
}