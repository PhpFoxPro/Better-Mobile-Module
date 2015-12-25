<?php
/**
 * Created by JetBrains PhpStorm.
 * User: W7
 * Date: 10/3/12
 * Time: 3:06 PM
 * To change this template use File | Settings | File Templates.
 */
class Themesupporter_Service_Menu extends Phpfox_Service
{

    /**
     * Make active state in top menu
     */
    public function makeActiveMenu()
    {
        $sUrl = Phpfox::getLib('url')->getUrl();
        $sUrl = str_replace('/', '.', $sUrl);
        $aMenus = Phpfox::getLib('template')->getVar('aMenus');
        if(empty($aMenus)) {
            $aMenus = Phpfox::getLib('template')->getVar('aMainMenus');
            Phpfox::getLib('template')->assign('aMenus', $aMenus);
        }
        $iActiveLength = 0;
        $sActiveKey = '';
        foreach($aMenus as $aMenu){
            if($aMenu['url'] && strpos($sUrl, $aMenu['url']) !== false){
                $iLength = strlen($aMenu['url']);
                if($iLength > $iActiveLength){
                    $iActiveLength = $iLength;
                    $sActiveKey = $aMenu['url'];
                }
            }
        }

        Phpfox::getLib('template')
            ->assign(array(
            'sClass' => $sActiveKey,
            'aMenus' => $aMenus
        ));
    }
}
