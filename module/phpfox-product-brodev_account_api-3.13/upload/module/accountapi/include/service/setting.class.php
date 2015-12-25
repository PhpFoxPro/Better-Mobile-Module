<?php
/**
 * [PHPFOX_HEADER]
 */

defined('PHPFOX') or exit('NO DICE!');

/**
 * Service GCM for android push notification
 *
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		Raymond Benc
 * @package 		Phpfox_Service
 * @version 		$Id: setting.class.php 3223 2011-10-06 12:56:24Z Miguel_Espinoza $
 */

class Accountapi_Service_Setting extends Phpfox_Service
{
    /**
     * Get notification settings
     * @param $iUserId
     * @return mixed
     */
    public function getNotifications($iUserId)
    {
        $aNotifications = array();

        //select from database
        $aSettings = $this->database()
            ->select('user_notification')
            ->from(Phpfox::getT('accountapi_user_notification'))
            ->where('user_id = ' . (int) $iUserId)
            ->execute('getRows');

        //get notification setting of mobile
        foreach($aSettings as $iKey => $aSetting)
        {
            $aNotifications[$iKey] = $aSetting['user_notification'];
        }

        return array_values($aNotifications);
    }

    /**
     * Update notification setting
     * @param $iUserId
     * @param array $aVals
     * @return bool
     */
    public function update($iUserId, $aVals = array()) {

        //delete old data
        $this->database()->delete(Phpfox::getT('accountapi_user_notification'), 'user_id = ' . (int) $iUserId);

        //insert new data
        foreach ($aVals as $aVal)
        {
            $this->database()->insert(Phpfox::getT('accountapi_user_notification'), array(
                    'user_id' => $iUserId,
                    'user_notification' => $aVal
                )
            );
        }

        return true;
    }
}