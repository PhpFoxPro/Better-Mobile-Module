<?php

/**
 * [PHPFOX_HEADER]
 */

defined('PHPFOX') or exit('NO DICE!');

/**
 *
 *
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		Raymond Benc
 * @package 		Phpfox_Service
 * @version 		$Id: admincp.class.php 5382 2013-02-18 09:48:39Z Miguel_Espinoza $
 */

class Accountapi_Service_Admincp extends Phpfox_Service
{
    /**
     * Insert to database
     * @param $aData
     */
    public function pushNotification($aData)
    {
        $aTokenGCMs = $this->database()->select('*')
            ->from(Phpfox::getT('accountapi_gcm_user'))
            ->execute('getRows');

        $aTokenAPNSs = $this->database()->select('*')
            ->from(Phpfox::getT('accountapi_apns_user'))
            ->execute('getRows');

        if (isset($aTokenGCMs) && !empty($aTokenGCMs))
        {
            foreach($aTokenGCMs as $iKey => $aTokenGCM)
            {
                $GCMKey = array($aTokenGCM['gcm_regid']);
                Phpfox::getService('accountapi.gcm')->sendPushNotification($GCMKey, $aData);
            }
        }

        if (isset($aTokenAPNSs) && !empty($aTokenAPNSs))
        {
            foreach($aTokenAPNSs as $iKey => $aTokenAPNSs) {
                $aData['alert'] = substr($aData['alert'], 0, 30);
                Phpfox::getService('accountapi.apns')->pushNotification($aTokenAPNSs['token'], $aData);
            }
        }
    }
}