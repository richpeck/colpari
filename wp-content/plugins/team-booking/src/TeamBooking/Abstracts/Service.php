<?php

namespace TeamBooking\Abstracts;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Database\Forms,
    TeamBooking\Toolkit,
    TeamBooking\Actions;
use TeamBooking\Slot;

/**
 * Abstract Service Class
 *
 * Implemented by services
 *
 * @since    2.2.0
 * @author   VonStroheim
 */
abstract class Service
{
    /**
     * Class of this service
     *
     * @var string
     */
    protected $class = '';

    /**
     * ID for this service
     *
     * @var string
     */
    protected $id = '';

    /**
     * Name for this service
     *
     * @var string
     */
    protected $name = '';

    /**
     * Description for this service
     *
     * @var string
     */
    protected $description = '';

    /**
     * Color for this service
     *
     * @var string
     */
    protected $color = '';

    /**
     * Price for this service
     *
     * @var float
     */
    protected $price = 0;

    /**
     * State of this service
     *
     * @var boolean
     */
    protected $active;

    /**
     * Location of this service
     *
     * @var string
     */
    protected $location = '';

    /**
     * Redirect URL of this service
     *
     * @var string
     */
    protected $redirect_url = '';

    /**
     * E-mail notification (to Admin)
     *
     * @var array
     */
    protected $email_notification_admin = array();

    /**
     * E-mail notification (to Customer)
     *
     * @var array
     */
    protected $email_notification_customer = array();

    /**
     * E-mail cancellation (to Admin/Coworker)
     *
     * @var array
     */
    protected $email_cancellation_admin = array();

    /**
     * E-mail cancellation (to Customer)
     *
     * @var array
     */
    protected $email_cancellation_customer = array();

    /**
     * Settings of this service
     *
     * @var array
     */
    protected $settings = array(
        'location'                    => 'none',
        'redirect'                    => FALSE,
        'payment'                     => 'immediately',
        'bookable'                    => 'everyone',
        'customer_cancellation'       => TRUE,
        'cancellation_reason_allowed' => TRUE,
        'show_map'                    => TRUE
    );

    /**
     * Reservation form of this service
     *
     * @var integer
     */
    protected $form;

    public function __construct()
    {
        $this->class = $this->getClass();
        $this->setActive(TRUE);
        $this->settings['approval_rule'] = 'none';
        $this->setColor(sprintf('#%06X', mt_rand(0, 0xFFFFFF)));
        $this->email_notification_admin = array(
            'subject'     => esc_html__('A new reservation', 'team-booking'),
            'body'        => esc_html__('You have just got a new reservation!', 'team-booking'),
            'send'        => TRUE,
            'to'          => get_bloginfo('admin_email'),
            'attachments' => FALSE
        );
        $this->email_notification_customer = array(
            'subject' => esc_html__('Your reservation details', 'team-booking'),
            'body'    => esc_html__('Thanks for your reservation!', 'team-booking'),
            'from'    => 'admin',
            'send'    => TRUE
        );
        $this->email_cancellation_admin = array(
            'subject' => esc_html__('Reservation cancelled', 'team-booking'),
            'body'    => esc_html__('Your reservation was cancelled.', 'team-booking'),
            'send'    => TRUE
        );
        $this->email_cancellation_customer = array(
            'subject' => esc_html__('Reservation cancelled', 'team-booking'),
            'body'    => esc_html__('Your reservation was cancelled.', 'team-booking'),
            'from'    => 'admin',
            'send'    => TRUE
        );
    }

    /**
     * @param bool $as_label
     *
     * @return string
     */
    abstract public function getClass($as_label = FALSE);

    /**
     * @return array
     */
    public function getProperties()
    {
        return get_object_vars($this);
    }

    /**
     * Activate/deactivate this service
     *
     * @param boolean $bool
     */
    public function setActive($bool)
    {
        $this->active = (bool)$bool;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * Sets the ID of this service
     *
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = Toolkit\filterInput($id, TRUE);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the name of this service
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = Toolkit\filterInput($name);
    }

    /**
     * @param bool $filtered
     *
     * @return string
     */
    public function getName($filtered = FALSE)
    {
        if ($filtered) {
            return \TeamBooking\Actions\service_name(Toolkit\unfilterInput($this->name), $this);
        }

        return Toolkit\unfilterInput($this->name);
    }

    /**
     * Sets the description of this service
     *
     * @param string $desc
     */
    public function setDescription($desc)
    {
        $this->description = Toolkit\filterInput($desc);
    }

    /**
     * @param bool $filtered
     *
     * @return string
     */
    public function getDescription($filtered = FALSE)
    {
        if ($filtered) {
            return \TeamBooking\Actions\service_description(Toolkit\unfilterInput($this->description), $this);
        }

        return Toolkit\unfilterInput($this->description);
    }

    /**
     * Sets the color of this service
     *
     * @param string $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Sets the price of this service
     *
     * @param float $float
     */
    public function setPrice($float)
    {
        $this->price = (float)$float;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param Slot $slot
     *
     * @return float|int
     */
    public function getPotentialPrice($slot = NULL)
    {
        if (NULL !== $slot){
            $potential_price = $slot->getPriceBase();
        } else {
            $potential_price = $this->getPrice();
        }
        $increment = 0;
        $fields = Forms::getCustom($this->getForm());
        foreach ($fields as $field) {
            if (NULL !== $field->getData('options')) {
                foreach ($field->getData('options') as $option) {
                    if ($option['price_increment'] > $increment) {
                        $increment = $option['price_increment'];
                    }
                }
            }
        }

        return $potential_price + $increment;
    }

    /**
     * Sets the location address of this service
     *
     * @param string $address
     */
    public function setLocation($address)
    {
        $this->location = $address;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Sets the redirect URL of this service
     *
     * @param string $url
     */
    public function setRedirectUrl($url)
    {
        $this->redirect_url = $url;
    }

    /**
     * If a reservation ID is given,
     * the redirect URL will include it
     *
     * @param null|string $reservation_id
     *
     * @return string
     */
    public function getRedirectUrl($reservation_id = NULL)
    {
        $url = parse_url($this->redirect_url);
        if (!isset($url['scheme'])) {
            $url['scheme'] = 'http';
        }
        $params = array();
        if (isset($url['query'])) {
            parse_str($url['query'], $params);
        }
        if (NULL !== $reservation_id) {
            $params['reservation_database_id'] = $reservation_id;
        }
        $redirect = $url['scheme'] . '://';
        if (isset($url['host'])) $redirect .= $url['host'];
        if (isset($url['path'])) $redirect .= $url['path'];
        if (!empty($params)) $redirect .= '?' . http_build_query($params);

        return Actions\service_redirect_url($redirect, $this->id);
    }

    /**
     * @param string $param
     * @param string $value
     */
    public function setEmailToAdmin($param, $value)
    {
        if (isset($this->email_notification_admin[ $param ])) {
            $this->email_notification_admin[ $param ] = $value;
        }
    }

    /**
     * @param string $param
     *
     * @return string|bool
     */
    public function getEmailToAdmin($param)
    {
        return $this->email_notification_admin[ $param ];
    }

    /**
     * @param string $param
     * @param string $value
     */
    public function setEmailToCustomer($param, $value)
    {
        if (isset($this->email_notification_customer[ $param ])) {
            $this->email_notification_customer[ $param ] = $value;
        }
    }

    /**
     * @param string $param
     *
     * @return string|bool
     */
    public function getEmailToCustomer($param)
    {
        return $this->email_notification_customer[ $param ];
    }

    /**
     * @param string $param
     * @param string $value
     */
    public function setEmailCancellationToAdmin($param, $value)
    {
        if (isset($this->email_cancellation_admin[ $param ])) {
            $this->email_cancellation_admin[ $param ] = $value;
        }
    }

    /**
     * @param string $param
     *
     * @return string|bool
     */
    public function getEmailCancellationToAdmin($param)
    {
        return $this->email_cancellation_admin[ $param ];
    }

    /**
     * @param string $param
     * @param string $value
     */
    public function setEmailCancellationToCustomer($param, $value)
    {
        if (isset($this->email_cancellation_customer[ $param ])) {
            $this->email_cancellation_customer[ $param ] = $value;
        }
    }

    /**
     * @param string $param
     *
     * @return string|bool
     */
    public function getEmailCancellationToCustomer($param)
    {
        return $this->email_cancellation_customer[ $param ];
    }

    /**
     * @param $form_id
     */
    public function setForm($form_id)
    {
        $this->form = $form_id;
    }

    /**
     * @return int
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Get a property setting
     *
     * @param $property
     *
     * @return mixed
     */
    public function getSettingsFor($property)
    {
        if (!isset($this->settings[ $property ])) {
            return NULL;
        }

        if ($property === 'approval_rule' && $this->settings['payment'] !== 'later') {
            return 'none';
        }

        return $this->settings[ $property ];
    }

    /**
     * Set a property setting
     *
     * @param $property
     * @param $value
     */
    public function setSettingsFor($property, $value)
    {
        if (!isset($this->settings[ $property ])) {
            throw new \UnexpectedValueException('This property does not exist: ' . $property);
        }
        if (!$this->validateSettingValues($property, $value)) {
            throw new \UnexpectedValueException('This value is not allowed: ' . $value);
        }
        $this->settings[ $property ] = $value;
    }

    /**
     * Checks a property setting against given value
     *
     * @param string $property
     * @param mixed  $value
     *
     * @return bool
     */
    public function isSettingsFor($property, $value)
    {
        return $this->getSettingsFor($property) === $value;
    }

    /**
     * The REST API resource of this service
     *
     * @return array
     */
    public function getApiResource()
    {
        $return = array(
            'type'                      => 'service',
            'id'                        => $this->getId(),
            'name'                      => $this->getName(),
            'color'                     => $this->getColor(),
            'description'               => $this->getDescription(),
            'isActive'                  => $this->isActive(),
            'notificationEmailAdmin'    => array(
                'send'    => $this->email_notification_admin['send'],
                'address' => $this->email_notification_admin['to'],
                'subject' => $this->email_notification_admin['subject'],
                'body'    => $this->email_notification_admin['body']
            ),
            'notificationEmailCustomer' => array(
                'send'    => $this->email_notification_customer['send'],
                'subject' => $this->email_notification_customer['subject'],
                'body'    => $this->email_notification_customer['body']
            ),
            'cancellationByCustomer'    => array(
                'allow' => $this->getSettingsFor('customer_cancellation')
            ),
            'payments'                  => array(
                'price'    => $this->getPrice(),
                'required' => $this->getSettingsFor('payment')
            ),
            'bookable'                  => $this->getSettingsFor('bookable'),
            'location'                  => array(
                'setting' => $this->getSettingsFor('location'),
                'address' => $this->getLocation()
            ),
            'redirectURL'               => $this->getRedirectUrl()
        );

        return $return;
    }

    /**
     * Whitelist of setting values
     *
     * @param string $property
     * @param mixed  $value
     *
     * @return bool
     */
    protected function validateSettingValues($property, $value)
    {
        $whitelist = array(
            'location'                    => array('none', 'inherited', 'fixed'),
            'location_visibility'         => array('visible', 'hidden'),
            'redirect'                    => array(TRUE, FALSE),
            'payment'                     => array('immediately', 'discretional', 'later'),
            'logged_only'                 => array(TRUE, FALSE),
            'customer_cancellation'       => array(TRUE, FALSE),
            'cancellation_reason_allowed' => array(TRUE, FALSE),
            'show_map'                    => array(TRUE, FALSE),
            'approval_rule'               => array('none', 'admin', 'coworker'),
        );

        if (!isset($whitelist[ $property ])) return TRUE;

        return in_array($value, $whitelist[ $property ]);
    }

}