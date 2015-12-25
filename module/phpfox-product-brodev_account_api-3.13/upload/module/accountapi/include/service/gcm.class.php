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
 * @version 		$Id: gcm.class.php 3223 2011-10-06 12:56:24Z Miguel_Espinoza $
 */

class Accountapi_Service_Gcm extends Phpfox_Service
{
    //construct
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('accountapi_gcm_user');
        $this->_sGCMKey = Phpfox::getParam('accountapi.gcm_api_key');

        $this->_GCMSend = 'https://android.googleapis.com/gcm/send';
    }

    /**
     * insert gcm user into database
     *
     * @param $sEmail
     * @param $gcmRegId
     * @param $iUserId
     */
    public function storeGCMUser($sEmail, $gcmRegId, $iUserId)
    {
        $aRow = $this->getGCMRegId($iUserId);

        if (empty($aRow)) {
            //insert into database
            $this->database()->insert($this->_sTable, array(
                'user_id' => $iUserId,
                'gcm_regid' => $gcmRegId,
                'email' => $sEmail,
                'timestamp' => PHPFOX_TIME
            ));
        } else {
            //if exist, update
            $this->database()->update($this->_sTable, array('gcm_regid' => $gcmRegId), 'user_id = ' . (int) $iUserId);
        }

    }

    /**
     * unregister gcm
     * @param $iUserId
     * @return bool
     */
    public function unRegisterGCM($iUserId)
    {
        $aRow = $this->getGCMRegId($iUserId);
        if (!empty($aRow)) {
            $this->database()->delete($this->_sTable, 'user_id = '. $iUserId);
            return true;
        }
        return false;
    }

    /**
     * get gcm register id
     *
     * @param $iUserId
     * @return mixed
     */
    public function getGCMRegId($iUserId)
    {
        return $this->database()
            ->select('gcm_regid')
            ->from($this->_sTable)
            ->where('user_id = ' . $iUserId)
            ->execute('getField');
    }

    /**
     * Function sending push notification
     *
     * @param $sRegisterId
     * @param $message
     * @return mixed
     */
    public function sendPushNotification($sRegisterId, $message)
    {
        $aFields = array(
            'registration_ids' => $sRegisterId,
            'data' => $message
        );

        $headers = array(
            'Authorization: key=' . $this->_sGCMKey,
            'Content-Type: application/json'
        );

        // Open connection
        $ch = curl_init();

        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $this->_GCMSend);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($aFields));

        // Execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }

        // Close connection
        curl_close($ch);

        return $result;
    }
}