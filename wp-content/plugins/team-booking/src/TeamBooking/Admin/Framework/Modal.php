<?php

namespace TeamBooking\Admin\Framework;

class Modal implements Element
{
    protected $id = '';
    protected $show_buttons = TRUE;
    protected $close_only = FALSE;
    protected $header_text = array(
        'main'    => '',
        'sub'     => '',
        'escaped' => TRUE
    );
    protected $button_text = array(
        'approve'   => '',
        'deny'      => '',
        'secondary' => ''
    );
    protected $content = array();
    protected $tabs = array();
    protected $data = array();
    protected $more_buttons = array();
    protected $class = '';
    protected $small = TRUE;
    protected $error_text = '';

    public function __construct($id)
    {
        $this->id = $id;
        $this->button_text['approve'] = __('Save', 'team-booking');
        $this->button_text['deny'] = __('Close', 'team-booking');
    }

    public function setButtonText(array $text)
    {
        if (isset($text['approve'])) $this->button_text['approve'] = $text['approve'];
        if (isset($text['deny'])) $this->button_text['deny'] = $text['deny'];
        if (isset($text['secondary'])) $this->button_text['secondary'] = $text['secondary'];
    }

    public function setHeaderText(array $text)
    {
        if (isset($text['main'])) $this->header_text['main'] = $text['main'];
        if (isset($text['sub'])) $this->header_text['sub'] = $text['sub'];
        if (isset($text['escaped'])) $this->header_text['escaped'] = $text['escaped'];
    }

    public function addTab($id, $title, $content)
    {
        $this->tabs[ $id ] = array('title' => $title, 'content' => $content, 'active' => empty($this->tabs));
    }

    public function addClass($class)
    {
        $this->class = $class;
    }

    public function addData(array $array)
    {
        foreach ($array as $data => $value) {
            $this->data[ $data ] = $value;
        }
    }

    public function addErrorText($text)
    {
        $this->error_text = $text;
    }

    public function setButtons($bool)
    {
        $this->show_buttons = (bool)$bool;
    }

    public function closeOnly($bool)
    {
        $this->close_only = (bool)$bool;
    }

    public function addContent($content)
    {
        $this->content[] = $content;
    }

    public function additionalButton(array $button)
    {
        $this->more_buttons[] = $button;
    }

    public function setWide()
    {
        $this->small = FALSE;
    }

    public function render()
    {
        echo '<div class="ui ' . ($this->small ? 'small' : '') . ' modal tbk-modal'
            . (empty($this->tabs) ? '' : ' tbk-tabbed-modal')
            . ' ' . $this->class
            . '" id="' . $this->id . '"';
        if (!empty($this->data)) {
            foreach ($this->data as $data => $value) {
                echo ' data-' . $data . '="' . $value . '"';
            }
        }
        echo '>';
        echo '<i class="close tb-icon"></i>';
        echo '<div class="header">' . ($this->header_text['escaped'] ? esc_html($this->header_text['main']) : $this->header_text['main']);
        if (!empty($this->header_text['sub'])) {
            echo ' <div class="sub header">'
                . ($this->header_text['sub'] instanceof Element
                    ? $this->header_text['sub']->get()
                    : ($this->header_text['escaped'] ? esc_html($this->header_text['sub']) : $this->header_text['sub']))
                . '</div>';
        }
        echo '</div>';
        if (!empty($this->tabs)) {
            echo '<div class="tbk-tabs">';
            foreach ($this->tabs as $id => $tab) {
                echo '<a class="' . ($tab['active'] ? 'tbk-active ' : '') . 'tbk-tab-selector"'
                    . ($tab['active'] ? '' : ' tabindex="0"')
                    . ' data-show="tb-' . $id . '">';
                echo esc_html($tab['title']);
                echo '</a>';
            }
            echo '</div>';
        }
        echo '<div class="content" style="width: calc(100% - 3rem);">';

        if (empty($this->tabs)) {

            foreach ($this->content as $item) {
                if ($item instanceof Element) {
                    $item->render();
                } else {
                    echo $item;
                }
            }

        } else {
            foreach ($this->tabs as $id => $tab) {
                echo '<div class="tb-data tb-' . $id . '"' . ($tab['active'] ? '' : ' style="display:none;"') . '>';
                if ($tab['content'] instanceof Element) {
                    $tab['content']->render();
                } else {
                    echo $tab['content'];
                }
                echo '</div>';
            }
        }

        \TeamBooking\Actions\backend_modal_end_content($this->id);

        echo '</div>';
        if ($this->show_buttons) {
            echo '<div class="actions">';
            if (!empty($this->error_text)) {
                echo '<div class="tbk-modal-error-message" style="display: none;color: darkred;font-weight: 700;">';
                esc_html_e($this->error_text);
                echo '</div>';
            }
            if (!$this->close_only) {
                echo '<div class="ui positive button" style="height:auto;position: relative;">';
                echo esc_html($this->button_text['approve']);
                if (!empty($this->button_text['secondary'])) {
                    echo '</div><div class="ui ok blue button tbk-secondary" style="height:auto;position: relative;">';
                    echo esc_html($this->button_text['secondary']);
                }
                echo '</div>';
            }
            foreach ($this->more_buttons as $button) {
                echo '<div class="ui button ' . (isset($button['class']) ? $button['class'] : '') . '" style="height:auto;position: relative;"';
                if (isset($button['data'])) {
                    foreach ($button['data'] as $data => $value) {
                        echo ' data-' . $data . '="' . $value . '"';
                    }
                }
                echo '>';
                echo esc_html($button['text']);
                echo '</div>';
            }
            echo '<div class="ui deny black button" style="height:auto;position: relative;">';
            echo esc_html($this->button_text['deny']);
            echo '</div></div>';
        }
        echo '</div>';
    }
}