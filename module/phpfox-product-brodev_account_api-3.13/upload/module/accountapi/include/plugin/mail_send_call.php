<?php
if (Phpfox::getParam('accountapi.enable_push_notification')) {
	if ($this->_sNotification === 'friend.new_friend_request') {
        if (defined('PHPFOX_APP_ID')) {
            $aFromUser = Phpfox::getService('user')->getUser(Phpfox::getUserId());
            $sPrepend = $aFromUser['full_name'];
        } else {
            $sPrepend = '';
        }

		Phpfox::getService('accountapi.push')->friend($aUser['user_id'], $sPrepend . $sSubject);
	}

    if ($this->_sNotification === 'mail.new_message') {
        if (defined('PHPFOX_APP_ID')) {
            $aFromUser = Phpfox::getService('user')->getUser(Phpfox::getUserId());
            $sPrepend = $aFromUser['full_name'];
        } else {
            $sPrepend = '';
        }

        $aFromUser = Phpfox::getService('user')->getUser(Phpfox::getUserId());
        Phpfox::getService('accountapi.push')->mail($aUser['user_id'], $aFromUser['full_name'] . ': ' . $this->_aMessage[1]['message'], $aFromUser);
    }

}