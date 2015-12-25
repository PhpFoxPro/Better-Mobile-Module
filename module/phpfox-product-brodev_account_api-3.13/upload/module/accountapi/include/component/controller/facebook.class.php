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
 * @package 		Phpfox_Component
 * @version 		$Id: facebook.class.php 1931 2010-10-25 11:58:06Z Raymond_Benc $
 */
class Accountapi_Component_Controller_Facebook extends Phpfox_Component
{
	public function process() 
	{
        header('Content-Type: text/json');
		//service facebook accountapi
		$oFbService = Phpfox::getService('accountapi.facebook');

        if (isset($_REQUEST['email'])) {
            $aVals['email'] = $_REQUEST['email'];
        }
        if (isset($_REQUEST['fullname'])) {
            $aVals['full_name'] = $_REQUEST['fullname'];
        }
        if (isset($_REQUEST['gender'])) {
            $aVals['gender'] = $_REQUEST['gender'];
        }
        if (isset($_REQUEST['birthday'])) {
            $aVals['birthday'] = $_REQUEST['birthday'];

            $aParts = explode('/', $aVals['birthday']);
            $aVals['day'] = (isset($aParts[1]) ? $aParts[1] : '1');
            $aVals['month'] = (isset($aParts[0]) ? $aParts[0] : '1');
            $aVals['year'] = (isset($aParts[2]) ? $aParts[2] : '1982');
        }
        if (isset($_REQUEST['uid'])) {
            $iUid = $_REQUEST['uid'];

            $aVals['password'] = md5($iUid);
        }

        if (isset($_REQUEST['accessToken'])) {
            $sAccessToken = $_REQUEST['accessToken'];
        }

        if (!isset($aVals)) {
            echo json_encode(array('status' => 'error', 'message' => Phpfox::getPhrase('accountapi.missing_parameters')));
            exit;
        }

		$bAdd = $oFbService->addUserFacebook($aVals, $iUid, $sAccessToken);
		//get user info facebook
		$aUserFB = Phpfox::getService('facebook')->getUser($iUid);
		//get app id
		$sAppId = Phpfox::getParam('accountapi.app_id');
		$aKey = Phpfox::getService('accountapi.user')->checkKey($sAppId, $aUserFB);

		echo json_encode($aKey);
		exit;
	}
}
		