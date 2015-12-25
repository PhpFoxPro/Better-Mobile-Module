<?php
/**
 * Created by JetBrains PhpStorm.
 * User: huy nguyen
 * Date: 10/22/13
 * Time: 4:33 PM
 * To change this template use File | Settings | File Templates.
 */
class Accountapi_Service_Oldevent extends Phpfox_Service{
    public function __construct(){
        $this->_sTable = Phpfox::getT('event');
    }

    /**
     * get blog detail
     * @param $iId
     * @param $iUserId
     * @return mixed
     */
    public function getDetail($iId, $iUserId) {
        if (Phpfox::isModule('like'))
        {
            $this->database()->select('lik.like_id AS is_liked, ')->leftJoin(Phpfox::getT('like'), 'lik', 'lik.type_id = \'event\' AND lik.item_id = e.event_id AND lik.user_id = '. $iUserId);
        }
        $this->database()->select('ei.invite_id, ei.rsvp_id, ')->leftJoin(Phpfox::getT('event_invite'), 'ei', 'ei.event_id = e.event_id AND ei.invited_user_id = ' . $iUserId);
        $aReturn = $this->database()->select('e.*, et.description_parsed, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'e')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = e.user_id')
            ->join(Phpfox::getT('event_text'), 'et', 'et.event_id = e.event_id')
            ->where('e.event_id = '. $iId)
            ->execute('getSlaveRow');
        $this->_process($aReturn, $iUserId);
        return $aReturn;
    }

    /**
     * change time stamp, add blog image
     * @param $aRow
     * @param $iUserId
     * @return array
     */
    private function _process(&$aRow, $iUserId) {
        $aRow['info'] = Phpfox::getLib('parse.output')->parse($aRow['description_parsed']);
        $aRow['image_path'] = Phpfox::getLib('image.helper')->display(array(
                'path' => 'event.url_image',
                'server_id' => $aRow['server_id'],
                'file' => $aRow['image_path'],
                'suffix' => '_120',
                'return_url' => true
            )
        );
        $aRow['start_time_micro'] = Phpfox::getTime('Y-m-d', $aRow['start_time']);

        $aCategories = Phpfox::getService('event.category')->getCategoriesById($aRow['event_id']);
        if ($aCategories != null) {
            foreach($aCategories as $iKey => $aCategory) {
                unset($aCategories[$iKey][1]);
            }
        } else {
            $aCategories = array();
        }

        $aRow['categories'] = $aCategories;

        $aRow['start_event_date'] = Phpfox::getTime(Phpfox::getParam('event.event_basic_information_time'), $aRow['start_time']);

        $aRow['end_event_date'] = Phpfox::getTime(Phpfox::getParam('event.event_basic_information_time'), $aRow['end_time']);

        $aRow['map_location'][] = $aRow['location'];
        if (!empty($aRow['address']))
        {
            $aRow['map_location'][] = $aRow['address'];

        }
        if (!empty($aRow['city']))
        {
            $aRow['map_location'][] = $aRow['city'];
        }
        if (!empty($aRow['postal_code']))
        {
            $aRow['map_location'][] = $aRow['postal_code'];
        }
        if (!empty($aRow['country_child_id']))
        {
            $aRow['map_location'][] = Phpfox::getService('core.country')->getChild($aRow['country_child_id']);
        }
        if (!empty($aRow['country_iso']))
        {
            $aRow['map_location'][] = Phpfox::getService('core.country')->getCountry($aRow['country_iso']);
        }
        $aRow['map_location'] = array_values($aRow['map_location']);
        $bCanPostComment = true;
        if (isset($aRow['privacy_comment']) && $aRow['user_id'] != $iUserId && !Phpfox::getUserParam('privacy.can_comment_on_all_items'))
        {
            switch ($aRow['privacy_comment'])
            {
                // Everyone is case 0. Skipped.
                // Friends only
                case 1:
                    if(!Phpfox::getService('friend')->isFriend($iUserId, $aRow['user_id']))
                    {
                        $bCanPostComment = false;
                    }
                    break;
                // Friend of friends
                case 2:
                    if (!Phpfox::getService('friend')->isFriendOfFriend($aRow['user_id']))
                    {
                        $bCanPostComment = false;
                    }
                    break;
                // Only me
                case 3:
                    $bCanPostComment = false;
                    break;
            }
        }
        $aRow['can_post_comment'] = $bCanPostComment;
        $aRow['feed_callback'] = array(
            'module' => 'event',
            'table_prefix' => 'event_',
            'ajax_request' => 'event.addFeedComment',
            'item_id' => $aRow['event_id'],
            'disable_share' => ($bCanPostComment ? false : true)
        );

    }
    /**
     * get list blog
     * @param $sList
     * @param $iCategory
     * @param $iPage
     * @param $iUserId
     * @return array
     */
    public function getEvents($sList, $iCategory, $iPage, $iUserId) {
        $iTimeDisplay = Phpfox::getLib('date')->mktime(0, 0, 0, Phpfox::getTime('m'), Phpfox::getTime('d'), Phpfox::getTime('Y'));
        $sWhere = "e.privacy = 0 AND e.start_time >= " . Phpfox::getLib('date')->convertToGmt($iTimeDisplay);
        if ($sList == "my") {
            $sWhere .= " AND e.user_id = ". $iUserId;

        } else if ($sList == "friend") {
            list(, $aFriends) = Phpfox::getService('friend')->get(array(), 'friend.time_stamp DESC', '', '', true, false, false, $iUserId);
            $sWhere .= " AND e.user_id in (0";
            foreach($aFriends as $aFriend) {
                $sWhere .= ", ". $aFriend['user_id'];
            }
            $sWhere .= ")";

        }


        if ($iCategory != 0) {
            $iVideoIds = $this->database()
                ->select('event_id')
                ->from(Phpfox::getT('event_category_data'))
                ->where('category_id = '. $iCategory)
                ->execute('getRows');
            $sWhere .= " AND e.event_id in (0";
            foreach($iVideoIds as $iId) {
                $sWhere .= ", ". $iId['event_id'];
            }
            $sWhere .= ")";
        }

        $iCnt = $this->database()->select('COUNT(*)')
            ->from($this->_sTable, 'e')
            ->where($sWhere)
            ->execute('getSlaveField');


        if ($iPage == 0 ) {
            $iPage = 1;
        }
        $iSize = Phpfox::getParam('accountapi.event_page_size');
        $this->database()->select('ei.invite_id, ei.rsvp_id, ')->leftJoin(Phpfox::getT('event_invite'), 'ei', 'ei.event_id = e.event_id AND ei.invited_user_id = ' . $iUserId);
        if (Phpfox::isModule('like'))
        {
            $this->database()->select('lik.like_id AS is_liked, ')->leftJoin(Phpfox::getT('like'), 'lik', 'lik.type_id = \'event\' AND lik.item_id = e.event_id AND lik.user_id = '. $iUserId);
        }
        $aRows = $this->database()->select('e.*, et.description_parsed, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'e')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = e.user_id')
            ->join(Phpfox::getT('event_text'), 'et', 'et.event_id = e.event_id')
            ->where($sWhere)
            ->limit($iPage, $iSize, $iCnt)
            ->order('e.time_stamp DESC')
            ->execute('getSlaveRows');

        if (!empty($aRows)) {
            foreach ($aRows as $iKey => $aRow)
            {
                $this->_process($aRows[$iKey], $iUserId);
            }

            return array($iCnt, $aRows);
        } else {
            $aReturn['notice'] = Phpfox::getPhrase('event.no_events_found');
            return array($iCnt, $aReturn);
        }

    }

    public function getMember($iEventId, $iPage) {
        if ($iPage < 1) {
            $iPage = 1;
        }
        $iPageSize = 8;
        $iLimit = $iPageSize * ($iPage -1);
        list($iTotalInvite, $aInvites) = Phpfox::getService('event')->getInvites($iEventId, 1, $iPage);
        if ($iTotalInvite < $iLimit) {
            $aInvites = array();
        }
        if (!empty($aInvites)) {
            foreach($aInvites as $iKey => $aItem) {
                $this->_processMember($aInvites[$iKey]);
            }
        }
        list($iTotalAwaiting, $aAwaitingInvites) = Phpfox::getService('event')->getInvites($iEventId, 0, $iPage);
        if ($iTotalAwaiting < $iLimit) {
            $aAwaitingInvites = array();
        }
        if (!empty($aAwaitingInvites)) {
            foreach($aAwaitingInvites as $iKey => $aItem) {
                $this->_processMember($aAwaitingInvites[$iKey]);
            }
        }
        list($iTotalMaybe, $aMaybeInvites) = Phpfox::getService('event')->getInvites($iEventId, 2, $iPage);
        if ($iTotalAwaiting < $iTotalMaybe) {
            $aMaybeInvites = array();
        }
        if (!empty($aMaybeInvites)) {
            foreach($aMaybeInvites as $iKey => $aItem) {
                $this->_processMember($aMaybeInvites[$iKey]);
            }
        }
        list($iTotalNot, $aNotAttendingInvites) = Phpfox::getService('event')->getInvites($iEventId, 3, $iPage);
        if ($iTotalNot < $iTotalMaybe) {
            $aNotAttendingInvites = array();
        }
        if (!empty($aNotAttendingInvites)) {
            foreach($aNotAttendingInvites as $iKey => $aItem) {
                $this->_processMember($aNotAttendingInvites[$iKey]);
            }
        }
        $iTotal = $iTotalAwaiting + $iTotalInvite + $iTotalMaybe + $iTotalNot;
        $aReturn = array(
            $aAwaitingInvites, $aInvites, $aMaybeInvites, $aNotAttendingInvites, $iTotal
        );
        return $aReturn;
    }

    private function _processMember(&$aRow) {
        $aRow['user_image'] = Phpfox::getLib('image.helper')->display(array(
                'server_id' => $aRow['user_server_id'],
                'user' => $aRow,
                'suffix' => '_75_square',
                'return_url' => true
            )
        );
    }
    /**
     * add new blog
     * @param $aVals
     * @return mixed
     */
    public function add($aVals) {
        if ($aVals['start_time'] != "") {
            $aTempTime = preg_split('[/]', $aVals['start_time']);
            $aVals['start_month'] = $aTempTime[0];
            $aVals['start_day'] = $aTempTime[1];
            $aVals['start_year'] = $aTempTime[2];
            $aVals['start_minute'] = $aTempTime[4];
            $aVals['start_hour'] = $aTempTime[3];
            unset($aVals['start_time']);
        } else {
            $aVals['start_month'] = date('M');
            $aVals['start_day'] = date('d');
            $aVals['start_year'] = date('Y');
            $aVals['start_minute'] = date('i');
            $aVals['start_hour'] = date('h') + 2;
            unset($aVals['start_time']);
        }
        if ($aVals['end_time'] != "") {
            $aTempTime = preg_split('[/]', $aVals['end_time']);
            $aVals['end_month'] = $aTempTime[0];
            $aVals['end_day'] = $aTempTime[1];
            $aVals['end_year'] = $aTempTime[2];
            $aVals['end_minute'] = $aTempTime[4];
            $aVals['end_hour'] = $aTempTime[3];
            unset($aVals['end_time']);
        } else {
            $aVals['end_month'] = date('M');
            $aVals['end_day'] = date('d');
            $aVals['end_year'] = date('Y');
            $aVals['end_minute'] = date('i');
            $aVals['end_hour'] = date('h') + 4;
            unset($aVals['end_time']);
        }

        return Phpfox::getService('event.process')->add($aVals);
    }
}