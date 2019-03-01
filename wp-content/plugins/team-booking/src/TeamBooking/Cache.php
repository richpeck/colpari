<?php

namespace TeamBooking;
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Cache Class
 *
 * @since    2.2.0
 * @author   VonStroheim
 */
class Cache
{
    protected static $instance = NULL;
    protected $objects = array();

    final protected function __construct()
    {
    }

    final protected function __clone()
    {
    }

    /**
     * @return Cache
     */
    private static function getInstance()
    {
        if (Cache::$instance === NULL) {
            Cache::$instance = new Cache();
        }

        return Cache::$instance;
    }

    public static function add($object, $key)
    {
        Cache::getInstance()->objects[ $key ] = $object;
    }

    public static function get($key)
    {
        $cached = Cache::getInstance()->objects;

        return isset($cached[ $key ]) ? $cached[ $key ] : NULL;
    }

}