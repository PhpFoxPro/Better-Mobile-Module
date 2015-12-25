<?php

class Accountapi_Service_Cloud extends Phpfox_Service {

	public function __construct() {
		$this->_sTable = Phpfox::getT('accountapi_user');
		$this->_sAppKey = Phpfox::getParam('accountapi.native_app_key');
		$this->_sChannel = 'common'; //Phpfox::getParam('core.host');

		$this->_aRequestUrl = array(
			'createUser' => 'https://api.cloud.appcelerator.com/v1/users/create.json',
			'deleteUser' => 'https://api.cloud.appcelerator.com/v1/users/delete.json', 
			'login' => 'https://api.cloud.appcelerator.com/v1/users/login.json', 
			'pushNotify' => 'https://api.cloud.appcelerator.com/v1/push_notification/notify.json'
		);
		$this->removeUser();
		$this->createUser();
		$this->login();
	}
	
	function getUser($iUserId) {
		return  $this->database()->select('cloud_id')->from($this->_sTable)->where('user_id='. $iUserId)->execute('getField');
	}
	
	function removeUser() {
		return $this->getData($this->_getRequest('deleteUser'));
	}
	
	function addUser($iUserId, $iCloudUserId) {
		$this->database()->delete($this->_sTable, 'user_id = '. $iUserId);
		$this->database()->insert($this->_sTable, array(
			'user_id' => $iUserId,
			'cloud_id' => $iCloudUserId,
			'timestamp' => PHPFOX_TIME
		));	
	}

	function createUser() {
		$aPost = array('username' => 'system', 'password' => 'system', 'password_confirmation' => 'system');	
		
		return $this->getData($this->_getRequest('createUser'), $aPost);
	}

	function login() {
		$aPost = array('login' => 'system', 'password' => 'system');

		return $this->getData($this->_getRequest('login'), $aPost);
	}

	function pushNotify($sCloudUserId, $aData) {
		if (empty($sCloudUserId)) {
			return false;
		}
		$aData = array(
			'channel' => $this->_sChannel, 
			'to_ids' => $sCloudUserId, 
			'payload' => $aData
		);		
		$sContent = $this->getData($this->_getRequest('pushNotify'), $aData);
	}

	function getData($sUrl, $aData = null) {		
		$mCurlHandle = curl_init($sUrl);
		$sCookiePath = Phpfox::getParam('core.dir_cache') . 'cookies.txt';
		if (empty($aData)) {
			curl_setopt($mCurlHandle, CURLOPT_CUSTOMREQUEST, "GET");	
		} else {
			$sData = json_encode($aData);
			curl_setopt($mCurlHandle, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($mCurlHandle, CURLOPT_POSTFIELDS, $sData);
			curl_setopt($mCurlHandle, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($sData)));
		}		
		curl_setopt($mCurlHandle, CURLOPT_COOKIEJAR, $sCookiePath);
		curl_setopt($mCurlHandle, CURLOPT_COOKIEFILE, $sCookiePath);
		curl_setopt($mCurlHandle, CURLOPT_RETURNTRANSFER, true);
		$sResult = curl_exec($mCurlHandle);

		return $sResult;
	}

	function _getRequest($sName) {
		return $this->_aRequestUrl[$sName] . '?key=' . $this->_sAppKey;
	}

}
