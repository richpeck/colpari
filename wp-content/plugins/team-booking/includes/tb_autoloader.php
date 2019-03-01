<?php

// Blocks direct access to this file
defined('ABSPATH') or die("No script kiddies please!");

spl_autoload_register('teambooking_autoloader');

function teambooking_autoloader($class_name)
{
    if (FALSE !== strpos($class_name, 'TeamBooking')) {

        if (FALSE !== strpos($class_name, 'TeamBooking\\Google')) {
            $class_name = str_replace('TeamBooking\\Google', '', $class_name);
            $classes_dir = realpath(TEAMBOOKING_PATH) . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'google' . DIRECTORY_SEPARATOR . 'src';
            $what_to_replace = array('_', '\\');
            $class_file = str_replace($what_to_replace, DIRECTORY_SEPARATOR, $class_name) . '.php';
            $filePath = $classes_dir . $class_file;
            if (file_exists($filePath)) {
                require_once $filePath;
            }

            return;
        }
        // legacy
        switch ($class_name) {
            case 'TeamBookingSettings':
                $class_name = 'TeamBooking_Settings';
                break;
            case 'TeamBookingCoworker':
                $class_name = 'TeamBooking_Coworker';
                break;
            case 'TeamBookingType':
                $class_name = 'TeamBooking_Legacy_Service';
                break;
            case 'TeamBookingFormTextField':
                $class_name = 'TeamBooking_Legacy_TextField';
                break;
            case 'TeamBookingFormTextarea':
                $class_name = 'TeamBooking_Legacy_TextArea';
                break;
            case 'TeamBookingFormSelect':
                $class_name = 'TeamBooking_Legacy_Select';
                break;
            case 'TeamBookingFormRadio':
                $class_name = 'TeamBooking_Legacy_Radio';
                break;
            case 'TeamBookingFormFileUpload':
                $class_name = 'TeamBooking_Legacy_FileUpload';
                break;
            case 'TeamBookingFormCheckbox':
                $class_name = 'TeamBooking_Legacy_Checkbox';
                break;
            case 'TeamBooking_Components_Form_Option':
                $class_name = 'TeamBooking_Legacy_Option';
                break;
            case 'TeamBookingCustomBTSettings':
                $class_name = 'TeamBooking_CoworkerServiceSettings';
                break;
        }
        if (strpos($class_name, 'RRule')) {
            $class_name = str_replace('TeamBooking', '', $class_name);
            $classes_dir = realpath(TEAMBOOKING_PATH) . DIRECTORY_SEPARATOR . 'libs';
        } else {
            $classes_dir = realpath(TEAMBOOKING_PATH) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
        }
        $what_to_replace = array('_', '\\');
        $class_file = str_replace($what_to_replace, DIRECTORY_SEPARATOR, $class_name) . '.php';
        if (file_exists($classes_dir . $class_file)) {
            require_once $classes_dir . $class_file;
        }
    }
}
