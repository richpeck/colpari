<?php

namespace TeamBooking\Admin;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Database\Promotions;
use TeamBooking\Database\Reservations;
use TeamBooking\Functions;
use TeamBooking\Database\Services;
use TeamBooking\Toolkit;

/**
 * Class PromotionTable
 *
 * @since    2.3.0
 * @author   VonStroheim
 */
class PromotionTable extends \WP_List_Table
{
    public $total_promotions_all;
    public $total_promotions_coupon;
    public $total_promotions_campaign;
    public $total_promotions_running;
    public $reservations;
    public $promotions;
    public $services;
    public $filter_by;
    public $order;
    public $order_by;
    public $current_page;
    public $items_per_page = 30;
    public $currency_details;
    public $discount_counts;

    /**
     * PromotionTable constructor.
     */
    public function __construct()
    {
        parent::__construct(array(
            'singular' => __('Promotion', 'team-booking'),
            'plural'   => __('Promotions', 'team-booking'),
            'ajax'     => TRUE
        ));
        $this->process_bulk_action();
        #$this->process_single_action();
        $this->reservations = Reservations::getAll();
        $this->discount_counts = Functions\count_used_discounts();
        $this->total_promotions_all = Promotions::count();
        $this->total_promotions_coupon = Promotions::count('coupon');
        $this->total_promotions_campaign = Promotions::count('campaign');
        $this->total_promotions_running = Promotions::count('running');
        $this->services = Services::get();
        $this->currency_details = \TeamBooking\Toolkit\getCurrencies(Functions\getSettings()->getCurrencyCode());
        $this->filter_by = isset($_GET['filter']) ? $_GET['filter'] : FALSE;
        $this->current_page = $this->get_pagenum();
        $this->order = isset($_GET['order']) && in_array($_GET['order'], array('asc', 'desc')) ? $_GET['order'] : 'desc';
        $order_by = isset($_GET['orderby']) && in_array($_GET['orderby'], array_keys($this->get_sortable_columns())) ? $_GET['orderby'] : 'id';
        switch ($order_by) {
            case 'name':
                $this->order_by = 'name';
                break;
            case 'usages':
                $this->order_by = 'usages';
                break;
            case 'start':
                $this->order_by = 'start_time';
                break;
            case 'end':
                $this->order_by = 'end_time';
                break;
            case 'status':
                $this->order_by = 'status';
                break;
            default:
                $this->order_by = 'id';
                break;
        }
    }

    public function no_items()
    {
        _e('No promotions available.', 'team-booking');
    }

    /**
     * @return array
     */
    public function get_columns()
    {
        $columns = array(
            'cb'      => '<input type="checkbox" />',
            'name'    => esc_html__('Name', 'team-booking'),
            'usages'  => esc_html__('Uses', 'team-booking'),
            'status'  => esc_html__('Status', 'team-booking'),
            'start'   => esc_html__('Start', 'team-booking'),
            'end'     => esc_html__('End', 'team-booking'),
            'actions' => esc_html__('Actions', 'team-booking')
        );

        return $columns;
    }

    public function prepare_items()
    {
        $which_items = 'total_promotions_' . (!$this->filter_by ? 'all' : $this->filter_by);
        $this->set_pagination_args(array(
            'total_items' => $this->$which_items,
            'per_page'    => $this->items_per_page
        ));

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $this->get_items();
    }

    /**
     * @param object $item
     * @param string $column_name
     *
     * @return mixed
     */
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'name':
            case 'usages':
            case 'status':
            case 'start':
            case 'end':
            case 'actions':
                return $item[ $column_name ];
            default:
                return print_r($item, TRUE);
        }
    }

    public function process_bulk_action()
    {
        if (isset($_POST['_wpnonce']) && !empty($_POST['_wpnonce'])) {

            $nonce = filter_input(INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING);
            $action = 'bulk-' . $this->_args['plural'];

            if (!wp_verify_nonce($nonce, $action))
                wp_die('Nope! Security check failed!');

        }

        if (!isset($_POST['promotions']) || empty($_POST['promotions'])) {
            return;
        }

        switch ($_POST['action']) {

            case 'tbk-bulk-delete':
                if (Functions\isAdmin()) {
                    Promotions::delete(array_values($_POST['promotions']));
                }
                break;

            case 'tbk-bulk-pause':
                foreach ($_POST['promotions'] as $promotion_id) {
                    $promotion = Promotions::getById($promotion_id);
                    $promotion->setStatus(FALSE);
                    Promotions::update($promotion_id, $promotion);
                }
                break;

            case 'tbk-bulk-run':
                foreach ($_POST['promotions'] as $promotion_id) {
                    $promotion = Promotions::getById($promotion_id);
                    $promotion->setStatus(TRUE);
                    Promotions::update($promotion_id, $promotion);
                }
                break;

            default:
                return;
                break;
        }
    }

    /**
     * @return array
     */
    public function get_views()
    {
        $current = 'all';
        if ($this->filter_by === 'campaign') $current = 'campaign';
        if ($this->filter_by === 'coupon') $current = 'coupon';
        if ($this->filter_by === 'running') $current = 'running';

        $status_links = array(
            'all' => '<a href="'
                . admin_url('/admin.php?page=team-booking-pricing') . '"'
                . ($current === 'all' ? 'class="current"' : '')
                . '>'
                . esc_html__('All', 'team-booking')
                . ' <span class="count">(' . $this->total_promotions_all . ')</span>'
                . '</a>'
        );
        if ($this->total_promotions_campaign > 0) {
            $status_links['campaign'] = '<a href="'
                . admin_url('/admin.php?page=team-booking-pricing&filter=campaign') . '"'
                . ($current === 'campaign' ? 'class="current"' : '')
                . '>'
                . esc_html__('Campaigns', 'team-booking')
                . ' <span class="count">(' . $this->total_promotions_campaign . ')</span>'
                . '</a>';
        }
        if ($this->total_promotions_coupon > 0) {
            $status_links['coupon'] = '<a href="'
                . admin_url('/admin.php?page=team-booking-pricing&filter=coupon') . '"'
                . ($current === 'coupon' ? 'class="current"' : '')
                . '>'
                . esc_html__('Coupons', 'team-booking')
                . ' <span class="count">(' . $this->total_promotions_coupon . ')</span>'
                . '</a>';
        }
        if ($this->total_promotions_running > 0) {
            $status_links['running'] = '<a href="'
                . admin_url('/admin.php?page=team-booking-pricing&filter=running') . '"'
                . ($current === 'running' ? 'class="current"' : '')
                . '>'
                . ucfirst(esc_html__('Running', 'team-booking'))
                . ' <span class="count">(' . $this->total_promotions_running . ')</span>'
                . '</a>';
        }

        return $status_links;
    }

    /**
     * @param string $which
     */
    public function extra_tablenav($which = 'top')
    {
        // Nothing yet
    }

    /**
     * @param object $item
     *
     * @return mixed
     */
    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="promotions[]" value="%s" />', $item['ID']
        );
    }

    /**
     * @return array
     */
    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'name'   => array('name', FALSE),
            'usages' => array('usages', FALSE),
            'status' => array('status', FALSE),
            'start'  => array('start', FALSE),
            'end'    => array('end', FALSE)
        );

        return $sortable_columns;
    }

    /**
     * @return array
     */
    public function get_bulk_actions()
    {
        return array(
            'tbk-bulk-delete' => esc_html__('Delete', 'team-booking'),
            'tbk-bulk-pause'  => esc_html__('Pause', 'team-booking'),
            'tbk-bulk-run'    => esc_html__('Run', 'team-booking')
        );
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function get_items()
    {
        $this->promotions = Promotions::getAll($this->filter_by === 'running', $this->filter_by, $this->items_per_page, $this->current_page, $this->order_by, $this->order);
        $items = array();
        $now = current_time('timestamp', TRUE);
        $timezone = Toolkit\getTimezone();
        foreach ($this->promotions as $db_id => $promotion) {
            if ($now > $promotion->getEndTime()) {
                $status_string = Framework\Html::span(array(
                    'text'  => __('expired', 'team-booking'),
                    'class' => 'tbk-table-status-expired'
                ));
            } elseif (!$promotion->getStatus()) {
                $status_string = Framework\Html::span(array(
                    'text'  => __('Deactivated', 'team-booking'),
                    'class' => 'tbk-table-status-expired'
                ));
            } elseif ($now >= $promotion->getStartTime() && $now <= $promotion->getEndTime()) {
                if (isset($this->discount_counts[ $db_id ])
                    && $promotion->getLimit()
                    && $this->discount_counts[ $db_id ] >= $promotion->getLimit()
                ) {
                    $status_string = Framework\Html::span(array(
                        'text'  => __('Limit reached', 'team-booking'),
                        'class' => 'tbk-cancelled'
                    ));
                } else {
                    $status_string = Framework\Html::span(array(
                        'text'  => __('Running', 'team-booking'),
                        'class' => 'tbk-confirmed'
                    ));
                }
            } else {
                $status_string = Framework\Html::span(array(
                    'text'  => __('Not running', 'team-booking'),
                    'class' => 'tbk-table-status-expired'
                ));
            }
            if (($promotion instanceof \TeamBooking_Promotions_Coupon) && count($promotion->getList()) > 0) {
                $out_of = sprintf(esc_html_x('out of %d', 'expressing ratio like: 1 out of 3', 'team-booking'), count($promotion->getList()));
            } else {
                $out_of = (!$promotion->getLimit())
                    ? esc_html__('Unlimited', 'team-booking')
                    : sprintf(esc_html_x('out of %d', 'expressing ratio like: 1 out of 3', 'team-booking'), $promotion->getLimit());
            }
            $items[ $db_id ] = array(
                'ID'     => $db_id,
                'name'   => $promotion->getName() . Framework\Html::span(array(
                        'text'  => ($promotion instanceof \TeamBooking_Promotions_Coupon) ? esc_html__('Coupon', 'team-booking') : esc_html__('Campaign', 'team-booking'),
                        'class' => 'tbk-description'
                    )),
                'usages' => (isset($this->discount_counts[ $db_id ]) ? $this->discount_counts[ $db_id ] : 0)
                    . Framework\Html::span(array(
                        'text'  => $out_of,
                        'class' => 'tbk-description'
                    )),
                'status' => $status_string,
                'start'  => Functions\dateFormatter($promotion->getStartTime(), TRUE)->date
                    . Framework\Html::span(array(
                        'text'  => $timezone->getName(),
                        'class' => 'tbk-description'
                    )),
                'end'    => Functions\dateFormatter($promotion->getEndTime(), TRUE)->date
                    . Framework\Html::span(array(
                        'text'  => $timezone->getName(),
                        'class' => 'tbk-description'
                    ))
            );

            $items[ $db_id ]['actions'] = $this->getPromotionActions($promotion, $db_id);
        }

        return $items;
    }

    /**
     * @param \TeamBooking\Promotions\Promotion $promotion
     * @param                                   $id
     *
     * @return string
     */
    public function getPromotionActions(\TeamBooking\Promotions\Promotion $promotion, $id)
    {
        $now = current_time('timestamp');
        ob_start();

        if ($now >= $promotion->getStartTime() && $now <= $promotion->getEndTime()) {
            if ($promotion->getStatus()) {
                $button = new Framework\ActionButton('dashicons-controls-pause');
                $button->addClass('tbk-promotion-action-pause');
                $button->addData('id', $id);
                $button->addData('promotion', $promotion->getName());
                $button->setTitle(__('Pause', 'team-booking'));
                $button->render();
            } else {
                $button = new Framework\ActionButton('dashicons-controls-play');
                $button->addClass('tbk-promotion-action-run');
                $button->addData('id', $id);
                $button->addData('promotion', $promotion->getName());
                $button->setTitle(__('Run', 'team-booking'));
                $button->render();
            }
        }
        $button = new Framework\ActionButton('dashicons-trash');
        $button->addClass('tbk-promotion-action-delete');
        $button->addData('id', $id);
        $button->addData('name', $promotion->getName());
        $button->setTitle(__('Delete', 'team-booking'));
        $button->render();
        $button = new Framework\ActionButton('dashicons-edit');
        $button->addClass('tbk-promotion-action-edit');
        $button->addData('id', $id);
        $button->addData('decimals', $this->currency_details['decimal'] ? 2 : 0);
        $button->addData('promotion', $promotion->getName());
        $button->setTitle(__('Edit', 'team-booking'));
        $button->render();

        $this->getEditPromotionModal($promotion, $id)->render();

        return ob_get_clean();
    }

    /**
     * @param \TeamBooking\Promotions\Promotion $promotion
     * @param                                   $id
     *
     * @return Framework\Modal
     */
    private function getEditPromotionModal(\TeamBooking\Promotions\Promotion $promotion, $id)
    {
        $start_time = \DateTime::createFromFormat('U', $promotion->getStartTime());
        $end_time = \DateTime::createFromFormat('U', $promotion->getEndTime());
        $bound_start_time = \DateTime::createFromFormat('U', NULL !== $promotion->getStartBound() ? $promotion->getStartBound() : $promotion->getStartTime());
        $bound_end_time = \DateTime::createFromFormat('U', NULL !== $promotion->getEndBound() ? $promotion->getEndBound() : $promotion->getEndTime());
        $usages = (isset($this->discount_counts[ $id ]) ? $this->discount_counts[ $id ] : 0);

        $modal = new Framework\Modal($promotion->getName() . '-edit-modal');
        $modal->setHeaderText(array('main' => $promotion->getName()));
        $modal->setButtonText(array('approve' => __('Save', 'team-booking'), 'deny' => __('Cancel', 'team-booking')));
        $modal->addErrorText(__('Error: please select at least one service', 'team-booking'));
        $modal->addData(array('promotion' => $promotion->getName()));
        $modal->addClass('tbk-edit-promotion-modal');

        ob_start();
        ?>
        <ul class="tbk-list">
            <li>
                <h4><?= esc_html__('Name', 'team-booking') ?></h4>
                <?= Framework\Html::textfield(array('name' => 'promotion_name', 'value' => $promotion->getName(), 'required' => TRUE)) ?>
                <p class="error name" style="display:none;">
                    <?= esc_html__('This name is already in use, please provide a fresh one.', 'team-booking') ?>
                </p>
            </li>

            <li>
                <h4><?= esc_html__('Discount', 'team-booking') ?></h4>
                <p><?= esc_html__('It will be applied to the base price of the service.', 'team-booking') ?></p>
                <table>
                    <tbody>
                    <tr>
                        <td>
                            <input type="number" name="discount_value" min="1" step="1" class="small-text"
                                   value="<?= $promotion->getDiscount() ?>">
                        </td>
                        <td>
                            <?= Framework\Html::radio(array(
                                'text'    => '%',
                                'name'    => $promotion->getName() . '-discount_type',
                                'value'   => 'percentage',
                                'checked' => 'percentage' === $promotion->getDiscountType()
                            )) ?>
                            <br>
                            <?= Framework\Html::radio(array(
                                'text'    => Functions\currencyCodeToSymbol(),
                                'name'    => $promotion->getName() . '-discount_type',
                                'value'   => 'direct',
                                'checked' => 'direct' === $promotion->getDiscountType()
                            )) ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </li>

            <li>
                <h4><?= esc_html__('Promotion period', 'team-booking') ?></h4>
                <p><?= esc_html__('The promotion will be running within this date range.', 'team-booking') ?></p>
                <?= Framework\Html::textfield(array(
                    'name'        => 'validity_date_range',
                    'value'       => $start_time->format('m/d/Y'),
                    'required'    => TRUE,
                    'placeholder' => 'MM/DD/YYYY',
                    'class'       => 'tb-flatpickr',
                    'data'        => array(
                        'dateformat'       => 'm/d/Y',
                        'altformat'        => str_replace('jS', 'J', get_option('date_format')),
                        'locale'           => TEAMBOOKING_SHORT_LANG,
                        'defaultdatestart' => $start_time->format('m/d/Y'),
                        'defaultdateend'   => $end_time->format('m/d/Y'),
                        'mode'             => 'range'
                    )
                )) ?>

                <p class="error start-date" style="display:none;">
                    <?= esc_html__('Wrong date or format. Please retry.', 'team-booking') ?>
                </p>
            </li>
        </ul>
        <?php
        $modal->addTab('general', __('General', 'team-booking'), ob_get_clean());
        ob_start();
        ?>
        <ul class="tbk-list">
            <li>
                <h4><?= esc_html__('Maximum uses', 'team-booking') ?></h4>
                <p><?= esc_html__('How many times (reservations) the discount can be used. Set 0 for no limit.', 'team-booking') ?></p>
                <input type="number" name="promotion_limit" min="0" step="1" class="small-text"
                       value="<?= $promotion->getLimit() ?>">
                <?php if ($usages) { ?>
                    <p>
                        <em><?= sprintf(esc_html__('Already used %d times, please set either 0 or >= %d', 'team-booking'), $usages, $usages) ?></em>
                    </p>
                <?php } ?>
            </li>

            <?php
            if ($promotion->checkClass('coupon')) {
                /** @var $promotion \TeamBooking_Promotions_Coupon */
                $coupon_list = $promotion->getList();
                ?>
                <li>
                    <h4><?= esc_html__('Coupon mode', 'team-booking') ?></h4>
                    <p><?= esc_html__('If fixed, the coupon text will be equal to the promotion name. Otherwise, you can provide a list of comma-separated single use coupons. Check the documentation for more details.', 'team-booking') ?></p>
                    <?= Framework\Html::radio(array(
                        'text'    => esc_html__('fixed', 'team-booking'),
                        'name'    => $promotion->getName() . '-coupon_mode',
                        'value'   => 'fixed',
                        'checked' => empty($coupon_list)
                    )) ?>
                    <br>
                    <?= Framework\Html::radio(array(
                        'text'    => esc_html__('list', 'team-booking'),
                        'name'    => $promotion->getName() . '-coupon_mode',
                        'value'   => 'list',
                        'checked' => !empty($coupon_list)
                    )) ?>
                    <?= Framework\Html::textarea(array(
                        'placeholder' => esc_html__('List of comma-separated coupons (e.g. Summer171, Summer172, Summer173)', 'team-booking'),
                        'class'       => 'tbk-coupon-list-values',
                        'style'       => 'width:100%;' . (empty($coupon_list) ? 'display:none;' : ''),
                        'rows'        => 4,
                        'name'        => $promotion->getName() . '-coupon_list_values',
                        'value'       => implode(',', $coupon_list)
                    )) ?>
                    <script>
                        jQuery('.tbk-modal').find('.tb-advanced').on('click', 'input[name="<?= $promotion->getName() ?>-coupon_mode"]', function () {
                            $clicked = jQuery(this);
                            if (jQuery('input[name="<?= $promotion->getName() ?>-coupon_mode"]:checked', '.tb-advanced').val() == 'list') {
                                $clicked.closest('li').find('.tbk-coupon-list-values').show();
                            } else {
                                $clicked.closest('li').find('.tbk-coupon-list-values').hide();
                            }
                        });
                    </script>
                </li>
            <?php } ?>

            <li>
                <h4><?= esc_html__('Timeslots min start date', 'team-booking') ?></h4>
                <p><?= esc_html__('If active, this promotion will be applied only to timeslots that begin from this date or subsequently.', 'team-booking') ?></p>
                <?= Framework\Html::textfield(array(
                    'name'        => 'bound_start_date',
                    'value'       => $bound_start_time->format('m/d/Y'),
                    'required'    => TRUE,
                    'placeholder' => 'MM/DD/YYYY',
                    'class'       => 'tb-flatpickr',
                    'data'        => array(
                        'dateformat' => 'm/d/Y',
                        'altformat'  => str_replace('jS', 'J', get_option('date_format')),
                        'locale'     => TEAMBOOKING_SHORT_LANG
                    )
                )) ?>
                <?= Framework\Html::checkbox(array(
                    'text'    => __('active', 'team-booking'),
                    'name'    => 'bound_start_date_active',
                    'checked' => NULL !== $promotion->getStartBound()
                ));
                ?>
                <p>
                    <em><?= esc_html__('This setting will be ignored by unscheduled services.', 'team-booking') ?></em>
                </p>
                <p class="error start-date" style="display:none;">
                    <?= esc_html__('Wrong date or format. Please retry.', 'team-booking') ?>
                </p>
            </li>
            <li>
                <h4><?= esc_html__('Timeslots max end date', 'team-booking') ?></h4>
                <p><?= esc_html__('If active, this promotion will be applied only to timeslots that end within this date.', 'team-booking') ?></p>
                <?= Framework\Html::textfield(array(
                    'name'        => 'bound_end_date',
                    'value'       => $bound_end_time->format('m/d/Y'),
                    'required'    => TRUE,
                    'placeholder' => 'MM/DD/YYYY',
                    'class'       => 'tb-flatpickr',
                    'data'        => array(
                        'dateformat' => 'm/d/Y',
                        'altformat'  => str_replace('jS', 'J', get_option('date_format')),
                        'locale'     => TEAMBOOKING_SHORT_LANG
                    )
                )) ?>
                <?= Framework\Html::checkbox(array(
                    'text'    => __('active', 'team-booking'),
                    'name'    => 'bound_end_date_active',
                    'checked' => NULL !== $promotion->getEndBound()
                ));
                ?>
                <p>
                    <em><?= esc_html__('This setting will be ignored by unscheduled services.', 'team-booking') ?></em>
                </p>
                <p class="error end-date" style="display:none;">
                    <?= esc_html__('Wrong date or format. Please retry.', 'team-booking') ?>
                </p>
            </li>
        </ul>
        <?php
        $modal->addTab('advanced', __('Advanced', 'team-booking'), ob_get_clean());
        ob_start();
        ?>
        <ul class="tbk-list">
            <li>
                <table class="tbk-promotion-services" style="width: 100%">
                    <tbody>
                    <tr>
                        <th>
                            <h4><?= esc_html__('Services', 'team-booking') ?></h4>
                        </th>
                        <th>
                            <h4><?= esc_html__('Base price', 'team-booking') ?></h4>
                        </th>
                        <th>
                            <h4><?= esc_html__('Discounted price', 'team-booking') ?></h4>
                        </th>
                    </tr>
                    <?php foreach ($this->services as $service) {
                        if ($service->getPrice() <= 0) {
                            continue;
                        }
                        ?>
                        <tr>
                            <td style="border-bottom: 1px dashed lightgrey;">
                                <?= Framework\Html::checkbox(array(
                                    'text'    => $service->getName(TRUE),
                                    'name'    => $promotion->getName() . $service->getId(),
                                    'value'   => $service->getId(),
                                    'checked' => $promotion->checkService($service->getId())
                                )) ?>
                            </td>
                            <td style="border-bottom: 1px dashed lightgrey;">
                                        <span id="<?= $promotion->getName() . '-' . $service->getId() ?>-base"
                                              data-price="<?= $service->getPrice() ?>"
                                              class='tbk-promotion-service-base-price'>
                                            <?= Functions\currencyCodeToSymbol($service->getPrice()) ?>
                                        </span>
                            </td>
                            <td style="border-bottom: 1px dashed lightgrey;">
                                        <span id="<?= $promotion->getName() . '-' . $service->getId() ?>-discounted"
                                              data-a-sign="<?= Functions\currencyCodeToSymbol() ?>"
                                              class='tbk-promotion-service-discounted-price'
                                            <?php if (!$promotion->checkService($service->getId())) { ?>
                                                style="display: none"
                                            <?php } ?>
                                        >
                                            <?php if ($promotion->checkService($service->getId())) { ?>
                                                <?php if ($promotion->getDiscountType() === 'percentage') { ?>
                                                    <?= $service->getPrice() - $service->getPrice() * $promotion->getDiscount() / 100 ?>
                                                <?php } else {
                                                    if ($service->getPrice() - $promotion->getDiscount() < 0) {
                                                        ?>
                                                        0
                                                    <?php } else { ?>
                                                        <?= $service->getPrice() - $promotion->getDiscount() ?>
                                                    <?php }
                                                }
                                            } ?>
                                        </span>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </li>
        </ul>
        <?php
        $modal->addTab('services', __('Services', 'team-booking'), ob_get_clean());

        return $modal;
    }

}