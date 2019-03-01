<?php

use TeamBooking\Functions,
    TeamBooking\Toolkit;

function tbRenderPreviewCSS()
{
    $border = Functions\getSettings()->getBorder();
    $pattern = Functions\getSettings()->getPattern();
    ?>
    <style>
        /*Frontend Calendar CSS */
        .ui.grid .tbk-row-preview.tb-days:last-child {
            padding-bottom: 2px;
        }

        .ui.grid .tbk-row-preview.tb-days {
            margin: 0;
        }

        .ui.grid .tbk-row-preview.tb-days > .tbk-column-preview {
            padding: 2px;
        }

        .tb-frontend-calendar {
            font-family: 'Oswald';
            overflow: hidden;
            margin: 0 !important;
        }

        #tb-frontend-preview .tb-frontend-calendar > .tbk-row-preview:first-child > .tbk-column-preview {
            font-size: 30px;
            text-align: center;
            vertical-align: middle;
        }

        #tb-frontend-preview .tb-frontend-calendar > .tbk-row-preview:first-child {
            margin-left: 0;
            margin-bottom: 0;
            padding: 0;
        }

        .tb-frontend-calendar > .tbk-row-preview {
            line-height: normal;
        }

        .tb-change-month {
            cursor: pointer;
        }

        .tb-change-month span.dashicons {
            font-size: 35px;
            padding: 5px;
            margin: 0;
            float: none;
            line-height: inherit;
        }

        .tb-change-month span.dashicons:before {
            margin: -8px;
        }

        .tb-calendar-line > .tbk-column-preview {
            padding-left: 0 !important;
            padding-right: 0px !important;
        }

        .tb-calendar-line > .tbk-column-preview > .tb-weekline-day {
            font-size: 12px;
            padding: 5px 0;
            text-align: center;
        }

        .tb-frontend-calendar .ui.tb-day {
            font-size: 16px;
            padding: 5px;
            cursor: pointer;
            text-align: center;
        }

        .tb-pointing-label-dots {
            font-family: 'Open Sans';
            min-width: 8px !important;
            min-height: 0 !important;
            height: 8px;
            font-size: 8px !important;
        }

        .ui.grid .tbk-row-preview.tb-days,
        .ui.grid .tbk-row-preview.tb-calendar-line {
            padding: 0 2px;
            float: none;
        }

        .calendar_main_container div {
            border: none;
        }

        /* RESET */

        .ui.form .field > label {
            line-height: inherit;
        }

        .tbk-cal-column {
            box-sizing: border-box;
        }

        .tb-frontend-calendar {
            border: <?= $border['size'] ?>px solid <?= $border['color'] ?>;
            border-radius: <?= $border['radius'] ?>px;
            background: <?= Functions\getSettings()->getColorBackground() ?> url(<?= Toolkit\getPattern($pattern['calendar'], Functions\getSettings()->getColorBackground())?>)
        }

        .tb-frontend-calendar > .tbk-cal-row {
            color: <?= Functions\getRightTextColor(Functions\getSettings()->getColorBackground()) ?>;
        }

        .tb-frontend-calendar .ui.tb-day.pastday {
            color: <?= Functions\getRightHoverColor(Functions\getSettings()->getColorBackground()) ?>;
        }

        .tb-frontend-calendar .ui.tb-day.today {
            background: <?= Functions\getRightHoverColor(Functions\getSettings()->getColorBackground()) ?>;
        }

        .tb-change-month:hover {
            background-color: <?= Functions\getRightHoverColor(Functions\getSettings()->getColorBackground()) ?>;
        }

        .tbk-cal-column .tb-day:not(.pastday):hover {
            background-color: <?= Functions\getRightHoverColor(Functions\getSettings()->getColorBackground()) ?>;
        }

        .tb-frontend-calendar .ui.tb-day.slots {
            background-color: <?= Functions\getSettings()->getColorFreeSlot() ?>;
            color: <?= Functions\getRightTextColor(Functions\getSettings()->getColorFreeSlot()) ?>;
        }

        .tb-frontend-calendar .ui.tb-day.slots.soldout {
            background-color: <?= Functions\getSettings()->getColorSoldoutSlot() ?>;
            color: <?= Functions\getRightTextColor(Functions\getSettings()->getColorSoldoutSlot()) ?>;
        }

        .tb-frontend-calendar .ui.tb-day.slots:hover {
            background: <?= Functions\getSettings()->getColorFreeSlot() ?>;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.15) 0%, rgba(0, 0, 0, 0.15) 100%),
            linear-gradient(to bottom,  <?= Functions\getSettings()->getColorFreeSlot() ?> 0%,<?= Functions\getSettings()->getColorFreeSlot() ?> 100%); /* W3C */
        }

        .tb-frontend-calendar .ui.tb-day.slots.soldout:hover {
            background: <?= Functions\getSettings()->getColorSoldoutSlot() ?>;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.15) 0%, rgba(0, 0, 0, 0.15) 100%),
            linear-gradient(to bottom,  <?= Functions\getSettings()->getColorSoldoutSlot() ?> 0%,<?= Functions\getSettings()->getColorSoldoutSlot() ?> 100%); /* W3C */
        }

        #week-line {
            background: <?= Functions\getSettings()->getColorWeekLine() ?> url(<?= Toolkit\getPattern($pattern['weekline'], Functions\getSettings()->getColorWeekLine())?>);
            color: <?= Functions\getRightTextColor(Functions\getSettings()->getColorWeekLine()) ?>;
        }
    </style>
    <?php
}

//------------------------------------------------------------

function tbRenderPreview()
{
    ob_start();
    tbRenderPreviewCSS();
    ?>
    <div class="ui form grid  tb-frontend-calendar">
        <div class="tbk-row-preview tbk-cal-row">
            <div class="three wide tbk-column-preview tbk-cal-column tb-change-month">
                <span class="dashicons dashicons-arrow-left-alt2"></span>
            </div>
            <div class="ten wide tbk-cal-column tbk-column-preview"><?= strtoupper(date_i18n('F, Y')) ?></div>
            <div class="three wide tbk-column-preview tbk-cal-column tb-change-month">
                <span class="dashicons dashicons-arrow-right-alt2"></span>
            </div>
        </div>
        <div id="week-line"
             class="tb-calendar-line equal width tbk-cal-column tbk-column-preview tbk-row-preview tbk-cal-row">
            <?php
            $weekdays = array(
                'Mon',
                'Tue',
                'Wed',
                'Thu',
                'Fri',
                'Sat',
                'Sun',
            );
            ?>
            <?php foreach ($weekdays as $day) { ?>
                <div class="tbk-column-preview tbk-cal-column">
                    <div class="tb-weekline-day">
                        <?= $day ?>
                    </div>
                </div>
            <?php } ?>
        </div>
        <div class="equal width tbk-cal-column tbk-column-preview tbk-cal-row tbk-row-preview tb-days">
            <div class="tbk-column-preview tbk-cal-column"></div>
            <div class="tbk-column-preview tbk-cal-column"></div>
            <div class="tbk-column-preview tbk-cal-column"></div>
            <div class="tbk-column-preview tbk-cal-column"></div>
            <div class="tbk-column-preview tbk-cal-column"></div>
            <div class="tbk-column-preview tbk-cal-column">
                <div class="ui tb-day  pastday">
                    <div>
                        1
                    </div>
                </div>
            </div>
            <div class="tbk-column-preview tbk-cal-column">
                <div class="ui tb-day  pastday">
                    <div>
                        2
                    </div>
                </div>
            </div>
        </div>
        <div class="equal width tbk-cal-column tbk-column-preview tbk-cal-row tbk-row-preview tb-days">
            <?php for ($i = 3; $i <= 7; $i++) { ?>
                <div class="tbk-column-preview tbk-cal-column">
                    <div class="ui tb-day pastday">
                        <div>
                            <?= $i ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <div class="tbk-column-preview tbk-cal-column">
                <div class="ui tb-day today">
                    <div>
                        8
                    </div>
                </div>
            </div>
            <div class="tbk-column-preview tbk-cal-column">
                <div class="ui tb-day ">
                    <div>
                        9
                    </div>
                </div>
            </div>
        </div>
        <div class="equal width tbk-cal-column tbk-column-preview tbk-cal-row tbk-row-preview tb-days">
            <?php for ($i = 10; $i <= 12; $i++) { ?>
                <div class="tbk-column-preview tbk-cal-column">
                    <div class="ui tb-day ">
                        <div>
                            <?= $i ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <div class="tbk-column-preview tbk-cal-column">
                <div id="free-slot" class="ui tb-day slots">
                    <div>
                        13
                    </div>
                    <div class="ui small pointing above label tablet computer only tbk-column-preview tbk-cal-column"
                         style="margin: 4px 0 0 0;padding: 4px 0 5px 0;text-align: center;width: 100%;">
                        <span class="ui mini circular blue label tb-pointing-label-dots">4</span>
                        <span class="ui mini circular orange label tb-pointing-label-dots">11</span>
                    </div>
                </div>
            </div>
            <?php for ($i = 14; $i <= 16; $i++) { ?>
                <div class="tbk-column-preview tbk-cal-column">
                    <div class="ui tb-day">
                        <div>
                            <?= $i ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        <div class="equal width tbk-cal-column tbk-column-preview tbk-cal-row tbk-row-preview tb-days">
            <?php for ($i = 17; $i <= 23; $i++) { ?>
                <div class="tbk-column-preview tbk-cal-column">
                    <div <?= ($i == 18) ? 'id="soldout-slot"' : '' ?>
                        class="ui tb-day <?= ($i == 18) ? 'slots soldout' : '' ?>">
                        <div>
                            <?= $i ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        <div class="equal width tbk-cal-column tbk-column-preview tbk-cal-row tbk-row-preview tb-days">
            <?php for ($i = 24; $i <= 30; $i++) { ?>
                <div class="tbk-column-preview tbk-cal-column">
                    <div class="ui tb-day">
                        <div>
                            <?= $i ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
