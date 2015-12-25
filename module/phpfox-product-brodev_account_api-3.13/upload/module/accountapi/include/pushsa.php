<?php

define('PHPFOX', true);
define('PHPFOX_DS', DIRECTORY_SEPARATOR);

define('PHPFOX_DIR', dirname(dirname(dirname(dirname(__FILE__)))) . PHPFOX_DS);

$_SERVER['HTTP_USER_AGENT'] = null;

// Require phpFox Init
require (PHPFOX_DIR . 'include' . PHPFOX_DS . 'init.inc.php');

$sleepTime = Phpfox::getParam('accountapi.time_wait_push_notification');
$checkTimeOut = Phpfox::getParam('accountapi.notification_interval_check');

set_time_limit($checkTimeOut);

$iStartTime = time();

//check module is exist
if (Phpfox::isModule('accountapi'))
{
    while(true) {

        echo (time() - $iStartTime . "\n");
        //call function push notification
        Phpfox::getService('accountapi')->pushNotificationFromFile();

        usleep($sleepTime);

        if (time() - $iStartTime > $checkTimeOut) {
            break;
        }

    }

}
