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
 * @version 		$Id: apns.class.php 3223 2011-10-06 12:56:24Z Miguel_Espinoza $
 */


class Accountapi_Service_Apns extends Phpfox_Service
{
    //construct
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('accountapi_apns_user');
        //get pass phrase setting
        $this->_sPassPhrase = Phpfox::getParam('accountapi.pass_phrase_apns');
        //get APNS url setting
        $this->_sAPNSUrl = Phpfox::getParam('accountapi.apns_url');
    }

    /**
     * Store Apple push notification service user
     * @param $sEmail
     * @param $sToken
     * @param $iUserId
     */
    public function storeAPNSUser($sEmail, $sToken, $iUserId) {

        $sRow = $this->getAPNSToken($iUserId);

        if (empty($sRow)) {
            //insert into database
            $this->database()->insert($this->_sTable, array(
                'user_id' => $iUserId,
                'token' => $sToken,
                'email' => $sEmail,
                'timestamp' => PHPFOX_TIME
            ));
        } else {
            // update
            $this->database()->update($this->_sTable, array('token' => $sToken), 'user_id = ' . (int) $iUserId);
        }
    }

    /**
     * unregister Apple push notification service
     * @param $iUserId
     * @return bool
     */
    public function unRegisterAPNS($iUserId)
    {
        $aRow = $this->getAPNSToken($iUserId);
        if (!empty($aRow)) {
            $this->database()->delete($this->_sTable, 'user_id = '. $iUserId);
            return true;
        }
        return false;
    }

    /**
     * get apns token
     *
     * @param $iUserId
     * @return mixed
     */
    public function getAPNSToken($iUserId)
    {
        return $this->database()
            ->select('token')
            ->from($this->_sTable)
            ->where('user_id = ' . $iUserId)
            ->execute('getField');
    }

    /**
     * Send Push Notification
     *
     * @param $sToken
     * @param $message
     * @return int
     */
    public function pushNotification($sToken, $message) {
        //check if have push certificate key

        $sDesUrl = PHPFOX_DIR . 'file' . PHPFOX_DS . 'accountapi' . PHPFOX_DS . 'certificate' . PHPFOX_DS . 'PushAppCerKey.pem';

        if (file_exists($sDesUrl))
        {
            $ctx = stream_context_create();
            stream_context_set_option($ctx, 'ssl', 'local_cert', $sDesUrl);
            stream_context_set_option($ctx, 'ssl', 'passphrase', $this->_sPassPhrase);
            stream_context_set_option($ctx, 'ssl', 'cafile', PHPFOX_DIR . 'file' . PHPFOX_DS . 'accountapi' . PHPFOX_DS . 'cert' . PHPFOX_DS . 'entrust_2048_ca.cer');

            // Open a connection to the APNS server
            $fp = stream_socket_client('ssl://' . $this->_sAPNSUrl, $err, $errstr, 30, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

            if (!$fp) {
                echo 'Failed to connect:' .  $err  . ' ' . $errstr;
                return;
            }

            echo 'Connected to APNS' . PHP_EOL . $this->_sAPNSUrl;


            // Create the payload body
            $body['aps'] = $message;

            // Encode the payload as JSON
            $payload = json_encode($body);

            // Build the binary notification
            $msg = chr(0) . pack('n', 32) . pack('H*', $sToken) . pack('n', strlen($payload)) . $payload;

            // Send it to the server
            $result = fwrite($fp, $msg, strlen($msg));

            // Close the connection to the server
            fclose($fp);

            return $result;
        }

    }
}

