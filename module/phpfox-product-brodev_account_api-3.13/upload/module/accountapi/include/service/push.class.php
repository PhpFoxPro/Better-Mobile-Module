<?php

class Accountapi_Service_Push extends Phpfox_Service {

	var $_iUserId;
    var $_sCacheFile;
    var $_sChmod;
	
    public function __construct()
    {
        $this->_oApi = Phpfox::getService('api');
        $this->_sCacheFile = PHPFOX_DIR . 'file' . PHPFOX_DS . 'pushsa';
        $this->_sChmod = 0777;
    }

	/**
	 * Push notification
	 */
    public function notification($iUserId) {

		$aNotification = Phpfox::getService('accountapi.notification')->getNewestNotification($iUserId);

		if (!$aNotification) {
			return false;
		}

        if ($this->wantToSend($aNotification['type_id'], $iUserId)) {
            return false;
        }

		$aData = array(
            'notify' => 'notification',
			'badge' => $this->_getBadge($iUserId), 
			'sound' => 'default',
            'link' => $aNotification['link'],
			'alert' => $aNotification['message'], 
			'action' => $aNotification['social_app']['link']
		);
        $this->insertToFile($iUserId, $aData);

    }

    /**
     * Check send notification
     * @param $sType
     * @param $iUserId
     * @return bool
     */
    protected function wantToSend($sType = null, $iUserId, $sTypeMail = null)
    {
        $aSettings = $this->database()
            ->select('user_notification')
            ->from(Phpfox::getT('accountapi_user_notification'))
            ->where('user_id = ' . (int) $iUserId)
            ->execute('getRows');

        if (!empty ($aSettings))
        {
            foreach ($aSettings as $iKey => $aSetting) {
                if (!empty($sType)) {
                    if ($aSettings[$iKey]['user_notification'] == 'comment.add_new_comment' && strpos($sType, 'comment') !== false) {
                        return true;
                    } elseif ($aSettings[$iKey]['user_notification'] == 'like.new_like' && (strpos($sType, 'like') !== false || strpos($sType, 'photo') !== false)) {
                        return true;
                    } elseif ($aSettings[$iKey]['user_notification'] == 'friend.new_friend_accepted' && strpos($sType, 'friend') !== false) {
                        return true;
                    }
                } else {
                    if ($aSettings[$iKey]['user_notification'] == 'mail.new_message' && $sTypeMail == 'mail') {
                        return true;
                    } elseif ($aSettings[$iKey]['user_notification'] == 'friend.new_friend_request' && $sTypeMail == 'friend') {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Insert data to file
     * @param $iUserId
     * @param $aData
     */
    public function insertToFile($iUserId, $aData)
    {
        //build data
        $sJson = json_encode($aData);

        //check if don't have push cache folder
        if (!is_dir($this->_sCacheFile))
        {
            //exec('mkdir -p /path/to/folder');
            //exec('chmod 777 /path/to/folder');
            Phpfox::getLib('file')->mkdir($this->_sCacheFile, true, $this->_sChmod);
        }

        //write to file
        if (is_dir($this->_sCacheFile))
        {
            $hFile = fopen($this->_sCacheFile . PHPFOX_DS . 'push_sa_' . PHPFOX_TIME . '.txt', 'w');
            fwrite($hFile, $iUserId . '|' . $sJson);
            fclose($hFile);
        }

    }

	/**
	 * Push mail
	 */
	public function mail($iUserId, $sMessage, $aUser) {

		$aMail = Phpfox::getService('accountapi.mail')->getNewestMail($iUserId);
        $aMail['link']['request']['user_id'] = $aUser['user_id'];

        if ($this->wantToSend(null, $iUserId, 'mail')) {
            return false;
        }

		if (!$aMail) {
			return false;
		}
		$aData = array(
            'notify' => 'mail',
			'badge' => $this->_getBadge($iUserId),
			'sound' => 'default',
			'alert' => $sMessage,
			'action' => $aMail['link'],
            'preview' => $aMail['preview'],
            'thread' => $aMail['thread_id'],
            'full_name' => $aMail['full_name']
		);

        $this->insertToFile($iUserId, $aData);

	}
	
	/**
	 * Push mail
	 */
	public function friend($iUserId, $sMessage) {

		$aRequest = Phpfox::getService('accountapi.friend')->getNewestFriendRequest($iUserId);
		if (!$aRequest) {
			return false;
		}

        if ($this->wantToSend(null, $iUserId, 'friend')) {
            return false;
        }

		$aData = array(
            'notify' => 'add_friend',
			'badge' => $this->_getBadge($iUserId), 
			'sound' => 'default', 
			'alert' => $sMessage, 
			'action' => $aRequest
		);
        $this->insertToFile($iUserId, $aData);
	}
	
	public function _getBadge($iUserId) {

        $aTotalNotify = Phpfox::getService('accountapi.core')->getNotifyStatus();

        return $aTotalNotify['friend'] + $aTotalNotify['mail'] + $aTotalNotify['notification'];
	}

    public function setUserId($iUserId) {
        $this->_iUserId = $iUserId;
    }

    public function getUserId() {
        return $this->_iUserId;
    }


}