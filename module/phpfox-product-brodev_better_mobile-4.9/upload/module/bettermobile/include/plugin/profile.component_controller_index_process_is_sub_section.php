<?php
/**
 * Created by Phpfox Pro.
 * User: Huy Nguyen
 * Date: 1/21/14
 * Time: 7:25 PM
 */

if (Phpfox::isMobile()) {
    $aProfileMenus = Phpfox::getService('profile')->getProfileMenu($aRow);
    $aFilterMenu = array();
    foreach($aProfileMenus as $aMenu) {
        $aFilterMenu[$aMenu['phrase']] = Phpfox::getLib('url')->makeUrl($aMenu['url']);
    }
    Phpfox::getLib('setting')->setParam('profile_menus', $aFilterMenu);


}
