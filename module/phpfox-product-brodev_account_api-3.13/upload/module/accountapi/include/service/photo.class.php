<?php

class Accountapi_Service_Photo extends Phpfox_Service {
	
	var $_aParams = array();
	
	public function user($iUser) {
		$this->_aParams['user_id'] = $iUser;
		return $this;
	}
	
	public function module($sModule) {
		$this->_aParams['module_id'] = $sModule;
		return $this;
	}
	
	public function item($iItemId) {
		$this->_aParams['item_id'] = $iItemId;
		return $this;
	}
	
    /**
     * Get photo album by userid
     * @param int $iUserId
     * @return mixed
     */
    public function getPhotoAlbums($iPage, $iLimit = 2) {
    	$bIsUserProfile = false;
    	
    	if (defined('PHPFOX_IS_USER_PROFILE')) {
    		$bIsUserProfile = true;
    		$aUser = Phpfox::getService('user')->get($this->_aParams['user_id']);    			
    	}
    	
    	$oLibSearch = Phpfox::getLib('search');
    	Phpfox::getLib('request')->set('page', $iPage);
    	
    	
        $aBrowseParams = array(
			'module_id' => 'photo.album',
			'alias' => 'pa',
			'field' => 'album_id',
			'table' => Phpfox::getT('photo_album'),
			'hide_view' => array('pending', 'myalbums')
		);		
		
		$oLibSearch->set(array(
				'type' => 'photo.album',
				'field' => 'pa.album_id',				
				'search_tool' => array(
					'table_alias' => 'pa',
					'search' => array(
						'default_value' => Phpfox::getPhrase('photo.search_photo_albums'),
						'name' => 'search',
						'field' => 'pa.name'
					),
					'sort' => array(
						'latest' => array('pa.time_stamp', Phpfox::getPhrase('photo.latest')),
						'most-talked' => array('pa.total_comment', Phpfox::getPhrase('photo.most_discussed'))
					),
					'show' =>  array($iLimit)
				)
			)
		);			
		if ($bIsUserProfile)
		{
			$oLibSearch->setCondition('AND pa.view_id ' . ($aUser['user_id'] == Phpfox::getUserId() ? 'IN(0,2)' : '= 0') . ' AND pa.privacy IN(' . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($aUser)) . ') AND  pa.user_id = ' . (int) $aUser['user_id']);
		}
		else
		{
			if ($this->request()->get('view') == 'myalbums')
			{
				Phpfox::isUser(true);

				$oLibSearch->setCondition('AND pa.user_id = ' . Phpfox::getUserId());
			}
			else
			{
				$oLibSearch->setCondition('AND pa.view_id = 0 AND pa.privacy IN(%PRIVACY%) AND pa.total_photo > 0');
			}
		}
		$aParentModule = $this->_aParams;
    	if ($aParentModule !== null && !empty($aParentModule['item_id']))
		{
			$this->search()->setCondition('AND pa.module_id = \'' . $aParentModule['module_id']. '\' AND pa.group_id = ' . (int) $aParentModule['item_id']);
		}
		
        $oLibSearch->browse()->params($aBrowseParams)->execute();
		
		$aAlbums = $oLibSearch->browse()->getRows();
		$iCnt = $oLibSearch->browse()->getCount();	

		foreach ($aAlbums as $iKey => $aAlbum) {
			$aAlbums[$iKey] = $this->processAlbum($aAlbum);
		}

		Phpfox::getLib('pager')->set(array('page' => $iPage, 'size' => $oLibSearch->getDisplay(), 'count' => $oLibSearch->browse()->getCount()));
		
        return array($iCnt, $aAlbums);
    }

    /**
     * Get list of photos
     * @param $iUserId
     * @param $iPhotoAlbumId
     * @return mixed
     */
    public function getPhotos($iPhotoAlbumId = null, $iPage) {
    	$oLibSearch = Phpfox::getLib('search');
    	$oUrlSearch = Phpfox::getLib('url');
        Phpfox::getLib('request')->set('page', $iPage);
    	$bIsUserProfile = false;
    	
    	if (defined('PHPFOX_IS_USER_PROFILE')) {
    		$bIsUserProfile = true;
    		$aUser = Phpfox::getService('user')->get($this->_aParams['user_id']);    			
    	}
    	
    	$sCategory = $aParentModule = null;	
		$aSearch = $this->request()->getArray('search');
		$bIsTagSearch = false;
		$sView = $this->request()->get('view', false);

        $aPageSizes = (empty($iPhotoAlbumId) ? array(20) : array(1000));
		
		$aSort = array(
			'latest' => array('photo.photo_id', Phpfox::getPhrase('photo.latest')),
		);
		
    	$oLibSearch->set(array(
				'type' => 'photo',
				'field' => 'photo.photo_id',				
				'search_tool' => array(
					'table_alias' => 'photo',
					'search' => array(
						'default_value' => Phpfox::getPhrase('photo.search_photos'),
						'name' => 'search',
						'field' => 'photo.title'
					),
					'sort' => $aSort,
					'show' => $aPageSizes
				),
				'display' => 1
			)
		);		

		$aBrowseParams = array(
			'module_id' => 'photo',
			'alias' => 'photo',
			'field' => 'photo_id',
			'table' => Phpfox::getT('photo'),
			'hide_view' => array('pending', 'my')
		);	
		
		if ($bIsUserProfile) {
			$oLibSearch->setCondition('AND photo.view_id ' . ($aUser['user_id'] == Phpfox::getUserId() ? 'IN(0,2)' : '= 0') . ' AND photo.group_id = 0 AND photo.type_id = 0 AND photo.privacy IN(' . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($aUser)) . ') AND photo.user_id = ' . (int) $aUser['user_id']);
		}
		else
		{
			if (defined('PHPFOX_IS_CUSTOM_MODULE')) {
				$this->search()->setCondition('AND photo.view_id = 0 AND photo.module_id = \'' . Phpfox::getLib('database')->escape($this->_aParams['module_id']) . '\' AND photo.group_id = ' . (int) $this->_aParams['item_id'] . ' AND photo.privacy IN(%PRIVACY%)');
			} else {
				$oLibSearch->setCondition('AND photo.view_id = 0 AND photo.group_id = 0 AND photo.type_id = 0 AND photo.privacy IN(%PRIVACY%)');
			}
		}
    	
    	if (!empty($iPhotoAlbumId)) {
    		$oLibSearch->setCondition('AND photo.album_id = '. $iPhotoAlbumId);
    	}
    	
    	$oLibSearch->browse()->params($aBrowseParams)->execute();
		
		$aPhotos = $oLibSearch->browse()->getRows();
		$iCnt = $oLibSearch->browse()->getCount();


		Phpfox::getLib('pager')->set(array('page' => $oLibSearch->getPage(), 'size' => $oLibSearch->getDisplay(), 'count' => $oLibSearch->browse()->getCount()));
		
		foreach ($aPhotos as $iKey => $aPhoto) {
			if (Phpfox::isModule('like')) {
				$aPhoto['is_liked'] = $this->database()->select('lik.like_id AS is_liked')->from(Phpfox::getT('like'), 'lik')->where('lik.type_id = \'photo\' AND lik.item_id = '. $aPhoto['photo_id']. ' AND lik.user_id = ' . Phpfox::getUserId())->execute('getField');
			}	
	
			$aPhoto['is_friend'] = $this->database()->select('f.friend_id AS is_friend')->from(Phpfox::getT('friend'), 'f')->where('f.user_id = '. $aPhoto['user_id']. ' AND f.friend_user_id = ' . Phpfox::getUserId())->execute('getField');
			
			$aPhoto = $this->processPhoto($aPhoto);
			$aPhoto['type_id'] = 'photo';
			$aPhoto = Phpfox::getService('accountapi.feed')->processFeed($aPhoto, null, $aPhoto['user_id'], true);
			$aPhotos[$iKey] = $aPhoto;
		}
		
		return array($iCnt, $aPhotos);
    }
    
	/**
	 * Cook some information for album: cover image url, time...
	 * 
	 */
    public function processAlbum($aAlbum) {
    	$aSizes = Phpfox::getParam('photo.photo_pic_sizes');
    	$aAlbum['photo_sizes'] = array();
    	foreach ($aSizes as $iSize) {
    		$aAlbum['photo_sizes'][$iSize] = Phpfox::getLib('image.helper')->display(array(
	    			'server_id' => $aAlbum['server_id'],
	    			'file' => $aAlbum['destination'],
	    			'path' => 'photo.url_photo',
			        'suffix' => '_'. $iSize,
			        'return_url' => true
	    		)
    		);
    	}
    	$aAlbum['time_phrase'] = Phpfox::getLib('date')->convertTime($aAlbum['time_stamp'], 'photo.photo_image_details_time_stamp');
    	
    	return $aAlbum;
    }
    
	/**
	 * Get a photo from id
	 */
    public function getPhoto($iPhoto) {
    	$aPhoto = Phpfox::getService('photo')->getPhoto($iPhoto);
		$aPhoto = Phpfox::getService('accountapi.photo')->processPhoto($aPhoto);
		
		return $aPhoto;
    }
    
	/**
	 * Cook some information for photo: image url, time..
	 */
    public function processPhoto($aPhoto) {
    	$aSizes = Phpfox::getParam('photo.photo_pic_sizes');
    	$aPhoto['photo_sizes'] = array();
    	foreach ($aSizes as $iSize) {
    		$aPhoto['photo_sizes'][$iSize] = Phpfox::getLib('image.helper')->display(array(
    			'file' => $aPhoto['destination'],
    			'server_id' => $aPhoto['server_id'],
    			'path' => 'photo.url_photo',
		        'suffix' => '_'. $iSize,
		        'return_url' => true
    		));
    	}
    	$aPhoto['photo_origin'] = Phpfox::getLib('image.helper')->display(array(
    			'file' => $aPhoto['destination'],
    			'server_id' => $aPhoto['server_id'],
    			'path' => 'photo.url_photo',
		        'return_url' => true
    	));
    	
    	$aPhoto['time_phrase'] = Phpfox::getLib('date')->convertTime($aPhoto['time_stamp'], 'photo.photo_image_details_time_stamp');
    	$aPhoto['item_id'] = $aPhoto['photo_id'];
    	$aPhoto['full_url'] = Phpfox::callback('photo.getLink', $aPhoto);
    	 
    	return $aPhoto;
    }
}
?>