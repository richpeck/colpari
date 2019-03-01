<?php

namespace TeamBooking\Abstracts;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Toolkit;

/**
 * Abstract EmailTemplate Class
 *
 * Implemented by e-mail templates
 *
 * @since    2.5.0
 * @author   VonStroheim
 */
abstract class EmailTemplate
{
    /**
     * ID for this template
     *
     * @var string
     */
    protected $id = '';

    /**
     * Name for this template
     *
     * @var string
     */
    protected $name = '';

    /**
     * Description for this template
     *
     * @var string
     */
    protected $description = '';

    /**
     * Content of this template
     *
     * @var string
     */
    protected $content = '';

    /**
     * Settings of this service
     *
     * @var array
     */
    protected $settings = array();

    public function __construct()
    {
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return get_object_vars($this);
    }

    /**
     * Sets the ID of this template
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
     * Sets the name of this template
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = Toolkit\filterInput($name);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return Toolkit\unfilterInput($this->name);
    }

    /**
     * Sets the description of this template
     *
     * @param string $desc
     */
    public function setDescription($desc)
    {
        $this->description = Toolkit\filterInput($desc);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return Toolkit\unfilterInput($this->description);
    }

    /**
     * Sets the content of this template
     *
     * @param string $html
     */
    public function setContent($html)
    {
        $this->content = $html;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
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
            'type'        => 'email-template',
            'id'          => $this->getId(),
            'name'        => $this->getName(),
            'description' => $this->getDescription(),
            'content'     => $this->getContent()
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
        $whitelist = array();

        if (!isset($whitelist[ $property ])) return TRUE;

        return in_array($value, $whitelist[ $property ]);
    }

}