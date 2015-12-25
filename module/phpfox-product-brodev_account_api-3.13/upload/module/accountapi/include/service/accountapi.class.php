<?php
/**
 * [PHPFOX_HEADER]
 */

defined('PHPFOX') or exit('NO DICE!');

/**
 *
 *
 * @copyright       [PHPFOX_COPYRIGHT]
 * @author          Chuong Dang
 * @package         Phpfox_Service
 * @version         $Id: service.class.php 67 2009-01-20 11:32:45Z Raymond_Benc $
 */
class Accountapi_Service_Accountapi extends Phpfox_Service
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('app');
		$this->_bThrough = false;
        $this->_sCacheFile = PHPFOX_DIR . 'file' . PHPFOX_DS . 'pushsa';
        $this->_sCacheFilePath = $this->_sCacheFile . PHPFOX_DS . 'push_sa_processing.txt';
    }

    public function getAppById($iId, $iUserId)
    {
        $aApp = $this->database()->select('a.*, p.page_id, p.total_like, au.install_id as is_installed, ac.category_id, ac.name as category_name, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('app'),'a')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = a.user_id')
            ->leftjoin(Phpfox::getT('app_installed'), 'au', 'au.app_id = a.app_id AND au.user_id = ' . $iUserId)
            ->leftjoin(Phpfox::getT('app_category_data'), 'acd', 'acd.app_id = a.app_id')
            ->leftjoin(Phpfox::getT('app_category'), 'ac', 'ac.category_id = acd.category_id')
            ->leftjoin(Phpfox::getT('pages'), 'p', 'p.app_id = a.app_id')
            ->where('a.public_key = \'' . $iId. '\'')
            ->execute('getSlaveRow');

        if (empty($aApp))
        {
            return Phpfox_Error::display(Phpfox::getPhrase('apps.this_app_does_not_exist'));
        }

        $aApp['category_name'] = Phpfox::getLib('locale')->convert($aApp['category_name']);

        return $aApp;
    }

    public function install($iAppId, $iUserId, $aDisallow)
    {

        if ( ((int)$iAppId) < 1)
        {
            return Phpfox_Error::set('Invalid App');
        }
        $this->database()->insert(Phpfox::getT('app_installed'), array(
            'app_id' => (int)$iAppId,
            'user_id' => (int)$iUserId,
            'time_stamp' => PHPFOX_TIME));

        if (!empty($aDisallow))
        {
            $oParse = Phpfox::getLib('parse.input');
            foreach ($aDisallow as $sFunction)
            {
                $sFunction = $oParse->clean($sFunction);
                if (!empty($sFunction))
                {
                    $this->database()->insert(Phpfox::getT('app_disallow'), array(
                        'app_id' => (int)$iAppId,
                        'user_id' => (int)$iUserId,
                        'var_name' => $sFunction
                    ));
                }
            }
        }

        $this->cache()->remove(array('user', 'apps_' . Phpfox::getUserId()));

        return true;
    }

    public function getKey($iAppId, $aUser)
    {
		$aKey = $this->database()->select('*')->from(Phpfox::getT('app_key'))->where('app_id = ' . $iAppId . ' AND user_id = ' . $aUser['user_id'])->execute('getRow');
		if ($aKey) {
	        $this->database()->update(Phpfox::getT('app_key'), array(
	                'time_stamp' => PHPFOX_TIME
	            ), 'app_id = ' . $iAppId . ' AND user_id = ' . $aUser['user_id']
	        );
			return $aKey['key_check'];
		} else {
			$sKey = md5(((int) $iAppId) . uniqid() . $aUser['email'] . uniqid() . $aUser['password_salt']);
			$this->database()->insert(Phpfox::getT('app_key'), array(
	                'key_check' => $sKey,
	                'app_id' => (int)$iAppId,
	                'user_id' => (int)$aUser['user_id'],
	                'time_stamp' => PHPFOX_TIME
	            )
	        );
			return $sKey;
		}
    }

    public function getUserByEmailOrUsername($sLogin)
    {
        return $this->database()
            ->select('*')
            ->from(Phpfox::getT('user'))
            ->where("user_name = '" . $sLogin . "' OR email = '" . $sLogin . "'")
            ->execute('getRow');
    }

    /**
     * cron job for push notification using file
     */
    public function pushNotificationFromFile() {
        //check if exist push folder in cache
        if (is_dir($this->_sCacheFile))
        {
            //open dir
            if ($dh = opendir($this->_sCacheFile))
            {
                while (($file = readdir($dh)) !== false)
                {
                    if ($file != "." && $file != "..")
                    {
                        //rename file before processing
                        Phpfox::getLib('file')->rename($this->_sCacheFile . PHPFOX_DS . $file, $this->_sCacheFilePath);
                        //get content from file
                        $sContent = file_get_contents($this->_sCacheFilePath);
                        if ($sContent !== '')
                        {
                            //explode data
                            $aData = explode('|', $sContent, 2);
                            //send push notification
                            $aDataPush = json_decode($aData[1], true);
                            //get token key for gcm and apns
                            //get gcm key
                            $aGCMRegId = Phpfox::getService('accountapi.gcm')->getGCMRegId($aData[0]);

                            //get token for Apple push notification
                            $sTokenAPNS = Phpfox::getService('accountapi.apns')->getAPNSToken($aData[0]);

                            //push notification using gcm
                            if(!empty($aGCMRegId))
                            {
                                $GCMKey = array($aGCMRegId);
                                Phpfox::getService('accountapi.gcm')->sendPushNotification($GCMKey, $aDataPush);
                            }

                            //push notification using APNS
                            if (!empty($sTokenAPNS))
                            {
                                if ($aDataPush['notify'] == 'mail')
                                {
                                    unset($aDataPush['preview']);
                                    unset($aDataPush['thread']);
                                    unset($aDataPush['full_name']);
                                    $aDataPush['alert'] = substr($aDataPush['alert'], 0, 30);
                                } else if ($aDataPush['notify'] == 'notification')
                                {
                                    unset($aDataPush['link']);
                                }
                                Phpfox::getService('accountapi.apns')->pushNotification($sTokenAPNS, $aDataPush);
                            }
                        }
                        //remove file
                        Phpfox::getLib('file')->unlink($this->_sCacheFilePath);
                    }
                }
                closedir($dh);
            }
        }
    }

    /**
     * get last bacground update time
     */
    public function getBGtime() {
        $sFile = Phpfox::getParam('core.dir_pic') . 'accountapi'. PHPFOX_DS. 'mobile'. PHPFOX_DS. 'background.jpg';

        if (file_exists($sFile)) {
            return filectime($sFile);
        }
        return false;
    }

    /**
     * @param null $sDestinationDir
     * @param null $aDelFiles
     */
    public function clearCachedImages($sDestinationDir = null, $aDelFiles = null) {

    }

    /**
	 * get URL Request
	 */
	public function getReq($iReq) {
		$sReq = urldecode($_GET['do']);
		$aReq = explode('/', $sReq);
		
		return (empty($aReq[$iReq]) ? '' : $aReq[$iReq]);
	}

	/**
	 * set Through
	 */
	public function setThrough($bThrough) {
		$this->_bThrough = $bThrough;
	}
	
	/**
	 * get Through
	 */
	public function getThrough() {
		return $this->_bThrough;
	}
}
