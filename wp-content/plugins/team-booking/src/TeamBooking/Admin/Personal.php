<?php

namespace TeamBooking\Admin;
defined('ABSPATH') or die('No script kiddies please!');

use TeamBooking\Database\Events,
    TeamBooking\Functions,
    TeamBooking\Fetch,
    TeamBooking\Toolkit,
    TeamBooking\Google;

class Personal
{
    /** @var \TeamBookingCoworker $coworker */
    private $coworker;
    private $settings;
    private $access_token;
    private $calendar_service;

    public function __construct()
    {
        /* @var $settings \TeamBookingSettings */
        $this->settings = Functions\getSettings();
        $this->coworker = $this->settings->getCoworkerData(get_current_user_id());
        $this->access_token = $this->coworker->getAccessToken();
        $this->calendar_service = new \TeamBooking\Calendar();
    }

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
            $column->addElement($this->getCalendarList());
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
    private function getCalendarList()
    {
        $panel = new Framework\PanelForList(__('Google Calendars', 'team-booking'));
        if (!empty($this->access_token)) {
            // TODO: handle the $calendar_list error
            $calendar_list = $this->calendar_service->getGoogleCalendarList($this->access_token);
            if ($calendar_list instanceof Google\Google_Exception) {
                if ($calendar_list instanceof Google\Google_IO_Exception) {
                    return $panel;
                }
            }
            $coworker_google_email = $this->calendar_service->getTokenEmailAccount($this->access_token, $this->coworker->getId());
            if ($coworker_google_email instanceof Google\Google_Auth_Exception) {
                $coworker_google_email = $coworker_google_email->getMessage();
                $this->coworker->dropAllCalendarIds();
                Events::removeCoworker($this->coworker->getId());
            }
            if ($this->coworker->getAuthAccount() !== $coworker_google_email) {
                $this->coworker->setAuthAccount($coworker_google_email);
                Functions\getSettings()->updateCoworkerData($this->coworker);
                Functions\getSettings()->save();
            }
            // Revoke button
            $button = new Framework\PanelTitleAddNewButton(__('Revoke', 'team-booking'));
            $button->setId('team_booking_revoke_personal_token');
            $panel->addTitleContent(Framework\ElementFrom::content(Framework\Html::span(array(
                'class'  => isset(json_decode($this->access_token)->refresh_token) ? 'tbk-coworker-gmail-account' : 'tbk-coworker-gmail-account partial',
                'text'   => (isset(json_decode($this->access_token)->refresh_token) ? $coworker_google_email : ($coworker_google_email . ' (' . __('please revoke and authorize again', 'team-booking') . ')')) . ' ' . $button->get(),
                'escape' => FALSE
            ))));
            // Revoke Auth modal
            $modal = new Framework\Modal('tb-personal-token-revoke-modal');
            $modal->setHeaderText(array('main' => __('Are you sure?', 'team-booking')));
            $modal->addContent(Framework\Html::paragraph(__('You are going to revoke your authorization of your Google Account', 'team-booking')));
            if (filter_var($coworker_google_email, FILTER_VALIDATE_EMAIL)) {
                $modal->addContent(Framework\Html::h4($coworker_google_email));
            }
            $modal->addContent(Framework\Html::paragraph(__('All your Google Calendars will be unlinked. You can reauthorize with another Google Account, if not already used by another coworker.', 'team-booking')));
            $modal->setButtonText(array('approve' => __('Yes', 'team-booking'), 'deny' => __('No', 'team-booking')));
            $panel->addTitleContent($modal);

            // Add new calendar button
            $button = new Framework\PanelTitleAddNewButton();
            $button->addClass('tbk-add-google-calendar');
            $panel->addTitleContent($button);

            //Add calendar modal
            $modal = new Framework\Modal('tb-personal-add-calendar-modal');
            $modal->setHeaderText(array('main' => __('Add a Google Calendar', 'team-booking')));
            if (is_array($calendar_list)) {
                $select_options = array(
                    1 => array(
                        'text'      => __('Please select one of your owned calendars', 'team-booking'),
                        'separator' => TRUE,
                        'value'     => '',
                        'selected'  => TRUE
                    )
                );
                foreach ($calendar_list as $calendar_entry) {
                    /* @var $calendar_entry Google\Google_Service_Calendar_CalendarListEntry */
                    foreach ($this->coworker->getCalendars() as $calendar_data) {
                        if ($calendar_entry->getId() === $calendar_data['calendar_id']) {
                            continue 2;
                        }
                    }
                    $select_options[ $calendar_entry->getId() ] = $calendar_entry->getSummary();
                }
                $modal->addContent(Framework\Html::select(array(
                    'id'      => 'tb-personal-add-calendar-id',
                    'options' => $select_options
                )));
                $modal->addContent(Framework\Html::paragraph(array(
                    'text' => __('This Google Calendar ID cannot be found in your authorized Google Account anymore.', 'team-booking'),
                    'id'   => 'tbk-gcal-not-found',
                    'show' => FALSE
                )));
            } else {
                $modal->addContent(Framework\Html::paragraph($calendar_list));
                $modal->closeOnly(TRUE);
            }
            $panel->addElement($modal);

            // Building the list
            $table = new Framework\Table();
            $table->addColumns(array(
                'name'        => __('Name', 'team-booking'),
                'state'       => __('State', 'team-booking'),
                'independent' => __('Independent', 'team-booking'),
                'id'          => __('Id', 'team-booking'),
                'timezone'    => __('Timezone', 'team-booking') . ' (' . __('local:', 'team-booking') . ' ' . get_option('timezone_string') . ')',
                'actions'     => __('Actions', 'team-booking')
            ));
            foreach ($this->coworker->getCalendars() as $calendar_id => $calendar_data) {
                $row = array();
                $found = FALSE;
                foreach ($calendar_list as $calendar_entry) {
                    if ($calendar_entry->getId() === $calendar_id) {
                        $found = TRUE;
                        $label = new Framework\CircularColorLabel(Toolkit\getGcalHexColor($calendar_entry->getColorId()));
                        $row['name'][] = $label;
                        $row['name'][] = ' <strong>' . $calendar_entry->getSummary() . '</strong>';
                        $row['timezone'] = $calendar_entry->getTimeZone();
                    }
                }
                if (!$found) {
                    $row['name'] = Framework\TextLabel::red(__('Not found anymore!', 'team-booking'));
                }
                if ($found) {
                    $row['state'][] = Framework\Html::span(array(
                        'class'  => 'tbk-calendar-state-sync',
                        'text'   => Framework\TextLabel::green(__('synced', 'team-booking'))->get(),
                        'show'   => $this->coworker->getSyncToken($calendar_id),
                        'escape' => FALSE
                    ));
                    $row['state'][] = Framework\Html::span(array(
                        'class'  => 'tbk-calendar-state-not-sync',
                        'text'   => Framework\TextLabel::basic(__('not synced', 'team-booking'))->get(),
                        'show'   => !$this->coworker->getSyncToken($calendar_id),
                        'escape' => FALSE
                    ));
                }
                $slider = new Framework\OnOffSlider('tbk-dependancy-' . Toolkit\randomNumber(8));
                $slider->setChecked(!isset($calendar_data['independent']) || $calendar_data['independent']);
                $slider->addCheckedCallback("tbToggleCalendarIndependency('1', '" . $calendar_id . "');");
                $slider->addUncheckedCallback("tbToggleCalendarIndependency('0', '" . $calendar_id . "');");
                $row['independent'] = $slider;
                $row['id'] = $calendar_id;
                // delete button
                $button = new Framework\ActionButton('dashicons-trash');
                $button->setTitle(__('Remove', 'team-booking'));
                $button->addClass('tb-remove-calendar-id');
                $button->addData('key', $calendar_id);
                $row['actions'][] = $button;
                if ($found) {
                    // sync button
                    $button = new Framework\ActionButton('dashicons-randomize');
                    $button->setTitle(__('Sync', 'team-booking'));
                    $button->addClass('tb-sync-calendar-id');
                    $button->addData('key', $calendar_id);
                    $row['actions'][] = $button;
                    // clean button
                    $button = new Framework\ActionButton('dashicons-editor-removeformatting');
                    $button->setTitle(__('Delete past events', 'team-booking'));
                    $button->addClass('tb-clean-calendar-id');
                    $button->addData('key', $calendar_id);
                    $row['actions'][] = $button;
                }
                $table->addRow($row);
            }
            $panel->addElement($table);

        } else {
            $client_id = $this->settings->getApplicationClientId();
            $client_secret = $this->settings->getApplicationClientSecret();
            if (!empty($client_id) && !empty($client_secret)) {
                $calendar = new \TeamBooking\Calendar();
                $authorization_url = $calendar->createAuthUrl();
                $button = new Framework\PanelTitleAddNewButton(__('Authorize', 'team-booking'));
                $button->setHref($authorization_url);
                $panel->addTitleContent(Framework\ElementFrom::content(Framework\Html::span(array(
                    'class'  => 'tbk-coworker-gmail-account',
                    'text'   => $button->get(),
                    'escape' => FALSE
                ))));
            }
        }

        // Remove calendar modal
        $modal = new Framework\Modal('tb-personal-remove-calendar-modal');
        $modal->setHeaderText(array('main' => __('Are you sure?', 'team-booking')));
        $modal->setButtonText(array('approve' => __('Yes', 'team-booking'), 'deny' => __('No', 'team-booking')));
        $modal->addContent(Framework\Html::paragraph(__('By removing this Google Calendar, eventual availabilities on it will be ignored.', 'team-booking')));
        $panel->addElement($modal);

        // Clean calendar modal
        $modal = new Framework\Modal('tb-personal-clean-calendar-modal');
        $modal->setHeaderText(array('main' => __('Are you sure?', 'team-booking')));
        $modal->setButtonText(array('approve' => __('Yes', 'team-booking'), 'deny' => __('No', 'team-booking')));
        $modal->addContent(Framework\Html::paragraph(__('You are going to delete all the past events on this calendar (going backwards from 7 days ago)', 'team-booking')));
        $panel->addElement($modal);

        // Footer links
        $gcal_link = Framework\Html::anchor(array('text' => 'Google Calendar', 'href' => 'https://www.google.com/calendar/render', 'target' => '_blank'));
        $permissions_link = Framework\Html::anchor(array('text' => __('Your authorized apps', 'team-booking'), 'href' => 'https://security.google.com/settings/security/permissions?pli=1', 'target' => '_blank'));
        $panel->addElement(Framework\ElementFrom::content(Framework\Html::paragraph(array('text' => $gcal_link . ' | ' . $permissions_link, 'escape' => FALSE))));

        return $panel;
    }
}
