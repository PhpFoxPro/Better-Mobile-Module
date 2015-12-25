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
 
 class Accountapi_Service_Facebook extends Phpfox_Service
 {
 	
	public function __construct() {
		$this->_sTable = Phpfox::getT('fbconnect');
	}

     /**
      * Get facebook id
      * @param $iUserId
      * @return bool
      */
     public function getFacebookId($iUserId)
    {
        $iFacebookId = $this->database()
            ->select('fb_user_id')
            ->from($this->_sTable)
            ->where('user_id = ' . (int) $iUserId)
            ->execute('getField');

        if (empty($iFacebookId)) {
            return false;
        }

        return $iFacebookId;
    }

	/**
	 * Add facebook user
	 */
	public function addUserFacebook($aVals, $iFacebookUserId, $sAccessToken) {
		if (!defined('PHPFOX_IS_FB_USER'))
		{
			define('PHPFOX_IS_FB_USER', true);
		}		
		//get facebook setting
		$bFbConnect = Phpfox::getParam('facebook.enable_facebook_connect');
		
		if ($bFbConnect == false) {
			return false;
		} else {
			if(Phpfox::getService('accountapi.facebook')->checkUserFacebook($iFacebookUserId) == false) {

                if (Phpfox::getParam('user.disable_username_on_sign_up')) {
                    $aVals['user_name'] = Phpfox::getLib('parse.input')->cleanTitle($aVals['full_name']);
                }
                $aVals['country_iso'] = null;
                if (Phpfox::getParam('user.split_full_name')) {
                    $aNameSplit = preg_split('[ ]', $aVals['full_name']);
                    $aVals['first_name'] = $aNameSplit[0];
                    unset($aNameSplit[0]);
                    $aVals['last_name'] = implode(' ', $aNameSplit);
                }
				$iUserId = Phpfox::getService('user.process')->add($aVals);
				if($iUserId === false) {
					return false;
				} else {
					Phpfox::getService('facebook.process')->addUser($iUserId, $iFacebookUserId);	
					//update fb profile image to db
					$bCheck = Phpfox::getService('accountapi.facebook')->addImagePicture($sAccessToken, $iUserId);
				}
			}
			
		}
		return true;
	}

	/**
	 * check user facebook in db
	 */
	public function checkUserFacebook($iFacebookUserId) {
		$iCount = (int) $this->database()
					->select(COUNT('*'))
					->from($this->_sTable)
					->where('fb_user_id = \'' . $this->database()->escape($iFacebookUserId) . '\'')
					->execute('getField');
		
		if($iCount > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Insert facebook profile picture
	 */
	public function addImagePicture($sAccessToken, $iUserId) 
	{
		$sImage = 'https://graph.facebook.com/me/picture?type=large&access_token=' . $sAccessToken;
		Phpfox::getLib('file')->writeToCache('fb_' . $iUserId . '_' . md5($sImage), file_get_contents($sImage));							
		$sNewImage = 'fb_' . $iUserId . '_' . md5($sImage) . '%s.jpg';
		copy(PHPFOX_DIR_CACHE . 'fb_' . $iUserId . '_' . md5($sImage), Phpfox::getParam('core.dir_user') . sprintf($sNewImage, ''));
		foreach(Phpfox::getParam('user.user_pic_sizes') as $iSize)
		{
			Phpfox::getLib('image')->createThumbnail(Phpfox::getParam('core.dir_user') . sprintf($sNewImage, ''), Phpfox::getParam('core.dir_user') . sprintf($sNewImage, '_' . $iSize), $iSize, $iSize);
			Phpfox::getLib('image')->createThumbnail(Phpfox::getParam('core.dir_user') . sprintf($sNewImage, ''), Phpfox::getParam('core.dir_user') . sprintf($sNewImage, '_' . $iSize . '_square'), $iSize, $iSize, false);
		}	
		unlink(PHPFOX_DIR_CACHE . 'fb_' . $iUserId . '_' . md5($sImage));
									
		Phpfox::getLib('database')->update(Phpfox::getT('user'), array('user_image' => $sNewImage, 'server_id' => 0), 'user_id = ' . (int) $iUserId);
		
		return true;
	}
	
 }
