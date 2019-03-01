<?php

namespace TeamBooking\Admin\Framework;

class PanelTitleAddNewButton implements Element
{
    protected $classes = array();
    protected $id = '';
    protected $additional_content = '';
    protected $text = '';
    protected $href = '#';
    protected $data = array();

    public function __construct($text = FALSE)
    {
        if ($text) $this->text = $text;
    }

    public function setClasses(array $classes)
    {
        $this->classes = $classes;
    }

    public function addClass($class)
    {
        $this->classes[] = $class;
    }

    public function setHref($link)
    {
        $this->href = $link;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function addData(array $array)
    {
        foreach ($array as $data => $value) {
            $this->data[ $data ] = $value;
        }
    }

    public function setAdditionalContent($unescaped_content)
    {
        $this->additional_content = $unescaped_content;
    }

    public function get()
    {
        ob_start();
        $this->render();

        return ob_get_clean();
    }

    public function render()
    {
        echo '<a href="' . $this->href . '"';
        if (!empty($this->id)) echo ' id="' . $this->id . '"';
        if (!empty($this->data)) {
            foreach ($this->data as $data => $value) {
                echo ' data-' . $data . '="' . $value . '"';
            }
        }
        echo ' class="page-title-action';
        foreach ($this->classes as $class) {
            echo ' ' . $class;
        }
        echo '">' . ((!empty($this->text)) ? esc_html($this->text) : esc_html__('Add new', 'team-booking')) . '</a>';
        if (!empty($this->additional_content)) {
            if ($this->additional_content instanceof Element) {
                $this->additional_content->render();
            } else {
                echo $this->additional_content;
            }
        }
    }
}