<?php

namespace TeamBooking\Admin\Framework;

class Panel implements Element
{
    /** @var Element[] */
    protected $elements = array();
    protected $title;
    protected $title_extra_content = array();
    protected $content_id;


    public function __construct($title)
    {
        $this->title = esc_html($title);
    }

    public function addElement(Element $element)
    {
        $this->elements[] = $element;
    }

    public function addTitleContent(Element $content)
    {
        $this->title_extra_content[] = $content;
    }

    public function setContentId($id)
    {
        $this->content_id = $id;
    }

    public function render()
    {
        echo '<div class="tbk-panel">';
        echo '<div class="tbk-panel-title"><h4>' . $this->title . ' ';
        foreach ($this->title_extra_content as $item) {
            if ($item instanceof Element) $item->render();
        }
        echo '</h4></div>';
        echo '<div class="tbk-content"';
        if (!empty($this->content_id)) echo ' id="' . $this->content_id . '"';
        echo '>';
        echo '<ul class="tbk-list">';
        foreach ($this->elements as $element) {
            echo '<li>';
            $element->render();
            echo '</li>';
        }
        echo '</ul></div></div>';
    }
}