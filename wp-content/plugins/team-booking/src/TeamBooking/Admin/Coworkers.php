<?php

namespace TeamBooking\Admin;
defined('ABSPATH') or die('No script kiddies please!');
use TeamBooking\Database\Services;
use TeamBooking\Functions;

/**
 * Class Coworkers
 *
 * @author VonStroheim
 */
class Coworkers
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
            $column->addElement($this->getCoworkerList());
            $column->appendTo($row);
            $row->render();
            ?>
        </div>

        <?php
        return ob_get_clean();
    }

    /**
     * @return Framework\PanelForList
     */
    private function getCoworkerList()
    {
        $panel = new Framework\PanelForList(ucfirst(__('Coworkers', 'team-booking')));

        // List
        $table = new Framework\Table();
        $table->setId('tbk-coworkers-overview');
        // Preparing the table columns
        $table->addColumns(array(
            esc_html__('Name', 'team-booking'),
            esc_html__('E-mail', 'team-booking'),
            esc_html__('WP Roles', 'team-booking'),
            esc_html__('Google Calendars', 'team-booking'),
            esc_html__('Actions', 'team-booking')
        ));

        // Preparing the table rows
        foreach (Functions\getAllCoworkersList() as $id => $coworker) {
            if (isset($coworker['allowed_no_more'])) {
                $gcal_label = new Framework\TextLabel(__('WP role no longer allowed', 'team-booking'));
                $gcal_label->setColor('red');
            } elseif (empty($coworker['calendars']) && $coworker['token'] === 'refresh') {
                $gcal_label = new Framework\TextLabel(__('not set', 'team-booking'));
                $gcal_label->setColor('yellow');
            } elseif (!empty($coworker['calendars']) && $coworker['token'] === 'refresh') {
                $to_be_sync = 0;
                foreach ($coworker['calendars'] as $calendar) {
                    if (NULL === $calendar['sync_token']) {
                        $to_be_sync++;
                    }
                }
                if (!$to_be_sync) {
                    $gcal_label = new Framework\TextLabel(__('ready', 'team-booking') . ' (' . count($coworker['calendars']) . ')');
                    $gcal_label->setColor('green');
                } else {
                    $gcal_label = new Framework\TextLabel(__('sync needed', 'team-booking') . ' (' . $to_be_sync . '/' . count($coworker['calendars']) . ')');
                    $gcal_label->setColor('yellow');
                }
            } elseif ($coworker['token'] === 'access') {
                $gcal_label = new Framework\TextLabel(__('temporarily authorized', 'team-booking'));
                $gcal_label->setColor('red');
            } else {
                $gcal_label = new Framework\TextLabel(__('not authorized', 'team-booking'));
            }
            $gcal_label->setClass('tbk-calendars-state');
            $row = array(
                0 => ucwords($coworker['name']) . (($id == get_current_user_id()) ? ' (' . esc_html__('you', 'team-booking') . ')' : ''),
                1 => $coworker['email'],
                2 => ucwords(implode(', ', $coworker['roles'])),
                3 => $gcal_label
            );
            if (isset($coworker['allowed_no_more'])) {
                $row[4] = new Framework\ActionButton('dashicons-no');
                $row[4]->addClass('tbk-coworkers-action-clean');
                $row[4]->addClass('tb-remove-residual-data');
                $row[4]->setTitle(__('Remove data', 'team-booking'));
                $row[4]->setData(array('coworker' => $id));
            } else {
                $action = new Framework\ActionButton('dashicons-admin-tools');
                $action->addClass('tbk-coworkers-action-settings');
                $action->setTitle(__('Settings', 'team-booking'));
                $action->setData(array('coworker' => $id));
                $row[4][] = $action;
                $modal = new Framework\Modal('tb-coworker-settings-modal-' . $id);
                $modal->setHeaderText(array('main' => __('Settings', 'team-booking')));
                $modal->addContent('<form id="team-booking-coworker-settings-form-' . $id . '" action="" method="POST">');
                $modal->addContent(' <div style="font-style: italic;font-weight: 300;">' . esc_html__('Profile URL (leave it blank for WordPress default)', 'team-booking') . '</div>');
                $modal->addContent('<input type="text" data-ays-ignore="true" class="large-text" style="margin-bottom:10px;" name="coworker_url" value="' . Functions\getSettings()->getCoworkerUrl($id) . '">');
                $modal->addContent(' <div style="font-style: italic;font-weight: 300;">' . esc_html__('Services allowed:', 'team-booking') . '</div>');
                foreach (Services::get() as $service) {
                    $modal->addContent('<label><input type="checkbox" data-ays-ignore="true" name="coworker_services[]" value="'
                        . $service->getId() . '"'
                        . (in_array($service->getId(), $coworker['services_allowed']) ? ' checked="checked"' : '')
                        . '>' . $service->getName(TRUE) . '</label><br>');
                }
                $modal->addContent('</form>');
                $row[4][] = $modal;
                if (!empty($coworker['token'])) {
                    $action = new Framework\ActionButton('dashicons-editor-unlink');
                    $action->addClass('tbk-coworkers-action-unlink');
                    $action->addClass('tbk-revoke-coworker-token');
                    $action->setTitle(__('Revoke Authorization', 'team-booking'));
                    $action->setData(array(
                        'coworker' => $id,
                        'name'     => ucwords($coworker['name'])
                    ));
                    $row[4][] = $action;
                    // sync button
                    $action = new Framework\ActionButton('dashicons-randomize');
                    $action->setTitle(__('Sync', 'team-booking'));
                    $action->addClass('tb-sync-all-calendars');
                    $action->addData('coworker', $id);
                    $row[4][] = $action;
                }
            }
            $table->addRow($row);
        }
        $panel->addElement($table);

        $modal = new Framework\Modal('tb-coworker-token-revoke-modal');
        $modal->setHeaderText(array('main' => __('Are you sure?', 'team-booking')));
        $modal->setButtonText(array(
            'approve' => __('Yes', 'team-booking'),
            'deny'    => __('No', 'team-booking')
        ));
        $modal->addContent('<p>' . esc_html__('You are going to revoke authorization given to TeamBooking by the Google Account of:', 'team-booking') . ' ' . Framework\Html::span(array('id' => 'revoke-coworker-name')) . '</p>');
        $modal->addContent(Framework\Html::paragraph(__("He won't be able to place availability to your services until he gives authorization again", 'team-booking')));
        $panel->addElement($modal);

        return $panel;
    }

}
