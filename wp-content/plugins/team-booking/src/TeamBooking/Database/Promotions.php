<?php

namespace TeamBooking\Database;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Functions,
    TeamBooking\Cache;

/**
 * Class Promotions
 *
 * @author VonStroheim
 */
class Promotions
{
    /**
     * @param      $param
     * @param      $value
     * @param bool $running
     *
     * @return \TeamBooking\Promotions\Promotion[]
     */
    private static function getBy($param, $value, $running = FALSE)
    {
        if (NULL !== Cache::get('promotions_getby' . $param . $value . $running)) {
            $return = Cache::get('promotions_getby' . $param . $value . $running);
        } else {
            global $wpdb;
            $table_name = $wpdb->prefix . 'teambooking_promotions';
            if ($running) {
                $results = $wpdb->get_results($wpdb->prepare("
                SELECT id, data_object
                FROM $table_name WHERE $param = %s AND start_time < UTC_TIMESTAMP() AND end_time > UTC_TIMESTAMP()
                ", array($value)));
            } else {
                $results = $wpdb->get_results($wpdb->prepare("
                SELECT id, data_object
                FROM $table_name WHERE $param = %s
                ", array($value)));
            }
            $return = array();
            foreach ($results as $result) {
                $return[ $result->id ] = unserialize($result->data_object);
            }
            Cache::add($return, 'promotions_getby' . $param . $value . $running);
        }

        return $return;
    }

    /**
     * @param $id
     *
     * @return \TeamBooking\Promotions\Promotion
     */
    public static function getById($id)
    {
        return reset(Promotions::getBy('id', $id));
    }

    /**
     * @param      $name
     * @param bool $running
     *
     * @return \TeamBooking\Promotions\Promotion[]
     */
    public static function getByName($name, $running = FALSE)
    {
        $promotions = self::getAll($running);
        $return = array();
        foreach ($promotions as $id => $promotion) {
            if ($promotion->getName() == $name) {
                $return[ $id ] = $promotion;
                break;
            }
        }

        return $return;
    }

    /**
     * @param      $class
     * @param bool $active
     * @param bool $running
     *
     * @return \TeamBooking\Promotions\Promotion[]
     */
    public static function getByClass($class, $active = FALSE, $running = FALSE)
    {
        $promotions = self::getBy('class', $class, $running);
        if ($active) {
            foreach ($promotions as $promotion_id => $promotion) {
                if (!$promotion->getStatus()) {
                    unset($promotions[ $promotion_id ]);
                }
            }
        }

        return $promotions;
    }

    /**
     * @param      $id
     * @param bool $active
     * @param bool $running
     *
     * @return \TeamBooking\Promotions\Promotion[]
     */
    public static function getByService($id, $active = FALSE, $running = FALSE)
    {
        $promotions = self::getAll($running);
        foreach ($promotions as $promotion_id => $promotion) {
            if (!$promotion->checkService($id)) {
                unset($promotions[ $promotion_id ]);
            } elseif ($active && !$promotion->getStatus()) {
                unset($promotions[ $promotion_id ]);
            }
        }

        return $promotions;
    }

    /**
     * @param      $class
     * @param      $service_id
     * @param bool $active
     * @param bool $running
     *
     * @return \TeamBooking\Promotions\Promotion[]
     */
    public static function getByClassAndService($class, $service_id, $active = FALSE, $running = FALSE)
    {
        $promotions = self::getByService($service_id, $active, $running);
        foreach ($promotions as $promotion_id => $promotion) {
            if (!$promotion->checkClass($class)) {
                unset($promotions[ $promotion_id ]);
            }
        }

        return $promotions;
    }

    /**
     * @param bool   $running
     * @param bool   $filter
     * @param int    $per_page
     * @param int    $page_number
     * @param string $order_by
     * @param string $order
     *
     * @return \TeamBooking\Promotions\Promotion[]
     */
    public static function getAll($running = FALSE, $filter = FALSE, $per_page = 0, $page_number = 0, $order_by = 'id', $order = 'asc')
    {
        if (NULL !== Cache::get('promotions' . $running . $filter . $per_page . $page_number . $order_by . $order)) {
            $return = Cache::get('promotions' . $running . $filter . $per_page . $page_number . $order_by . $order);
        } else {
            global $wpdb;
            $table_name = $wpdb->prefix . 'teambooking_promotions';
            $columns = 'id, data_object';
            $query = "SELECT $columns FROM $table_name";
            if ($running) {
                $query = "SELECT $columns FROM $table_name WHERE start_time < UTC_TIMESTAMP() AND end_time > UTC_TIMESTAMP()";
            }
            if ($filter === 'campaign') {
                $query = $wpdb->prepare("SELECT $columns FROM $table_name WHERE class = %s", 'campaign');
            }
            if ($filter === 'coupon') {
                $query = $wpdb->prepare("SELECT $columns FROM $table_name WHERE class = %s", 'coupon');
            }
            if ($order_by === 'status' || $order_by === 'usages' || $order_by === 'name') {
                $query .= ' ORDER BY ' . 'id' . ' ' . strtoupper($order);
            } else {
                $query .= ' ORDER BY ' . $order_by . ' ' . strtoupper($order);
            }
            if ($per_page !== 0 && $page_number !== 0) {
                $query .= ' LIMIT ' . ($page_number - 1) * $per_page . ', ' . $per_page;
            }
            $results = $wpdb->get_results($query);
            $return = array();
            foreach ($results as $result) {
                if ($running) {
                    /** @var $promotion \TeamBooking\Promotions\Promotion */
                    $promotion = unserialize($result->data_object);
                    if ($promotion->getStatus()) {
                        $return[ $result->id ] = $promotion;
                    }
                } else {
                    $return[ $result->id ] = unserialize($result->data_object);
                }
            }

            if ($order_by === 'status') {
                uasort($return, function ($a, $b) use ($order) {
                    /** @var $a \TeamBooking\Promotions\Promotion */
                    /** @var $b \TeamBooking\Promotions\Promotion */
                    if ($a->getStatus() === $b->getStatus()) return 0;
                    if ($order === 'asc') {
                        return $a->getStatus() > $b->getStatus();
                    } else {
                        return $a->getStatus() < $b->getStatus();
                    }
                });
            } elseif ($order_by === 'usages') {
                $usages = Functions\count_used_discounts();
                uksort($return, function ($a, $b) use ($order, $usages) {
                    $a_count = isset($usages[ $a ]) ? $usages[ $a ] : 0;
                    $b_count = isset($usages[ $b ]) ? $usages[ $b ] : 0;
                    if ($a_count === $b_count) return 0;
                    if ($order === 'asc') {
                        return $a_count > $b_count;
                    } else {
                        return $a_count < $b_count;
                    }
                });
            } elseif ($order_by === 'name') {
                uasort($return, function ($a, $b) use ($order) {
                    /** @var $a \TeamBooking\Promotions\Promotion */
                    /** @var $b \TeamBooking\Promotions\Promotion */
                    if ($a->getName() === $b->getName()) return 0;
                    if ($order === 'asc') {
                        return $a->getName() > $b->getName();
                    } else {
                        return $a->getName() < $b->getName();
                    }
                });
            }
            Cache::add($return, 'promotions' . $running . $filter . $per_page . $page_number . $order_by . $order);
        }

        return $return;
    }

    /**
     * @param \TeamBooking\Promotions\Promotion $data
     *
     * @return mixed
     */
    public static function insert(\TeamBooking\Promotions\Promotion $data)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_promotions';
        if ($data instanceof \TeamBooking_Promotions_Campaign) {
            $class = 'campaign';
        } elseif ($data instanceof \TeamBooking_Promotions_Coupon) {
            $class = 'coupon';
        }
        $created = current_time('mysql');
        $data_object = serialize($data);
        $wpdb->insert($table_name, array(
            'created'     => $created,
            'class'       => $class,
            'start_time'  => date('Y-m-d H:i:s', $data->getStartTime()),
            'end_time'    => date('Y-m-d H:i:s', $data->getEndTime()),
            'data_object' => $data_object,
        ));

        return $wpdb->insert_id;
    }

    /**
     * @param bool|string $filter
     *
     * @return mixed
     */
    public static function count($filter = FALSE)
    {
        global $wpdb;
        $query = "SELECT COUNT(*) FROM {$wpdb->prefix}teambooking_promotions";
        if ($filter === 'coupon') {
            $query = $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}teambooking_promotions WHERE class = %s", 'coupon');
        }
        if ($filter === 'campaign') {
            $query = $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}teambooking_promotions WHERE class = %s", 'campaign');
        }
        if ($filter === 'running') {
            $promotions = self::getAll(TRUE);

            return count($promotions);
        }

        return $wpdb->get_var($query);
    }

    /**
     * @param                                   $id
     * @param \TeamBooking\Promotions\Promotion $data
     *
     * @return mixed
     */
    public static function update($id, \TeamBooking\Promotions\Promotion $data)
    {
        global $wpdb;
        $data_object = serialize($data);
        $table_name = $wpdb->prefix . 'teambooking_promotions';
        $result = $wpdb->update($table_name,
            array(
                'data_object' => $data_object,
                'start_time'  => date('Y-m-d H:i:s', $data->getStartTime()),
                'end_time'    => date('Y-m-d H:i:s', $data->getEndTime()),
            ),
            array('id' => $id)
        );

        return $result;
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public static function delete($id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'teambooking_promotions';

        if (is_array($id)) {
            $how_many = count($id);
            $placeholders = array_fill(0, $how_many, '%d');
            $format = implode(', ', $placeholders);
            $result = $wpdb->get_results($wpdb->prepare("DELETE FROM $table_name WHERE id IN ($format)", $id));
        } else {
            $result = $wpdb->delete($table_name, array('id' => $id));
        }

        return $result;
    }
}