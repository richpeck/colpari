<?php

namespace TeamBooking\Admin;
defined('ABSPATH') or die('No script kiddies please!');
include_once __DIR__ . '/preview.php';

use TeamBooking\Admin,
    TeamBooking\Functions,
    TeamBooking\Toolkit;

/**
 * Class Style
 *
 * @author VonStroheim
 */
class Style
{
    /**
     * @return string
     */
    public function getPostBody()
    {
        ob_start();
        ?>
        <form method="POST" action="<?= Admin::add_params_to_admin_url(admin_url('admin-post.php')) ?>">
            <input type="hidden" name="action" value="tbk_save_style">
            <?php wp_nonce_field('team_booking_options_verify') ?>
            <div class="tbk-wrapper">
                <?php
                $row = new Framework\Row();
                $column = Framework\Column::ofWidth(3);
                $column->addElement($this->getColours());
                $column->appendTo($row);
                $column = Framework\Column::ofWidth(3);
                $column->addElement($this->getSlotList());
                $column->addElement($this->get62dot5fix());
                $column->addElement($this->getTemplateVPages());
                $column->appendTo($row);
                $column = Framework\Column::ofWidth(6);
                $column->addElement($this->getCalendarPreview());
                $column->addElement($this->getMapStyles());
                $column->appendTo($row);
                $row->render();
                ?>
            </div>
        </form>
        <?php
        return ob_get_clean();
    }

    /**
     * @return Framework\Panel
     */
    private function getColours()
    {
        $border = Functions\getSettings()->getBorder();
        $pattern = Functions\getSettings()->getPattern();
        $panel = new Framework\Panel(ucfirst(__('Frontend calendar', 'team-booking')));

        // Background color
        $element = new Framework\PanelSettingColorPicker(__('Background color', 'team-booking'));
        $element->addFieldname('tb-background-color');
        $element->setValue(Functions\getSettings()->getColorBackground());
        $element->appendTo($panel);

        // Background pattern
        $element = new Framework\PanelSettingRadios(__('Background pattern overlay', 'team-booking'));
        $element->addFieldname('tb-calendar-pattern');
        $element->addOption(array(
            'label'   => __('None', 'team-booking'),
            'value'   => 0,
            'checked' => $pattern['calendar'] == 0
        ));
        for ($i = 1; $i <= 6; $i++) {
            $element->addOption(array(
                'label'   => Framework\ElementFrom::content('<span class="tbk-pattern-preview" style="background:url(' . Toolkit\getPattern($i, '#FFFFFF') . ');"></span>'),
                'value'   => $i,
                'checked' => $pattern['calendar'] == $i
            ));
        }
        $element->appendTo($panel);

        // Week line color
        $element = new Framework\PanelSettingColorPicker(__('Week line color', 'team-booking'));
        $element->addFieldname('tb-weekline-color');
        $element->setValue(Functions\getSettings()->getColorWeekLine());
        $element->appendTo($panel);

        // Week line pattern
        $element = new Framework\PanelSettingRadios(__('Week line pattern overlay', 'team-booking'));
        $element->addFieldname('tb-weekline-pattern');
        $element->addOption(array(
            'label'   => __('None', 'team-booking'),
            'value'   => 0,
            'checked' => $pattern['weekline'] == 0
        ));
        for ($i = 1; $i <= 6; $i++) {
            $element->addOption(array(
                'label'   => Framework\ElementFrom::content('<span class="tbk-pattern-preview" style="background:url(' . Toolkit\getPattern($i, '#FFFFFF') . ');"></span>'),
                'value'   => $i,
                'checked' => $pattern['weekline'] == $i
            ));
        }
        $element->appendTo($panel);

        // Free slot color
        $element = new Framework\PanelSettingColorPicker(__('Free slot color', 'team-booking'));
        $element->addFieldname('tb-freeslot-color');
        $element->setValue(Functions\getSettings()->getColorFreeSlot());
        $element->appendTo($panel);

        // Free slot color
        $element = new Framework\PanelSettingColorPicker(__('Soldout slot color', 'team-booking'));
        $element->addFieldname('tb-soldoutslot-color');
        $element->setValue(Functions\getSettings()->getColorSoldoutSlot());
        $element->appendTo($panel);

        // Border color
        $element = new Framework\PanelSettingColorPicker(__('Border color', 'team-booking'));
        $element->addFieldname('tb-border-color');
        $element->setValue($border['color']);
        $element->appendTo($panel);

        // Border size
        $element = new Framework\PanelSettingNumber(__('Border size', 'team-booking'));
        $element->addFieldname('tb-border-size');
        $element->setMin(0);
        $element->setMax(20);
        $element->setStep(1);
        $element->setValue($border['size']);
        $element->setFieldDescription('px');
        $element->appendTo($panel);

        // Border radius
        $element = new Framework\PanelSettingNumber(__('Border radius', 'team-booking'));
        $element->addFieldname('tb-border-radius');
        $element->setMin(0);
        $element->setMax(100);
        $element->setStep(1);
        $element->setValue($border['radius']);
        $element->setFieldDescription('px');
        $element->appendTo($panel);

        // Numbered dots meaning
        $element = new Framework\PanelSettingRadios(__('Numbered dots meaning', 'team-booking'));
        $element->addFieldname('tb-numbered-dots-logic');
        $element->addDescription(__('What do you want the dots to show for each service?', 'team-booking'));
        $element->addOption(array(
            'label'   => __('Total number of slots available', 'team-booking'),
            'value'   => 'slots',
            'checked' => Functions\getSettings()->getNumberedDotsLogic() === 'slots'
        ));
        $element->addOption(array(
            'label'   => __('Total number of tickets available', 'team-booking'),
            'value'   => 'tickets',
            'checked' => Functions\getSettings()->getNumberedDotsLogic() === 'tickets'
        ));
        $element->addOption(array(
            'label'   => __('Name of the service', 'team-booking'),
            'value'   => 'service',
            'checked' => Functions\getSettings()->getNumberedDotsLogic() === 'service'
        ));
        $element->addOption(array(
            'label'   => __('Name of the service', 'team-booking') . ' + ' . __('Total number of slots available', 'team-booking'),
            'value'   => 'slots_service',
            'checked' => Functions\getSettings()->getNumberedDotsLogic() === 'slots_service'
        ));
        $element->addOption(array(
            'label'   => __('Name of the service', 'team-booking') . ' + ' . __('Total number of tickets available', 'team-booking'),
            'value'   => 'tickets_service',
            'checked' => Functions\getSettings()->getNumberedDotsLogic() === 'tickets_service'
        ));
        $element->addOption(array(
            'label'   => __('Hide the dots', 'team-booking'),
            'value'   => 'hide',
            'checked' => Functions\getSettings()->getNumberedDotsLogic() === 'hide'
        ));
        $element->appendTo($panel);

        // Numbered dots threshold
        $element = new Framework\PanelSettingNumber(__('Numbered dots threshold', 'team-booking'));
        $element->addFieldname('tb-numbered-dots-lower-bound');
        $element->addDescription(__('Do you want the number inside the dots to disappear under a certain threshold?', 'team-booking'));
        $element->addToDescription(' ' . __('Put here a very big value, if you want to hide the numbers, while keeping the dots.', 'team-booking'));
        $element->setMin(0);
        $element->setStep(1);
        $element->setValue(Functions\getSettings()->getNumberedDotsLowerBound());
        $element->appendTo($panel);

        // Save changes
        $element = new Framework\PanelSaveButton(__('Save changes', 'team-booking'), 'tbk_save_style');
        $element->appendTo($panel);

        return $panel;
    }

    /**
     * @return Framework\Panel
     */
    private function getSlotList()
    {
        $panel = new Framework\Panel(ucfirst(__('Frontend schedule list', 'team-booking')));

        // Sort slots
        $element = new Framework\PanelSettingRadios(__('Sort slots', 'team-booking'));
        $element->addFieldname('tb-group-slots-by');
        $element->addOption(array(
            'label'   => __('By time', 'team-booking'),
            'value'   => 'bytime',
            'checked' => Functions\getSettings()->isGroupSlotsByTime()
        ));
        $element->addOption(array(
            'label'   => __('By coworker', 'team-booking'),
            'value'   => 'bycoworker',
            'checked' => Functions\getSettings()->isGroupSlotsByCoworker()
        ));
        $element->addOption(array(
            'label'   => __('By service', 'team-booking'),
            'value'   => 'byservice',
            'checked' => Functions\getSettings()->isGroupSlotsByService()
        ));
        $element->appendTo($panel);

        // Price label color
        $element = new Framework\PanelSettingRadios(__('Price label color', 'team-booking'));
        $element->addFieldname('tb-price-tag-color');
        $colors = array(
            'red',
            'orange',
            'yellow',
            'olive',
            'green',
            'teal',
            'blue',
            'violet',
            'purple',
            'pink',
            'brown',
            'grey',
            'black'
        );
        foreach ($colors as $color) {
            $label = new Framework\TextLabel(ucfirst($color));
            $label->setColor($color);
            $element->addOption(array(
                'label'       => $label,
                'label_title' => ucfirst($color),
                'value'       => $color,
                'checked'     => Functions\getSettings()->getPriceTagColor() == $color
            ));
        }
        $element->appendTo($panel);

        // Slots style
        $element = new Framework\PanelSettingSelector(__('Slots style', 'team-booking'));
        $element->addDescription(__('Choose a style to display the slots.', 'team-booking'));
        $element->addToDescription(' ' . __('This can be overridden by the relative shortcode attribute.', 'team-booking'));
        $element->addOption(0, __('Basic', 'team-booking'));
        $element->addOption(1, __('Elegant', 'team-booking'));
        $element->addFieldname('tb-slots-style');
        $element->setSelected(Functions\getSettings()->getSlotStyle());
        $element->appendTo($panel);

        // Save changes
        $element = new Framework\PanelSaveButton(__('Save changes', 'team-booking'), 'tbk_save_style');
        $element->appendTo($panel);

        return $panel;
    }

    /**
     * @return Framework\Panel
     */
    private function getCalendarPreview()
    {
        $panel = new Framework\Panel(ucfirst(__('Frontend calendar preview', 'team-booking')));
        $panel->setContentId('tb-frontend-preview');
        $element = new Framework\PanelSettingWildcard(NULL);
        $element->addNotice(__('This is a rough preview. Colors only are updated in real-time. For accurate results, please save the changes.', 'team-booking'));
        $element->addContent(tbRenderPreview());
        $element->appendTo($panel);

        return $panel;
    }

    /**
     * @return Framework\Panel
     */
    private function get62dot5fix()
    {
        $panel = new Framework\Panel(ucfirst(__('CSS fix for small fonts', 'team-booking')));

        $element = new Framework\PanelSettingYesOrNo(__('Active', 'team-booking'));
        $element->addFieldname('tb-css-fix');
        $element->addNotice(__('Remember to empty the cache after activating or deactivating this option, or you may not be able to see any change', 'team-booking'));
        $element->addDescription(__("Some of calendar fonts appear too small? This is often due the so-called 62.5% hack used by some themes. If your theme's stylesheet applies a 62.5% font size to the html element, then you must activate this option.", 'team-booking'));
        $element->setState(Functions\getSettings()->getFix62dot5());
        $element->appendTo($panel);

        // Save changes
        $element = new Framework\PanelSaveButton(__('Save changes', 'team-booking'), 'tbk_save_style');
        $element->appendTo($panel);

        return $panel;
    }

    /**
     * @return Framework\Panel
     */
    private function getTemplateVPages()
    {
        $panel = new Framework\Panel(ucfirst(__('Landing page template of e-mail links', 'team-booking')));

        $element = new Framework\PanelSettingSelector(__('Template', 'team-booking'));
        $element->addFieldname('tb-template-vpages');
        $element->addNotice(__('If you switch the theme, remember to check this setting again!', 'team-booking'));
        $element->addDescription(__('This will be the template used by the plugin to render the cancellation or approval pages shown when the customer or the service provider clicks on the relative e-mail links', 'team-booking'));
        $element->setSelected(Functions\getSettings()->getTemplateVPages());
        $element->addOption('page.php', __('Default Template', 'team-booking'));
        $templates = get_page_templates(NULL, 'page');
        ksort($templates);
        foreach ($templates as $name => $file) {
            $element->addOption($file, $name);
        }
        $element->appendTo($panel);

        // Save changes
        $element = new Framework\PanelSaveButton(__('Save changes', 'team-booking'), 'tbk_save_style');
        $element->appendTo($panel);

        return $panel;
    }

    /**
     * @return Framework\Panel
     */
    private function getMapStyles()
    {
        $panel = new Framework\Panel(ucfirst(__('Map styles', 'team-booking')));
        $panel->addTitleContent(Framework\ElementFrom::content('<span style="font-weight:300;font-style: italic;">- by <a target="_blank" href="https://snazzymaps.com">Snazzy Maps</a></span>'));
        $panel->setContentId('tb-frontend-mapstyles');

        $element = new Framework\PanelSettingRadios(NULL);
        $element->addFieldname('tb-map-style');
        $element->addOption(array(
            'label'       => Framework\ElementFrom::content('<p>Light Gray <span>(ColorByt)</span></p>' . '<div class="tbk-map-style-preview" style="background-image: url(' . TEAMBOOKING_URL . '/images/mapstyle-light-gray.png);"></div>'),
            'label_title' => 'Light Gray',
            'value'       => 0,
            'checked'     => Functions\getSettings()->getMapStyle(TRUE) == 0
        ));
        $element->appendTo($panel);
        $element = new Framework\PanelSettingRadios(NULL);
        $element->addFieldname('tb-map-style');
        $element->addOption(array(
            'label'       => Framework\ElementFrom::content('<p>Unsaturated Browns <span>(Simon Goellner)</span></p>' . '<div class="tbk-map-style-preview" style="background-image: url(' . TEAMBOOKING_URL . '/images/mapstyle-unsaturated-browns.png);"></div>'),
            'label_title' => 'Unsaturated Browns',
            'value'       => 1,
            'checked'     => Functions\getSettings()->getMapStyle(TRUE) == 1
        ));
        $element->appendTo($panel);
        $element = new Framework\PanelSettingRadios(NULL);
        $element->addFieldname('tb-map-style');
        $element->addOption(array(
            'label'       => Framework\ElementFrom::content('<p>Pale Dawn <span>(Adam Krogh)</span></p>' . '<div class="tbk-map-style-preview" style="background-image: url(' . TEAMBOOKING_URL . '/images/mapstyle-pale-dawn.png);"></div>'),
            'label_title' => 'Pale Dawn',
            'value'       => 2,
            'checked'     => Functions\getSettings()->getMapStyle(TRUE) == 2
        ));
        $element->appendTo($panel);
        $element = new Framework\PanelSettingRadios(NULL);
        $element->addFieldname('tb-map-style');
        $element->addOption(array(
            'label'       => Framework\ElementFrom::content('<p>Orange <span>(bjorn)</span></p>' . '<div class="tbk-map-style-preview" style="background-image: url(' . TEAMBOOKING_URL . '/images/mapstyle-orange.png);"></div>'),
            'label_title' => 'Orange',
            'value'       => 3,
            'checked'     => Functions\getSettings()->getMapStyle(TRUE) == 3
        ));
        $element->appendTo($panel);
        $element = new Framework\PanelSettingRadios(NULL);
        $element->addFieldname('tb-map-style');
        $element->addOption(array(
            'label'       => Framework\ElementFrom::content('<p>Coy Beauty <span>(Danika Pariseau)</span></p>' . '<div class="tbk-map-style-preview" style="background-image: url(' . TEAMBOOKING_URL . '/images/mapstyle-coy-beauty.png);"></div>'),
            'label_title' => 'Coy Beauty',
            'value'       => 4,
            'checked'     => Functions\getSettings()->getMapStyle(TRUE) == 4
        ));
        $element->appendTo($panel);
        $element = new Framework\PanelSettingRadios(NULL);
        $element->addFieldname('tb-map-style');
        $element->addOption(array(
            'label'       => Framework\ElementFrom::content('<p>MapBox <span>(Sam Herbert)</span></p>' . '<div class="tbk-map-style-preview" style="background-image: url(' . TEAMBOOKING_URL . '/images/mapstyle-mapbox.png);"></div>'),
            'label_title' => 'MapBox',
            'value'       => 5,
            'checked'     => Functions\getSettings()->getMapStyle(TRUE) == 5
        ));
        $element->appendTo($panel);
        $element = new Framework\PanelSettingRadios(NULL);
        $element->addFieldname('tb-map-style');
        $element->addOption(array(
            'label'       => Framework\ElementFrom::content('<p>Shades of Grey <span>(Adam Krogh)</span></p>' . '<div class="tbk-map-style-preview" style="background-image: url(' . TEAMBOOKING_URL . '/images/mapstyle-shades-of-grey.png);"></div>'),
            'label_title' => 'Shades of Grey',
            'value'       => 6,
            'checked'     => Functions\getSettings()->getMapStyle(TRUE) == 6
        ));
        $element->appendTo($panel);
        $element = new Framework\PanelSettingRadios(NULL);
        $element->addFieldname('tb-map-style');
        $element->addOption(array(
            'label'       => Framework\ElementFrom::content('<p>Subtle Grayscale <span>(Paulo Avila)</span></p>' . '<div class="tbk-map-style-preview" style="background-image: url(' . TEAMBOOKING_URL . '/images/mapstyle-subtle-grayscale.png);"></div>'),
            'label_title' => 'Subtle Grayscale',
            'value'       => 7,
            'checked'     => Functions\getSettings()->getMapStyle(TRUE) == 7
        ));
        $element->appendTo($panel);
        $element = new Framework\PanelSettingRadios(NULL);
        $element->addFieldname('tb-map-style');
        $element->addOption(array(
            'label'       => Framework\ElementFrom::content('<p>Bluish <span>(Stefan)</span></p>' . '<div class="tbk-map-style-preview" style="background-image: url(' . TEAMBOOKING_URL . '/images/mapstyle-bluish.png);"></div>'),
            'label_title' => 'Bluish',
            'value'       => 8,
            'checked'     => Functions\getSettings()->getMapStyle(TRUE) == 8
        ));
        $element->appendTo($panel);
        $element = new Framework\PanelSettingRadios(NULL);
        $element->addFieldname('tb-map-style');
        $element->addOption(array(
            'label'       => Framework\ElementFrom::content('<p>even lighter <span>(anda)</span></p>' . '<div class="tbk-map-style-preview" style="background-image: url(' . TEAMBOOKING_URL . '/images/mapstyle-even-lighter.png);"></div>'),
            'label_title' => 'even lighter',
            'value'       => 9,
            'checked'     => Functions\getSettings()->getMapStyle(TRUE) == 9
        ));
        $element->appendTo($panel);
        $element = new Framework\PanelSettingRadios(NULL);
        $element->addFieldname('tb-map-style');
        $element->addOption(array(
            'label'       => Framework\ElementFrom::content('<p>Two Tone <span>(Arvin)</span></p>' . '<div class="tbk-map-style-preview" style="background-image: url(' . TEAMBOOKING_URL . '/images/mapstyle-two-tone.png);"></div>'),
            'label_title' => 'Two Tone',
            'value'       => 10,
            'checked'     => Functions\getSettings()->getMapStyle(TRUE) == 10
        ));
        $element->appendTo($panel);
        $element = new Framework\PanelSettingRadios(NULL);
        $element->addFieldname('tb-map-style');
        $element->addOption(array(
            'label'       => Framework\ElementFrom::content('<p>buhler <span>(Colin Zwicker)</span></p>' . '<div class="tbk-map-style-preview" style="background-image: url(' . TEAMBOOKING_URL . '/images/mapstyle-buhler.png);"></div>'),
            'label_title' => 'buhler',
            'value'       => 11,
            'checked'     => Functions\getSettings()->getMapStyle(TRUE) == 11
        ));
        $element->appendTo($panel);

        $element = new Framework\PanelSettingYesOrNo(__('Use the default Google Maps style', 'team-booking'));
        $element->addFieldname('tb-map-use-default');
        $element->setState(Functions\getSettings()->getMapStyleUseDefault());
        $element->addNotice(__('Do you want more styles? Do you want to create your own style? Just set this to YES, and install the free WordPress plugin of Snazzy Maps! TeamBooking is compatible with it!', 'team-booking'));
        $element->appendTo($panel);

        $element = new Framework\PanelSettingNumber(__('Map zoom level', 'team-booking'));
        $element->addFieldname('tb-map-zoom');
        $element->setMin(0);
        $element->setMax(19);
        $element->setValue(Functions\getSettings()->getGmapsZoomLevel());
        $element->appendTo($panel);

        // Save changes
        $element = new Framework\PanelSaveButton(__('Save changes', 'team-booking'), 'tbk_save_style');
        $element->appendTo($panel);

        return $panel;
    }

}
