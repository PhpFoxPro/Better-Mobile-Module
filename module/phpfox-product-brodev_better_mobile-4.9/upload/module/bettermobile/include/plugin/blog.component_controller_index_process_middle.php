<?php
/**
 * Created by Phpfox Pro.
 * User: Huy Nguyen
 * Date: 1/24/14
 * Time: 4:11 PM
 */
if (isset($aParentModule['module_id']) && $aParentModule['module_id'] == "pages" && Phpfox::isMobile()) {
    $aPageMenus = Phpfox::getService('pages')->getMenu($aPage);

    $aFilterMenu = array();
    foreach ($aPageMenus as $aPageMenu)
    {
        $aFilterMenu[$aPageMenu['phrase']] = $aPageMenu['url'];
    }

    $this->template()->buildSectionMenu('pages', $aFilterMenu);
}