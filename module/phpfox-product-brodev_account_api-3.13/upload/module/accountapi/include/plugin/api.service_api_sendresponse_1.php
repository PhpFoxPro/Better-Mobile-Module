<?php
if (isset($this->_aApi['public_key']) && $this->_aApi['public_key'] == Phpfox::getParam('accountapi.app_id' )) {

	$aOutput = json_decode($sOutput, true);
	$aOutput['social_app']['notify'] = Phpfox::getService('accountapi.api')->getNotifyStatus();
	$sOutput = json_encode($aOutput);
    $aOutput['api']['pages'] = Phpfox::getLib('pager')->getTotalPages();
}
header('content-type: application/json; charset=utf-8');