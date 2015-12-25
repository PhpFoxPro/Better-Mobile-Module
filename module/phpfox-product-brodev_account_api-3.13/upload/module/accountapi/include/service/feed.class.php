<?php
defined('PHPFOX') or die('NO DICE!');

class Accountapi_Service_Feed extends Phpfox_Service
{
    private $sColor;

    public function __construct() {
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

    public function processFeed($aRow, $sKey, $iUserid, $bFirstCheckOnComments)
    {
        $oOutput = Phpfox::getLib('parse.output');
        $sOrgType = $aRow['type_id'];
        switch ($aRow['type_id']) {
            case 'comment_profile':
            case 'comment_profile_my':
                $aRow['type_id'] = 'accountapi_profile_comment';
                break;
            case 'profile_info':
                $aRow['type_id'] = 'accountapi_custom';
                break;
            case 'comment_photo':
                $aRow['type_id'] = 'accountapi_photo_comment';
                break;
            case 'comment_blog':
                $aRow['type_id'] = 'accountapi_blog_comment';
                break;
            case 'comment_video':
                $aRow['type_id'] = 'accountapi_video_comment';
                break;
            case 'comment_group':
                $aRow['type_id'] = 'accountapi_pages_comment';
                break;
            case 'music_song':
                $aRow['type_id'] = 'accountapi_music';
                break;
            case 'music_song_comment':
                $aRow['type_id'] = 'accountapi_music_comment';
                break;
            case 'photo':
            case 'video':
            case 'photo_comment':
            case 'video_comment':
            case 'link':
            case 'poll':
            case 'music':
            case 'pages':
                //case 'user_status':
                $aRow['type_id'] = 'accountapi_' . $aRow['type_id'];
                break;
        }

        if (preg_match('/(.*)_feedlike/i', $aRow['type_id'])
            || $aRow['type_id'] == 'profile_design'
        ) {
            $this->database()->delete(Phpfox::getT('feed'), 'feed_id = ' . (int)$aRow['feed_id']);

            return false;
        }

        $aRow['user_image'] = Phpfox::getLib('image.helper')->display(array(
            'user' => $aRow,
            'server_id' => $aRow['user_server_id'],
            'suffix' => '_50_square',
            'return_url' => true,
        ));

        if (!empty($aRow['feed_id'])) {
            if (!Phpfox::hasCallback($aRow['type_id'], 'getActivityFeed')) {
                return false;
            }
            $aFeed = Phpfox::callback($aRow['type_id'] . '.getActivityFeed', $aRow, null);
        } else {
            $aFeed = Phpfox::callback($aRow['type_id'] . '.getParamFeed', $aRow, null);
        }


        if ($aFeed === false) {
            return false;
        }

        if (!empty($aRow['feed_reference']) && $aRow['type_id'] != 'feed_comment') {
            $aRow['item_id'] = $aRow['feed_reference'];
        }

        if (isset($this->_aViewMoreFeeds[$sKey])) {
            foreach ($this->_aViewMoreFeeds[$sKey] as $iSubKey => $aSubRow) {
                $mReturnViewMore = $this->processFeed($aSubRow, $iSubKey, $iUserid, $bFirstCheckOnComments);

                if ($mReturnViewMore === false) {
                    continue;
                }

                $aFeed['more_feed_rows'][] = $mReturnViewMore;
            }
        }

        if (Phpfox::isModule('like') && isset($aFeed['like_type_id']) && (int)$aFeed['feed_total_like'] > 0) {
            $aFeed['likes'] = Phpfox::getService('like')->getLikesForFeed($aFeed['like_type_id'], (isset($aFeed['like_item_id']) ? $aFeed['like_item_id'] : $aRow['item_id']), ((int)$aFeed['feed_is_liked'] > 0 ? true : false), Phpfox::getParam('feed.total_likes_to_display'));

            if ($aFeed['feed_total_like'] > 1) {
                $aFeed['like_phrase'] = Phpfox::getPhrase('user.total_people_like_this', array('total' => $aFeed['feed_total_like']));
            } elseif ($aFeed['feed_total_like'] == 1) {
                if ($aFeed['feed_is_liked']) {
                    $aFeed['like_phrase'] = Phpfox::getPhrase('feed.you_like_this');
                } else {
                    $aFeed['like_phrase'] = Phpfox::getPhrase('user.1_person_likes_this');
                }
            }
        }


        if (isset($aFeed['comment_type_id']) && (int)$aFeed['total_comment'] > 0 && Phpfox::isModule('comment')) {
            $aFeed['comments'] = $this->getCommentsForFeed($aFeed['comment_type_id'], $aRow['item_id'], Phpfox::getParam('comment.total_comments_in_activity_feed'));
        }

        if (isset($aRow['app_title']) && $aRow['app_id']) {
            $aFeed['app_link'] = Phpfox::permalink('apps', $aRow['app_id'], $aRow['app_title']);
        }

        // Check if user can post comments on this feed/item
        $bFirstCheckOnComments = false;
        if (Phpfox::getParam('feed.allow_comments_on_feeds') && Phpfox::isUser() && Phpfox::isModule('comment')) {
            $bFirstCheckOnComments = true;
        }

        $bCanPostComment = false;
        if ($bFirstCheckOnComments) {
            $bCanPostComment = true;
        }
        if ($iUserid !== null && $iUserid != Phpfox::getUserId()) {
            switch ($aRow['privacy_comment']) {
                case '1':
                    if (!Phpfox::getService('user')->getUserObject($iUserid)->is_friend) {
                        $bCanPostComment = false;
                    }
                    break;
                case '2':
                    if (!Phpfox::getService('user')->getUserObject($iUserid)->is_friend && !Phpfox::getService('user')->getUserObject($iUserid)->is_friend_of_friend) {
                        $bCanPostComment = false;
                    }
                    break;
                case '3':
                    $bCanPostComment = false;
                    break;
            }
        }

        if ($iUserid === null) {
            if ($aRow['user_id'] != Phpfox::getUserId()) {
                switch ($aRow['privacy_comment']) {
                    case '1':
                    case '2':
                        if (!$aRow['is_friend']) {
                            $bCanPostComment = false;
                        }
                        break;
                    case '3':
                        $bCanPostComment = false;
                        break;
                }
            }
        }

        $aRow['can_post_comment'] = (Phpfox::getUserParam('feed.can_post_comment_on_feed') && (isset($aFeed['comment_type_id']) && $bCanPostComment) || (!isset($aFeed['comment_type_id']) && isset($aFeed['total_comment'])));

        if (isset ($aRow['feed_content'])) {
            $aRow['feed_content'] = $oOutput->clean(strip_tags($aRow['feed_content']));
        }

        if (isset ($aRow['feed_title'])) {
            $aRow['feed_title'] = $oOutput->clean(strip_tags($aRow['feed_title']));
        }

        $aRow = array_merge($aRow, $aFeed);

        $aRow['time_phrase'] = Phpfox::getLib('date')->convertTime($aRow['time_stamp'], 'feed.feed_display_time_stamp');

        $aRow['title_phrase'] = $aRow['full_name'] . (empty($aFeed['feed_info']) ? '' : ' ' . strip_tags($aFeed['feed_info']));

        $aRow['title_phrase_html'] = '<b><font color="'. $this->sColor .'">' . $aRow['full_name'] .'</font></b>'. (empty($aFeed['feed_info']) ? '' : ' ' . strip_tags($aFeed['feed_info']));
        if (isset($aRow['location_name']) && !empty($aRow['location_name']))
        {
            $aRow['title_phrase_html'] .= ' ' . Phpfox::getPhrase('feed.at_location', array('location' => $aRow['location_name']));
        }

        if (!empty($aRow['feed_image'])) {
            if (!is_array($aRow['feed_image'])) {
                $aRow['feed_image'] = array($aRow['feed_image']);
            } else {
                $aPhoto = $this->database()->select('photo.*, l.like_id AS is_liked, pi.description, pfeed.photo_id AS extra_photo_id, pa.album_id, pa.name')
                    ->from(Phpfox::getT('photo'), 'photo')
                    ->join(Phpfox::getT('photo_info'), 'pi', 'pi.photo_id = photo.photo_id')
                    ->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = \'photo\' AND l.item_id = photo.photo_id AND l.user_id = ' . Phpfox::getUserId())
                    ->leftJoin(Phpfox::getT('photo_feed'), 'pfeed', 'pfeed.feed_id = ' . (int)$aRow['feed_id'])
                    ->leftJoin(Phpfox::getT('photo_album'), 'pa', 'pa.album_id = photo.album_id')
                    ->where('photo.photo_id = ' . (int)$aRow['item_id'])
                    ->execute('getSlaveRow');
                $aPhotoInfo['total_like'] = $aPhoto['total_like'];
                $aPhotoInfo['total_comment'] = $aPhoto['total_comment'];
                $aPhotoInfo['is_liked'] = $aPhoto['is_liked'];

                $aPhotos = $this->database()->select('p.photo_id, l.like_id AS is_liked, p.album_id, p.total_like, p.total_comment, p.user_id, p.title, p.server_id, p.destination, p.mature')
                    ->from(Phpfox::getT('photo_feed'), 'pfeed')

                    ->join(Phpfox::getT('photo'), 'p', 'p.photo_id = pfeed.photo_id' . (!empty($aPhoto['module_id']) ? ' AND p.module_id = \'' . $this->database()->escape($aPhoto['module_id']) . '\'' : ''))
                    ->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = \'photo\' AND l.item_id = p.photo_id AND l.user_id = ' . Phpfox::getUserId())
                    ->where('pfeed.feed_id = ' . (int)$aRow['feed_id'])
                    ->limit(((Phpfox::getService('profile')->timeline() || Phpfox::isMobile()) ? 2 : 3))
                    ->order('p.time_stamp DESC')
                    ->execute('getSlaveRows');

                foreach ($aPhotos as $iKey => $bPhoto) {
                    $aRow['photos_id'][$iKey] = $bPhoto['photo_id'];
                    $aRow['photo_info'][$iKey]['total_like'] = $bPhoto['total_like'];
                    $aRow['photo_info'][$iKey]['total_comment'] = $bPhoto['total_comment'];
                    $aRow['photo_info'][$iKey]['is_liked'] = $bPhoto['is_liked'];
                }
                if (isset($aRow['custom_rel'])) {
                    array_unshift($aRow['photos_id'], $aRow['custom_rel']);
                    array_unshift($aRow['photo_info'], $aPhotoInfo);
                }
            }
            foreach ($aRow['feed_image'] as $iKey => $sFeedImage) {
                preg_match('/<img [^>]*src="([^"]+)"[^>]*>/', $sFeedImage, $aMatches);

                if (!empty($aMatches[1])) {
                    $sFeedImage = $aMatches[1];
                }
                $aRow['feed_image'][$iKey] = str_replace('_100.', '_500.', $sFeedImage);

            }

        }

        $aRow['can_share_item_on_feed'] = Phpfox::hasCallback($sOrgType, 'canShareItemOnFeed');

        //get sharing feed
        if (isset($aRow['parent_feed_id']) && $aRow['parent_feed_id'] != 0 && !empty($aRow['parent_module_id'])) {
            $aRow['share_feed'] = Phpfox::callback($aRow['parent_module_id'] . '.getActivityFeed', array(
                    'feed_id' => (int)$aRow['parent_feed_id'],
                    'item_id' => $aRow['parent_feed_id']
                ), null, true
            );

            //get url image
            if (!empty($aRow['share_feed']['feed_image'])) {
                if (is_array($aRow['share_feed']['feed_image'])){
                    foreach($aRow['share_feed']['feed_image'] as $iKey => $aImage) {
                        preg_match('/<img [^>]*src="([^"]+)"[^>]*>/', $aRow['share_feed']['feed_image'][$iKey], $aShareMatches);

                        if (!empty($aShareMatches[1])) {
                            $aRow['share_feed']['feed_image'][$iKey] = $aShareMatches[1];
                        }
                    }
                } else {
                    preg_match('/<img [^>]*src="([^"]+)"[^>]*>/', $aRow['share_feed']['feed_image'], $aShareMatches);

                    if (!empty($aShareMatches[1])) {
                        $aRow['share_feed']['feed_image'] = $aShareMatches[1];
                    }
                }

            }
        }

        //filter word
        $sFilterWord = $this->filterWord($aRow);

        if (!empty ($sFilterWord)) {
            $aRow['feed_status'] = $sFilterWord;
            if ($aRow['type_id'] == 'blog') {
                $aRow['feed_status'] = substr($aRow['feed_status'], 0, 100);
            }
        }

        // strip html tags
        if (isset($aRow['feed_status'])) {
            $aRow['feed_status_html'] = $aRow['feed_status'];
            $aRow['feed_status'] = str_replace(array('<br>', '<br />'), "\n", Phpfox::getService('accountapi.emoticon')->processEmoticon($aRow['feed_status']));
        }

        if (isset($aRow['feed_content'])) {
            $aRow['feed_content_html'] = Phpfox::getService('ban.word')->clean($aFeed['feed_content']);
            $aRow['feed_content'] = Phpfox::getService('ban.word')->clean(Phpfox::getService('accountapi.emoticon')->processEmoticon($aFeed['feed_content']));
            if ($aRow['type_id'] == 'blog') {
                $aRow['feed_content'] = substr($aRow['feed_content'], 0, 100);
            }
        }

        //get share feed link
        if (!empty ($aRow['feed_link'])) {
            $aRow['feed_link_share'] = Phpfox::getPhrase('share.hi_check_this_out_bbcode', array('url' => $aRow['feed_link']));

            $aRow['feed_link_share_url'] = Phpfox::getPhrase('share.hi_check_this_out_url', array('url' => $aRow['feed_link']));
        }

        $aRow['social_app'] = array(
            'type_id' => $sOrgType,
            'link' => $this->_route($sOrgType, $aRow)
        );
        $aRow['can_report'] = Phpfox::getService('report')->canReport($aRow['type_id'], $aRow['item_id']);
        if (isset($aRow['text'])) {
            $aRow['text_html'] = $aRow['text'];
            $aRow['text'] = Phpfox::getService('accountapi.emoticon')->processEmoticon($aRow['text']);
        }
        $aRow['can_delete'] = false;
        if ((defined('PHPFOX_FEED_CAN_DELETE')) || (Phpfox::getUserParam('feed.can_delete_own_feed') && $aRow['user_id'] == Phpfox::getUserId()) || Phpfox::getUserParam('feed.can_delete_other_feeds')) {
            $aRow['can_delete'] = true;
        }

        //location
        if (Phpfox::getParam('feed.enable_check_in') && Phpfox::getParam('core.google_api_key') != '' && isset($aRow['location_latlng']))
        {
            $aRow['location_img'] = $this->coreHttp . 'maps.googleapis.com/maps/api/staticmap?center='
                . $aRow['location_latlng']['latitude'] . ',' . $aRow['location_latlng']['longitude'] . '&amp;zoom=16&amp;size=390x250&amp;sensor=false&amp;maptype=roadmap'
                . '&amp;markers=color:red%7Clabel:S%7C' . $aRow['location_latlng']['latitude'] . ',' . $aRow['location_latlng']['longitude'];

            $aRow['location_link'] = $this->coreHttp . 'maps.google.com/maps?daddr=' . $aRow['location_latlng']['latitude'] . ',' . $aRow['location_latlng']['longitude'];
        }

        return $aRow;
    }


    /**
     * filter word
     * @param $aFeed
     * @return mixed
     */
    public function filterWord($aFeed)
    {
        //filter word
        if (isset ($aFeed['feed_status'])) {
            $aFeed['feed_status'] = preg_replace('/\[.*?\]/', '', $aFeed['feed_status']);
            $sFilterWord = Phpfox::getService('ban.word')->clean($aFeed['feed_status']);
        } else if (isset ($aFeed['text'])) {
            $sFilterWord = Phpfox::getService('ban.word')->clean($aFeed['text']);
        }

        if (!empty ($sFilterWord))
            return $sFilterWord;

        return null;
    }

    public function getCommentsForFeed($sType, $iItemId, $iLimit = 2, $mPager = null, $iCommentId = null)
    {
        if ($mPager !== null) {
            $this->database()->limit(Phpfox::getLib('request')->getInt('page'), $iLimit, $mPager);
        } else {
            $this->database()->limit($iLimit);
        }

        if ($iCommentId !== null) {
            $this->database()->where('c.comment_id = ' . (int)$iCommentId . '');
        } else {
            $this->database()->where('c.parent_id = 0 AND c.type_id = \'' . $this->database()->escape($sType) . '\' AND c.item_id = ' . (int)$iItemId . ' AND c.view_id = 0');
        }

        $this->database()->select('l.like_id AS is_liked, ')->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = \'feed_mini\' AND l.item_id = c.comment_id AND l.user_id = ' . Phpfox::getUserId());

        $aFeedComments = $this->database()->select('c.*, ' . (Phpfox::getParam('core.allow_html') ? "ct.text_parsed" : "ct.text") . ' AS text, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('comment'), 'c')
            ->join(Phpfox::getT('comment_text'), 'ct', 'ct.comment_id = c.comment_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = c.user_id')
            ->order('c.time_stamp DESC')
            ->execute('getSlaveRows');

        $aComments = array();
        if (count($aFeedComments)) {
            foreach ($aFeedComments as $iFeedCommentKey => $aFeedComment) {
                $aFeedComments[$iFeedCommentKey] = $this->processComment($aFeedComment);
            }

            $aComments = array_reverse($aFeedComments);
        }

        return $aComments;
    }

    public function processComment($aComment)
    {
        $aComment['user_image'] = Phpfox::getLib('image.helper')->display(array(
            'user' => $aComment,
            'server_id' => $aComment['user_server_id'],
            'suffix' => '_50_square',
            'return_url' => true,
        ));

        $aComment['time_phrase'] = Phpfox::getLib('date')->convertTime($aComment['time_stamp'], 'feed.feed_display_time_stamp');
        if (Phpfox::getParam('comment.comment_is_threaded')) {
            $aComment['children'] = $this->_getChildren($aComment['comment_id'], $sType, $iItemId, $iCommentId);
        }

        $aComment['text_html'] = $aComment['text'];

        $aComment['text'] = Phpfox::getService('accountapi.emoticon')->processEmoticon($aComment['text']);
        //filter word
        $sFilterWord = $this->filterWord($aComment);

        if (!empty ($sFilterWord)) {
            $aComment['text'] = $sFilterWord;
        }

        return $aComment;
    }

    private function _getChildren($iParentId, $sType, $iItemId, $iCommentId = null, $iCnt = 0)
    {
        $iTotalComments = $this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('comment'), 'c')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = c.user_id')
            ->where('c.parent_id = ' . (int)$iParentId . ' AND c.type_id = \'' . $this->database()->escape($sType) . '\' AND c.item_id = ' . (int)$iItemId . ' AND c.view_id = 0')
            ->execute('getSlaveField');

        $this->database()->select('l.like_id AS is_liked, ')->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = \'feed_mini\' AND l.item_id = c.comment_id AND l.user_id = ' . Phpfox::getUserId());

        if ($iCommentId === null) {
            $this->database()->limit(Phpfox::getParam('comment.thread_comment_total_display'));
        }

        $aFeedComments = $this->database()->select('c.*, ' . (Phpfox::getParam('core.allow_html') ? "ct.text_parsed" : "ct.text") . ' AS text, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('comment'), 'c')
            ->join(Phpfox::getT('comment_text'), 'ct', 'ct.comment_id = c.comment_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = c.user_id')
            ->where('c.parent_id = ' . (int)$iParentId . ' AND c.type_id = \'' . $this->database()->escape($sType) . '\' AND c.item_id = ' . (int)$iItemId . ' AND c.view_id = 0')
            ->order('c.time_stamp ASC')
            ->execute('getSlaveRows');

        $iCnt++;
        if (count($aFeedComments)) {
            foreach ($aFeedComments as $iFeedCommentKey => $aFeedComment) {
                $aFeedComments[$iFeedCommentKey] = $this->processComment($aFeedComment);
                $aFeedComments[$iFeedCommentKey]['iteration'] = $iCnt;
            }
        }

        return array('total' => (int)($iTotalComments - Phpfox::getParam('comment.thread_comment_total_display')), 'comments' => $aFeedComments);
    }

    private function _route($sType, $aRow)
    {
        $iEnd = strpos($sType, '_');
        $iEnd = ($iEnd ? $iEnd : strlen($sType));
        $sModule = substr($sType, 0, $iEnd);
        if (!isset($aRow['custom_rel'])) {
            $aRow['custom_rel'] = $aRow['item_id'];
        }

        switch ($sModule) {
            case 'photo':
                return array(
                    'route' => 'photo/viewPhoto',
                    'request' => array(
                        'photo_id' => $aRow['custom_rel']
                    )
                );
            case 'friend':
                return array(
                    'route' => 'user/profile',
                    'request' => array(
                        'user_id' => $aRow['parent_user_id']
                    )
                );
            case 'pages':
                return array(
                    'route' => 'pages/dashboard',
                    'request' => array(
                        'page_id' => $aRow['item_id']
                    )
                );
        }
    }
}