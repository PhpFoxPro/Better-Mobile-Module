<?php
/**
 * [PHPFOX_HEADER]
 */

defined('PHPFOX') or exit('NO DICE!');

/**
 *
 *
 * @copyright       [PHPFOX_COPYRIGHT]
 * @author          Raymond_Benc
 * @package         Phpfox_Component
 * @version         $Id: add.class.php 4080 2012-03-28 15:08:47Z Miguel_Espinoza $
 */
class Accountapi_Component_Controller_Checkkey extends Phpfox_Component
{
    /**
     * Class process method wnich is used to execute this component.
     */
    public function process()
    {
        header('Content-Type: text/json');

    	$oServiceAccountapi = Phpfox::getService('accountapi');

        // get username password from url
        $sAppId = $oServiceAccountapi->getReq(3);
        $sUsername = $oServiceAccountapi->getReq(4);
        $sPassword = $oServiceAccountapi->getReq(5);// if fail to get
        // -> get from $_POST
        if (empty($sAppId) && empty($sUsername) && empty($sPassword)) {
            if (isset($_POST['email'])) {
                $sUsername = $_POST['email'];
            }
            if (isset($_POST['password'])) {
                $sPassword = $_POST['password'];
            }
        }

        if (!is_int($sAppId)) {
        	$sPassword = $sUsername;
        	$sUsername = $sAppId;
        	$sAppId = Phpfox::getParam('accountapi.app_id');
        }

		if (empty($sUsername)) {
			$sUsername = $this->request()->get('email');
			$sPassword = $this->request()->get('password');
		}

        if (!$sAppId || !$sUsername || !$sPassword) {
            echo json_encode(array('status' => 'error', 'message' => Phpfox::getPhrase('accountapi.missing_parameters')));
            exit;
        }

        $aUser = Phpfox::getService('accountapi')->getUserByEmailOrUsername($sUsername);

        if (!$aUser || !is_array($aUser)) {
            echo json_encode(array('status' => 'error', 'message' => Phpfox::getPhrase('accountapi.username_invalid')));
            exit;
        }

        if (Phpfox::isModule('semigrator') && strlen($aUser['password_salt']) > 3) {
            if ($aUser['password'] !== Phpfox::getService('semigrator.se.user')->setHash($sPassword, $aUser['password_salt'])) {
                echo json_encode(array('status' => 'error', 'message' => Phpfox::getPhrase('accountapi.password_invalid')));
                exit;
            }
        } else if ($aUser['password'] !== Phpfox::getLib('hash')->setHash($sPassword, $aUser['password_salt'])) {
            echo json_encode(array('status' => 'error', 'message' => Phpfox::getPhrase('accountapi.password_invalid')));
            exit;
        }

        if (isset($aUser['status_id']) && $aUser['status_id'] == 1 && Phpfox::getParam('user.verify_email_at_signup')) {
            echo json_encode(array('status' => 'error', 'message' => strip_tags(Phpfox::getPhrase('user.you_need_to_verify_your_email_address_before_logging_in', array('email' => $aUser['email'])))));
            exit;
        }

        $aKey = Phpfox::getService('accountapi.user')->checkKey($sAppId, $aUser);
		if (!$aKey) {
			exit;
		}

        (($sPlugin = Phpfox_Plugin::get('accountapi.component_controller_checkkey_get_json_value')) ? eval($sPlugin) : false);
        echo json_encode($aKey);
        exit;
    }
}