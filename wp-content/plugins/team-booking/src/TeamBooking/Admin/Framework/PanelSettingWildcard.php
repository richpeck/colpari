<?php

namespace TeamBooking\Admin\Framework;

class PanelSettingWildcard extends PanelSetting implements Element
{
    protected $content = array();

    public function addContent($content_unescaped)
    {
        $this->content[] = $content_unescaped;
    }

    public function render()
    {
        if (!empty($this->title)) echo '<h4>' . $this->title . '</h4>';
        if (!empty($this->description)) echo '<p>' . $this->description . '</p>';
        foreach ($this->content as $item) {
            if ($item instanceof Element) {
                $item->render();
            } else {
                echo $item;
            }
        }
        $this->renderAlerts();
    }
}