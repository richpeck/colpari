<?php

namespace TeamBooking\Admin;
defined('ABSPATH') or die('No script kiddies please!');
use TeamBooking\Admin,
    TeamBooking\Functions,
    TeamBooking\Toolkit;

/**
 * Class Payments
 *
 * @author VonStroheim
 */
class Payments
{
    /**
     * @return string
     */
    public function getPostBody()
    {
        ob_start();
        ?>
        <div class="tbk-wrapper">
            <?php
            $row = new Framework\Row();
            $column = Framework\Column::fullWidth();
            $column->addElement(Framework\ElementFrom::content($this->getCurrencyHeader()));
            $column->appendTo($row);
            $row->render();

            $row = new Framework\Row();
            foreach (Functions\getSettings()->getPaymentGatewaySettingObjects() as $gateway) {
                $column = Framework\Column::ofWidth(6);
                $column->addElement($gateway->getBackendSettingsTab());
                $column->appendTo($row);
            }
            $row->render();
            ?>
        </div>

        <?php
        return ob_get_clean();
    }

    /**
     * @return string
     */
    private function getCurrencyHeader()
    {
        ob_start();
        ?>
        <form method="POST" action="<?= Admin::add_params_to_admin_url(admin_url('admin-post.php')) ?>">
            <input type="hidden" name="action" value="tbk_save_payments">
            <?php wp_nonce_field('team_booking_options_verify') ?>
            <div class="tbk-panel tbk-content">
                <div class="tbk-settings-title">
                    <?= Framework\Html::h3(array('text' => __('Currency', 'team-booking'), 'class' => 'tbk-heading')) ?>

                    <p class="tbk-excerpt">
                        <select name="currency_code">
                            <option value=""><?= esc_html__('Select currency...', 'team-booking') ?></option>
                            <?php foreach (Toolkit\getCurrencies() as $code => $data) { ?>
                                <option
                                    value="<?= $code ?>" <?php selected(Functions\getSettings()->getCurrencyCode(), $code, TRUE); ?>>
                                    <?= $code ?> (<?= $data['label'] ?>)
                                </option>
                            <?php } ?>
                        </select>
                    </p>
                    <input type="submit" name="tbk_save_payments"
                           class="button button-hero button-primary"
                           value="<?= esc_attr__('Save changes', 'team-booking') ?>">
                </div>
            </div>
        </form>
        <?php
        return ob_get_clean();
    }

}
