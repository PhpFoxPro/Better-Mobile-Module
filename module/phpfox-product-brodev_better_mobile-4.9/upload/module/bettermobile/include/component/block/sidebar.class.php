<?php

class Bettermobile_Component_Block_Sidebar extends Phpfox_Component
{
    public function process()
    {
        $aUserSidebar = Phpfox::getUserBy();
        $aLocale = Phpfox::getLib('locale')->getLang();
        $this->template()->assign(array(
                'aMobileMenus' => Phpfox::getService('mobile')->getMenu(),
                'bIsMobileIndex' => true,
                'aUserSidebar' => $aUserSidebar,
                'sLocaleName' => $aLocale['title'],
            )
        );
    }
}
