<?php
/**
 * Undocumented function
 *
 * @return void
 */
function cs_menumng_settings_page(){
    global $menu;
    global $submenu;
    global $cs_abbua_menu;
    global $cs_abbua_submenu;

    if (!is_array($cs_abbua_menu) || sizeof($cs_abbua_menu) == 0) {
        $cs_abbua_menu      = $menu;

        $rename_menu        = cs_rename_menu();
        $cs_abbua_menu      = $rename_menu;

        $neworder           = cs_adminmenu_neworder();

        $cs_abbua_menu      = cs_adminmenu_newmenu($neworder,$cs_abbua_menu);

        $GLOBALS['cs_abbua_menu'] = $cs_abbua_menu;

        // echo '<h3>No existia, la creó</h3>';
    }

    if (!is_array($cs_abbua_submenu) || sizeof($cs_abbua_submenu) == 0) {
        $cs_abbua_submenu   = $submenu;
        
        $rename_submenu     = cs_rename_submenu();
        $cs_abbua_submenu   = $rename_submenu;

        $newsuborder        = cs_adminmenu_neworder();

        $cs_abbua_submenu   = cs_adminmenu_newsubmenu($newsuborder,$cs_abbua_submenu,$cs_abbua_menu);

        $GLOBALS['cs_abbua_submenu'] = $cs_abbua_submenu;
    }

    if (!cs_get_settings('sidebar_status')){
        $notice = __('The custom admin menu is not activated. To see the changes you must activate it from the "ABBUA Admin Settings" panel.','abbua_admin');
        echo "<div class='notice notice-warning'>{$notice}</div>";
    }

    // i18n support
    $text_menumanager_title     = __('ABBUA Admin Menu Manager','abbua_admin');
    $text_menu_original         = __('Original','abbua_admin');
    $text_menu_rename           = __('Rename to','abbua_admin');
    $text_menu_icon             = __('Menu Icon','abbua_admin');
    $text_instructions          = __('Instructions','abbua_admin');
    $text_instructions_list     = sprintf(__('<li>Drag and Drop %s to rearrange menu and sub menu items.</li><li>Click on %s icon to show or hide the menu or submenu item.</li><li>Click on %s icon to edit menu name and icon, and submenu name.</li>','abbua_admin'),'<span><i class="fei fei-move"></i></span>','<span><i class="fei fei-eye"></i></span>','<span><i class="feo fei-chevron-down"></i></span>');
    $text_btn_reset             = __('Reset to Original','abbua_admin');
    $text_btn_save              = __('Save Menu','abbua_admin');


    echo "
    <div class='wrap'>
        <h1>{$text_menumanager_title}</h1>
        <div id='cs-abbua-admin-menu-management'>
            <div id='cs-menu-manager'>
        ";
    $menudisable    = cs_get_option("cs_abbuaadmin_menudisable", "");
    $menudisablearr = array_unique(array_filter(explode("|", $menudisable)));

    $submenudisable = cs_get_option("cs_abbuaadmin_submenudisable", "");

    $submenudisablearr = array_unique(array_filter(explode("|", $submenudisable)));

    $menu_icons_panel = cs_menuicons_list();

    foreach ($cs_abbua_menu as $menuid => $menuarr) {

        /* ---------------- 
          menu tab
          ---------------- */
        if (strpos($menuarr[4], "wp-menu-separator") !== false) {

        } else {
            // menu item
            $tabid = $menuid;
            if (isset($menuarr['original'])) {
                $tabid = $menuarr['original'];
            }

            $sid = $tabid;
            if (isset($menuarr[5])) {
                $sid = $menuarr[5];
            }

            $menupage = $tabid;
            if (isset($menuarr[2])) {
                $menupage = $menuarr[2];
            }

            $originalname = $menuarr[0];
            if (isset($menuarr['originalname'])) {
                $originalname = $menuarr['originalname'];
            }

            $originalicon = "";
            if (isset($menuarr[6])) {
                $originalicon = $menuarr[6];
                if (isset($menuarr['originalicon'])) {
                    $originalicon = $menuarr['originalicon'];
                }
            }

            if (isset($menuarr[5]) && in_array($menuarr[5], $menudisablearr)) {
                $menu_state         = 'disabled';
                $menu_class         = 'cs-menu-disabled';
                $menu_state_icon    = 'fei-eye-off';
            } else {
                $menu_state         = 'enabled';
                $menu_class         = 'cs-menu-enabled';
                $menu_state_icon    = 'fei-eye';
            }

            echo "
                <div class='cs-menu-manager_menu-wrapper cs-admin-menu-item {$menu_state}' data-id='{$tabid}' data-menu-id='{$sid}'>
                    <div class='cs-menu-manager_item'>
                        <div class='cs-menu-manager_item-heading'>
                            <div class='cs-menu-manager_item-heading-title'>
                                <span class='cs-menu-title-icon dashicons-before " . $menuarr[6] . "'></span>
                                <span class='cs-menu-title'>" . $menuarr[0] . "</span>
                            </div>
                            <div class='cs-menu-manager_item-heading-toolbar'>
                                <div class='cs-menu-manager_item-heading-toolbar_action cs-mm-action-move'><i class='fei fei-move'></i></div>
                                <div class='cs-menu-manager_item-heading-toolbar_action cs-mm-action-display {$menu_class}'><i class='fei {$menu_state_icon}'></i></div>
                                <div class='cs-menu-manager_item-heading-toolbar_action cs-mm-action-toggle'><i class='fei fei-chevron-down'></i></div>
                            </div>
						</div>

						<div class='cs-menu-manager_item-edit-panel'>
							<div>
								<span class='ufield'>{$text_menu_original}:</span>
								<span class='uvalue'>{$originalname}</span>
								<div class='clearboth'></div>
								<span class='ufield'>{$text_menu_rename}:</span>
								<span class='uvalue'><input type='text' data-id='{$tabid}' data-menu-id='{$sid}' class='cs-admin-menu-rename' value='" . cs_reformatstring($menuarr[0]) . "'></span>
								<div class='clearboth'></div>
								<span class='ufield'>{$text_menu_icon}</span>
								<span class='uvalue'>
									<input type='hidden' data-id='{$tabid}' data-menu-id='{$sid}' class='cs-menu-icon' value='" . $menuarr[6] . "'>
									<span data-class='" . $menuarr[6] . "' class='cs-menu-icon-panel_toggle dashicons-before " . $menuarr[6] . "'></span>
                                    {$menu_icons_panel}
								</span>
								<div class='clearboth'></div>
							</div>
                        </div>
                    </div>
                ";

            /* --------------------
              submenu tabs
              ---------------------- */
            echo "<div class='cs-menu-manager-submenu-wrapper'>";
            if (isset($cs_abbua_submenu[$menuarr[2]])) {

                $parentpage = "";
                if (isset($menuarr[2])) {
                    $parentpage = $menuarr[2];
                }

                foreach ($cs_abbua_submenu[$menuarr[2]] as $submenuid => $submenuarr) {
                    $subtabid = $submenuid;
                    if (isset($submenuarr['original'])) {
                        $subtabid = $submenuarr['original'];
                    }

                    $originalsubname = $submenuarr[0];
                    if (isset($submenuarr['originalsubname'])) {
                        $originalsubname = $submenuarr['originalsubname'];
                    }

                    if (in_array($menupage . ":" . $subtabid, $submenudisablearr)) {
                        $submenu_state      = 'disabled';
                        $submenu_class      = 'cs-menu-disabled';
                        $submenu_state_icon = 'fei-eye-off';
                    } else {
                        $submenu_state      = 'enabled';
                        $submenu_class      = 'cs-menu-enabled';
                        $submenu_state_icon = 'fei-eye';
                    }

                    echo "
                        <div class='cs-menu-manager_menu-wrapper cs-admin-submenu-item {$submenu_state}' data-id='{$subtabid}' data-parent-id='{$tabid}' data-parent-page='{$parentpage}'>
                            <div class='cs-menu-manager_item'>	
                                <div class='cs-menu-manager_item-heading'>
                                    <div class='cs-menu-manager_item-heading-title'>
                                        <span class='cs-menu-title'>" . $submenuarr[0] . "</span>
                                    </div>
                                    <div class='cs-menu-manager_item-heading-toolbar'>
                                        <div class='cs-menu-manager_item-heading-toolbar_action cs-mm-action-move'><i class='fei fei-move'></i></div>
                                        <div class='cs-menu-manager_item-heading-toolbar_action cs-mm-action-display {$submenu_class}'><i class='fei {$submenu_state_icon}'></i></div>
                                        <div class='cs-menu-manager_item-heading-toolbar_action cs-mm-action-toggle'><i class='fei fei-chevron-down'></i></div>
                                    </div>
								</div>

								<div class='cs-menu-manager_item-edit-panel closed'>
									<div>
										<span class='ufield'>{$text_menu_original}:</span>
										<span class='uvalue'>{$originalsubname}</span>
										<div class='clearboth'></div>
										<span class='ufield'>{$text_menu_rename}:</span>
										<span class='uvalue'><input type='text' data-parent-page='{$parentpage}'  data-id='{$subtabid}' data-parent-id='{$tabid}' class='cs-admin-submenu-rename' value='" . cs_reformatstring($submenuarr[0]) . "'></span>
										<div class='clearboth'></div>
									</div>
                                </div>
                            </div>    
						</div>";
                }

                //print_r($submenu[$menuarr[2]]);
            }
            echo "</div>"; // submenu end
            echo "</div>"; // menu end
        }
    }

    echo '</div>';

    echo "
            <div id='cs-menu-manager_actions-wrapper'>
                <div class='postbox'>
                    <h2 class='hndle ui-sortable-handle'><span>{$text_instructions}</span></h2>
                    <div class='inside'>
                        <ul>
                            {$text_instructions_list}
                        </ul>
                        <div id='major-publishing-actions'>
                            <div id='delete-action'>
                                <a id='cs-admin-menu_reset' class='cs-mm-btn submitdelete deletion' href='#'>{$text_btn_reset}</a>
                            </div>
                            <div id='publishing-action'>
                                <div class='cs-abbua-spinner spinner'></div>
                                <input id='cs-admin-menu_save' name='cs-admin-menu_save' class='cs-mm-btn button-primary' value='{$text_btn_save}' type='button'>
                            </div>
                            <div class='clear'></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    "; // .wrap
}












































function cs_adminmenu_neworder() {
    $new = array();
    $subnew = array();
    $ret = "";

    $neworder = cs_get_option("cs_abbuaadmin_menuorder", "");
    $newsuborder = cs_get_option("cs_abbuaadmin_submenuorder", "");

    $exp = explode("|", $neworder);
    $subexp = explode("|", $newsuborder);

    // set menu in new array
    foreach ($exp as $id) {
        if (trim($id) != "") {
            $new[] = $id;
        }
    }

    // set submenu in new array with menu id
    foreach ($subexp as $id) {
        if (trim($id) != "") {
            $subid = explode(":", $id);
            $subnew[$subid[0]][] = $subid[1];
        }
    }

    $ret['menu'] = $new;
    $ret['submenu'] = $subnew;

    return $ret;
}

function cs_adminmenu_newmenu($neworder, $menu) {
    $relation = array();

    foreach ($menu as $id => $valarr) {
        if (isset($valarr[5])) {
            $relation[$valarr[5]] = $id;
        }
    }

    $ret = array();
    $allids = $menu;

    $k = 100000;
    foreach ($neworder['menu'] as $newmenuid) {
        if (isset($relation[$newmenuid])) {
            $k++;
            $ret[$k] = $menu[$relation[$newmenuid]];
            $ret[$k]['original'] = $relation[$newmenuid];
            unset($allids[$relation[$newmenuid]]);
        }
    }

    foreach ($allids as $itemid => $item) {
        $k++;
        $ret[$k] = $item;
        $ret[$k]['original'] = $itemid;
    }

    return $ret;
}

function cs_adminmenu_newsubmenu($newsuborder, $submenu, $menu) {
    $allids = $menu;
    $allsubids = $submenu;


    $ret = array();
    foreach ($newsuborder['submenu'] as $submenuid => $arr) {
        $k = 1100000;
        $k = 0;
        foreach ($arr as $linkid) {
            $submenu[$submenuid][$linkid]['original'] = $linkid;
            $ret[$submenuid][$k] = $submenu[$submenuid][$linkid];
            unset($allsubids[$submenuid][$linkid]);
            $k++;
        }
    }

    foreach ($allsubids as $itemid => $item) {
        $k = 1100000;
        $k = 0;
        foreach ($item as $a => $b) {
            $allsubids[$itemid][$a]['original'] = $a;
            $ret[$itemid][$k] = $allsubids[$itemid][$a];
            $k++;
        }
    }

    return $ret;
}

function cs_rename_menu_getnewID($menuarr, $field, $value){
    foreach ($menuarr as $key => $product){
        if (isset($product[$field]) && $product[$field] === $value){
            return $key;
        }
    }
    return false;
}

function cs_rename_menu() {
    global $menu;

    $menurename = cs_get_option("cs_abbuaadmin_menurename", "");

    if (trim($menurename) != "") {

        $exp = explode("∞∞[#@!@#]∞∞", $menurename);

        foreach ($exp as $str) {

            if (trim($str) != "") {

                $id = $val = $icon = $original = "";

                $arr = explode("∞∞[&%&]∞∞", $str);
                if (isset($arr[0])) {
                    $id = $arr[0];
                }
                if (isset($arr[1])) {
                    $str = $arr[1];
                }
                $expstr = explode("∞∞[!%#%!]∞∞", $str);
                if (isset($expstr[0])) {
                    $val = $expstr[0];
                }
                if (isset($expstr[1])) {
                    $icon = $expstr[1];
                }

                if ($id != "") {
                    $expid = explode(":", $id);
                    $id = $expid[0];
                    $sid = $expid[1];
                }

                // get new id
                $id = cs_rename_menu_getnewID($menu, "5", $sid);

                if (isset($menu[$id][0]) && isset($menu[$id][5]) && $menu[$id][5] == $sid) {
                    $original = $menu[$id][0];
                    $menu[$id][0] = $val;
                    $menu[$id]['originalname'] = $original;
                }

                if (isset($menu[$id][6]) && isset($menu[$id][5]) && $menu[$id][5] == $sid) {
                    $originalicon = $menu[$id][6];
                    $menu[$id][6] = $icon;
                    $menu[$id]['originalicon'] = $originalicon;
                }
            }
        }
    }

    return $menu;
}

function cs_rename_submenu() {

    global $submenu;
    $submenurename = cs_get_option("cs_abbuaadmin_submenurename", "");

    if (trim($submenurename) != "") {

        $exp = explode("∞∞[#@!@#]∞∞", $submenurename);
        foreach ($exp as $str) {

            $idstr = $page = $parentid = $id = $val = $original = "";

            $arr = explode("∞∞[&%&]∞∞", $str);
            if (isset($arr[0])) {
                $idstr = $arr[0];
            }
            $idexp = explode("∞∞[$(@)$]∞∞", $idstr);
            if (isset($idexp[0])) {
                $page = $idexp[0];
            }
            if (isset($idexp[1])) {
                $idexp2 = explode(":", $idexp[1]);
            }
            if (isset($idexp2[0])) {
                $parentid = $idexp2[0];
            }
            if (isset($idexp2[1])) {
                $id = $idexp2[1];
            }
            if (isset($arr[1])) {
                $val = $arr[1];
            }

            //echo $page." - ". $parentid. " - ". $id." - ". $val."<br>";

            if (isset($submenu[$page][$id][0])) {
                $original = $submenu[$page][$id][0];
                $submenu[$page][$id][0] = $val;
                $submenu[$page][$id]['originalsubname'] = $original;
            }
            //echo $id. " : ". $val."<br>";
        }
    }
    //echo "<pre>"; print_r($submenu); echo "</pre>"; 
    return $submenu;
}

function cs_adminmenu_disable($menu) {
    $menudisable = cs_get_option("cs_abbuaadmin_menudisable", "");
    $exp = array_unique(array_filter(explode("|", $menudisable)));

    foreach ($menu as $id => $arr) {
        if (isset($arr[5]) && in_array($arr[5], $exp)) {
            unset($menu[$id]);
        }
    }

    return $menu;
}

function cs_adminsubmenu_disable($submenu) {
    global $menu;

    //enabled menu items 
    $enabledmenu = array();
    foreach ($menu as $key => $value) {
        $enabledmenu[] = $value[2];
    }

    // map array of id and .php page of menu first
    $menumap = array();
    foreach ($menu as $v) {
        //$menumap[$v[2]] = $v[5];//$v['original'];
    }

    $submenudisable = cs_get_option("cs_abbuaadmin_submenudisable", "");

    $exp = array_unique(array_filter(explode("|", $submenudisable)));

    foreach ($submenu as $key => $value) {

        // check if parent menu is enabled. if not then unset it from submenu
        if (!in_array($key, $enabledmenu)) {
            unset($submenu[$key]);
        } else {
            $parentid = "";

            foreach ($value as $k => $v) {
                $subid = "";
                if (isset($v['original'])) {
                    $subid = $v['original'];
                }
                if (in_array($key . ":" . $subid, $exp)) {
                    unset($submenu[$key][$k]);
                }
            }
        }
    }
    return $submenu;
}

function cs_menuicons_list() {
    $ret = "";
    $ret .= "<div class='cs-menu-manager_icons-panel'>";

    $str = cs_dashiconscsv();
    $exp = explode(",", $str);
    foreach ($exp as $key => $value) {
        $valexp = explode(":", $value);
        $class = trim($valexp[0]);
        $code = trim($valexp[1]);

        $ret .= "<span data-class = 'dashicons-{$class}' class='cs-menu-manager_icons-panel-icon dashicons-before dashicons-{$class}'></span>";
    }

    $ret .= "</div>";
    return $ret;
}