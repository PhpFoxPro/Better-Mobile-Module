<?php
if(Phpfox::isMobile()){
    $sControllerName = Phpfox::getLib('module')->getFullControllerName();
    if ($sControllerName == 'mobile.index') {
        Phpfox::getLib('session')->set('redirect', preg_replace('[mobile\/]', '', Phpfox::getLib('session')->get('redirect')));
        define('PHPFOX_DONT_SAVE_PAGE', true);
    }
}

