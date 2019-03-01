<?php

namespace TeamBooking;

use TeamBooking\Database\Services;

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class Order
 *
 * @author VonStroheim
 * @since  2.5.0
 */
class Order
{
    /** @var $items \TeamBooking_ReservationData[] */
    private $items = array();
    /** @var $items_with_error \TeamBooking_ReservationData[] */
    private $items_with_error = array();
    private $customer_id;
    private $customer_email;
    private $datetime;
    private $id;
    private $redirect_url;

    public function __construct()
    {
        $id = Actions\order_id_prefix('O-')
            . Toolkit\generateToken('alnum_upper', 4)
            . '-' . Toolkit\generateToken('alnum_upper', 4)
            . '-' . Toolkit\generateToken('alnum_upper', 4);
        $this->id = Actions\generate_order_id($id);
    }

    /**
     * @param \TeamBooking_ReservationData $item
     */
    public function add_item(\TeamBooking_ReservationData $item)
    {
        $this->items[ $item->getToken() ] = $item;
        if (NULL === $this->datetime) {
            $this->datetime = $item->getCreationInstant();
        }
        if (NULL === $this->customer_id) {
            $this->customer_id = $item->getCustomerUserId();
        }
        if (NULL === $this->customer_email) {
            $this->customer_email = $item->getCustomerEmail();
        }
    }

    /**
     * @param \TeamBooking_ReservationData $item
     * @param \TeamBooking_Error           $error
     */
    public function add_item_with_error(\TeamBooking_ReservationData $item, \TeamBooking_Error $error)
    {
        $item->error_text = $error->getDisplayText();
        $this->items_with_error[ $item->getToken() ] = $item;
        if (NULL === $this->datetime) {
            $this->datetime = $item->getCreationInstant();
        }
        if (NULL === $this->customer_id) {
            $this->customer_id = $item->getCustomerUserId();
        }
        if (NULL === $this->customer_email) {
            $this->customer_email = $item->getCustomerEmail();
        }
    }

    /**
     * @param string $token
     *
     * @return bool|\TeamBooking_ReservationData
     */
    public function get_item($token)
    {
        return isset($this->items[ $token ]) ? $this->items[ $token ] : FALSE;
    }

    /**
     * @param string $token
     *
     * @return bool|\TeamBooking_ReservationData
     */
    public function get_item_with_error($token)
    {
        return isset($this->items_with_error[ $token ]) ? $this->items_with_error[ $token ] : FALSE;
    }

    /**
     * @return \TeamBooking_ReservationData[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return \TeamBooking_ReservationData[]
     */
    public function getItemsWithError()
    {
        return $this->items_with_error;
    }

    /**
     * @param array $items
     */
    public function setItems(array $items)
    {
        $this->items = $items;
    }

    public function setRedirectUrl($url)
    {
        $this->redirect_url = $url;
    }

    public function getRedirectUrl()
    {
        return $this->redirect_url;
    }

    /**
     * @param string $token
     */
    public function remove_item($token)
    {
        unset($this->items[ $token ]);
    }

    /**
     * @param string $token
     */
    public function remove_item_with_error($token)
    {
        unset($this->items_with_error[ $token ]);
    }

    public function remove_items()
    {
        $this->items = array();
    }

    public function remove_items_with_error()
    {
        $this->items_with_error = array();
    }

    /**
     * @return int
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * @param int $unix
     */
    public function setDatetime($unix)
    {
        $this->datetime = $unix;
    }

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->customer_id;
    }

    /**
     * @param mixed $customer_id
     */
    public function setCustomerId($customer_id)
    {
        $this->customer_id = $customer_id;
    }

    /**
     * @return mixed
     */
    public function getCustomerEmail()
    {
        return $this->customer_email;
    }

    /**
     * @param mixed $customer_email
     */
    public function setCustomerEmail($customer_email)
    {
        $this->customer_email = $customer_email;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return float|int
     */
    public function get_payable_amount()
    {
        $amount = 0;
        foreach ($this->items as $item) {
            try {
                if (!$item->isPaid() && Services::get($item->getServiceId())->getSettingsFor('payment') !== 'later') {
                    $amount += $item->getPriceIncremented() * $item->getTickets();
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $amount;
    }

    /**
     * @return float|int
     */
    public function get_paid_amount()
    {
        $amount = 0;
        foreach ($this->items as $item) {
            if ($item->isPaid()) {
                $amount += $item->getPriceIncremented() * $item->getTickets();
            }
        }

        return $amount;
    }

    /**
     * @return float|int
     */
    public function get_full_amount()
    {
        $amount = 0;
        foreach ($this->items as $item) {
            $amount += $item->getPriceIncremented() * $item->getTickets();
        }

        return $amount;
    }

    /**
     * @return string
     */
    public function get_customer_display_name()
    {
        $lead = reset($this->items);

        return $lead instanceof \TeamBooking_ReservationData ? $lead->getCustomerDisplayName() : '';
    }

    /**
     * @return float|int
     */
    public function get_to_be_paid_amount()
    {
        $amount = 0;
        foreach ($this->items as $item) {
            if ($item->isToBePaid()) {
                $amount += $item->getPriceIncremented() * $item->getTickets();
            }
        }

        return $amount;
    }

    /**
     * @return float|int
     */
    public function get_immediate_amount()
    {
        $amount = 0;
        foreach ($this->items as $item) {
            try {
                if (!$item->isPaid() && Services::get($item->getServiceId())->getSettingsFor('payment') === 'immediately') {
                    $amount += $item->getPriceIncremented() * $item->getTickets();
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $amount;
    }

    /**
     * @return array
     */
    public function get_reservation_ids()
    {
        $return = array();
        foreach ($this->items as $item) {
            $return[] = $item->getDatabaseId();
        }

        return $return;
    }

    /**
     * @return mixed
     */
    public function get_currency()
    {
        $lead = reset($this->items);

        return $lead->getCurrencyCode();
    }

    /**
     * @return int
     */
    public function countItems()
    {
        return count($this->getItems());
    }

    /**
     * @return int
     */
    public function countItemsCancelled()
    {
        $i = 0;
        foreach ($this->items as $item) {
            if ($item->isCancelled()) $i++;
        }

        return $i;
    }

}