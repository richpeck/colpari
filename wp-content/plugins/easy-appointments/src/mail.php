<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Event handler for EMAIL notifications and actions
 */
class EAMail
{
    // PHP 5.2
    // const CREATED_AT = 'created';
    // const SALT = 'CStK4zYJSuQPnjbJ1npM';
    /**
     * @var EADBModels
     */
    protected $models;

    /**
     * @var EALogic
     */
    protected $logic;

    /**
     * @var wpdb
     */
    protected $wpdb;

    /**
     * @var EAOptions
     */
    protected $options;

    /**
     * EAMail constructor.
     * @param wpdb $wpdb
     * @param EADBModels $models
     * @param EALogic $logic
     * @param EAOptions $options
     */
    function __construct($wpdb, $models, $logic, $options)
    {
        $this->wpdb = $wpdb;
        $this->models = $models;
        $this->logic = $logic;
        $this->options = $options;
    }

    /**
     *
     */
    public function init()
    {
        // email notification
        add_action('ea_user_email_notification', array($this, 'send_user_email_notification_action'), 10, 1);
        add_action('ea_admin_email_notification', array($this, 'send_admin_email_notification_action'), 10, 2);

        // we want to check if it is link from EA mail
        add_action('init', array($this, 'parse_mail_link'));

        // we need to pars
        add_filter('ea_format_notification_params', array($this, 'format_data'), 100, 2);
    }

    /**
     * FILTER: ea_format_notification_params
     * ORDER: 100
     *
     * Add additional data for template :
     * 1. URL for Confirm ACTION - "#link_confirm#"
     * 2. URL for Cancel ACTION  - "#link_cancel#"
     *
     * @param array $params
     * @param array $appointment
     * @return array
     */
    public function format_data($params, $appointment)
    {
        $token_confirm = $this->generate_token($appointment, 'confirm');
        $token_cancel  = $this->generate_token($appointment, 'cancel');

        $link_confirm = $this->generate_link($appointment['id'], $token_confirm, 'confirm');
        $link_cancel  = $this->generate_link($appointment['id'], $token_cancel, 'cancel');

        $params['#link_confirm#'] = $link_confirm;
        $params['#link_cancel#'] = $link_cancel;

        $params['#url_cancel#'] = $this->generate_url($appointment, 'cancel');
        $params['#url_confirm#'] = $this->generate_url($appointment, 'confirm');

        return $params;
    }

    /**
     * Main action for parsing email link - cancel or confirm appointment
     */
    public function parse_mail_link()
    {
        if (empty($_GET['_ea-action']) || empty($_GET['_ea-app']) || empty($_GET['_ea-t'])) {
            return;
        }

        if ($this->is_bot()) {
            wp_redirect(get_home_url());
            return;
        }

        $app_id = (int)$_GET['_ea-app'];

        $data = $this->models->get_appintment_by_id($app_id);

        if (empty($data)) {
            header('Refresh:3; url=' . get_home_url());
            die(__('No appointment.', 'easy-appointments'));
        }

        // invalid token
        if ($this->generate_token($data, $_GET['_ea-action']) != $_GET['_ea-t']) {
            header('Refresh:3; url=' . get_home_url());
            die(__('Invalid token.', 'easy-appointments'));
        }

        $table = 'ea_appointments';
        $app_fields = array('id', 'location', 'service', 'worker', 'date', 'start', 'end', 'status', 'user', 'price');
        $app_data = array();

        foreach ($app_fields as $value) {
            if (array_key_exists($value, $data)) {
                $app_data[$value] = $data[$value];
            }
        }

        // confirm appointment
        if ($_GET['_ea-action'] == 'confirm') {

            if ($data['status'] != 'pending') {
                die(__('Appointment can\'t be confirmed!', 'easy-appointments'));
            }

            $app_data['status'] = 'confirmed';
            $response = $this->models->replace($table, $app_data, true);

            // trigger new appointment
            do_action('ea_new_app', $app_data['id'], $app_data);

            // for user
            do_action('ea_user_email_notification', $app_data['id']);

            // for admin
            do_action('ea_admin_email_notification', $app_data['id']);

            $url = apply_filters( 'ea_confirmed_redirect_url', get_home_url());

            header('Refresh:3; url=' . $url);
            die(__('Appointment has been confirmed.', 'easy-appointments'));
        }

        // cancel appointment
        if ($_GET['_ea-action'] == 'cancel') {
            $app_data['status'] = 'canceled';

            // only pending and confirmed appointments can be canceled
            if ($data['status'] != 'pending' && $data['status'] != 'confirmed') {
                die(__('Appointment can\'t be canceled!', 'easy-appointments'));
            }

            $response = $this->models->replace($table, $app_data, true);

            // trigger new appointment
            do_action('ea_new_app', $app_data['id'], $app_data);

            // for user
            do_action('ea_user_email_notification', $app_data['id']);

            // for admin
            do_action('ea_admin_email_notification', $app_data['id']);

            if (new DateTime() > new DateTime($app_data['date'] . ' ' . $app_data['start'])) {
                $url = apply_filters( 'ea_cant_be_canceled_redirect_url', get_home_url());

                header('Refresh:3; url=' . $url);
                die(__('Appointment can\'t be canceled', 'easy-appointments'));
            }

            $url = apply_filters( 'ea_cancel_redirect_url', get_home_url());

            header('Refresh:3; url=' . $url);
            die(__('Appointment has been canceled', 'easy-appointments'));
        }
    }

    /**
     *
     * @param  int $app_id Application id
     */
    public function send_user_email_notification_action($app_id)
    {
        $send_user_mail = $this->options->get_option_value('send.user.email', false);

        if (!empty($send_user_mail)) {
            $this->send_status_change_mail($app_id);
        }
    }

    /**
     *
     * @param  int $app_id Application id
     * @param bool $worker_only
     */
    public function send_admin_email_notification_action($app_id, $worker_only = false)
    {
        $this->send_notification(array('id' => $app_id), $worker_only);
    }

    /**
     * Token for link
     *
     * @param array $data
     * @param string $action Action type, options ["confirm", "cancel"]
     * @return string Token
     */
    public function generate_token($data, $action)
    {
        // moved from const because PHP 5.2
        $CREATED_AT = 'created';
        $SALT = 'CStK4zYJSuQPnjbJ1npM';

        return md5($SALT . $data[$CREATED_AT] . $action);
    }

    /**
     * Generate link for cancel or confirm for email
     * @param $id
     * @param $token
     * @param string $action
     * @return string
     */
    public function generate_link($id, $token, $action = 'cancel')
    {
        $params = array(
            '_ea-action' => $action,
            '_ea-app'    => $id,
            '_ea-t'      => $token
        );

        return get_home_url() . '?' . http_build_query($params);
    }

    /**
     * Generate just URL for link
     *
     * @param $data
     * @param $action
     * @return string
     */
    public function generate_url($data, $action)
    {
        $token = $this->generate_token($data, $action);

        return $this->generate_link($data['id'], $token, $action);
    }

    /**
     * Wrap url with <a> element
     *
     * @param array $data
     * @param string $action
     * @param string $link_text
     * @return string HTML a element as string
     */
    public function generate_link_element($data, $action, $link_text = '')
    {
        $token = $this->generate_token($data, $action);
        $link = $this->generate_link($data['id'], $token, $action);

        if ($link_text == '') {
            $link_text = $link;
        }

        return "<a href='$link'>$link_text</a>";
    }

    /**
     * Send email notification for admin users
     *
     * @param $input_data
     * @param bool $worker_only
     */
    public function send_notification($input_data, $worker_only = false)
    {
        $emails = array();

        // get admin emails
        $pendingEmails = $this->options->get_option_value('pending.email', '');

        if (!empty($pendingEmails)) {
            $emails = array_merge($emails, explode(',', $pendingEmails));
        }

        $app_id = $input_data['id'];

        $raw_data = $this->models->get_appintment_by_id($app_id);

        $raw_data = $this->escape_data($raw_data);

        // worker email
        if ($this->options->get_option_value('send.worker.email', '0') == '1') {
            // if we only want to send it to worker
            if ($worker_only) {
                $emails = array();
            }

            $emails[] = $raw_data['worker_email'];
        }

        if (empty($emails)) {
            return;
        }

        $meta = $this->models->get_all_rows('ea_meta_fields', array(), array('position' => 'ASC'));

        $params = array();

        $time_format = get_option('time_format', 'H:i');
        $date_format = get_option('date_format', 'F j, Y');

        // vars for template
        $data = array();

        foreach ($raw_data as $key => $value) {
            if ($key == 'start' || $key == 'end') {
                $value = date_i18n($time_format, strtotime("{$raw_data['date']} $value"));
            }

            if ($key == 'date') {
                $value = date_i18n($date_format, strtotime("$value {$raw_data['start']}"));
            }

            // translate status
            if ($key == 'status') {
                $value = $this->logic->get_status_translation($value);
            }

            $params["#$key#"] = $value;
            $data[$key] = $value;
        }

        // create links for cancel and confirm email
        $links = array();

        $links['#link_cancel#'] = $this->generate_link_element($raw_data, 'cancel', __('Cancel appointment', 'easy-appointments'));
        $links['#link_confirm#'] = $this->generate_link_element($raw_data, 'confirm', __('Confirm appointment', 'easy-appointments'));

        $links['#url_cancel#'] = $this->generate_url($raw_data, 'cancel');
        $links['#url_confirm#'] = $this->generate_url($raw_data, 'confirm');

        $subject_template = $this->options->get_option_value('pending.subject.email', 'Notification : #id#');
        $send_from = $this->options->get_option_value('send.from.email', '');

        $subject = str_replace(array_keys($params), array_values($params), $subject_template);

        $emails = apply_filters('ea_admin_mail_address_list', $emails, $raw_data);

        $body_template = $this->options->get_option_value('mail.admin', '');

        if (!empty($body_template)) {
            // custom email
            $mail_content = $this->custom_admin_mail($body_template, $raw_data);
        } else {
            // default email
            ob_start();

            require EA_SRC_DIR . 'templates/mail.notification.tpl.php';
            $mail_content = ob_get_clean();
            $mail_content = str_replace(array_keys($links), array_values($links), $mail_content);
        }

        $headers = array('Content-Type: text/html; charset=UTF-8');

        if (!empty($send_from)) {
            $headers[] = 'From: ' . $send_from;
        }

        $files = array();

        $files = apply_filters('ea_admin_mail_attachments', $files, $raw_data);

        if (empty($files)) {
            $files = array();
        }

        $this->send_email($emails, $subject, $mail_content, $headers, $files);
    }

    private function custom_admin_mail($body_template, $app_array)
    {
        $time_format = get_option('time_format', 'H:i');
        $date_format = get_option('date_format', 'F j, Y');

        foreach ($app_array as $key => $value) {
            if ($key == 'start' || $key == 'end') {
                $value = date_i18n($time_format, strtotime("{$app_array['date']} $value"));
            }

            if ($key == 'date') {
                $value = date_i18n($date_format, strtotime("$value {$app_array['start']}"));
            }

            if ($key == 'status') {
                $value = $this->logic->get_status_translation($value);
            }

            $params["#$key#"] = $value;
        }

        $params['#link_cancel#'] = $this->generate_link_element($app_array, 'cancel');
        $params['#link_confirm#'] = $this->generate_link_element($app_array, 'confirm');

        $params['#url_cancel#'] = $this->generate_url($app_array, 'cancel');
        $params['#url_confirm#'] = $this->generate_url($app_array, 'confirm');

        $mail_content = str_replace(array_keys($params), array_values($params), $body_template);

        return $mail_content;
    }

    /**
     * Sending mail with every status change to customer
     *
     * @param int $app_id
     */
    public function send_status_change_mail($app_id)
    {

        $table_name = 'ea_appointments';

        $app = $this->models->get_row($table_name, $app_id);

        $app_array = $this->models->get_appintment_by_id($app_id);

        // escape input data
        $app_array = $this->escape_data($app_array);

        $params = array();

        $time_format = get_option('time_format', 'H:i');
        $date_format = get_option('date_format', 'F j, Y');

        foreach ($app_array as $key => $value) {
            if ($key == 'start' || $key == 'end') {
                $value = date_i18n($time_format, strtotime("{$app_array['date']} $value"));
            }

            if ($key == 'date') {
                $value = date_i18n($date_format, strtotime("$value {$app_array['start']}"));
            }

            if ($key == 'status') {
                $value = $this->logic->get_status_translation($value);
            }

            $params["#$key#"] = $value;
        }

        $params["#link_cancel#"] = $this->generate_link_element($app_array, 'cancel');
        $params["#link_confirm#"] = $this->generate_link_element($app_array, 'confirm');

        $params['#url_cancel#'] = $this->generate_url($app_array, 'cancel');
        $params['#url_confirm#'] = $this->generate_url($app_array, 'confirm');

        $subject_template = $this->options->get_option_value('pending.subject.visitor.email', 'Reservation : #id#');

        // Hook for customize subject of email template
        $subject_template = apply_filters( 'ea_customer_mail_subject_template', $subject_template);

        $body_template = $this->options->get_option_value('mail.' . $app->status, 'mail');

        // Hook for customize body of email template
        $body_template = apply_filters( 'ea_customer_mail_template', $body_template);

        $send_from = $this->options->get_option_value('send.from.email', '');

        $body = str_replace(array_keys($params), array_values($params), $body_template);
        $subject = str_replace(array_keys($params), array_values($params), $subject_template);

        $email_key = null;

        // check if there are field called email
        if (array_key_exists('email', $app_array)) {
            $email_key = 'email';
        }

        // check if there is field called e-mail
        if (array_key_exists('e-mail', $app_array)) {
            $email_key = 'e-mail';
        }

        if ($email_key != null) {
            $headers = array('Content-Type: text/html; charset=UTF-8');

            if (!empty($send_from)) {
                $headers[] = 'From: ' . $send_from;
            }

            $files = array();

            $files = apply_filters('ea_user_mail_attachments', $files, $app_array);

            if (empty($files)) {
                $files = array();
            }

            $this->send_email($app_array[$email_key], $subject, $body, $headers, $files);
        }
    }

    /**
     * @param string $email
     * @param string $subject
     * @param string $body
     * @param array $headers
     * @param array $files
     */
    protected function send_email($email, $subject, $body, $headers, $files = array())
    {
        add_action('wp_mail_failed', array($this, 'log_email_error'), 1);

        wp_mail($email, $subject, $body, $headers, $files);

        remove_action('wp_mail_failed', array($this, 'log_email_error'), 1);
    }

    /**
     * @param WP_Error $error_obj
     */
    public function log_email_error($error_obj)
    {
        $table_name = $this->wpdb->prefix . 'ea_error_logs';

        $errors = json_encode($error_obj->errors);
        $errors_data = json_encode($error_obj->error_data);

        $data = array(
            'error_type'  => 'MAIL',
            'errors'      => $errors,
            'errors_data' => $errors_data
        );

        $this->wpdb->insert($table_name, $data, array('%s', '%s', '%s'));
    }

    protected function escape_data($data)
    {
        $clean = array();

        foreach ($data as $key => $value) {
            $clean[$key] = htmlspecialchars($value);
        }

        return $clean;
    }

    /**
     * Check if it a bot
     *
     * @return bool
     */
    private function is_bot()
    {

        if (version_compare(PHP_VERSION, '5.3', '>=')) {
            $crawlerDetect = new Jaybizzle\CrawlerDetect\CrawlerDetect;

            return $crawlerDetect->isCrawler();
        }

        $bots = array(
            'googlebot',
            'adsbot-google',
            'feedfetcher-google',
            'yahoo',
            'lycos',
            'bloglines subscriber',
            'dumbot',
            'sosoimagespider',
            'qihoobot',
            'fast-webcrawler',
            'superdownloads spiderman',
            'linkwalker',
            'msnbot',
            'aspseek',
            'webalta crawler',
            'youdaobot',
            'scooter',
            'gigabot',
            'charlotte',
            'estyle',
            'aciorobot',
            'geonabot',
            'msnbot-media',
            'baidu',
            'cococrawler',
            'google',
            'charlotte t',
            'yahoo! slurp china',
            'sogou web spider',
            'yodaobot',
            'msrbot',
            'abachobot',
            'sogou head spider',
            'altavista',
            'idbot',
            'sosospider',
            'yahoo! slurp',
            'java vm',
            'dotbot',
            'litefinder',
            'yeti',
            'rambler',
            'scrubby',
            'baiduspider',
            'accoona'
        );

        foreach($bots as $bot) {
            if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), trim($bot)) !== false) {
                return true;
            }
        }

        if (!empty($_SERVER['HTTP_USER_AGENT']) and preg_match('~(bot|crawl)~i', $_SERVER['HTTP_USER_AGENT'])){
            return true;
        }

        return false;
    }
}
