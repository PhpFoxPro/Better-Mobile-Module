<?php
class Accountapi_Service_Api extends Phpfox_Service
{
	var $_iInfinite = 1000000000;

    private $_aUser = array();

    private $sColor;

    private $_aContent = array(
        'text',
        'feed_content',
        'short_text',
        'preview',
        'description',
        'description_parsed',
        'mini_description',
        'feed_link_share',
        'feed_link_share_url'
    );

	function __construct()
	{
		$this->_sTable = Phpfox::getT('music_song');
        $this->_oApi = Phpfox::getService('api');

        if (empty($this->_aUser)) {

            $this->_aUser = Phpfox::getService('user')->getUser( $this->_oApi->getUserId());
        }
        Phpfox::getService('user.auth')->setUser($this->_aUser);

        //set default color
        $this->sColor = '#0084c9';
        //get color in setting
        $sColorSetting = Phpfox::getParam('accountapi.choose_color');

        switch ($sColorSetting) {
            case 'Pink':
                $this->sColor = '#ef4964';
                break;
            case 'Brown':
                $this->sColor = '#da6e00';
                break;
            case 'Green':
                $this->sColor = '#348105';
                break;
            case 'Violet':
                $this->sColor = '#8190db';
                break;
            case 'Red':
                $this->sColor = '#ff0606';
                break;
            case 'Dark Violet':
                $this->sColor = '#4e529b';
                break;
        }

        $this->coreHttp = (Phpfox::getParam('core.force_https_secure_pages') ? 'https://' : 'http://');
    }

	/**
	 * Get all songs of user
	 * @return array
	 */
	public function getAllSongs()
	{

        $iUserId = $this->_oApi->getUserId();
        $sType = $this->_oApi->get('type');
        $iGenreId = $this->_oApi->get('genre');
        $iPage = $this->_oApi->get('page');
        $sList = $this->_oApi->get('list');
        list($iCount, $aReturn) = Phpfox::getService('accountapi.music')->getAllSongs($iUserId, $sType, $iGenreId, $sList, $iPage);
        Phpfox::getService('api')->setTotal($iCount);
        return $aReturn;
	}

    /**
     * get list album
     * @return array
     */
    public function  getAlbums() {
        $sWhere = "1 = 1";
        $iUserId = $this->_oApi->getUserId();

        $sList = $this->_oApi->get('type');
        if ($sList == "my") {
            $sWhere .= " AND ma.user_id = ". $iUserId;
        }
        $aItems = $this->database()->select('ma.album_id')
            ->from(Phpfox::getT('music_album'),'ma')
            ->where($sWhere)
            ->order('ma.time_stamp desc')
            ->execute('getSlaveRows');
        $aAlbums = array();
        foreach ($aItems as $aItem)
        {
            $aAlbums[] = Phpfox::getService('accountapi.music')->getAlbum($aItem['album_id']);
        }
        if (!empty($aAlbums)) {
            return $aAlbums;
        } else {
            return array('notice' => Phpfox::getPhrase('music.no_songs_found'));
        }
    }
    /**
     * get all genre of music
     * @return array
     */
    public function getMusicGenre() {
        $aGenres = Phpfox::getService('music.genre')->getList();
        return $aGenres;
    }

    /**
     * get Song detail
     * @return mixed
     */
    public function getSong() {
        $iSongId = $this->_oApi->get('songId');
        return Phpfox::getService('accountapi.music')->getSong($iSongId);
    }
	public function getNothing() {
	}
	
	/**
	 * get Notify Status
	 */
	public function getNotifyStatus() {
		return Phpfox::getService('accountapi.core')->getNotifyStatus();
	}

	/**
	 * Get pending friend requests by user id
	 * @return array
	 */
	public function getPendingFriendRequest()
	{
		$iUserId = $this->_oApi->getUserId();
		$iPage = $this->_oApi->get('page');
		if(!$iPage) {
			$iPage = 1;
		}

		$iSize = Phpfox::getParam('friend.total_requests_display');

		list($iCount, $aRows) = Phpfox::getService('accountapi.friend')->getPending($iUserId, $iPage, $iSize, $this->_oApi->get('seen'));

		foreach ($aRows as $iKey => $aRow) {
			$aRows[$iKey]['user_image_path'] = Phpfox::getLib('image.helper')->display(array(
					'server_id' => $aRow['server_id'],
                    'user' => $aRow,
                    'suffix' => '_75_square',
                    'return_url' => true
				)
			);

			$aRows[$iKey]['time_phrase'] = Phpfox::getLib('date')->convertTime($aRow['time_stamp']);

			unset($aRows[$iKey]['password']);
			unset($aRows[$iKey]['password_salt']);
		}

		Phpfox::getService('api')->setTotal($iCount);
		Phpfox::getLib('request')->set('page', $iPage);
		Phpfox::getLib('pager')->set(array(
            'page' => $iPage,
            'size' => $iSize,
            'count' => $iCount,
		));
		
		if ($iPage == 1 && count($aRows) == 0) {
			return array(
				'notice' => Phpfox::getPhrase('friend.no_new_requests')
			);
		}

		return $aRows;
	}

	/**
	 * Approve friend request
	 * @return array
	 */
	public function approveFriendRequest()
	{
		// get user id from api key
		$iUserId = $this->_oApi->getUserId();

		if ($iFriendId = $this->_oApi->get('user_id')) {
		} else {	
			// get request from id and user id
			$aRequest = $this->database()->select('*')
				->from(Phpfox::getT('friend_request'))
				->where('request_id = '. (int) $this->_oApi->get('request_id'). ' AND user_id = '. (int) $iUserId)
				->execute('getSlaveRow');
				
			$iFriendId = $aRequest['friend_user_id'];
		}

		// if request existed
		if (!empty($iFriendId)) {
			// -> approve request using friend.process->add();
			Phpfox::getService('friend.process')->add($iUserId, $iFriendId);
			$aResult = array(
                'success' => true,
				'action' => 'approve',
                'message' => '',
			);
		} else {
			// -> show error
			$aResult = array(
                'success' => false,
                'message' => 'Request isn\'t existed',
			);
		}
		return $aResult;
	}


	/**
	 * Get feed
	 * @return array
	 */
	public function getFeed() {
		$oFeed = Phpfox::getService('accountapi.feed');
		$oOutput = Phpfox::getLib('parse.output');
		$oServiceFeed = Phpfox::getService('feed');

		$iPage = ((int) $this->_oApi->get('page') ? (int) $this->_oApi->get('page') : 1);
		$iFeedId = $this->_oApi->get('feed_id');
		$iUserId = $this->_oApi->get('user_id');
		$this->_oApi->setTotal($this->_iInfinite);

		Phpfox::getLib('request')->set('page', $iPage);
		Phpfox::getLib('pager')->set(array(
	        'page' => $iPage,
	        'size' => Phpfox::getParam('feed.feed_display_limit'),
	        'count' => $this->_iInfinite,
		));

		if ($sJsonCallback = $this->_oApi->get('callback')) {
			$aCallback = json_decode($sJsonCallback, true);
			$oServiceFeed->callback($aCallback);
		}

		if ($iFeedId) {
            $aRows = Phpfox::getService('feed')->get($iUserId, $iFeedId, null, null);
			if (count($aRows)) {
				$aRow = $aRows[0];
                if (isset($aRow['type_id']) && $aRow['type_id'] != 'blog') {
                    $aRow = $oFeed->processFeed($aRow, null, null, false);
                } else {
                    if (isset($aRow['feed_title']) && isset($aRow['feed_info']))
                        $aRow['title_phrase'] = $aRow['full_name'] . ' ' . $aRow['feed_info'];

                    if (isset($aRow['user_image'])) {
                        $aRow['user_image'] = Phpfox::getLib('image.helper')->display(array(
                            'user' => $aRow,
                            'suffix' => '_75_square',
                            'return_url' => true,
                        ));
                    }

                    if (isset($aRow['feed_time_stamp'])) {
                        $aRow['time_phrase'] = Phpfox::getLib('date')->convertTime($aRow['feed_time_stamp'], 'feed.feed_display_time_stamp');
                    }

                    if (isset($aRow['blog_id'])) {
                        $aRow['item_id'] = $aRow['blog_id'];
                    }
                    if (isset($aRow['feed_content'])) {
                        $aRow['feed_content_html'] = $aRow['feed_content'];
                        $aRow['feed_content'] = Phpfox::getService('accountapi.emoticon')->processEmoticon($aRow['feed_content']);
                    }

                    $aRow['text_html'] = $aRow['text'];

                    $aRow['text'] = Phpfox::getService('accountapi.emoticon')->processEmoticon($aRow['text']);
                }

				if ($aRow['feed_total_like'] > 1) {
					$aRow['like_phrase'] = Phpfox::getPhrase('user.total_people_like_this', array('total' => $aRow['feed_total_like']));
				} elseif ($aRow['feed_total_like'] == 1) {
					if ($aRow['feed_is_liked']) {
						$aRow['like_phrase'] = Phpfox::getPhrase('feed.you_like_this');
					} else {
						$aRow['like_phrase'] = Phpfox::getPhrase('user.1_person_likes_this');
					}
				}

				return $aRow;
			} else {
				return $this->_oApi->error('feed.feed_cannot_be_found', Phpfox::getPhrase('feed.the_activity_feed_you_are_looking_for_does_not_exist'));
			}

		}

		if (empty($iUserId)) {
			$iUserId = null;
		} else {
			$aUser = Phpfox::getService('user')->get($iUserId);
			if (!$aUser) {
				return $this->_oApi->error('user.user_cannot_be_found', Phpfox::getPhrase('user.that_user_does_not_exist'));
			}
		}

		$aRows = $oServiceFeed->get($iUserId, null, $iPage - 1, true);

		$aFeeds = array();

		foreach ($aRows as $iKey => $aRow) {

			if (($aReturn = $oFeed->processFeed($aRow, $iKey, null, true))) {

				if (isset($aReturn['force_user'])) {
					$aReturn['user_name'] = $aReturn['force_user']['user_name'];
					$aReturn['full_name'] = $aReturn['force_user']['full_name'];
					$aReturn['user_image'] = $aReturn['force_user']['user_image'];
					$aReturn['server_id'] = $aReturn['force_user']['server_id'];
				}
                if ($aReturn['item_id'] == 0) {
                    $aReturn['item_id'] = $aRow['item_id'];
                }
                $aFeeds[] = $aReturn;
			}
		}

		return $aFeeds;
	}

    /**
     * delete feed
     * @return mixed
     */
    public function deleteFeed() {
        $iItemId = $this->_oApi->get('item_id');
        $sType = $this->_oApi->get('type_id');
        return (Phpfox::getService('feed.process')->delete($sType, $iItemId));

    }
	public function getLikes() {
		$oServiceFeed = Phpfox::getService('feed');
		$iUserId = $this->_oApi->getUserId();
		$iFeedId = (int) $this->_oApi->get('feed_id');
		if (!empty($iFeedId)) {
			if ($sJsonCallback = $this->_oApi->get('callback')) {
				$aCallback = json_decode($sJsonCallback, true);
				$oServiceFeed->callback($aCallback);
			}
            $aFeed = $oServiceFeed->getFeed($iFeedId);
			if (!$aFeed) {
				return $this->_oApi->error('accountapi.feed_does_not_exist', Phpfox::getPhrase('feed.the_activity_feed_you_are_looking_for_does_not_exist'));
			}
			$sTypeId = $aFeed['like_type_id'];
			$iItemId = $aFeed['item_id'];
		} else {
			$iItemId = (int) $this->_oApi->get('item_id');
			$sTypeId = $this->_oApi->get('type');
		}

		$aLikes = Phpfox::getService('like')->getLikes($sTypeId, $iItemId);
		foreach ($aLikes as $iKey => $aLike) {
			$aLike['user_image_path'] = Phpfox::getLib('image.helper')->display(array(
              	'user' => $aLike,
              	'suffix' => '_50_square',
              	'return_url' => true,
			));

			$aLikes[$iKey] = $aLike;
		}
		return (!empty($aLikes) ? $aLikes : array('notice' => Phpfox::getPhrase('accountapi.no_user')));
	}

	/**
	 * Like a feed
	 * @return mixed
	 */
	public function like()
	{
		$oServiceFeed = Phpfox::getService('feed');
		$iUserId = $this->_oApi->getUserId();
		$iFeedId = (int) $this->_oApi->get('feed_id');
		if (!empty($iFeedId)) {
			if ($sJsonCallback = $this->_oApi->get('callback')) {
				$aCallback = json_decode($sJsonCallback, true);
				$oServiceFeed->callback($aCallback);
			}
			$aFeed = $oServiceFeed->getFeed($iFeedId);
			if (!$aFeed) {
				return $this->_oApi->error('accountapi.feed_does_not_exist', Phpfox::getPhrase('feed.the_activity_feed_you_are_looking_for_does_not_exist'));
			}
			$sTypeId = $aFeed['like_type_id'];
			$iItemId = $aFeed['item_id'];
		}else {
			$iItemId = (int) $this->_oApi->get('item_id');
			$sTypeId = $this->_oApi->get('type');
		}
		if (Phpfox::getService('like.process')->add($sTypeId, $iItemId, $iUserId)) {
			$aReturn = array(
        		'like' => true,
        		'feed_id' => (empty($iFeedId) ? $iItemId : $iFeedId), // when feed_id is null, we are viewing a single item, this property should be a number to indentifine the view of feed.
        		'phrase_many' => Phpfox::getPhrase('user.total_people_like_this'),
        		'phrase_one' => Phpfox::getPhrase('feed.you_like_this'),
        		'button' => Phpfox::getPhrase('feed.unlike')
			);
			if (empty($iFeedId)) {
				$aReturn['type_id'] = $sTypeId;
			}
			return $aReturn;
		}
		$aErrors = Phpfox_Error::get();
		return $this->_oApi->error('accountapi.already_liked_feed', $aErrors[count($aErrors) - 1]);
	}

	/**
	 * Unlink a feed
	 * @return mixed
	 */
	public function unlike() {
		$oServiceFeed = Phpfox::getService('feed');
		$iUserId = $this->_oApi->getUserId();
		$iFeedId = (int) $this->_oApi->get('feed_id');
		if (!empty($iFeedId)) {
			if ($sJsonCallback = $this->_oApi->get('callback')) {
				$aCallback = json_decode($sJsonCallback, true);
				$oServiceFeed->callback($aCallback);
			}
			$aFeed = $oServiceFeed->getFeed($iFeedId);
			if (!$aFeed) {
				return $this->_oApi->error('feed.the_feed_you_are_trying_to_like_unlike_does_not_exist_any_longer', Phpfox::getPhrase('feed.the_feed_you_are_trying_to_like_unlike_does_not_exist_any_longer'));
			}
			$sTypeId = $aFeed['like_type_id'];
			$iItemId = $aFeed['item_id'];
		}else {
			$iItemId = (int) $this->_oApi->get('item_id');
			$sTypeId = $this->_oApi->get('type');
		}

		if (Phpfox::getService('like.process')->delete($sTypeId, $iItemId, null)) {
			$aReturn = array(
        		'like' => false,
        		'feed_id' => (empty($iFeedId) ? $iItemId : $iFeedId), // when feed_id is null, we are viewing a single item, this property should be a number to indentifine the view of feed.
        		'phrase_many' => Phpfox::getPhrase('user.total_people_like_this'),
        		'phrase_one' => Phpfox::getPhrase('feed.you_like_this'),
        		'button' => Phpfox::getPhrase('feed.like')
			);
			if (empty($iFeedId)) {
				$aReturn['type_id'] = $sTypeId;
			}
			return $aReturn;
		}
		$aErrors = Phpfox_Error::get();
		return $this->_oApi->error('accountapi.already_unliked_feed', $aErrors[count($aErrors) - 1]);
	}

	/**
	 * Add comment
	 * @return bool
	 */
	public function comment()
	{
		$iUserId = $this->_oApi->getUserId();
		$iFeedId = (int) $this->_oApi->get('feed_id');
		if (!empty($iFeedId)) {
			$aFeed = Phpfox::getService('feed')->getFeed($iFeedId);
			if (!$aFeed) {
				return $this->_oApi->error('comment.cannot_comment_on_this_item_as_it_does_not_exist_any_longer', Phpfox::getPhrase('comment.cannot_comment_on_this_item_as_it_does_not_exist_any_longer'));
			}

			$aActFeed = Phpfox::callback($aFeed['comment_type_id']. '.getActivityFeed', $aFeed);

			$aVals = array(
				'is_via_feed' => $iFeedId,
				'item_id' => $aFeed['item_id'],
				'parent_id' => 0,
				'type' => $aActFeed['comment_type_id'],
				'text' => $this->_oApi->get('comment'),
			);
		}else {
			$iItemId = (int) $this->_oApi->get('item_id');
			$sType = $this->_oApi->get('type');
            $sViaFeed = $this->_oApi->get('via_feed');
			$aVals = array(
				'is_via_feed' => $iItemId,
				'item_id' => $iItemId,
				'parent_id' => 0,
				'type' => $sType,
				'text' => $this->_oApi->get('comment'),
			);

            if (!empty($sViaFeed)) {
                $aVals['is_via_feed'] = $sViaFeed;
            }
		}

		if ($aVals['type'] == 'profile' && !Phpfox::getService('user.privacy')->hasAccess($aVals['item_id'], 'comment.add_comment'))
		{
			return $this->_oApi->error(
          'bulletin.you_do_not_have_permission_to_add_a_comment_on_this_persons_profile', 
			Phpfox::getPhrase('bulletin.you_do_not_have_permission_to_add_a_comment_on_this_persons_profile')
			);
		}

		if ($aVals['type'] == 'group' && (!Phpfox::getService('group')->hasAccess($aVals['item_id'], 'can_use_comments', true)))
		{
			return $this->_oApi->error(
          'bulletin.only_members_of_this_group_can_leave_a_comment',
			Phpfox::getPhrase('bulletin.only_members_of_this_group_can_leave_a_comment')
			);
		}

		if (!Phpfox::getUserParam('comment.can_comment_on_own_profile') && $aVals['type'] == 'profile' && $aVals['item_id'] == Phpfox::getUserId() && empty($aVals['parent_id']))
		{
			return $this->_oApi->error(
          'comment.you_cannot_write_a_comment_on_your_own_profile',
			Phpfox::getPhrase('comment.you_cannot_write_a_comment_on_your_own_profile')
			);
		}

		if (($iFlood = Phpfox::getUserParam('comment.comment_post_flood_control')) !== 0)
		{
			$aFlood = array(
              'action' => 'last_post', // The SPAM action
              'params' => array(
                'field' => 'time_stamp', // The time stamp field
                'table' => Phpfox::getT('comment'), // Database table we plan to check
                'condition' => 'type_id = \'' . Phpfox::getLib('database')->escape($aVals['type']) . '\' AND user_id = ' . Phpfox::getUserId(), // Database WHERE query
                'time_stamp' => $iFlood * 60 // Seconds);	
			)
			);

			// actually check if flooding
			if (Phpfox::getLib('spam')->check($aFlood))
			{
				return $this->_oApi->error(
            'comment.posting_a_comment_a_little_too_soon_total_time',
				Phpfox::getPhrase('comment.posting_a_comment_a_little_too_soon_total_time', array('total_time' => Phpfox::getLib('spam')->getWaitTime()))
				);
			}
		}

		if (Phpfox::getLib('parse.format')->isEmpty($aVals['text']))
		{
			return $this->_oApi->error(
          'comment.add_some_text_to_your_comment',
			Phpfox::getPhrase('comment.add_some_text_to_your_comment')
			);
		}

		if (($mId = Phpfox::getService('comment.process')->add($aVals)) === false)
		{
			$aErrors = Phpfox_Error::get();
			return $this->_oApi->error('comment.could_not_comment_on_feed', $aErrors[count($aErrors)]);
		} else
		{
			$aComments = Phpfox::getService('comment')->getCommentsForFeed(null, null, null, $this->_iInfinite, $mId);

			$aComment = Phpfox::getService('accountapi.feed')->processComment($aComments[0]);

			return $aComment;
		}
	}

	/**
	 * add a new album
	 */
	public function addAlbum() {
		$aVals = array(
			'name' => $this->_oApi->get('title'),
			'description' => $this->_oApi->get('description'),
			'privacy' => $this->_oApi->get('privacy'),
			'privacy_comment' => $this->_oApi->get('privacy_comment'),
		);

		$iAlbumId = Phpfox::getService('photo.album.process')->add($aVals);
		if (is_int($iAlbumId)) {			
			$aVals['album_id'] = $iAlbumId;
			$aVals['album_title'] = $aVals['name'];
			$aVals['album_total'] = 0;
			return $aVals; 
		}
		
		$aErrors = Phpfox_Error::get();
		return $this->_oApi->error('accountapi.add_album_error', $aErrors[count($aErrors) - 1]);
	}


    /**
     * Get photo album
     */
    public function getPhotoAlbum()
    {
        $aPhotoAlbums = Phpfox::getService('photo.album')->getAll(Phpfox::getUserId(), false, false);
        $aAlbum = array();
        foreach ($aPhotoAlbums as $iAlbumKey => $aPhotoAlbum)
        {
            if ($aPhotoAlbum['profile_id'] > 0)
            {
                unset($aPhotoAlbums[$iAlbumKey]);
            }

            if (isset($aPhotoAlbums[$iAlbumKey])) {
                array_push($aAlbum, $aPhotoAlbums[$iAlbumKey]);
            }
        }

       return $aAlbum;
    }


    /**
     * @return array
     */
    public function uploadProfilePic()
    {
        if(!isset($_FILES['image'])) {
            return $this->_oApi->error('accountapi.please_add_photo', Phpfox::getPhrase('core.select_a_file_to_upload'));
        }

        $aImage = Phpfox::getLib('file')->load('image', array('jpg', 'gif', 'png'), (Phpfox::getUserParam('user.max_upload_size_profile_photo') === 0 ? null : (Phpfox::getUserParam('user.max_upload_size_profile_photo') / 1024)));

        if (!empty($aImage['name']))
        {
            $iUserId = $this->_oApi->getUserId();

            if ($this->_oApi->get('edit_profile'))
            {
                if (($aImage = Phpfox::getService('user.process')->uploadImage($iUserId, (isset($aVals['is_iframe']) ? true : (Phpfox::getUserParam('user.force_cropping_tool_for_photos') ? false : true)))) !== false)
                {
                    $sImage = Phpfox::getLib('image.helper')->display(array(
                            'file' => $aImage['user_image'],
                            'server_id' => $aImage['server_id'],
                            'path' => 'core.url_user',
                            'suffix' => '_75_square',
                            'return_url' => true
                        )
                    );
                    return array(
                        'image_url' => $sImage
                    );
                }
            }
        }

        $aErrors = Phpfox_Error::get();
        return $this->_oApi->error('accountapi.could_not_upload_photo', $aErrors[0]);

    }

    /**
     * Upload multiple photos
     * @return mixed
     */
    public function addMultiPhoto() {
        if (!isset($_FILES['image'])) {
            return $this->_oApi->error('accountapi.please_add_photo', Phpfox::getPhrase('core.select_a_file_to_upload'));
        }

        if (!Phpfox::getUserParam('photo.can_upload_photos')) {
            return $this->_oApi->error('accountapi.user_can_not_upload_photo', Phpfox::getPhrase('accountapi.cannot_upload'));
        }

        if (($iFlood = Phpfox::getUserParam('photo.flood_control_photos')) !== 0) {
            $aFlood = array(
                'action' => 'last_post', // The SPAM action
                'params' => array(
                    'field' => 'time_stamp', // The time stamp field
                    'table' => Phpfox::getT('photo'), // Database table we plan to check
                    'condition' => 'user_id = ' . Phpfox::getUserId(), // Database WHERE query
                    'time_stamp' => $iFlood * 60 // Seconds);
                )
            );

            // actually check if flooding
            if (Phpfox::getLib('spam')->check($aFlood)) {
                return $this->_oApi->error( 'accountapi.uploading_photos_a_little_too_soon', Phpfox::getPhrase('photo.uploading_photos_a_little_too_soon'). ' '. Phpfox::getLib('spam')->getWaitTime());
            }
        }

        $oFile = Phpfox::getLib('file');
        $oImage = Phpfox::getLib('image');

        $aVals = array(
            'album_id' => $this->_oApi->get('album_id')
        );

        $oServicePhotoProcess = Phpfox::getService('photo.process');
        $iFileSizes = 0;
        $iCnt = 0;

        foreach ($_FILES['image']['error'] as $iKey => $sError)
        {
            $sType = 'png';
            switch($_FILES['image']['type']) {
                case 'image/jpeg':
                case 'image/jpg':
                    $sType = 'jpg';
                    break;
                case 'image/gif':
                    $sType = 'gif';
                    break;
            }

            $_FILES['image']['name'][$iKey] = (!empty($aVals['name']) ? $aVals['name'] : Phpfox::getTime(Phpfox::getParam('photo.photo_image_details_time_stamp'), PHPFOX_TIME) . '_' . $iKey). '.' . $sType;

            if ($sError == UPLOAD_ERR_OK)
            {
                if ($aImage = $oFile->load('image[' . $iKey . ']', array(
                        'jpg',
                        'gif',
                        'png'
                    ), (Phpfox::getUserParam('photo.photo_max_upload_size') === 0 ? null : (Phpfox::getUserParam('photo.photo_max_upload_size') / 1024))
                )
                )
                {
                    if ($iId = $oServicePhotoProcess->add(Phpfox::getUserId(), array_merge($aVals, $aImage)))
                    {
                        $iCnt++;
                        $aPhoto = Phpfox::getService('photo')->getForProcess($iId);

                        // Move the uploaded image and return the full path to that image.
                        $sFileName = $oFile->upload('image[' . $iKey . ']',
                            Phpfox::getParam('photo.dir_photo'),
                            (Phpfox::getParam('photo.rename_uploaded_photo_names') ? Phpfox::getUserBy('user_name') . '-' . $aPhoto['title'] : $iId),
                            (Phpfox::getParam('photo.rename_uploaded_photo_names') ? array() : true)
                        );

                        // Get the original image file size.
                        $iFileSizes += filesize(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''));

                        // Get the current image width/height
                        $aSize = getimagesize(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''));


                        // Update the image with the full path to where it is located.
                        $oServicePhotoProcess->update(Phpfox::getUserId(), $iId, array(
                                'destination' => $sFileName,
                                'width' => $aSize[0],
                                'height' => $aSize[1],
                                'server_id' => Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID'),
                                'allow_rate' => (empty($aVals['album_id']) ? '1' : '0')
                            )
                        );

                        $aImages[] = array(
                            'photo_id' => $iId,
                            // 'album' => (isset($aAlbum) ? $aAlbum : null),
                            'server_id' => Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID'),
                            'destination' => $sFileName,
                            'name' => $aImage['name'],
                            'ext' => $aImage['ext'],
                            'size' => $aImage['size'],
                            'width' => $aSize[0],
                            'height' => $aSize[1],
                            'completed' => 'false'
                        );
                    }
                }
            }
        }

        $iFeedId = 0;
        // Make sure we were able to upload some images
        if (count($aImages))
        {
            foreach ($aImages as $iKey => $aImage)
            {
                if ($aImage['completed'] == 'false')
                {
                    $aPhoto = Phpfox::getService('photo')->getForProcess($aImage['photo_id']);
                    if (isset($aPhoto['photo_id']))
                    {
                        if (Phpfox::getParam('core.allow_cdn'))
                        {
                            Phpfox::getLib('cdn')->setServerId($aPhoto['server_id']);
                        }

                        if ($aPhoto['group_id'] > 0)
                        {
                            $iGroupId = $aPhoto['group_id'];
                        }

                        $sFileName = $aPhoto['destination'];

                        foreach(Phpfox::getParam('photo.photo_pic_sizes') as $iSize)
                        {
                            // Create the thumbnail
                            if ($oImage->createThumbnail(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''), Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, '_' . $iSize), $iSize, $iSize, true, ((Phpfox::getParam('photo.enabled_watermark_on_photos') && Phpfox::getParam('core.watermark_option') != 'none') ? (Phpfox::getParam('core.watermark_option') == 'image' ? 'force_skip' : true) : false)) === false)
                            {
                                continue;
                            }
                            if (Phpfox::getParam('photo.enabled_watermark_on_photos'))
                            {
                                $oImage->addMark(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, '_' . $iSize));
                            }

                            // Add the new file size to the total file size variable
                            $iFileSizes += filesize(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, '_' . $iSize));
                        }
                    }
                }
            }
            $aCallback = (!empty($aVals['callback_module']) ? Phpfox::callback($aVals['callback_module'] . '.addPhoto', $aVals['callback_item_id']) : null);
            Phpfox::getService('user.space')->update(Phpfox::getUserId(), 'photo', $iFileSizes);
            // Have we posted an album for these set of photos?
            if (isset($aVals['album_id']) && !empty($aVals['album_id']))
            {
                $aAlbum = Phpfox::getService('photo.album')->getAlbum(Phpfox::getUserId(), $aVals['album_id'], true);

                // Set the album privacy
                Phpfox::getService('photo.album.process')->setPrivacy($aVals['album_id']);

                // Check if we already have an album cover
                if (!Phpfox::getService('photo.album.process')->hasCover($aVals['album_id']))
                {
                    // Set the album cover
                    Phpfox::getService('photo.album.process')->setCover($aVals['album_id'], $iId);
                }

                // Update the album photo count
                if (!Phpfox::getUserParam('photo.photo_must_be_approved'))
                {
                    Phpfox::getService('photo.album.process')->updateCounter($aVals['album_id'], 'total_photo', false, count($aImages));
                }
                //if exists album id
                if (isset($aVals['album_id'])) {
                    (Phpfox::isModule('feed') ? $iFeedId = Phpfox::getService('feed.process')->callback($aCallback)->add('photo', $iId, (isset($aVals['privacy']) ? (int) $aVals['privacy'] : 0), (isset($aVals['privacy_comment']) ? (int) $aVals['privacy_comment'] : 0), (isset($aVals['parent_user_id']) ? (int) $aVals['parent_user_id'] : 0)) : null);

                } else {
                    (Phpfox::isModule('feed') ? $iFeedId = Phpfox::getService('feed.process')->callback($aCallback)->add('photo_album', $aVals['album_id'], $aAlbum['privacy'], $aAlbum['privacy_comment'], (isset($aVals['parent_user_id']) ? (int) $aVals['parent_user_id'] : 0)) : null);
                }
            } else {
                (Phpfox::isModule('feed') ? $iFeedId = Phpfox::getService('feed.process')->callback($aCallback)->add('photo', $iId, (isset($aVals['privacy']) ? (int) $aVals['privacy'] : 0), (isset($aVals['privacy_comment']) ? (int) $aVals['privacy_comment'] : 0), (isset($aVals['parent_user_id']) ? (int) $aVals['parent_user_id'] : 0)) : null);
            }

            foreach ($aImages as $aImage)
            {
                if ($aImage['photo_id'] == $aPhoto['photo_id'])
                {
                    continue;
                }

                Phpfox::getLib('database')->insert(Phpfox::getT('photo_feed'), array(
                        'feed_id' => $iFeedId,
                        'photo_id' => $aImage['photo_id']
                    )
                );
            }
            return array('upload' => 'done');
        }

        @unlink($_FILES['image']['tmp_name']);
        $aErrors = Phpfox_Error::get();
        return $this->_oApi->error('accountapi.could_not_upload_photo', $aErrors[0]);
    }

	/**
	 * Share a photo on feed
	 * @return mixed
	 */
	public function addPhoto()
	{
		if (!isset($_FILES['image'])) {
			return $this->_oApi->error('accountapi.please_add_photo', Phpfox::getPhrase('core.select_a_file_to_upload'));
		}

		if (!Phpfox::getUserParam('photo.can_upload_photos')) {
			return $this->_oApi->error('accountapi.user_can_not_upload_photo', Phpfox::getPhrase('accountapi.cannot_upload'));
		}

		if (($iFlood = Phpfox::getUserParam('photo.flood_control_photos')) !== 0) {
			$aFlood = array(
				'action' => 'last_post', // The SPAM action
				'params' => array(
					'field' => 'time_stamp', // The time stamp field
					'table' => Phpfox::getT('photo'), // Database table we plan to check
					'condition' => 'user_id = ' . Phpfox::getUserId(), // Database WHERE query
					'time_stamp' => $iFlood * 60 // Seconds);	
				)
			);

			// actually check if flooding
			if (Phpfox::getLib('spam')->check($aFlood)) {
				return $this->_oApi->error( 'accountapi.uploading_photos_a_little_too_soon', Phpfox::getPhrase('photo.uploading_photos_a_little_too_soon'). ' '. Phpfox::getLib('spam')->getWaitTime());
			}
		}
		
		// if has call back
		$aCallbackReq = array();
		if ($this->_oApi->get('is_callback')) {
			$sVals = $this->_oApi->get('val');
			$aCallbackReq = json_decode(stripslashes($sVals), true);
			$aCallbackReq['parent_user_id'] = isset($aCallbackReq['parent_user_id']) ? $aCallbackReq['parent_user_id'] : 0;
			$aCallbackReq['owner_user_id'] = isset($aCallbackReq['owner_user_id']) ? $aCallbackReq['owner_user_id'] : 0;
		}
        if ($this->_oApi->get('is_event')) {
            $sVals = $this->_oApi->get('val');
            $aCallbackReq = json_decode(stripslashes($sVals), true);
            $aCallbackReq['parent_user_id'] = isset($aCallbackReq['parent_user_id']) ? $aCallbackReq['parent_user_id'] : 0;
            $aCallbackReq['owner_user_id'] = isset($aCallbackReq['owner_user_id']) ? $aCallbackReq['owner_user_id'] : 0;
        }
		$aVals = array(
			'name' => $this->_oApi->get('title'),
	        'status_info' => $this->_oApi->get('status_info'),
	        'description' => $this->_oApi->get('description'),
	        'album_id' => $this->_oApi->get('album_id', 0),
			'privacy' => $this->_oApi->get('privacy', 0),			
			'privacy_comment' => $this->_oApi->get('privacy_comment', 0),			
			'parent_user_id' => $this->_oApi->get('parent_user_id', 0),
		);

		$oFile = Phpfox::getLib('file');
		$oImage = Phpfox::getLib('image');

		$sType = 'png';
		switch($_FILES['image']['type']) {
			case 'image/jpeg':
			case 'image/jpg':
				$sType = 'jpg';
				break;
			case 'image/gif':
				$sType = 'gif';
				break;
		}

		$sImagePath = Phpfox::getParam('photo.dir_photo') . uniqid() . '.' . $sType;

		move_uploaded_file($_FILES['image']['tmp_name'], $sImagePath);

		$_FILES['image']['tmp_name'] = $sImagePath;
		$_FILES['image']['name'] = (!empty($aVals['name']) ? $aVals['name'] : Phpfox::getTime(Phpfox::getParam('photo.photo_image_details_time_stamp'), PHPFOX_TIME)). '.' . $sType;

		
		if ($_FILES['image']['error'] == UPLOAD_ERR_OK) {
			if ($aImage = $oFile->load('image', array('jpg', 'gif', 'png'), (Phpfox::getUserParam('photo.photo_max_upload_size') === 0 ? null : (Phpfox::getUserParam('photo.photo_max_upload_size') / 1024)))) {
              	$aVals['description'] = empty($aVals['description']) ? $aVals['status_info'] : $aVals['description'];
              	$oServicePhotoProcess = Phpfox::getService('photo.process');
              	if ($iId = $oServicePhotoProcess->add(Phpfox::getUserId(), array_merge($aVals, $aImage))) {
              		$aPhoto = Phpfox::getService('photo')->getForProcess($iId);
              		// Move the uploaded image and return the full path to that image.
              		$sFileName = $oFile->upload('image', Phpfox::getParam('photo.dir_photo'), $iId);

              		// Get the original image file size.
              		$iFileSizes = filesize(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''));

              		// Get the current image width/height
              		$aSize = getimagesize(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''));

              		// Update the image with the full path to where it is located.
              		$oServicePhotoProcess->update(Phpfox::getUserId(), $iId, array(
                      'destination' => $sFileName,
                      'width' => $aSize[0],
                      'height' => $aSize[1],
                      'server_id' => Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID'),
                      'allow_rate' => (empty($aVals['album_id']) ? '1' : '0'),
                      'description' => $aVals['description']
              		));


              		if (Phpfox::getParam('core.allow_cdn')) {
              			Phpfox::getLib('cdn')->setServerId($aPhoto['server_id']);
              		}
              		 
              		if (isset($aImage['picup'])) {
              			$bIsPicup = true;
              		}
              		if (isset($aPhoto['photo_id'])) {
              			if (Phpfox::getParam('core.allow_cdn')) {
              				Phpfox::getLib('cdn')->setServerId($aPhoto['server_id']);
              			}

              			if ($aPhoto['group_id'] > 0) {
              				$iGroupId = $aPhoto['group_id'];
              			}

              			foreach(Phpfox::getParam('photo.photo_pic_sizes') as $iSize) {
              				// Create the thumbnail
              				if ($oImage->createThumbnail(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''), Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, '_' . $iSize), $iSize, $iSize, true, ((Phpfox::getParam('photo.enabled_watermark_on_photos') && Phpfox::getParam('core.watermark_option') != 'none') ? (Phpfox::getParam('core.watermark_option') == 'image' ? 'force_skip' : true) : false)) === false) {
              					continue;
              				}

              				if (Phpfox::getParam('photo.enabled_watermark_on_photos')) {
              					$oImage->addMark(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, '_' . $iSize));
              				}

              				// Add the new file size to the total file size variable
              				$iFileSizes += filesize(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, '_' . $iSize));
              			}

              			if (Phpfox::getParam('photo.enabled_watermark_on_photos')) {
              				$oImage->addMark(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''));
              			}
              		}
					if ($aPhoto['album_id'] > 0) {
					    $oAlbumProcessLibrary = Phpfox::getService('photo.album.process');
					    $oAlbumProcessLibrary->updateCounter($aPhoto['album_id'], 'total_photo');
						if (!$oAlbumProcessLibrary->hasCover($aPhoto['album_id'])) {
							$oAlbumProcessLibrary->setCover($aPhoto['album_id'], $aPhoto['photo_id']);
						}
					}	

              		Phpfox::getService('user.space')->update(Phpfox::getUserId(), 'photo', $iFileSizes);

              		$aCallback = (isset($aCallbackReq['callback_module']) ? Phpfox::callback($aCallbackReq['callback_module'] . '.addPhoto', $aCallbackReq['callback_item_id']) : null);

              		(Phpfox::isModule('feed') ? $iFeedId = Phpfox::getService('feed.process')->callback($aCallback)
              			->add('photo', $aPhoto['photo_id'], $aPhoto['privacy'], $aPhoto['privacy_comment'], (isset($aCallbackReq['callback_module']) ? $aCallbackReq['parent_user_id'] : $aVals['parent_user_id']), (isset($aCallbackReq['owner_user_id']) ? $aCallbackReq['owner_user_id'] : null)) : null);

                    //get single feed after sharing photo
                    if (!empty($aCallback)) {
                        $aFeeds = Phpfox::getService('feed')->callback($aCallback)->get(Phpfox::getUserId(), null, null, null);
                    } else {
                        $aFeeds = Phpfox::getService('feed')->get(Phpfox::getUserId(), $iFeedId, null, null);
                    }

                    $aFeed = $aFeeds[0];

                    //filter word
                    $sFilterWord = Phpfox::getService('accountapi.feed')->filterWord($aFeeds[0]);

                    if (!empty ($sFilterWord)) {
                        $aFeeds[0]['feed_status'] = $sFilterWord;
                    }

                    $aFeeds[0]['title_phrase_html'] = '<b><font color="'. $this->sColor .'">' . $aFeed['full_name'] .'</font></b>'. (empty($aFeed['feed_info']) ? '' : ' ' . strip_tags($aFeed['feed_info']));

                    if (isset($aFeed['location_name']) && !empty($aFeed['location_name']))
                    {
                        $aFeeds[0]['title_phrase_html'] .= ' ' . Phpfox::getPhrase('feed.at_location', array('location' => $aFeed['location_name']));
                    }

                    if (!empty($aFeed['feed_image'])) {

                        preg_match('/<img [^>]*src="([^"]+)"[^>]*>/', $aFeed['feed_image'], $aMatches);

                        if (!empty($aMatches[1])) {
                            $aFeeds[0]['feed_image'] = array($aMatches[1]);
                        }
                    }

                    $aFeeds[0]['social_app'] = array(
                        'type_id' => 'photo',
                        'link' => array(
                            'route' => 'photo/viewPhoto',
                            'request' => array(
                                'photo_id' => $iId
                            )
                        )
                    );

              		return $aFeeds;
              	}
              }
		}
		@unlink($_FILES['image']['tmp_name']);
		$aErrors = Phpfox_Error::get(); 
		return $this->_oApi->error('accountapi.could_not_upload_photo', $aErrors[0]);
	}

	public function getEmail() {
		$iUserId = $this->_oApi->get('user_id', Phpfox::getUserId());

		if (empty($iUserId) && !Phpfox::getUserId()) {
			return $this->_oApi->error(
          'accountapi.user_id_is_required',
          'User ID is required.'
          );
		} elseif(empty($iUserId) && Phpfox::getUserId()) {
			$iUserId = Phpfox::getUserId();
		}

		$bIsSentbox = ($this->request()->get('view') == 'sent' ? true : false);
		$bIsTrash = ($this->request()->get('view') == 'trash' ? true : false);
		$iPrivateBox = ($this->request()->get('view') == 'box' ? $this->request()->getInt('id') : false);

		$iPageSize = 8;
		$iPage = ((int) $this->_oApi->get('page') ? (int) $this->_oApi->get('page') : 1);;
		if (!$iPage) {
			$iPage = 1;
		}
        if (Phpfox::isModule('messenger')) {
            $aRows = Phpfox::getService('messenger.socialapp')->getLastMessage($iUserId, $iPage, 10, false);

            if (!empty($aRows)) {
                return array('emails' => $aRows);
            }
            return array('emails' => $aRows, 'notice' => Phpfox::getPhrase('mail.no_new_messages'));
        }

		$aFolders = Phpfox::getService('mail.folder')->get();

		$aConditions = array();
		if ($bIsTrash)
		{
			$aConditions[] = 'AND m.viewer_user_id = ' . $iUserId . ' AND m.viewer_type_id = 1';
		}
		elseif ($iPrivateBox)
		{
			if (isset($aFolders[$iPrivateBox]))
			{
				$aConditions[] = 'AND m.viewer_folder_id = ' . (int) $iPrivateBox . ' AND m.viewer_user_id = ' . $iUserId . ' AND m.viewer_type_id = 0';
			}
			else
			{
				return $this->_oApi->error(
            'accountapi.mail_folder_does_not_exist',
				Phpfox::getPhrase('mail.mail_folder_does_not_exist')
				);
			}
		}
		else
		{
			if ($bIsSentbox)
			{
				$aConditions[] = 'AND m.owner_user_id = ' . $iUserId . ' AND m.owner_type_id = 0';
			}
			else //inbox
			{
				$aConditions[] = 'AND m.viewer_folder_id = 0 AND m.viewer_user_id = ' . $iUserId . ' AND m.viewer_type_id = 0';
			}
		}

		list($iCnt, $aRows, $aInputs) = Phpfox::getService('accountapi.mail')->get($aConditions, 'm.time_stamp DESC', $iPage, $iPageSize, $bIsSentbox, $bIsTrash);
		$sIds = '';
		foreach($aRows as $iKey => $aRow) {
			$iId = empty($aRow['mail_id']) ? $aRow['thread_id'] : $aRow['mail_id'];
			$sIds .= $iId . ',';

			$aRows[$iKey]['user_image_path'] = Phpfox::getLib('image.helper')->display(array(
				'user' => $aRow,
				'server_id' => $aRow['user_server_id'],
				'suffix' => '_50_square',
				'return_url' => true
			));
			$aRows[$iKey]['is_unread'] = 0;
			if (isset($aInputs['unread']) && in_array($iId, $aInputs['unread'])) {
				$aRows[$iKey]['is_unread'] = 1;
			}
		}
		$sIds = rtrim($sIds, ',');
		
		if ($this->_oApi->get('seen') && !empty($sIds) && Phpfox::getParam('mail.update_message_notification_preview'))
		{
			if (!Phpfox::getParam('mail.threaded_mail_conversation')) {
				$this->database()->update(Phpfox::getT('mail'), array('viewer_is_new' => '0'), 'mail_id IN(' . $sIds . ')');	
			}
		}


		Phpfox::getLib('request')->set('page', $iPage);
		Phpfox::getLib('pager')->set(array(
          'page' => $iPage,
          'size' => $iPageSize,
          'count' => $iCnt
		));
		$this->_oApi->setTotal($iCnt);
		foreach($aRows as $iKey => $aRow) {
			$aRows[$iKey]['time_phrase'] = Phpfox::getLib('date')->convertTime($aRow['time_stamp']);
		}

		$sType = Phpfox::getParam('mail.threaded_mail_conversation') ? 'thread' : 'mail';
		
		if ($iPage == 1 && count($aRows) == 0) { 
			return array('emails' => $aRows, 'notice' => Phpfox::getPhrase('mail.no_new_messages'));
		}

		return array('emails' => $aRows, 'input' => $aInputs, 'view_type' => $sType);
	}

	public function sendEmail()
	{
		$sSubject = $this->_oApi->get('subject', '');
		if (empty($sSubject)) {
			$iId = Phpfox::getService('mail.process')->add(array(
				'to' => $this->_oApi->get('to'),
				'attachment' => '',
				'message' => $this->_oApi->get('message'),
				'parent_id' => $this->_oApi->get('parent_id')
			));
			return array('mail_id' => $iId);
		}
		
		if (!Phpfox::getUserParam('mail.can_compose_message', false))
		{
			return $this->_oApi->error(
          'accountapi.can_not_compose_message',
          'User donot have permission composing email.'  
          );
		}

		if (Phpfox::getParam('mail.spam_check_messages') && Phpfox::isSpammer()) {
			return $this->_oApi->error('accountapi.current_your_account_is_marked_as_a_spammer', Phpfox::getPhrase('mail.currently_your_account_is_marked_as_a_spammer'));
		}

		$aValidation = array(
          'subject' => Phpfox::getPhrase('mail.provide_subject_for_your_message'),
          'message' => Phpfox::getPhrase('mail.provide_message')
		);

		$oValid = Phpfox::getLib('validator')->set(array(
          'sFormName' => 'js_form',
          'aParams' => $aValidation
		)
		);
		$aVals = array(
        'subject' => $this->_oApi->get('subject'),
        'message' => $this->_oApi->get('message'),
        'to' => $this->_oApi->get('to'),
        'parent_id' => $this->_oApi->get('parent_id'),
        'thread_id' => $this->_oApi->get('thread_id')
		);

		if ($aVals['parent_id'] || $aVals['thread_id']) {
			if ($aVals['to'] == '') {
				$aVals['to'] = 1;
			}

			if ($aVals['subject'] == '') {
				$aVals['subject'] = 'BRODEV';
			}
		}

		if (((!isset($aVals['to'])) || (isset($aVals['to']) && !count($aVals['to']))) && (!isset($aVals['copy_to_self']) || $aVals['copy_to_self'] != 1)) {
			return $this->_oApi->error('accountapi.user_receive_email_is_missing', Phpfox::getPhrase('mail.select_a_member_to_send_a_message_to'));
		}

		if ($oValid->isValid($aVals))
		{
			if (Phpfox::getParam('mail.mail_hash_check'))
			{
				Phpfox::getLib('spam.hash', array(
              'table' => 'mail_hash',
              'total' => Phpfox::getParam('mail.total_mail_messages_to_check'),
              'time' => Phpfox::getParam('mail.total_minutes_to_wait_for_pm'),
              'content' => $aVals['message']
				)
				)->isSpam();
			}

			if (Phpfox::getParam('mail.spam_check_messages'))
			{
				if (Phpfox::getLib('spam')->check(array(
					'action' => 'isSpam',
              		'params' => array(
						'module' => 'comment',
						'content' => Phpfox::getLib('parse.input')->prepare($aVals['message'])
					)
				))) {
					return $this->_oApi->error('accountapi.spam_is_detected', Phpfox::getPhrase('mail.this_message_feels_like_spam_try_again'));
				}
			}

			if (Phpfox_Error::isPassed())
			{
				if ($aVals['thread_id'] && Phpfox::getParam('mail.threaded_mail_conversation')) {
					unset($aVals['to']);
					unset($aVals['parent_id']);
				} else {
					unset($aVals['thread_id']);
				}

				if ($aVals['parent_id'] > 0) {
					unset($aVals['thread_id']);
				} else {
					unset($aVals['parent_id']);
				}

				if (($iId = Phpfox::getService('accountapi.mail')->add($aVals)))
				{
					if (Phpfox::getParam('mail.threaded_mail_conversation')) {
						return array('thread_id' => $iId);	
					} else {
						return array('mail_id' => $iId);
					}
				}
			}
		}

		return $this->_oApi->error(
        'accountapi.error_is_detected',
		implode(', ', Phpfox_Error::get())
		);
	}

	public function denyFriendRequest()
	{
		$iUserId = $this->_oApi->getUserId();
		
		if ($iFriendId = $this->_oApi->get('user_id')) {
		} else {	
			// get request from id and user id
			$aRequest = $this->database()->select('*')
				->from(Phpfox::getT('friend_request'))
				->where('request_id = '. (int) $this->_oApi->get('request_id'). ' AND user_id = '. (int) $iUserId)
				->execute('getSlaveRow');
				
			$iFriendId = $aRequest['friend_user_id'];
		}
		
		// if request existed
		if (!empty($iFriendId)) {
			// -> deny request using friend.process->deny();
			Phpfox::getService('friend.process')->deny($iUserId, $iFriendId);
			$aResult = array(
                'success' => true,
				'action' => 'deny',
                'message' => '',
			);
		} else {
			// -> show error
			$aResult = array(
                'success' => false,
                'message' => 'Request cann\'t deny',
			);
		}
		return $aResult;
	}

	public function getFriends() {
		$iUserId = $this->_oApi->get('user_id');
		if (!$iUserId) {
			$iUserId = $this->_oApi->getUserId();
		}

		$iPage = ((int) $this->_oApi->get('page') ? (int) $this->_oApi->get('page') : 1);
		if (!$iPage) {
			$iPage = 1;
		}

        $bUserProfile = $this->_oApi->get('is_profile', false);

		$iPageSize = Phpfox::getParam('friend.friend_display_limit');

        //if have messenger module
        if (!$bUserProfile && Phpfox::isModule('messenger') && Phpfox::getService('messenger.process')->getValidKey()) {
            $aFriends = Phpfox::getService('messenger.socialapp')->getChatFriend($iUserId, $iPage);

            if ($iPage == 1 && count($aFriends) == 0) {
                return array('notice' => Phpfox::getPhrase('friend.no_friends'));
            }
            return $aFriends;
        }

        list($iCount, $aFriends) = Phpfox::getService('accountapi.friend')->getFriends($iUserId, $iPage, $iPageSize);
		$this->_oApi->setTotal($iCount);

		foreach ($aFriends as $iKey => $aFriend) {
			$aFriends[$iKey]['user_image_path'] = Phpfox::getLib('image.helper')->display(array(
				'user' => $aFriend,
				'user_server_id' => $aFriend['user_server_id'],
				'suffix' => '_75_square',
				'return_url' => true
			));
		}
		
		if ($iPage == 1 && count($aFriends) == 0) { 
			return array('notice' => Phpfox::getPhrase('friend.no_friends'));
		}		

		return $aFriends;
	}

	/**
	 * Get full info of user
	 * @return array
	 */
	public function getUserInfo() {
		$iUserId = $this->_oApi->get('user_id');
		$bIsLogin = $this->_oApi->get('login', false);
		$oUserService = Phpfox::getService('user');
		$aRow = Phpfox::getService('user')->get($iUserId, true);

		if (Phpfox::getService('user.block')->isBlocked($aRow['user_id'], Phpfox::getUserId()) && !Phpfox::getUserParam('user.can_override_user_privacy'))
		{
			return Phpfox::getPhrase('profile.profile_is_private');
		}

		if (Phpfox::getParam('friend.friends_only_profile')
		&& empty($aRow['is_friend'])
		&& !Phpfox::getUserParam('user.can_override_user_privacy')
		&& $aRow['user_id'] != Phpfox::getUserId()
		)
		{
			return Phpfox::getPhrase('profile.profile_is_private');
		}

		if (!Phpfox::getService('user.privacy')->hasAccess($aRow['user_id'], 'profile.view_profile'))
		{
			return Phpfox::getPhrase('profile.profile_is_private');
		}

		$sImagePath = $aRow['user_image'];

		$aRow['photo_50px'] = Phpfox::getLib('image.helper')->display(array(
		        'user' => $aRow,
		        'server_id' => $aRow['server_id'],
		        'suffix' => '_50',
		        'return_url' => true
			)
		);
			
		$aRow['photo_50px_square'] = Phpfox::getLib('image.helper')->display(array(
		        'user' => $aRow,
		        'server_id' => $aRow['server_id'],
		        'suffix' => '_50_square',
		        'return_url' => true
			)
		);

		$aRow['photo_75px_square'] = Phpfox::getLib('image.helper')->display(array(
                'user' => $aRow,
                'server_id' => $aRow['server_id'],
                'suffix' => '_75_square',
                'return_url' => true
			)
		);
			
		$aRow['photo_120px'] = Phpfox::getLib('image.helper')->display(array(
		        'user' => $aRow,
		        'server_id' => $aRow['server_id'],
		        'suffix' => '_120',
		        'return_url' => true
			)
		);

        $aRow['photo_120px_square'] = Phpfox::getLib('image.helper')->display(array(
                'user' => $aRow,
                'server_id' => $aRow['server_id'],
                'suffix' => '_120_square',
                'return_url' => true
            )
        );

        $aRow['photo_200px_square'] = Phpfox::getLib('image.helper')->display(array(
                'user' => $aRow,
                'server_id' => $aRow['server_id'],
                'suffix' => '_200_square',
                'return_url' => true
            )
        );
			
		$aRow['photo_original'] = Phpfox::getLib('image.helper')->display(array(
		        'user' => $aRow,
		        'server_id' => $aRow['server_id'],
		        'suffix' => '',
		        'return_url' => true
			)
		);

		$aCoverPhoto = Phpfox::getService('photo')->getCoverPhoto($aRow['cover_photo']);
		if (count($aCoverPhoto) > 0) {
			$aSizes = Phpfox::getParam('photo.photo_pic_sizes');
	    	$aRow['cover_photo'] = array();
	    	foreach ($aSizes as $iSize) {
	    		$aRow['cover_photo'][$iSize] = Phpfox::getLib('image.helper')->display(array(
	    			'file' => $aCoverPhoto['destination'],
	    			'server_id' => $aCoverPhoto['server_id'],
	    			'path' => 'photo.url_photo',
			        'suffix' => '_'. $iSize,
			        'return_url' => true
	    		));
	    	}
		}
		$aRow['birthday_time_stamp'] = $aRow['birthday'];	
		$aRow['birthday'] = $oUserService->age($aRow['birthday']);
		$aRow['birthday_phrase'] = $oUserService->getProfileBirthDate($aRow); $aRow['birthday_phrase'] = end($aRow['birthday_phrase']);
		$aRow['gender_phrase'] = $oUserService->gender($aRow['gender']);

        $aRow['info'] = array(
            Phpfox::getPhrase('profile.gender') => $aRow['gender_phrase'],
            Phpfox::getPhrase('user.age') => $aRow['birthday_phrase'],
            'Gender' => $aRow['gender_phrase'],
            'Age' => $aRow['birthday_phrase']
        );

		$sExtraLocation = '';
		$aRow['country_name'] = Phpfox::getService('core.country')->getCountry($aRow['country_iso']);
		if (!empty($aRow['city_location']))
		{
			$sExtraLocation .= Phpfox::getLib('parse.output')->clean($aRow['city_location']). ', ';
		}

		if ($aRow['country_child_id'] > 0)
		{
			$sExtraLocation .= Phpfox::getService('core.country')->getChild($aRow['country_child_id']);
		}

		if (!empty($aRow['country_iso']))
		{
			$aRow['location_phrase'] = $sExtraLocation. ' '. $aRow['country_name'];
            $aRow['info'][Phpfox::getPhrase('profile.location')] = $aRow['location_phrase'];
            $aRow['info']['Location'] = $aRow['location_phrase'];
		}

		if ((int) $aRow['last_login'] > 0 && ((!$aRow['is_invisible']) || (Phpfox::getUserParam('user.can_view_if_a_user_is_invisible') && $aRow['is_invisible']))) {
			$aRow['last_login_phrase'] = Phpfox::getLib('date')->convertTime($aRow['last_login'], 'core.profile_time_stamps');
            $aRow['info'][Phpfox::getPhrase('profile.last_login')] = $aRow['last_login_phrase'];
            $aRow['info']['Last Login'] = $aRow['last_login_phrase'];
		}

		if ((int) $aRow['joined'] > 0) {
			$aRow['joined_phrase'] = Phpfox::getLib('date')->convertTime($aRow['joined'], 'core.profile_time_stamps');
            $aRow['info'][Phpfox::getPhrase('profile.member_since')] = $aRow['joined_phrase'];
            $aRow['info']['Member Since'] = $aRow['joined_phrase'];
		}

		if (Phpfox::getUserGroupParam($aRow['user_group_id'], 'profile.display_membership_info'))
		{
			$aRow['membership'] = $aRow['prefix'] . Phpfox::getLib('locale')->convert($aRow['title']) . $aRow['suffix'];
            $aRow['info'][Phpfox::getPhrase('profile.membership')] = $aRow['membership'];
            $aRow['info']['Membership'] = $aRow['membership'];
		}

		$aRow['profile_views'] = $aRow['total_view'];
        $aRow['info'][Phpfox::getPhrase('profile.profile_views')] = $aRow['profile_views'];
        $aRow['info']['Profile Views'] = $aRow['profile_views'];

		if (Phpfox::isModule('rss') && Phpfox::getParam('rss.display_rss_count_on_profile') && Phpfox::getService('user.privacy')->hasAccess($aRow['user_id'], 'rss.display_on_profile'))
		{
			$aRow['rss_subscribers'] = $aRow['rss_count'];
            $aRow['info'][Phpfox::getPhrase('profile.rss_subscribers')] = $aRow['rss_subscribers'];
            $aRow['info']['RSS Subscribers'] = $aRow['rss_subscribers'];
		}

//        //get relationship
//        $sRelationship = Phpfox::getService('custom')->getRelationshipPhrase($aRow);
//        if(!empty($sRelationship)) {
//            $aRow['info'][Phpfox::getPhrase('user.custom_relationship_status')] =  $sRelationship;
//        }
        //get smoker drinker
//        $aCustomMain['user_panel'] = Phpfox::getService('custom')->getForDisplay('user_panel', $iUserId);
//
//        foreach ($aCustomMain['user_panel'] as $iKey => $aCustom) {
//            if (empty($aCustom['value'])) {
//                continue;
//            }
//            $aRow['info'][$aCustom['field_name']] = Phpfox::getPhrase($aCustom['value']);
//        }
        //add new
        $aMenus = Phpfox::getService('profile')->getProfileMenu($aRow);
 
        foreach($aMenus as $iKey => $aMenu) {
        	$aMenus[$iKey]['icon_image'] = Phpfox::getLib('image.helper')->display(array(
            	'theme' => $aMenu['icon'],
            	'return_url' => true,
            ));

            $aMenus[$iKey]['link'] = Phpfox::getLib('url')->makeUrl($aMenu['url']);

            if(in_array($aMenu['actual_url'], array(
                'profile',
                'profile_info',
                'profile_photo',
                'profile_friend'
            ))) {
                unset($aMenus[$iKey]);
            }
        }
        $aRow['menus'] = $aMenus;
        unset($aRow['password_salt']);
		unset($aRow['password']);
		
        if (!$bIsLogin) {
        	return $aRow;
		}
        
        $aRow['sidebar_item'] = Phpfox::getService('accountapi.pages')->getPages();
        //endnew

//		$aUserPanel = Phpfox::getService('custom')->getForDisplay('user_main', $aRow['user_id']);
//		$aRow['custom'] = array();
//		foreach ($aUserPanel as $aField) {
//			$aRow['custom'][] = array(
//				'phrase' => Phpfox::getPhrase($aField['phrase_var_name']),
//				'value' => strip_tags($aField['value']),
//			);
//		}
//		
		$aMods = Phpfox::getService('mobile')->getMenu();
        $oLibInput = Phpfox::getLib('parse.input');
        foreach($aMods as $iKey => $aModule)
        {
        	if(in_array($aModule['module'], array(
        		'feed',
        		'friend',
        		'mail',
        		'profile'
        		))) {
        			unset($aMods[$iKey]);
        	} else {
                $aMods[$iKey]['phrase'] = $oLibInput->stripInnerHtml($aModule['phrase']);
            }

        }

        $aRow['appMod'] = $aMods;
		
		if(Phpfox::getParam('accountapi.admob_publish_key')) {
			$aRow['key_admob'] = Phpfox::getParam('accountapi.admob_publish_key');
		}
		
		$oServiceAccountapiCore = Phpfox::getService('accountapi.core');
		$aRow['settings'] = $oServiceAccountapiCore->getSettings();
//		$aRow['user_settings'] = $oServiceAccountapiCore->getUserSettings();
		
		return $aRow;
	}

	/**
	 * Get list of photo album
	 * @return mixed
	 */
	public function getPhotoAlbums() {

		$iPage = ((int) $this->_oApi->get('page') != 1 ? (int) $this->_oApi->get('page') : 0);
		$iLimit = $this->_oApi->get('limit');
		
		if ($iUserId = $this->_oApi->get('user_id')) {
			define('PHPFOX_IS_USER_PROFILE', true);
			list($iCnt, $aPhotoAlbums) = Phpfox::getService('accountapi.photo')->user($iUserId)->getPhotoAlbums($iPage, $iLimit);
		} elseif ($sModule = $this->_oApi->get('module')) {
			$iItem = $this->_oApi->get('item_id');
			list($iCnt, $aPhotoAlbums) = Phpfox::getService('accountapi.photo')->module($sModule)->item($iItem)->getPhotoAlbums($iPage,$iLimit);
		} else {
			list($iCnt, $aPhotoAlbums) = Phpfox::getService('accountapi.photo')->getPhotoAlbums($iPage,$iLimit);
		}

		foreach ($aPhotoAlbums as $iKey => $aAlbum) {
			$aAlbum = Phpfox::getService('photo.album')->getAlbum(Phpfox::getUserId(), $aAlbum['album_id'], true);
			$aPhotoAlbums[$iKey]['description'] = (empty($aAlbum['description']) ? '' : $aAlbum['description']);
		}

		$this->_oApi->setTotal($iCnt);
		return $aPhotoAlbums;
	}

	/**
	 * Get list of photos
	 * @return mixed
	 */
	public function getPhotos() {
		$iAlbumId = $this->_oApi->get('album_id', 0); // is user or album id

        $iPage = ((int) $this->_oApi->get('page') != 1 ? (int) $this->_oApi->get('page') : 0);

		if ($iUserId = $this->_oApi->get('user_id')) {
            // get user photo
			define('PHPFOX_IS_USER_PROFILE', true);
			list($iCnt, $aPhotos) = Phpfox::getService('accountapi.photo')->user($iUserId)->getPhotos($iAlbumId, $iPage);
		} elseif ($sModule = $this->_oApi->get('module')) {
            // get photo item
			define('PHPFOX_IS_CUSTOM_MODULE', true);
			$iItem = $this->_oApi->get('item_id');
			list($iCnt, $aPhotos) = Phpfox::getService('accountapi.photo')->module($sModule)->item($iItem)->getPhotos($iAlbumId, $iPage);
		} else {
            // get public photos of an album by id
			list($iCnt, $aPhotos) = Phpfox::getService('accountapi.photo')->getPhotos($iAlbumId, $iPage);
		}
        if (empty($aPhotos)) {
            $aPhotos['notice'] = Phpfox::getPhrase("photo.no_photos_found");
        }
		$this->_oApi->setTotal($iCnt);
		return $aPhotos;
	}

	/**
	 * Get photo info by id
	 * @return mixed
	 */
	public function getPhoto() {
		$iPhotoId = $this->_oApi->get('photo_id');
		$aPhoto = Phpfox::getService('accountapi.photo')->getPhoto($iPhotoId);
		$aPhoto['type_id'] = 'photo';
		$aFeed = Phpfox::getService('accountapi.feed')->processFeed($aPhoto, null, $aPhoto['user_id'], true);

		return $aFeed;
	}


	/**
	 * Get search result
	 * @return string
	 */
	public function getSearchResult() {
		$sQuery = $this->_oApi->get('q', null);
		$sView = $this->_oApi->get('view', null);
		$sType = $this->_oApi->get('type', 'all');

		$iTotalShow = 10;
		$iPage = (int)$this->_oApi->get('page', 0);

		if ($sQuery !== null)
		{
			if (empty($sQuery))
			{
				return array(
                    'error' => true,
                    'aSearchResults' => Phpfox::getPhrase('search.provide_a_search_query')
				);
			}
			else
			{
				$aSearchResults = Phpfox::getService('accountapi.search')->query($sQuery, $iPage, $iTotalShow, $sView, $sType);
				if (count($aSearchResults))
				{
					return array(
                        'error' => false,
                        'aSearchResults' => $aSearchResults,
                        'sQuery' => $sQuery,
                        'sNextPage' => 'q=' . $sQuery . '&amp;view=' . $sView . '&amp;page=' . ($iPage + 1),
                        'count' => count($aSearchResults),
					);
				}else {
					if ($iPage > 0) {
						return array(
                            'error' => false,
                            'aSearchResults' => Phpfox::getPhrase('search.no_more_search_results_to_show'),
                            'count' => 0
						);
					} else {
						return array(
                            'error' => false,
                            'aSearchResults' => Phpfox::getPhrase('search.no_search_results_found'),
                            'count' => 0,
						);
					}
				}

			}
		}
	}

	/**
	 * Log out from mobile
	 * @return mixed
	 */
    public function logOut() {
        $sToken = md5(base64_decode($this->_oApi->get('token')));

        $aApi = $this->database()->select('a.*, aa.token_key, aa.token_private, aa.session_hash, ai.user_id AS target_user_id, u.user_group_id')
            ->from(Phpfox::getT('app_access'), 'aa')
            ->join(Phpfox::getT('app'), 'a', 'a.app_id = aa.app_id')
            ->join(Phpfox::getT('app_installed'), 'ai', 'ai.app_id = a.app_id AND ai.user_id = aa.user_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = ai.user_id')
            ->where('aa.token = \'' . $this->database()->escape($sToken) . '\'')
            ->execute('getSlaveRow');

        $iUserId = $this->_oApi->getUserId();
        $bDevice = (int) $this->_oApi->get('devive');

        $iAppId = $aApi['app_id'];

        $aApp = Phpfox::getService('accountapi')->getAppById($iAppId, $iUserId);
        if (!empty($aApp['is_installed'])) {
            $this->database()->delete(Phpfox::getT('app_installed'), 'app_id = ' . (int)$iAppId . ' AND user_id = ' . (int)$iUserId);
            $this->database()->delete(Phpfox::getT('app_disallow'), 'app_id = ' . (int)$iAppId . 'user_id = ' . (int)$iUserId);
        }
        $this->database()->delete(Phpfox::getT('app_key'), 'app_id = ' . $iAppId . ' AND user_id = ' . (int)$iUserId);

        $result = $this->database()
            ->delete(Phpfox::getT('app_access'), 'app_id = ' . (int)$iAppId . ' AND user_id = ' . (int)$iUserId .  ' AND token LIKE \'' . $this->database()->escape($sToken) . '\'');

        Phpfox::getLib('database')->delete(Phpfox::getT('log_session'), 'session_hash = \''. $aApi['session_hash'] .'\'');
        //Unregister gcm
        if ($bDevice != 0) {
            $bAPNS = Phpfox::getService('accountapi.apns')->unRegisterAPNS($iUserId);
        } else {
            $bGCM = Phpfox::getService('accountapi.gcm')->unRegisterGCM($iUserId);
        }

        return $result;
    }


    /**
	 * @author Hung at 2:57:59 PM Aug 20, 2012
	 * Get all modules
	 * return Array modules
	 */
	public function getModules() {
		$aModules = Phpfox::getService('mobile')->getMenu();
			
		return $aModules;
	}

	public function getFeedComments() {
		$iFeedId = (int) $this->_oApi->get('feed_id');
		$iPage = ((int) $this->_oApi->get('page') ? (int) $this->_oApi->get('page') : 1);;

		if (!empty($iFeedId)) {
			$aFeed = Phpfox::getService('feed')->getFeed($iFeedId);
		} else {
			$aFeed = array(
				'type_id' => $this->_oApi->get('type'),
				'item_id' => $this->_oApi->get('item_id'), 
			);
		}

		$this->_oApi->setTotal($this->_iInfinite);

		Phpfox::getLib('request')->set('page', $iPage);
		$aComments = Phpfox::getService('comment')->getCommentsForFeed($aFeed['type_id'], $aFeed['item_id'], Phpfox::getParam('comment.total_amount_of_comments_to_load'), $this->_iInfinite);
		foreach ($aComments as $iKey => $aComment) {
			$aComments[$iKey] = Phpfox::getService('accountapi.feed')->processComment($aComment);
		}
		
		Phpfox::getLib('request')->set('page', $iPage);
		Phpfox::getLib('pager')->set(array(
	        'page' => $iPage,
	        'size' => Phpfox::getParam('comment.total_amount_of_comments_to_load'),
	        'count' => $this->_oApi->get('total')
		));

		return $aComments;
	}

	public function getNotifications() {
		$aNotifications = Phpfox::getService('accountapi.notification')->get();
		if (count($aNotifications)) {
			return $aNotifications;
		}
		
		return array('notice' => Phpfox::getPhrase('notification.no_new_notifications'));
	}
	
	public function getMail() {
		$iMail = $this->_oApi->get('mail_id', 1);
		$oServiceMail = Phpfox::getService('mail');
		$bIsSentbox = false;
		$aMail = $oServiceMail->getMail($iMail);
		
		if ($aMail) {
			if (($aMail['viewer_user_id'] != Phpfox::getUserId() && $aMail['owner_type_id'] == 3) || ($aMail['viewer_user_id'] == Phpfox::getUserId() && $aMail['viewer_type_id'] == 3))
			{
				return $this->_oApi->error('mail.invalid_message', Phpfox::getPhrase('mail.invalid_message'));
			}
			
			$aMail['text_reply'] = preg_replace('#<br\s*/?>#i', "\n", $aMail['text_reply']);
			$aMail['preview'] = preg_replace('#<br\s*/?>#i', "\n", $aMail['preview']);
			$aMail['owner_user_image_path'] = Phpfox::getLib('image.helper')->display(array(
                'file' => $aMail['owner_user_image'],
    			'path' => 'core.url_user',
		        'suffix' => '_75_square',
		        'return_url' => true
			));
			$aMail['viewer_user_image_path'] = Phpfox::getLib('image.helper')->display(array(
                'file' => $aMail['viewer_user_image'],
    			'path' => 'core.url_user',
		        'suffix' => '_75_square',
		        'return_url' => true
			));
			$aMail['time_phrase'] = Phpfox::getLib('date')->convertTime($aMail['time_stamp'], 'mail.mail_time_stamp');
			$aMail['next_mail'] = $oServiceMail->getNext($aMail['time_updated'], $bIsSentbox);
			$aMail['prev_mail'] = $oServiceMail->getPrev($aMail['time_updated'], $bIsSentbox);
			return $aMail;
		} else {
			return $this->_oApi->error('mail.invalid_message', Phpfox::getPhrase('mail.invalid_message'));
		}
	}

	public function getThread() {
		$iPage = ((int) $this->_oApi->get('page') ? (int) $this->_oApi->get('page') : 1);;
		$oServiceMail = Phpfox::getService('mail');

		if (Phpfox::getParam('mail.threaded_mail_conversation')) {
			$iThreadId = $this->_oApi->get('thread_id');
			if ($iPage > 1) {
				list($aThread, $aMessages) = $oServiceMail->getThreadedMail($iThreadId, $iPage -1);
			} else {
				list($aThread, $aMessages) = $oServiceMail->getThreadedMail($iThreadId);
			}
			if (is_array($aMessages)) {
				Phpfox::getService('mail.process')->threadIsRead($iThreadId);
	
				foreach($aMessages as $iKey => $aMessage) {
					$aMessages[$iKey]['user_image_path'] = Phpfox::getLib('image.helper')->display(array(
	                        'user' => $aMessage,
	                        'suffix' => '_75_square',
	                        'return_url' => true
					)
					);

                    $aMessages[$iKey]['text_html'] = $aMessages[$iKey]['text'];
                    $aMessages[$iKey]['text'] = Phpfox::getService('accountapi.emoticon')->processEmoticon($aMessages[$iKey]['text']);
					$aMessages[$iKey]['time_phrase'] = Phpfox::getLib('date')->convertTime($aMessage['time_stamp']);
				}					
			} else {
				return $this->_oApi->error('mail.unable_to_find_a_conversation_history_with_this_user', Phpfox::getPhrase('mail.unable_to_find_a_conversation_history_with_this_user')); 
			}
			
			return $aMessages;
		} else {
			$iWithiUserId = $this->_oApi->get('user_id');

			list($iCount, $aMessages) = Phpfox::getService('accountapi.mail')->getMailWithUser(Phpfox::getUserId(), $iWithiUserId, $iPage);

			foreach($aMessages as $iKey => $aMessage) {
				$aMessages[$iKey]['user_image_path'] = Phpfox::getLib('image.helper')->display(array(
                        'user' => $aMessage,
                        'suffix' => '_75_square',
                        'return_url' => true
				)
				);
				$aMessages[$iKey]['time_phrase'] = Phpfox::getLib('date')->convertTime($aMessage['time_stamp']);
			}

			if ($aMessages < 10 || (($iPage - 1) * 10 + count($aMessages) >= $iCount)) {
				$bViewMore = false;
			} else {
				$bViewMore = true;
			}
			return array(
                'aMessage' => $aMessages,
                'bViewMore' => $bViewMore,
                'iPage' => $iPage
			);
		}
	}

    /**
     * Update status yourself
     * @return array|mixed
     */
    public function updateStatus() {
		if ($this->_oApi->get('is_callback')) {
			return $this->postStatusToPage();
		}
        if ($this->_oApi->get('is_event')) {
            return $this->postStatusToEvent();
        }
		if ($this->_oApi->get('parent_user_id')) {
			return $this->postStatusToFriend();
		}

		$aVals['status_info'] = $this->_oApi->get('status_info');
		$aVals['privacy'] = $this->_oApi->get('privacy');
        $sLatlng = $this->_oApi->get('Latlng');
        if ($sLatlng) {
            $aVals['location']['latlng'] = $sLatlng;
            $aVals['location']['name'] = $this->_oApi->get('name');
        }

		if (Phpfox::getLib('parse.format')->isEmpty($aVals['status_info']))
		{
			return $this->_oApi->error('user.add_some_text_to_share', Phpfox::getPhrase('user.add_some_text_to_share'));
		}

		$sStatus = $this->preParse()->prepare($aVals['status_info']);

        //share link
        if ($aLink = Phpfox::getService('accountapi.link')->checkLink($aVals['status_info']))
        {
            if ($iId = Phpfox::getService('accountapi.link')->add($aVals, $aLink['link'], $aLink)) {
                $aFeed = $this->getSingleFeed(true, $iId);
                return $aFeed;
            }
        }

		$aUpdates = $this->database()->select('content')
		->from(Phpfox::getT('user_status'))
		->where('user_id = ' . (int) Phpfox::getUserId())
		->limit(Phpfox::getParam('user.check_status_updates'))
		->order('time_stamp DESC')
		->execute('getSlaveRows');

		$iReplications = 0;
		foreach ($aUpdates as $aUpdate)
		{
			if ($aUpdate['content'] == $sStatus)
			{
				$iReplications++;
			}
		}

		if ($iReplications > 0)
		{
			return $this->_oApi->error('user.you_have_already_added_this_recently_try_adding_something_else', Phpfox::getPhrase('user.you_have_already_added_this_recently_try_adding_something_else'));
		}

		if (empty($aVals['privacy_comment']))
		{
			$aVals['privacy_comment'] = 0;
		}
        $aInsert = array(
            'user_id' => (int) Phpfox::getUserId(),
            'privacy' => $aVals['privacy'],
            'privacy_comment' => $aVals['privacy_comment'],
            'content' => $sStatus,
            'time_stamp' => PHPFOX_TIME
        );
        if (isset($aVals['location']) && isset($aVals['location']['latlng']) && !empty($aVals['location']['latlng']))
        {
            $aMatch = explode(',',$aVals['location']['latlng']);
            $aMatch['latitude'] = floatval($aMatch[0]);
            $aMatch['longitude'] = floatval($aMatch[1]);
            $aInsert['location_latlng'] = json_encode(array('latitude' => $aMatch['latitude'], 'longitude' => $aMatch['longitude']));
        }
        if (isset($aInsert['location_latlng']) && !empty($aInsert['location_latlng']) && isset($aVals['location']) && isset($aVals['location']['name']) && !empty($aVals['location']['name']))
        {
            $aInsert['location_name'] = Phpfox::getLib('parse.input')->clean($aVals['location']['name']);
        }
        //share status
        $iStatusId = $this->database()->insert(Phpfox::getT('user_status'), $aInsert);

		Phpfox::getService('user.process')->notifyTagged($sStatus, $iStatusId, 'status');

		$iId = Phpfox::getService('feed.process')->add('user_status', $iStatusId, $aVals['privacy'], $aVals['privacy_comment']);

        if (!empty($iId)) {
            $aFeed = $this->getSingleFeed(true, $iId);
            return $aFeed;
        }

        return $this->_oApi->error('accountapi.can_not_post_status', 'You can not post this status!');
	}

    /**
     * get single feed
     * @param $bUser
     * @param $iId
     * @param null $aCallback
     * @return array
     */
    private function getSingleFeed($bUser, $iId, $aCallback = null)
    {
        if ($bUser) {
            $aFeed =  Phpfox::getService('feed')->get(Phpfox::getUserId(), $iId);
        } else {
            $aFeed =  Phpfox::getService('feed')->callback($aCallback)->get(Phpfox::getUserId(), $iId);
        }

        //filter word
        $sFilterWord = Phpfox::getService('accountapi.feed')->filterWord($aFeed[0]);

        $aFeed[0]['title_phrase_html'] = '<b><font color="'. $this->sColor .'">' . $aFeed[0]['full_name'] .'</font></b>'. (empty($aFeed[0]['feed_info']) ? '' : ' ' . strip_tags($aFeed[0]['feed_info']));

        if (isset($aFeed[0]['location_name']) && !empty($aFeed[0]['location_name']))
        {
            $aFeed[0]['title_phrase_html'] .= ' ' . Phpfox::getPhrase('feed.at_location', array('location' => $aFeed[0]['location_name']));
        }

        if (Phpfox::getParam('feed.enable_check_in') && Phpfox::getParam('core.google_api_key') != '' && isset($aFeed[0]['location_latlng']))
        {
            $aFeed[0]['location_img'] = $this->coreHttp . 'maps.googleapis.com/maps/api/staticmap?center='
                . $aFeed[0]['location_latlng']['latitude'] . ',' . $aFeed[0]['location_latlng']['longitude'] . '&amp;zoom=16&amp;size=390x250&amp;sensor=false&amp;maptype=roadmap'
                . '&amp;markers=color:red%7Clabel:S%7C' . $aFeed[0]['location_latlng']['latitude'] . ',' . $aFeed[0]['location_latlng']['longitude'];

            $aFeed[0]['location_link'] = $this->coreHttp . 'maps.google.com/maps?daddr=' . $aFeed[0]['location_latlng']['latitude'] . ',' . $aFeed[0]['location_latlng']['longitude'];
        }

        if (!empty ($sFilterWord)) {
            $aFeed[0]['feed_status'] = $sFilterWord;
        }

        return array_values($aFeed);
    }

	/**
	 * Post new status on friend's wall
	 * @return mixed
	 */
	public function postStatusToFriend() {
		$aVals['user_status'] = $this->_oApi->get('status_info');
		$aVals['parent_user_id'] = $this->_oApi->get('parent_user_id', 0);
        $sLatlng = $this->_oApi->get('Latlng');
        if ($sLatlng) {
            $aVals['location']['latlng'] = $sLatlng;
            $aVals['location']['name'] = $this->_oApi->get('name');
        }
		if (Phpfox::getLib('parse.format')->isEmpty($aVals['user_status']))
		{
			return $this->_oApi->error('user.add_some_text_to_share', Phpfox::getPhrase('user.add_some_text_to_share'));
		}

        //share link
        if ($aLink = Phpfox::getService('accountapi.link')->checkLink($aVals['user_status']))
        {
            $aVals['status_info'] = $aVals['user_status'];
            if ($iId = Phpfox::getService('accountapi.link')->add($aVals, $aLink['link'], $aLink)) {
                $aFeed = $this->getSingleFeed(true, $iId);
                return $aFeed;
            }
        }

		if (!isset($aVals['user_status']) || (!$iId = Phpfox::getService('feed.process')->addComment($aVals))) {
			return $this->_oApi->error('accountapi.can_not_post_status', 'You can not post this status!');
		}

        if (!empty($iId)) {
            $aFeed = $this->getSingleFeed(true, $iId);
            return $aFeed;
        }

		return $iId;
	}
	
	/**
	 * Post new status to pages
	 * @return mixed
	 */
	public function postStatusToPage() {
        $sVals = $this->_oApi->get('val');
		$aVals = json_decode(stripslashes($sVals), true);

		if (Phpfox::getLib('parse.format')->isEmpty($aVals['user_status']))
		{
			return $this->_oApi->error('user.add_some_text_to_share', Phpfox::getPhrase('user.add_some_text_to_share'));		
		}			
		
		$aPage = Phpfox::getService('pages')->getPage($aVals['callback_item_id']);

		if (!isset($aPage['page_id']))
		{
			return $this->_oApi->error('pages.unable_to_find_the_page_you_are_trying_to_comment_on', Phpfox::getPhrase('pages.unable_to_find_the_page_you_are_trying_to_comment_on'));
		}
		
		$sLink = Phpfox::getService('pages')->getUrl($aPage['page_id'], $aPage['title'], $aPage['vanity_url']);
		$aCallback = array(
			'module' => 'pages',
			'table_prefix' => 'pages_',
			'link' => $sLink,
			'email_user_id' => $aPage['user_id'],
			'subject' => Phpfox::getPhrase('pages.full_name_wrote_a_comment_on_your_page_title', array('full_name' => Phpfox::getUserBy('full_name'), 'title' => $aPage['title'])),
			'message' => Phpfox::getPhrase('pages.full_name_wrote_a_comment_link', array('full_name' => Phpfox::getUserBy('full_name'), 'link' => $sLink, 'title' => $aPage['title'])),
			'notification' => (empty($aVals['custom_pages_post_as_page']) ? null : 'pages_comment'),
			'feed_id' => 'pages_comment',
			'item_id' => $aPage['page_id']
		);

		$aVals['parent_user_id'] = $aVals['callback_item_id'];

        //share link
        if ($aLink = Phpfox::getService('accountapi.link')->checkLink($aVals['user_status']))
        {
            if ($iId = Phpfox::getService('accountapi.link')->add($aVals, $aLink['link'], $aLink)) {

                $aFeed = $this->getSingleFeed(false, $iId, $aCallback);
                return $aFeed;
            }
        }
		
		if (isset($aVals['user_status']) && ($iId = Phpfox::getService('feed.process')->callback($aCallback)->addComment($aVals)))
		{
			Phpfox::getLib('database')->updateCounter('pages', 'total_comment', 'page_id', $aPage['page_id']);

            if (!empty($iId)) {
                $aFeed = $this->getSingleFeed(false, $iId, $aCallback);
                return $aFeed;
            }
		}
		
		return true;
	}

    /**
     * Post new status to pages
     * @return mixed
     */
    public function postStatusToEvent() {
        $sVals = $this->_oApi->get('val');
        $aVals = json_decode(stripslashes($sVals), true);

        if (Phpfox::getLib('parse.format')->isEmpty($aVals['user_status']))
        {
            return $this->_oApi->error('user.add_some_text_to_share', Phpfox::getPhrase('user.add_some_text_to_share'));
        }

        $aRow = Phpfox::getService('event')->getEvent($aVals['callback_item_id']);


        if (!isset($aRow['event_id']))
        {
            return $this->_oApi->error('pages.unable_to_find_the_page_you_are_trying_to_comment_on', Phpfox::getPhrase('pages.unable_to_find_the_page_you_are_trying_to_comment_on'));
        }

        $sLink = Phpfox::permalink('event', $aRow['event_id'], $aRow['title']);
        $aCallback = array(
            'module' => 'event',
            'table_prefix' => 'event_',
            'link' => $sLink,
            'email_user_id' => $aRow['user_id'],
            'subject' => Phpfox::getPhrase('event.full_name_wrote_a_comment_on_your_event_title', array('full_name' => Phpfox::getUserBy('full_name'), 'title' => $aRow['title'])),
            'message' => Phpfox::getPhrase('event.full_name_wrote_a_comment_on_your_event_message', array('full_name' => Phpfox::getUserBy('full_name'), 'link' => $sLink, 'title' => $aRow['title'])),
            'notification' => 'event_comment',
            'feed_id' => 'event_comment',
            'item_id' => $aRow['event_id']
        );

        $aVals['parent_user_id'] = $aVals['callback_item_id'];

        //share link
        if ($aLink = Phpfox::getService('accountapi.link')->checkLink($aVals['user_status']))
        {
            if ($iId = Phpfox::getService('accountapi.link')->add($aVals, $aLink['link'], $aLink)) {
                $aFeed = $this->getSingleFeed(false, $iId, $aCallback);
                return $aFeed;
            }
        }

        if (isset($aVals['user_status']) && ($iId = Phpfox::getService('feed.process')->callback($aCallback)->addComment($aVals)))
        {
            Phpfox::getLib('database')->updateCounter('event', 'total_comment', 'event_id', $aRow['event_id']);
            if (!empty($iId)) {
                $aFeed = $this->getSingleFeed(false, $iId, $aCallback);
                return $aFeed;
            }
        }


        return true;
    }


    /**
	 * get Item info of all module
	 */
	function getItem() {
		$sModule = $this->_oApi->get('module');
		
		// view single feed
		if ($sTypeId = $this->_oApi->get('type_id')) {
            if ($sTypeId == "event_comment") {
                $aFeed = $this->database()->select('feed.*')->from(Phpfox::getT('event_feed'), 'feed')->where('feed.type_id = \''. $sTypeId. '\' AND feed.item_id = '. $this->_oApi->get('item_id'))->limit(1)->execute('getRow');
                $aEvent = Phpfox::getService('accountapi.event')->getDetail($aFeed['parent_user_id'], $this->_oApi->getUserId());
                Phpfox::getLib('request')->set('callback', json_encode($aEvent['feed_callback']));
            } else {
                $aFeed = $this->database()->select('feed.*')->from(Phpfox::getT('feed'), 'feed')->where('feed.type_id = \''. $sTypeId. '\' AND feed.item_id = '. $this->_oApi->get('item_id'))->limit(1)->execute('getRow');
            }

			if (!$aFeed) {
				Phpfox::getLib('request')->set('type_id', '');
				Phpfox::getLib('request')->set('module', $sTypeId);
				return $this->getItem();
			}
			Phpfox::getLib('request')->set('feed_id', $aFeed['feed_id']);
			Phpfox::getLib('request')->set('user_id', $aFeed['user_id']);
			return $this->getFeed();
		}

		switch ($sModule) {
			case 'photo':
				$iPhoto = $this->_oApi->get('item_id');
				$aPhoto = Phpfox::getService('accountapi.photo')->getPhoto($iPhoto);
				$aPhoto['type_id'] = 'photo';
				$aFeed = Phpfox::getService('accountapi.feed')->processFeed($aPhoto, null, $aPhoto['user_id'], true);
				return $aFeed;

			case 'pages':
				$iPage = $this->_oApi->get('item_id');
				$aPage = Phpfox::getService('accountapi.pages')->getPage($iPage);
				$aPage['type_id'] = 'pages';
				$aPage['feed_callback'] = array(
					'module' => 'pages',
					'table_prefix' => 'pages_',
					'ajax_request' => 'pages.addFeedComment',
					'item_id' => $aPage['page_id'],
					'disable_share' => (Phpfox::getService('user.block')->isBlocked($aPage['user_id'], Phpfox::getUserId()) ? false : true),
					'feed_comment' => 'pages_comment'
				);
				return $aPage;

				break;
            case 'event':
                $aFeed = $this->database()->select('feed.*')->from(Phpfox::getT('event_feed'), 'feed')->where('feed.feed_id = '. $this->_oApi->get('item_id'))->limit(1)->execute('getRow');
                $aEvent = Phpfox::getService('accountapi.event')->getEvent($aFeed['parent_user_id']);
                Phpfox::getLib('request')->set('callback', json_encode($aEvent['feed_callback']));
                Phpfox::getLib('request')->set('feed_id', $aFeed['feed_id']);
                Phpfox::getLib('request')->set('user_id', $aFeed['user_id']);
                return $this->getFeed();

                break;
        }
	}


    /**
     * Post share on wall
     * @return mixed
     */
    public function postShare()
    {

        $aPost['post_type'] = $this->_oApi->get('post_type');
        $aPost['friends'] = json_decode(stripslashes($this->_oApi->get('friends')), true);
        $aPost['post_content'] = $this->_oApi->get('post_content');
        $aPost['parent_feed_id'] = $this->_oApi->get('parent_feed_id');
        $aPost['parent_module_id'] = $this->_oApi->get('parent_module_id');

        if ($aPost['post_type'] == '2')
        {


            if (!isset($aPost['friends']) || (isset($aPost['friends']) && !count($aPost['friends'])))
            {
                Phpfox_Error::set('Select a friend to share this with.');
            }
            else
            {

                $iCnt = 0;

                foreach ($aPost['friends'] as $iFriendId)
                {

                    $aVals = array(
                        'user_status' => $aPost['post_content'],
                        'parent_user_id' => $iFriendId,
                        'parent_feed_id' => $aPost['parent_feed_id'],
                        'parent_module_id' => $aPost['parent_module_id']
                    );

                    if (Phpfox::getService('user.privacy')->hasAccess($iFriendId, 'feed.share_on_wall') && Phpfox::getUserParam('profile.can_post_comment_on_profile'))
                    {
                        $iCnt++;

                        Phpfox::getService('feed.process')->addComment($aVals);
                    }
                }

                $sMessage = Phpfox::getPhrase('feed.successfully_shared_this_item_on_your_friends_wall');
                if (!$iCnt)
                {
                    $sMessage = Phpfox::getPhrase('user.unable_to_share_this_post_due_to_privacy_settings');
                }


                return $sMessage;
            }

            return;
        }

        $aVals = array(
            'user_status' => $aPost['post_content'],
            'privacy' => '0',
            'privacy_comment' => '0',
            'parent_feed_id' => $aPost['parent_feed_id'],
            'parent_module_id' => $aPost['parent_module_id']
        );

        if (($iId = Phpfox::getService('user.process')->updateStatus($aVals)))
        {
            Phpfox::getPhrase('feed.successfully_shared_this_item');
        }
    }


    public function messageShare(){
        $aPost['to'] = json_decode(stripslashes($this->_oApi->get('friends')), true);
        $aPost['subject'] = $this->_oApi->get('subject');
        $aPost['message'] = $this->_oApi->get('message');

        if(Phpfox::getService('mail.process')->add($aPost)){
            Phpfox::getPhrase('share.message_successfully_sent');
        }

    }

    public function emailShare(){
        $aPost['to'] = $this->_oApi->get('email');
        $aPost['subject'] = $this->_oApi->get('subject');
        $aPost['message'] = $this->_oApi->get('message');

        if (Phpfox::getService('share.process')->sendEmails($aPost))
        {
            Phpfox::getPhrase('share.message_successfully_sent');
        }

    }


    /**
	 * Send user cloud id
	 */
	function sendCloudUser() {
		Phpfox::getService('accountapi.cloud')->addUser(Phpfox::getUserId(), $this->_oApi->get('cloud_user_id'));
		
		return true;
	}
	
	/**
	 * Search for Friends
	 * 
	 */
	function searchFriend() {
		$aUsers = Phpfox::getService('friend')->getFromCache(false,$this->_oApi->get('search_for'));
		
		return $aUsers;
	}
	
	/**
	 * Add friend
	 */
	public function addFriendRequest()
	{
		Phpfox::getUserParam('friend.can_add_friends', true);
		
		$aUser = Phpfox::getService('user')->getUser($this->_oApi->get('user_id'), 'u.user_id, u.user_name, u.full_name, u.user_image, u.server_id');
		
		if (Phpfox::getUserId() === $aUser['user_id'])
		{
			return array(
				'success' => false,
			);
		}
		elseif (Phpfox::getService('friend.request')->isRequested(Phpfox::getUserId(), $aUser['user_id']))
		{	
			return array(
				'success' => false,
				'notice' => Phpfox::getPhrase('friend.you_have_already_asked_full_name_to_be_your_friend', array('full_name' => $aUser['full_name']))
			);
		}		
		elseif (Phpfox::getService('friend.request')->isRequested($aUser['user_id'], Phpfox::getUserId()))
		{
			return array(
				'success' => false,
				'notice' => Phpfox::getPhrase('friend.full_name_has_already_asked_to_be_your_friend', array('full_name' => $aUser['full_name']))
			);
		}
		elseif (Phpfox::getService('friend')->isFriend($aUser['user_id'], Phpfox::getUserId()))
		{	
			return array(
				'success' => false,
				'notice' => Phpfox::getPhrase('friend.you_are_already_friends_with_this_user')
			);
		}
		
		if (Phpfox::getService('friend.request.process')->add(Phpfox::getUserId(), $this->_oApi->get('user_id'), 0))
		{	
			return array(
				'success' => true,
				'notice' => Phpfox::getPhrase('friend.friend_request_successfully_sent')
			);
		}			
	}

	/**
	 * Redirect
	 */
	function redirect() {
		$sUrl = $this->_oApi->get('url');

		$iUserId = Phpfox::getUserId();
		$aVals = Phpfox::getService('user')->get($iUserId);

        //get facebook id
        $iFacebookId = Phpfox::getService('accountapi.facebook')->getFacebookId($iUserId);

		if (Phpfox::getParam('user.login_type') == 'email') {
			$aVals['login'] = $aVals['email'];
		}  else {
			$aVals['login'] = $aVals['user_name'];
		}

        if (!empty($iFacebookId)) {
            Phpfox::getLib('database')->update(Phpfox::getT('fbconnect'), array('is_unlinked' => 1), 'user_id = ' . (int) $iUserId);
            list($bLogged, $aUser) = (Phpfox::getService('user.auth')->login($aVals['user_name'], null, false, 'user_name', true));
        } else {
            list($bLogged, $aUser) = (Phpfox::getService('user.auth')->login($aVals['login'], $aVals['password'], false, Phpfox::getParam('user.login_type'), true));
        }

		if (is_numeric(strpos($sUrl, Phpfox::getParam('core.path')))) {
			$sUrl = str_replace(Phpfox::getParam('core.path'), '', $sUrl);
			$sUrl = str_replace('index.php?do=/', '', $sUrl);
			$sUrl = Phpfox::getParam('core.path'). 'index.php?do=/mobile/'. $sUrl;
			Phpfox::getLib('session')->set('social_app', true);
		}

		Phpfox::getLib('url')->send($sUrl);
	}


    /**
     * Get all page
     * @return mixed
     */
     function getAllPage()
     {
     	$iPage = ((int) $this->_oApi->get('page') ? (int) $this->_oApi->get('page') : 1);
		list($iCnt, $aPages) = Phpfox::getService('accountapi.pages')->getAllPage($iPage);

        foreach ($aPages as $iKey => $aPage) {
            $aPages[$iKey]['user_image_path'] = sprintf($aPages[$iKey]['image'], '_75_square');

        }

		$this->_oApi->setTotal($iCnt);

        if (empty($aPages)) {
            return array('notice' => Phpfox::getPhrase('pages.no_pages_found'));
        }

		return $aPages;
     }
	/**
	 * Get list contact user
	 */
	function getFilterContact() {

		$iUserId = $this ->_oApi->get('user_id');
		
		$_aCallback = array();
		// $sJsonCallback = "[[\"auser@brodev.com\"],[\"test@yahoo.com\"],[\"testing1@brodev.com\"],[\"testman@gmail.com\"],[\"Porta@brodev.com\"]]";
		if ($sJsonCallback = $this->_oApi->get('callback')) {
			$_aCallback = json_decode($sJsonCallback, true);
		}

		$_aContacts = array();
		$sEmail = Phpfox::getService('user') -> getUser($iUserId, 'u.email');

		foreach ($_aCallback as $iKey => $aContact) {
			//remove email of myself
			if ($aContact == $sEmail['email']) {
				continue;
			}
			$_aContacts[$iKey] = $aContact;
		}

		$aListContact = Phpfox::getService('accountapi.contact')->getListContact($iUserId, $_aContacts);
		//get list unregistered user
		$_aList = Phpfox::getService('accountapi.contact')->getUserNotFriend($iUserId, $aListContact);
		//get list registered user but not is friend
		$aList = Phpfox::getService('accountapi.contact')->getNotUser($_aContacts, $aListContact);

		$aResult = array('notUser' => array('contact' => $aList, 'length' => count($aList)), 'isUser' => array('contact' => $_aList, 'length' => count($_aList)));

		return $aResult;
	}

	/**
	 * invite email to inviter
	 */
	function inviteEmail() 
	{
		$iUserId = $this -> _oApi -> get('user_id');
		// $sJsonCallback = "[\"auser1@brodev.com\",\"admin1@brodev.com\",\"test@yahoo.com\",\"testing1@brodev.com\",\"testman1@gmail.com\"]";
		$_aCallback = array();
		if ($sJsonCallback = $this->_oApi->get('callback')) {
			$_aCallback = json_decode($sJsonCallback, true);
		}

		$_aContacts = array();
		foreach ($_aCallback as $iKey => $aValue) {
			if (is_array($aValue) && count($aValue) > 0) {
				$aValue = $aValue[0];
			}
			$_aContacts[$iKey] = $this->database()->escape($aValue);
		}

		$aResult = Phpfox::getService('accountapi.contact')->emailInviter($iUserId, $_aContacts);

		return $aResult;
	}

	/**
	 * add friend from contact
	 */
	function getRequestFriend() {

		Phpfox::getUserParam('friend.can_add_friends', true);

		// $iUserId = $this->_oApi->get('user_id');
		$_aCallback = array();

		if ($sJsonCallback = $this -> _oApi -> get('callback')) {
			$_aCallback = json_decode($sJsonCallback, true);
		}

		//send request add friend to user
		$aResult = array();
		foreach ($_aCallback as $iKey => $aValue) {
			if (is_array($aValue) && count($aValue) > 0) {
				$aValue = $aValue[0];
			}
			$aResult = Phpfox::getService('accountapi.friend')->requestAddFriend($_aCallback[$iKey], $_aCallback[$iKey]['user_id']);
		}

		return $aResult;
	}
	
	/**
	 * import friends to contact
	 */
	function importContact() {
		
		$aResult = array();
		$iUserId = $this->_oApi->get('user_id');
		if (!$iUserId) {
			$iUserId = $this->_oApi->getUserId();
		}
		
		$iPage = ((int) $this->_oApi->get('page') ? (int) $this->_oApi->get('page') : 1);;
		if (!$iPage) {
			$iPage = 1;
		}
		$iPageSize = Phpfox::getParam('accountapi.how_many_friends_to_import');
		$iSize = 1;
		$aResult = Phpfox::getService('accountapi.friend')->importFriendToContact($iUserId, $iPage, $iSize, $iPageSize);
		
		return $aResult;
	}

    /**
     * Get forum
     * @return mixed
     */
    public function getForums() {
        $aForum = Phpfox::getService('accountapi.forum')->getForums();
        return $aForum;
    }

    /**
     * Get my threads
     *
     * @return mixed
     */
    public function getMyThreads() {

        //get page number
        $iPage = ((int) $this->_oApi->get('page') ? (int) $this->_oApi->get('page') : 1);
        /**
         * get thread name
         * my threads = my-thread
         * my posts = new
        */
        $sThread = $this->_oApi->get('thread');

        //get forum id
        $iForumId = (int) $this->_oApi->get('forum_id');

        $aThreads = Phpfox::getService('accountapi.forum')->getMyThreadsPosts($iPage, $sThread, 10, $iForumId);

        return $aThreads;
    }

    /**
     * Add thread forum
     *
     * @return array
     */
    public function addThread()
    {
        //get forum id
        $iForumId = $this->_oApi->get('forum_id');
        //get thread title
        $sTitle = $this->_oApi->get('title');
        //get thread text
        $sText = $this->_oApi->get('text');
        //get subcribe
        $iSubcribe = $this->_oApi->get('subcribe');

        $iId = Phpfox::getService('accountapi.forum')->addThread($iForumId, $sTitle, $sText, $iSubcribe);

        if (!empty($iId))
            return $iId;

        return array();
    }

    /**
     * Add post to thread
     *
     * @return mixed
     */
    public function addPost()
    {
        //get thread id
        $iThreadId = $this->_oApi->get('thread_id');
        //get text
        $sText = $this->_oApi->get('text');
        //get subcribe
        $iSubcribe = $this->_oApi->get('subcribe');

        $iId = Phpfox::getService('accountapi.forum')->addPost($iThreadId, $sText, $iSubcribe);

        return $iId;
    }

    /**
     * Get sub forum
     * @return mixed
     */
    public function getSubForums() {
        //get forum id
        $iForumId = (int) $this->_oApi->get('forum_id');

        $aSubForums = Phpfox::getService('accountapi.forum')->getSubForums($iForumId);

        return $aSubForums;
    }


    public function getThreadById() {
        //get forum id
        $iThreadId = (int) $this->_oApi->get('thread_id');

        //get post id
        $iPost = (int) $this->_oApi->get('post_id') ? (int) $this->_oApi->get('post_id') : null;

        //get page
        $iPage = ((int) $this->_oApi->get('page') ? (int) $this->_oApi->get('page') : 1);

        $aThreads = Phpfox::getService('accountapi.forum')->getThreadById($iThreadId, $iPage, 10, $iPost);

        return $aThreads;
    }

    public function getThreadByPostId() {
        //get post id
        $iPostId = (int) $this->_oApi->get('post_id');

        $aThreads = Phpfox::getService('accountapi.forum')->getThreadByPostId($iPostId);

        return $aThreads;
     }

    /**
     * register gcm
     */
    public function registergcm() {
        //get email
        $sEmail = $this->_oApi->get('email');
        //get user id
        $iUserId = (int) $this->_oApi->get('userId');
        //get gcm reg id
        $sRegId = $this->_oApi->get('regId');

        if(!empty($sEmail) && !empty($iUserId) && !empty($sRegId)) {
            Phpfox::getService('accountapi.gcm')->storeGCMUser($sEmail, $sRegId, $iUserId);
            return array('registerGCM' => 'success');
        }

        return array('registerGCM' => 'fail');
    }

    /**
     * Register Apple push notification service
     */
    public function registerAPNS() {
        $sEmail = $this->_oApi->get('email');
        //get user id
        $iUserId = (int) $this->_oApi->get('userId');
        //get apns reg id
        $sToken = $this->_oApi->get('token_apns');

        if(!empty($sEmail) && !empty($iUserId) && !empty($sToken)) {
            Phpfox::getService('accountapi.apns')->storeAPNSUser($sEmail, $sToken, $iUserId);
            return array('APNS' => 'success');
        }

        return array('APNS' => 'fail');
    }

    public function getSearchFriend() {
        $iUserId = (int) $this->_oApi->get('user_id');
        $sFind = $this->_oApi->get('find');

        if (Phpfox::isModule('messenger') && Phpfox::getParam('messenger.key')) {
            $aFriends = Phpfox::getService('messenger.socialapp')->searchFriend($iUserId, $sFind);

            if (empty ($aFriends)) {
                return array(
                    'error' => false,
                    'aSearchResults' => Phpfox::getPhrase('search.no_search_results_found'),
                    'count' => 0
                );
            }

            return array(
                'error' => false,
                'aSearchResults' => $aFriends,
                'count' => count($aFriends)
            );

            return $aFriends;
        }

        $aFriends = Phpfox::getService('accountapi.search')->searchFriend($iUserId, $sFind);

        return $aFriends;
    }

    /**
     * get list blogs
     * @return array
     */
    public function getBlogs() {
        $iUserId = $this->_oApi->getUserId();
        $sList = $this->_oApi->get('list');
        $iCategory = $this->_oApi->get('category');
        $iPage = $this->_oApi->get('page');

        list($iCnt, $aReturns) = Phpfox::getService('accountapi.blog')->getBlogs($sList, $iCategory, $iPage, $iUserId);
        Phpfox::getService('api')->setTotal($iCnt);
        return $aReturns;

    }

    /**
     * get 1 blog detail
     * @return mixed
     */
    public function getBlog() {
        $iBlogId = $this->_oApi->get('blogId');
        return Phpfox::getService('accountapi.blog')->getBlogDetail($iBlogId);
    }
    /**
     * get all Categories
     * @return mixed
     */
    public function getBlogCategories() {
        $aCategories = Phpfox::getService('blog.category')->getCategories('c.user_id = 0');
        return $aCategories;
    }

    /**
     * get all categories
     * @return mixed
     */
    public function getAllBlogCategories() {
        $aCategories = Phpfox::getService('blog.category')->getCategories('1 = 1');
        return $aCategories;
    }

    /**
     * add new blog and return notice
     * @return array
     */
    public function postBlog() {
        $aData = array(
            'title' =>  $this->_oApi->get('title'),
            'text' => $this->_oApi->get('content'),
            'selected_categories' =>  $this->_oApi->get('category'),
            'privacy' => $this->_oApi->get('privacy')
        );
        if (Phpfox::getService('accountapi.blog')->addBlog($aData)) {
            return array(
                'success' => true,
                'notice' => Phpfox::getPhrase('blog.your_blog_has_been_added')
            );
        } else {
            return array(
                'success' => false,
                'notice' => Phpfox::getPhrase('blog.no_blogs_added')
            );
        }

    }
    /**
     * get list blogs
     * @return array
     */
    public function getVideos() {
        $iUserId = $this->_oApi->getUserId();
        $sList = $this->_oApi->get('list');
        $iCategory = $this->_oApi->get('category');
        $iPage = $this->_oApi->get('page');

        list($iCount, $aReturns) = Phpfox::getService('accountapi.video')->getVideos($sList, $iCategory, $iPage, $iUserId);
        Phpfox::getService('api')->setTotal($iCount);
        return $aReturns;

    }

    /**
     * @return mixed
     */
    public function getVideo() {
        $iId = $this->_oApi->get('videoId');
        return Phpfox::getService('accountapi.video')->getDetail($iId);
    }

    /**
     * Get video by id
     * @return mixed
     */
    public function getVideoById() {
        $iId = $this->_oApi->get('videoId');
        return Phpfox::getService('accountapi.video')->get($iId);
    }

    /**
     * get all video categories
     * @return mixed
     */
    public function getVideoCategories() {
        $iCategory = $this->_oApi->get('category_id');
        if ($iCategory == 0) {
            $iCategory = null;
        }
        $aCategories = Phpfox::getService('video.category')->getForBrowse($iCategory);
        return $aCategories;
    }

    /**
     * get all market place categories
     * @return mixed
     */
    public function getMarketPlaceCategories() {
        $iCategory = $this->_oApi->get('category_id');
        if ($iCategory == 0) {
            $iCategory = null;
        }
        $aCategories = Phpfox::getService('marketplace.category')->getForBrowse($iCategory);

        return $aCategories;
    }

    public function getListings() {
        $iUserId = $this->_oApi->getUserId();
        $sList = $this->_oApi->get('list');
        $iCategory = $this->_oApi->get('category');
        $iPage = $this->_oApi->get('page');

        list($iCnt, $aReturns) = Phpfox::getService('accountapi.marketplace')->getListings($sList, $iCategory, $iPage, $iUserId);
        $this->_oApi->setTotal($iCnt);
        return $aReturns;

    }
    public function getListing() {
        $iId = $this->_oApi->get('listingId');
        return Phpfox::getService('accountapi.marketplace')->getDetail($iId);
    }

    public function addListing() {
        $aData = array(
            'title' => $this->_oApi->get('title'),
            'mini_description' => $this->_oApi->get('mini_description'),
            'description' => $this->_oApi->get('description'),
            'currency_id' => $this->_oApi->get('currency_id'),
            'price' => $this->_oApi->get('price'),
            'country_iso' => $this->_oApi->get('country_iso'),
            'city' => $this->_oApi->get('city'),
            'postal_code' => $this->_oApi->get('postal_code'),
            'privacy' => $this->_oApi->get('privacy'),
            'privacy_comment' => $this->_oApi->get('privacy_comment'),
            'category' => array($this->_oApi->get('category_id'))
        );
        if ($iId = Phpfox::getService('marketplace.process')->add($aData)) {
            if (isset($_FILES['image'])) {
                Phpfox::getService('marketplace.process')->update($iId, $aData);
            }
            return array(
                'success' => true,
                'notice' => Phpfox::getPhrase('marketplace.listing_successfully_added')
            );
        } else {
            return array(
                'success' => false,
                'notice' => Phpfox::getPhrase('blog.no_blogs_added')
            );
        }
    }

    public function findNotFriend() {
        $iUserId = $this->_oApi->getUserId();
        $oEmail = $this->_oApi->get('emails');
        list($iCount, $aUsers) = Phpfox::getService('accountapi.user')->getUserBy($iUserId, $oEmail, "email");
        $this->_oApi->setTotal($iCount);
        return array_values($aUsers);
    }

    /**
     * get user for setting
     * @return mixed
     */
    public function getUserSetting() {
        $iUserId = $this->_oApi->getUserId();
        $aUser = Phpfox::getService('accountapi.user')->getUserSetting($iUserId);
        return $aUser;
    }

    public function updateUserSeting() {
        $oServiceAccountapiCore = Phpfox::getService('accountapi.core');
        $iUserId = $this->_oApi->getUserId();
        $aVals = array(
            'full_name' => $this->_oApi->get('full_name'),
            'user_name' => $this->_oApi->get('user_name'),
            'old_user_name' => $this->_oApi->get('old_user_name'),
            'email' => $this->_oApi->get('email'),
            'language_id' => $this->_oApi->get('language_id'),
            'time_zone' => $this->_oApi->get('time_zone'),
            'default_currency' => $this->_oApi->get('default_currency'),
        );

        if (Phpfox::getParam('user.split_full_name'))
        {
            preg_match('/(.*) (.*)/', $aVals['full_name'], $aNameMatches);
            if (isset($aNameMatches[1]) && isset($aNameMatches[2]))
            {
                $aVals['first_name'] = $aNameMatches[1];
                $aVals['last_name'] = $aNameMatches[2];
            }
            else
            {
                $aVals['first_name'] = $aVals['full_name'];
                $aVals['last_name'] = " ";
            }
        }

        Phpfox::getService('user.process')->update($iUserId, $aVals, array(
            'changes_allowed' => Phpfox::getUserParam('user.total_times_can_change_user_name'),
            'total_user_change' => $this->_oApi->get('total_user_change'),
            'full_name_changes_allowed' => Phpfox::getUserParam('user.total_times_can_change_own_full_name'),
            'total_full_name_change' => $this->_oApi->get('total_full_name_change'),
            'current_full_name' => $this->_oApi->get('current_full_name')), true);
        return array('notice' => Phpfox::getPhrase('user.account_settings_updated'), 'phrases' => $oServiceAccountapiCore->getPhrases($aVals['language_id']));
    }

    public function changePassword() {

        $sCurrentPassword = $this->_oApi->get('current_password');
        $sOldPassword = $this->_oApi->get('password');
        $sSaltPassword = $this->_oApi->get('password_salt');
        $sNewPassword = $this->_oApi->get('new_password');

        if (Phpfox::getLib('hash')->setHash($sCurrentPassword, $sSaltPassword) != $sOldPassword)
        {
            return array('notice' => Phpfox_Error::set(Phpfox::getPhrase('user.your_current_password_does_not_match_your_old_password')));
        }
        $aVals = array(
            'old_password' => $sOldPassword,
            'new_password' => $sNewPassword,
            'confirm_password' => $sNewPassword
        );
        $sSalt = $this->_getSalt();
        $aInsert = array();
        $aInsert['password'] = Phpfox::getLib('hash')->setHash($aVals['new_password'], $sSalt);
        $aInsert['password_salt'] = $sSalt;

        $this->database()->update(Phpfox::getT('user'), $aInsert, 'user_id = ' . $this->_oApi->getUserId());

        $this->database()->insert(Phpfox::getT('user_ip'), array(
                'user_id' => $this->_oApi->getUserId(),
                'type_id' => 'update_password',
                'ip_address' => Phpfox::getIp(),
                'time_stamp' => PHPFOX_TIME
            )
        );
    }
    private function _getSalt($iTotal = 3)
    {
        $sSalt = '';
        for ($i = 0; $i < $iTotal; $i++)
        {
            $sSalt .= chr(rand(33, 91));
        }

        return $sSalt;
    }

    /**
     * check can report
     * @return array
     */
    public function canReport()
    {
        $iItemId = $this->_oApi->get('item_id');
        $sType = $this->_oApi->get('type');
        $bCanReport = Phpfox::getService('report')->canReport($sType, $iItemId);
        $aReturn['success'] = $bCanReport;
        if (!$bCanReport) {
            $aReturn['notice'] = Phpfox::getPhrase('report.you_have_already_reported_this_item');
        } else {
            $aReturn['options'] = Phpfox::getService('report')->getOptions($sType);
        }
        return array($aReturn);
    }

    /**
     * add report
     */
    public function addReport() {
        $iItemId = $this->_oApi->get('item_id');
        $sType = $this->_oApi->get('type');
        $iOptionId = $this->_oApi->get('option');
        $sMessage = $this->_oApi->get('message');
        Phpfox::getService('report.data.process')->add($iOptionId, $sType, $iItemId, $sMessage);
    }

    /**
     * delete report
     * @return mixed
     */
    public function deleteReport() {
        $iReportId = $this->_oApi->get('report_id');
        Phpfox::getService('report')->delete($iReportId);
        return;
    }

    public function canSendMessage() {
        $iUserId = $this->_oApi->get('user_id');
        return array('success' => Phpfox::getService('mail')->canMessageUser($iUserId));

    }

    /**
     * get list event
     * @return mixed
     */
    public function getEvents() {
        $iUserId = $this->_oApi->getUserId();
        $sList = $this->_oApi->get('list');
        $iCategory = $this->_oApi->get('category');
        $iPage = $this->_oApi->get('page');

        list($iCnt, $aReturns) = Phpfox::getService('accountapi.oldevent')->getEvents($sList, $iCategory, $iPage, $iUserId);
        $this->_oApi->setTotal($iCnt);
        return $aReturns;

    }

    /**
     * get list event
     * @return mixed
     */
    public function getEventsV2() {
        $iUserId = $this->_oApi->getUserId();
        $sView = $this->_oApi->get('view', false);
        $iCategory = $this->_oApi->get('category');
        $iPage = $this->_oApi->get('page');

        list($iCnt, $aReturns) = Phpfox::getService('accountapi.event')->get($iUserId, $iCategory, $sView, $iPage, 20);
        $this->_oApi->setTotal($iCnt);

        if (empty($aReturns)) {
            return array('notice' => Phpfox::getPhrase('event.no_events_found'));
        }
        return $aReturns;

    }

    /**
     * get event info
     * @return mixed
     */
    public function getEvent() {
        $iUserId = $this->_oApi->getUserId();
        $iEventId = $this->_oApi->get('event_id');
        $aReturns = Phpfox::getService('accountapi.oldevent')->getDetail($iEventId, $iUserId);
        return $aReturns;

    }

    /**
     * get event info
     * @return mixed
     */
    public function getEventV2() {
        $iEventId = $this->_oApi->get('event_id');
        $aReturns = Phpfox::getService('accountapi.event')->getEvent($iEventId);
        return $aReturns;
    }

    /**
     * get all event categories
     * @return mixed
     */
    public function getEventCategories() {
        $iCategory = $this->_oApi->get('category_id', null);
        if ($iCategory == 0) {
            $iCategory = null;
        }

        $aCategories = Phpfox::getService('event.category')->getForBrowse($iCategory);

        if(is_array($aCategories)) {
            foreach($aCategories as $iKey => $aCategory) {
                $aCategories[$iKey]['name'] = Phpfox::getLib('locale')->convert($aCategory['name']);
            }
            return $aCategories;
        }

        return array();
    }

    /**
     * Update rsvp
     */
    public function updateRSVP() {
        $iUserId = $this->_oApi->getUserId();
        $iRsvpId = $this->_oApi->get('rsvp_id');
        $iEventId = $this->_oApi->get('event_id');
        Phpfox::getService('event.process')->addRsvp($iEventId, $iRsvpId, $iUserId);
    }

    /**
     * get event category for add
     * @return mixed
     */
    public function getEventCategoryForAdd() {
        $iCategory = $this->_oApi->get('category_id', null);

        $aCategories = Phpfox::getService('accountapi.event')->getCategories($iCategory);

        return array(
            'children' => (!empty($iCategory) ? true : false),
            'category' => $aCategories);
    }

    /**
     * Get event member
     * @return mixed
     */
    public function getEventMemberV2() {
        $iEventId = $this->_oApi->get('event_id');
        $aReturn = Phpfox::getService('accountapi.event')->getMember($iEventId);
        return $aReturn;
    }

    public function getEventMember (){
        $iEventId = $this->_oApi->get('event_id');
        $iPage = $this->_oApi->get('page');
        $aReturn = Phpfox::getService('accountapi.oldevent')->getMember($iEventId, $iPage);
        $this->_oApi->setTotal($aReturn[4]);
        return $aReturn;
    }

    /**
     * get event member by rsvp
     * @return mixed
     */
    public function getEventMemberByRsvp() {
        $iEventId = $this->_oApi->get('event_id');
        $iRsvp = $this->_oApi->get('rsvp');
        $iPage = $this->_oApi->get('page');

        list($iCnt, $aList) = Phpfox::getService('accountapi.event')->getMemberLst($iEventId, $iRsvp, $iPage);
        $this->_oApi->setTotal($iCnt);

        return $aList;
    }

    /**
     * add new blog and return notice
     * @return array
     */
    public function postEvent() {
        $aData = array(
            'title' =>  $this->_oApi->get('title'),
            'description' => $this->_oApi->get('content'),
            'category' => explode (",", $this->_oApi->get('categorys')),
            'location' => $this->_oApi->get('location'),
            'privacy' => $this->_oApi->get('privacy'),
            'privacy_comment' => $this->_oApi->get('privacy_comment'),
            'start_time' => $this->_oApi->get('start_time'),
            'end_time' => $this->_oApi->get('end_time')

        );
        if ($iEventId = Phpfox::getService('accountapi.oldevent')->add($aData)) {
            return array(
                'success' => true,
                'notice' => " ",
                'aEvent' => Phpfox::getService('accountapi.event')->getDetail($iEventId,  $this->_oApi->getUserId())
            );
        } else {
            return array(
                'success' => false,
                'notice' => Phpfox::getPhrase('event.no_events_have_been_created')
            );
        }

    }

    /**
     * add new blog and return notice
     * @return array
     */
    public function createNewEvent() {
        $aInsert = array(
            'title' => $this->_oApi->get('title'),
            'location' => $this->_oApi->get('location'),
            'country_iso' => $this->_oApi->get('country_iso'),
            'country_child_id' => $this->_oApi->get('country_child_id'),
            'postal_code' => $this->_oApi->get('postal_code'),
            'city' => $this->_oApi->get('city'),
            'address' => $this->_oApi->get('address'),
            'description' => $this->_oApi->get('description'),
            'category' => explode (",", $this->_oApi->get('categories')),

            'privacy' => $this->_oApi->get('privacy'),
            'privacy_comment' => $this->_oApi->get('privacy_comment'),

            'start_hour' => $this->_oApi->get('start_hour'),
            'start_minute' => $this->_oApi->get('start_minute'),
            'start_month' => $this->_oApi->get('start_month'),
            'start_day' => $this->_oApi->get('start_day'),
            'start_year' => $this->_oApi->get('start_year'),

            'end_hour' => $this->_oApi->get('end_hour'),
            'end_minute' => $this->_oApi->get('end_minute'),
            'end_month' => $this->_oApi->get('end_month'),
            'end_day' => $this->_oApi->get('end_day'),
            'end_year' => $this->_oApi->get('end_year')
        );

        if ($iEventId = Phpfox::getService('event.process')->add($aInsert)) {
            return array(
                'notice' => Phpfox::getPhrase('accountapi.please_pull_to_refresh_your_list')
            );
        } else {
            return array(
                'notice' => Phpfox::getPhrase('event.no_events_have_been_created')
            );
        }
    }

    public function getListCountries() {
        $aCountries = Phpfox::getService('core.country')->get();
        $sUserCountryIso = PHpfox::getUserBy('country_iso');
        $aReturn = array();
        foreach($aCountries as $sIso => $sCountryName) {
            $aReturn[] = array(
                'iso' => $sIso,
                'name' => $sCountryName,
                'default' => $sIso == $sUserCountryIso ? true : false
            );
        }
        return $aReturn;
    }

    /**
     * Get countries
     * @return mixed
     */
    public function getCountries() {
        $sValue = $this->_oApi->get('iso');
        $aReturn = Phpfox::getService('accountapi.event')->getCountries($sValue);
        return array(
            'children' => (!empty($sValue) ? true : false),
            'country' => $aReturn);
    }

    /**
     * Get notification setting of user
     * @return array
     */
    public function getNotificationSettings() {
        $iUserId = $this->_oApi->getUserId();

        $aNotifications = Phpfox::getService('accountapi.setting')->getNotifications($iUserId);

        if (empty($aNotifications)) {
            return array();
        }

        return $aNotifications;
    }

    /**
     * Update notification setting
     * @return string
     */
    public function updateNotificationSettings() {
        $iUserId = $this->_oApi->getUserId();

        if ($aCallback = $this->_oApi->get('notification')) {
            $_aCallback = json_decode($aCallback, true);
        }

        if ($aResult = Phpfox::getService('accountapi.setting')->update($iUserId, $_aCallback)) {
            return Phpfox::getPhrase('accountapi.notification_settings_successfully_updated');
        }

        return Phpfox::getPhrase('accountapi.notification_settings_update_failed');
    }

    /**
     * Process output when return api
     * @param $aOutput
     * @return mixed
     */
    public function processOutput($aOutput)
    {
        if (is_array($aOutput['output']))
        {
            foreach($aOutput['output'] as $iKey => $aData) {
                if (is_array($aData)) {
                    foreach ($aData as $sKey => $sValue) {
                        if (!is_array($sValue)) {
                            if (in_array($sKey, $this->_aContent)) {
                                $aOutput['output'][$iKey][$sKey] = $this->_processString($sValue);
                            } else if (!is_array($sValue)){
                                $aOutput['output'][$iKey][$sKey] = Phpfox::getLib('locale')->convert($sValue);
                            }
                        }
                        else {
                            foreach($sValue as $valueKey => $valueData) {

                                if (!is_array($valueData) && in_array($valueKey, $this->_aContent)) {
                                    $aOutput['output'][$iKey][$sKey][$valueKey] = $this->_processString($valueData);
                                } else if (!is_array($valueData)){
                                    $aOutput['output'][$iKey][$sKey][$valueKey] = Phpfox::getLib('locale')->convert($valueData);
                                }
                            }
                        }
                    }
                } else {

                    if (in_array($iKey, $this->_aContent)) {

                        $aOutput['output'][$iKey] = $this->_processString($aData);
                    } else if (!is_array($aData)){
                         $aOutput['output'][$iKey] = Phpfox::getLib('locale')->convert($aData);
                    }
                }
            }
        }

        return $aOutput;
    }

    /**
     * get list emoticon
     * @return mixed
     */
    public function getEmotionList() {
        return Phpfox::getService('accountapi.emoticon')->getEmoticonList();
    }
}
