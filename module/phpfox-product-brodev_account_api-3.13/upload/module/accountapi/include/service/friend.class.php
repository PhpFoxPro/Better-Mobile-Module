<?php
class Accountapi_Service_Friend extends Phpfox_Service
{
    /**
     * Get pending requests by user id
     * @param $iUserId
     * @param int $iPage
     * @param int $iSize
     * @return array
     */
    public function getPending($iUserId, $iPage = 1, $iSize = 10, $bIsSeen = false)
    {
        $aRows = array();
        $sWhere = 'fr.user_id = ' . $iUserId;
        $sWhere .= ' AND fr.is_ignore = 0';

        $iCnt = $this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('friend_request'), 'fr')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = fr.friend_user_id')
            ->where($sWhere)
            ->execute('getSlaveField');

        if ($iCnt) {
            $aRows = $this->database()->select('fr.*, u.*')
                ->from(Phpfox::getT('friend_request'), 'fr')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = fr.friend_user_id')
                ->where($sWhere)
                ->limit($iPage, $iSize, $iCnt)
                ->order('fr.time_stamp DESC')
                ->execute('getSlaveRows');
        }
        
        $sIds = '';
        foreach ($aRows as $aRow) {
        	$sIds .= $aRow['request_id'] . ',';
        }
        
    	$sIds = rtrim($sIds, ',');
		
		if ($bIsSeen && !empty($sIds))
		{
			$this->database()->update(Phpfox::getT('friend_request'), array('is_seen' => '1'), 'request_id IN(' . $sIds . ')');
		}
		
        return array($iCnt, $aRows);
    }

    public function getFriends($iUserId, $iPage, $iSize, $iPageSize = 12) {
        $aUser = Phpfox::getService('user')->get($iUserId);
    	
		$iPage = $this->request()->getInt('page');
		
		$bMutual = true;
		
		if (!Phpfox::getService('user.privacy')->hasAccess($aUser['user_id'], 'friend.view_friend'))
		{
			return array(0, array());
		}

		$aFilters = array(
			'sort' => array(
				'type' => 'select',
				'options' => array(),
				'default' => 'full_name',
				'alias' => 'u'
			),
			'sort_by' => array(
				'type' => 'select',
				'options' => array(
					'DESC' => Phpfox::getPhrase('core.descending'),
					'ASC' => Phpfox::getPhrase('core.ascending')
				),
				'default' => 'ASC'
			),
			'search' => array(
				'type' => 'input:text',
				'search' => '(u.full_name LIKE \'%[VALUE]%\' OR u.email LIKE \'%[VALUE]%\') AND',
				'size' => '15',
				'onclick' => 'Search'
			)
		);		
		
		$oFilter = Phpfox::getLib('search')->set(array(
				'type' => 'friend',
				'filters' => $aFilters,
				'search' => 'search'
			)
		);
		
		list($iCnt, $aFriends) = Phpfox::getService('friend')->get($oFilter->getConditions(), $oFilter->getSort(), $oFilter->getPage(), $iPageSize, true, true, ($this->request()->get('view') ? true : false), ($bMutual === true ? $aUser['user_id'] : null));
		
		Phpfox::getLib('request')->set('page', $iPage);
		Phpfox::getLib('pager')->set(array('page' => $iPage, 'size' => $iPageSize, 'count' => $iCnt));		
		
		return array($iCnt, $aFriends);
    }

    /**
     * Get total friend
     * @return mixed
     */
    public function getTotalFriend() {
        $iUserId = Phpfox::getUserId();

        $aUser = Phpfox::getService('user')->get($iUserId, true);

        return $aUser['total_friend'];
	}
	/**
	 * request add friend
	 */
	public function requestAddFriend($aUser, $iUserId) {
		
		if (Phpfox::getUserId() === $aUser['user_id']) {
			return array('success' => false, );
		} elseif (Phpfox::getService('friend.request') -> isRequested(Phpfox::getUserId(), $aUser['user_id'])) {
			return array('success' => false, 'notice' => Phpfox::getPhrase('friend.you_have_already_asked_full_name_to_be_your_friend', array('full_name' => $aUser['full_name'])));
		} elseif (Phpfox::getService('friend.request') -> isRequested($aUser['user_id'], Phpfox::getUserId())) {
			return array('success' => false, 'notice' => Phpfox::getPhrase('friend.full_name_has_already_asked_to_be_your_friend', array('full_name' => $aUser['full_name'])));
		} elseif (Phpfox::getService('friend') -> isFriend($aUser['user_id'], Phpfox::getUserId())) {
			return array('success' => false, 'notice' => Phpfox::getPhrase('friend.you_are_already_friends_with_this_user'));
		}
		if (Phpfox::getService('friend.request.process') -> add(Phpfox::getUserId(), $iUserId, 0)) {
			return array('success' => true, 'notice' => Phpfox::getPhrase('friend.friend_request_successfully_sent'));
		}
	}

	/**
	 * get newest friend request
	 */
	 
	public function getNewestFriendRequest($iUserId) {
        list($iCnt, $aFriends) = $this->getPending($iUserId, 1, 1);
		if (count($iCnt) <= 0) {
			return false;
		}
		$aFriendRequest = $aFriends[0];
		$aAction = array(
			'route' => 'user/profile',
			'request' => array(
				'user_id' => $aFriendRequest['friend_user_id']
			)
		);
		
		return $aAction;
	}
	
	/**
	 * Import friend to contact on device
	 */	
	public function importFriendToContact($iUserId, $iPage, $iSize, $iPageSize) 
	{	
		list($iCnt, $aRows) = $this->getFriends($iUserId, $iPage, $iSize, $iPageSize);
		foreach ($aRows as $iKey => $aValue) 
		{
			$aParts = explode(' ' , $aValue['full_name'], 2);
			 
			$aRows[$iKey]['user_image_path'] = null;
			if($aValue['user_image'] != null) {
				$aRows[$iKey]['user_image_path'] = Phpfox::getLib('image.helper') -> display(array('user' => $aValue, 'suffix' => '_120', 'return_url' => true));
			}
			
			$aRows[$iKey]['birthday_phrase'] = Phpfox::getLib('date') -> convertTime($aValue['birthday_search'], 'user.user_dob_month_day_year');
			
			$aRows[$iKey]['first_name'] = $aParts[0];
			$aRows[$iKey]['last_name'] = '';
			if(count($aParts) > 1) {
				$aRows[$iKey]['last_name'] = $aParts[1];
			}
		}
		
		return array(
			'iLength' => (int) $iCnt,
			'friends' => array_values($aRows)
		);
		
	}

}
