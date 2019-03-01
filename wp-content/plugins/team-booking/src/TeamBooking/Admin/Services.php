<?php

namespace TeamBooking\Admin;
defined('ABSPATH') or die('No script kiddies please!');
use TeamBooking\Functions;

/**
 * Class Services
 *
 * @author VonStroheim
 */
class Services
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
            $column->addElement($this->getServiceList());
            $column->appendTo($row);
            $row->render();
            ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * @return Framework\PanelForList
     * @throws \Exception
     */
    private function getServiceList()
    {
        $user_data = Functions\getSettings()->getCoworkerData(get_current_user_id());
        $panel = new Framework\PanelForList(ucfirst(__('Services', 'team-booking')));
        if (Functions\isAdmin()) {
            $panel_button = new Framework\PanelTitleAddNewButton();
            $panel_button->addClass('team-booking-new-service');
            $panel_button->setAdditionalContent($this->getNewServiceModal());
            $panel->addTitleContent($panel_button);
        }

        // List
        $table = new Framework\Table();
        $table->setId('tbk-services-list');
        $table->setSelectable(TRUE);
        // Preparing the table columns
        $columns_array = array(
            esc_html__('Name', 'team-booking'),
            esc_html__('Class', 'team-booking'),
            esc_html__('Color', 'team-booking')
        );
        if (Functions\isAdmin()) {
            $columns_array[] = esc_html__('Active', 'team-booking');
        }
        $columns_array[] = esc_html__('Participate', 'team-booking');
        $columns_array[] = esc_html__('Actions', 'team-booking');
        $table->addColumns($columns_array);
        // Preparing the table rows
        foreach (\TeamBooking\Database\Services::get() as $service) {
            if (!Functions\isAdmin() && !$user_data->isServiceAllowed($service->getId())) continue;
            $participation_label_id = 'participation-label-' . $service->getId();
            $row = array(
                0 => $service->getName(),
                1 => ucfirst($service->getClass()),
                2 => Framework\CircularColorLabel::ofColor($service->getColor())
            );
            // Activation cell
            if (Functions\isAdmin()) {
                $slider = new Framework\OnOffSlider('toggle-service-activation-' . $service->getId());
                $slider->setChecked($service->isActive());
                $slider->addCheckedCallback(
                    "tbToggleService('activate', '" . $service->getId() . "');"
                    . "jQuery('#toggle-service-participation-" . $service->getId() . "').show();"
                    . "jQuery('#" . $participation_label_id . "').hide();"
                );
                $slider->addUncheckedCallback(
                    "tbToggleService('deactivate', '" . $service->getId() . "');"
                    . "jQuery('#toggle-service-participation-" . $service->getId() . "').hide();"
                    . "jQuery('#" . $participation_label_id . "').show();"
                );
                $row[] = $slider;
            }
            // Participation cell
            $row_content = array();
            if ($service->getClass() === 'unscheduled' && $service->getSettingsFor('assignment_rule') === 'direct') {
                if ($service->getDirectCoworkerId() === get_current_user_id()) {
                    $label = new Framework\TextLabel(__('assigned to you', 'team-booking'));
                    $label->setId('toggle-service-participation-' . $service->getId());
                    $label->setHidden(!$service->isActive());
                    $label->setColor('green');
                    $row_content[] = $label;
                } else {
                    $label = new Framework\TextLabel(__('assigned to someone else', 'team-booking'));
                    $label->setId('toggle-service-participation-' . $service->getId());
                    $label->setHidden(!$service->isActive());
                    $label->setColor('red');
                    $row_content[] = $label;
                }

            } else {
                $slider = new Framework\OnOffSlider('toggle-service-participation-' . $service->getId());
                $slider->setHidden(!$service->isActive());
                $slider->setChecked(Functions\getSettings()->getCoworkerData(get_current_user_id())->getCustomEventSettings($service->getId())->isParticipate());
                $slider->addCheckedCallback(
                    "tbToggleService('activate', '" . $service->getId() . "', true);"
                );
                $slider->addUncheckedCallback(
                    "tbToggleService('deactivate', '" . $service->getId() . "', true);"
                );
                $row_content[] = $slider;
            }
            $label = new Framework\TextLabel(__('disabled', 'team-booking'));
            $label->setId($participation_label_id);
            $label->setHidden($service->isActive());
            $row_content[] = $label;
            $row[] = $row_content;
            // Actions cell
            $row_content = array();
            $button = new Framework\ActionButton('dashicons-email');
            $button->setHref(admin_url('admin.php?page=team-booking-events&event=' . $service->getId()) . '&email=1');
            $button->setTitle(__('Email', 'team-booking'));
            $button->addClass('tbk-service-action-email');
            $row_content[] = $button;
            if (Functions\isAdmin()) {
                $button = new Framework\ActionButton('dashicons-editor-ul');
                $button->setHref(admin_url('admin.php?page=team-booking-events&event=' . $service->getId()) . '&form=1');
                $button->setTitle(__('Reservation form', 'team-booking'));
                $button->addClass('tbk-service-action-form');
                $row_content[] = $button;
            }
            if (Functions\isAdmin()) {
                $button = new Framework\ActionButton('dashicons-trash');
                $button->setTitle(__('Delete', 'team-booking'));
                $button->setClasses(array('team-booking-delete-service', 'tbk-service-action-delete'));
                $button->setData(array(
                    'servicename' => $service->getName(),
                    'serviceid'   => $service->getId()
                ));
                $row_content[] = $button;
            }
            if (Functions\isAdmin()) {
                $button = new Framework\ActionButton('dashicons-admin-page');
                $button->setTitle(__('Clone', 'team-booking'));
                $button->setClasses(array('team-booking-clone-service', 'tbk-service-action-clone'));
                $button->setData(array(
                    'serviceid' => $service->getId()
                ));
                $row_content[] = $button;
            }
            if (Functions\isAdmin()) {
                $button = new Framework\ActionButton('dashicons-admin-tools');
                $button->setHref(admin_url('admin.php?page=team-booking-events&event=' . $service->getId()));
                $button->setTitle(__('Settings', 'team-booking'));
                $button->addClass('tbk-service-action-settings');
                $row_content[] = $button;
            }
            if ($service->getClass() !== 'unscheduled') {
                $button = new Framework\ActionButton('dashicons-calendar');
                $button->setHref(admin_url('admin.php?page=team-booking-events&event=' . $service->getId()) . '&gcal=1');
                $button->setTitle(__('Personal availability settings', 'team-booking'));
                $button->addClass('tbk-service-action-calendar');
                $row_content[] = $button;
            }
            $row[] = $row_content;
            $row['data-row'] = $service->getId();
            $table->addRow($row);
        }
        $panel->addElement($table);

        ob_start();
        ?>
        <!-- Select services scripts -->
        <script>
            jQuery('#tbk-services-list').find('.tb-table-select-row').change(function () {
                var checked = jQuery('#tbk-services-list').find('.tb-table-select-row:checked').length;
                if (checked > 0) {
                    jQuery('#selected-services-actions').show();
                    jQuery('#selected-services-number').html(checked);
                } else {
                    jQuery('#selected-services-actions').hide();
                    jQuery('#selected-services-number').html(checked);
                }
            });
        </script>
        <!-- Select services notification -->
        <div class="tablenav">
            <div id="selected-services-actions" style="float:left;display: none;" class="displaying-num">
                <?= Framework\Html::span(array('id' => 'selected-services-number')) ?>
                <?= strtolower(esc_html__('selected', 'team-booking')) ?>
                <?php if (Functions\isAdmin()) { ?>
                    (<a
                        id="remove-selected-services"
                        href="#"><!--
                        --><?= strtolower(__('delete', 'team-booking')) ?><!--
                    --></a>)
                <?php } ?>
            </div>
        </div>
        <?php
        $panel->addElement(Framework\ElementFrom::content(ob_get_clean()));

        // Confirmation Modal
        $modal = new Framework\Modal('tb-booking-delete-modal');
        $modal->setButtonText(array(
            'approve' => __('Yes', 'team-booking'),
            'deny'    => __('No', 'team-booking')
        ));
        $modal->setHeaderText(array('main' => __('Are you sure?', 'team-booking')));
        $modal->addContent(sprintf(esc_html__('You are going to permanently delete %s', 'team-booking'), Framework\Html::span(array('class' => 'service-name'))));
        $panel->addElement($modal);

        // Confirmation Modal (selected services)
        $modal = new Framework\Modal('tb-service-delete-selected-modal');
        $modal->setButtonText(array(
            'approve' => __('Yes', 'team-booking'),
            'deny'    => __('No', 'team-booking')
        ));
        $modal->setHeaderText(array('main' => __('Are you sure?', 'team-booking')));
        $modal->addContent(sprintf(
            esc_html__('You are going to permanently delete %s', 'team-booking'),
            Framework\Html::span(array(
                'text'  => __('all the selected services', 'team-booking'),
                'class' => 'selected-services'
            ))
        ));
        $panel->addElement($modal);

        // Clone service modal
        $modal = new Framework\Modal('tb-booking-clone-service-modal');
        $modal->setButtonText(array(
            'approve' => __('Clone', 'team-booking'),
            'deny'    => __('Cancel', 'team-booking')
        ));
        $modal->setHeaderText(array('main' => __('Clone service?', 'team-booking')));
        $modal->addContent(Framework\Html::label(__('Please provide a new service id', 'team-booking')));
        $modal->addContent('<input class="large-text" type="text" id="tb-booking-clone-service-new-id" value="">');
        $modal->addContent(Framework\Html::paragraph(array(
            'text' => __('This service id is already in use, please provide a fresh one.', 'team-booking'),
            'id'   => 'tbk-clone-id-already-existant',
            'show' => FALSE
        )));
        $panel->addElement($modal);

        return $panel;
    }

    /**
     * @return Framework\Modal
     */
    private function getNewServiceModal()
    {
        $modal = new Framework\Modal('tb-booking-new-service-modal');
        $modal->setHeaderText(array('main' => __('New service', 'team-booking')));
        $modal->setButtonText(array(
            'approve' => __('Add', 'team-booking'),
            'deny'    => __('Cancel', 'team-booking')
        ));
        $list = new Framework\UnorderedList();
        $list->addItem(Framework\Html::h4(__('Name', 'team-booking'))); // 0
        $list->addStringToItem(0, '<input class="large-text" type="text" id="tb-booking-new-service-name" value="" placeholder="e.g. Dental Care">');
        $list->addStringToItem(0, Framework\Html::paragraph(array(
            'text' => __('This service name is already in use, please provide a fresh one.', 'team-booking'),
            'id'   => 'tbk-new-name-already-existant',
            'show' => FALSE
        )));
        $list->addItem(Framework\Html::h4(__('Id', 'team-booking'))); // 1
        $list->addStringToItem(1, '<input class="large-text" type="text" id="tb-booking-new-service-id" value="" placeholder="e.g. dental-care">');
        $list->addStringToItem(1, Framework\Html::paragraph(array(
            'text' => __('This service id is already in use, please provide a fresh one.', 'team-booking'),
            'id'   => 'tbk-new-id-already-existant',
            'show' => FALSE
        )));
        $list->addItem(Framework\Html::h4(__('Class', 'team-booking'))); // 2
        $list->addStringToItem(2, '<fieldset><label>');
        $list->addStringToItem(2, '<input type="radio" name="class" value="event" checked=""/>');
        $list->addStringToItem(2, Framework\Html::span(__('Event', 'team-booking')));
        $list->addStringToItem(2, Framework\Html::paragraph(__('A conference, a music lesson, hotel rooms, and so on. Everything that involves tickets and/or attendees, is an Event Class.', 'team-booking')));
        $list->addStringToItem(2, '</label><label>');
        $list->addStringToItem(2, '<input type="radio" name="class" value="appointment" checked=""/>');
        $list->addStringToItem(2, Framework\Html::span(__('Appointment', 'team-booking')));
        $list->addStringToItem(2, Framework\Html::paragraph(__('Calendar Owners can be techicians, psychologists, medics. This Class is made for that.', 'team-booking')));
        $list->addStringToItem(2, '</label><label>');
        $list->addStringToItem(2, '<input type="radio" name="class" value="unscheduled" checked=""/>');
        $list->addStringToItem(2, Framework\Html::span(__('Unscheduled service', 'team-booking')));
        $list->addStringToItem(2, Framework\Html::paragraph(__('A service with no scheduling needs. Think about support tickets, estimate request, and so on. Instead of the calendar, just a plain request form will be shown.', 'team-booking')));
        $list->addStringToItem(2, '</label></fieldset>');
        $modal->addContent($list);

        return $modal;
    }

}
