<?php

defined('ABSPATH') or die('No script kiddies please!');

/**
 * This class defines an error object used to return errors
 * to customer/frontend when a reservation fails.
 *
 * @author VonStroheim
 *
 * TODO: join TeamBooking_Error with TeamBooking_ErrorLog
 */
class TeamBooking_Error
{
    private $code;
    private $external_code;
    private $message;
    private $display_text;
    private $line;
    private $file;
    private $user;
    private $reservation_id;

    /**
     * TeamBooking_Error constructor.
     *
     * @param                $code
     * @param Exception|NULL $e
     */
    public function __construct($code, Exception $e = NULL)
    {
        $this->code = $code;
        switch ($code) {
            case 1:
                $this->message = 'The Coworker auth token is not present anymore';
                break;
            case 2:
                $this->message = 'Slot not available anymore or cancelled';
                break;
            case 3:
                $this->message = 'The customer has already booked the slot';
                break;
            case 4:
                $this->message = 'The customer has entered an invalid email address';
                break;
            case 5:
                $this->message = 'Generic Google API error'; // More specific with error message returned by Google
                break;
            case 6:
                $this->message = 'The event is full';
                $this->display_text = __('The event is full', 'team-booking');
                break;
            case 7:
                $this->message = 'The event is already cancelled or not marked as booked anymore';
                break;
            case 8:
                $this->message = 'The customer is trying to book a number of tickets that, together with his previous tickets, overcomes the maximum allowed';
                break;
            case 9:
                $this->message = "Can't decode the rendering parameters object";
                break;
            case 10:
                $this->message = 'Operation not allowed for this service class';
                break;
            case 11:
                $this->message = 'Tickets left are less than requested';
                $this->display_text = __('Tickets left for this event are less than requested', 'team-booking');
                break;
            case 50:
                $this->message = 'Reservation data cannot be read';
                $this->display_text = __('Reservation data cannot be read, please retry!', 'team-booking');
                break;
            case 51:
                $this->message = 'Service nonexistent';
                if (NULL !== $e) {
                    $this->display_text = $e->getMessage();
                    $this->file = $e->getFile();
                    $this->line = $e->getLine();
                }
                break;
            case 52:
                $this->message = 'Reservation already confirmed';
                $this->display_text = __('Reservation already confirmed!', 'team-booking');
                break;
            case 60:
                $this->message = 'Payment Gateway error';
                break;
            case 61:
                $this->message = 'Reservation checksum failed';
                break;
            case 70:
                $this->message = 'Nonce failed';
                $this->display_text = __('Please refresh the page...', 'team-booking');
                break;
        }
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @param $code
     */
    public function setExternalCode($code)
    {
        $this->external_code = (int)$code;
    }

    /**
     * @return int
     */
    public function getExternalCode()
    {
        return (int)$this->external_code;
    }

    /**
     * @return string
     */
    public function getDisplayText()
    {
        return $this->display_text;
    }

    /**
     * @param string $display_text
     */
    public function setDisplayText($display_text)
    {
        $this->display_text = $display_text;
    }

    /**
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @param int $line
     */
    public function setLine($line)
    {
        $this->line = $line;
    }

    /**
     * @return int
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param int $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return int|null
     */
    public function getReservationId()
    {
        return $this->reservation_id;
    }

    /**
     * @param int $reservation_id
     */
    public function setReservationId($reservation_id)
    {
        $this->reservation_id = $reservation_id;
    }

}
