<?php

namespace TeamBooking\Admin\Framework;

class PanelSetting
{
    protected $title;
    protected $description = '';
    protected $fieldname = '';
    protected $alert = '';
    protected $alert_dropcap;
    protected $warning_dropcap;
    protected $notice = '';
    protected $service_id = '';

    public function __construct($title = '')
    {
        $this->alert_dropcap = __('Note', 'team-booking');
        $this->warning_dropcap = __('Important', 'team-booking');
        $this->title = esc_html($title);
    }

    public function addDescription($string, $escape = TRUE)
    {
        if ($escape) {
            $this->description = esc_html($string);
        } else {
            $this->description = $string;
        }
    }

    public function addAlert($text, $escape = TRUE)
    {
        if ($escape) {
            $this->alert = esc_html($text);
        } else {
            $this->alert = $text;
        }
    }

    public function addAlertDropcap($text)
    {
        $this->alert_dropcap = $text;
    }

    public function addWarningDropcap($text)
    {
        $this->warning_dropcap = $text;
    }

    public function addNotice($text, $escape = TRUE)
    {
        if ($escape) {
            $this->notice = esc_html($text);
        } else {
            $this->notice = $text;
        }
    }

    public function addToDescription($string, $escape = TRUE)
    {
        if ($escape) {
            $this->description .= esc_html($string);
        } else {
            $this->description .= $string;
        }
    }

    public function addFieldname($fieldname)
    {
        $this->fieldname = $fieldname;
    }

    public function appendTo(Panel &$panel)
    {
        $panel->addElement($this);
    }

    public function renderAlerts()
    {
        if (!empty($this->alert)) {
            echo '<div class="tbk-setting-alert"><span>' . esc_html($this->warning_dropcap) . '</span> ' . $this->alert . '</div>';
        }
        if (!empty($this->notice)) {
            echo '<div class="tbk-setting-notice"><span>' . esc_html($this->alert_dropcap) . '</span> ' . $this->notice . '</div>';
        }
    }

    /**
     * @param string $id
     */
    public function setServiceId($id)
    {
        $this->service_id = $id;
    }

    /**
     * @return string
     */
    public function getServiceId()
    {
        return $this->service_id;
    }
}