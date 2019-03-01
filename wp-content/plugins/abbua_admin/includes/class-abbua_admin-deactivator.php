<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://www.castorstudio.com
 * @since      1.0.0
 *
 * @package    Abbua_admin
 * @subpackage Abbua_admin/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Abbua_admin
 * @subpackage Abbua_admin/includes
 * @author     Castorstudio <support@castorstudio.com>
 */
class Abbua_admin_Deactivator {

	/**
	 * Remove all plugin generated settings
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		if (cs_get_settings('resetsettings_status')){
			delete_option('cs_abbuaadmin_menuorder');
			delete_option('cs_abbuaadmin_submenuorder');
			delete_option('cs_abbuaadmin_menurename');
			delete_option('cs_abbuaadmin_submenurename');
			delete_option('cs_abbuaadmin_menudisable');
			delete_option('cs_abbuaadmin_submenudisable');
			delete_option('cs_abbuaadmin_settings');
		}
	}

}
