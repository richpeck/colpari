<?php

namespace TeamBooking\Admin\Framework;

class Notice implements Element
{
    protected $message;
    protected $additional_content;
    protected $type;

    public function __construct($message, $additional_content = NULL)
    {
        $this->message = $message;
        $this->additional_content = $additional_content;
    }

    public static function getPositive($message, $additional_content = NULL)
    {
        $notice = new Notice($message, $additional_content);
        $notice->type = 'updated';

        return $notice;
    }

    public static function getNegative($message, $additional_content = NULL)
    {
        $notice = new Notice($message, $additional_content);
        $notice->type = 'error';

        return $notice;
    }

    public static function getUpdate($message, $additional_content = NULL)
    {
        $notice = new Notice($message, $additional_content);
        $notice->type = 'update-nag';

        return $notice;
    }

    /**
     * @param bool $escape
     */
    public function render($escape = TRUE)
    {
        echo "<div class = 'notice " . $this->type . " is-dismissible'>";
        echo '<p>';
        if ($this->type === 'error') echo '<strong>' . esc_html__('Warning', 'team-booking') . '</strong>: ';
        if ($this->type === 'updated') echo '<strong>' . esc_html__('Ok', 'team-booking') . '</strong> ';
        if ($escape) {
            echo esc_html($this->message) . '</p>';
        } else {
            echo $this->message . '</p>';
        }
        echo $this->additional_content;
        echo "<button type='button' class='notice-dismiss'></button>";
        echo '</div>';
    }
}