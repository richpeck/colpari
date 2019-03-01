<?php

namespace TeamBooking\Admin\Framework;

class ActionButton implements Element
{
    protected $classes = array();
    protected $title = '';
    protected $data = array();
    protected $icon = '';
    protected $href = '#';
    protected $id = '';

    public function __construct($icon_class)
    {
        $this->icon = $icon_class;
    }

    public function setHref($link)
    {
        $this->href = $link;
    }

    public function setClasses(array $classes)
    {
        $this->classes = $classes;
    }

    public function addClass($class)
    {
        $this->classes[] = $class;
    }

    public function addData($name, $value)
    {
        $this->data[ $name ] = $value;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function render()
    {
        echo '<a class="ui circular label mini horizontal tbk-action-button';
        foreach ($this->classes as $class) {
            echo ' ' . $class;
        }
        echo '" href="' . $this->href . '"';
        if (!empty($this->id)) echo ' id="' . $this->id . '"';
        if (!empty($this->title)) echo ' title="' . esc_attr($this->title) . '"';
        foreach ($this->data as $name => $value) {
            echo ' data-' . $name . '="' . $value . '"';
        }
        echo '>';
        echo '<span class="dashicons ' . $this->icon . '"></span>';
        echo '</a>';
    }

    public function get()
    {
        ob_start();
        $this->render();

        return ob_get_clean();
    }
}