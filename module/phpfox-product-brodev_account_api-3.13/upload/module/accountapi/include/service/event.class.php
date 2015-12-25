<?php

/**
 * [PHPFOX_HEADER]
 */

defined('PHPFOX') or exit('NO DICE!');

/**
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		Raymond Benc
 * @package 		Phpfox_Service
 * @version 		$Id: event.class.php 3223 2011-10-06 12:56:24Z Miguel_Espinoza $
 */

class Accountapi_Service_Event extends Phpfox_Service
{
    /**
     * Get events
     * @param $iUserId
     * @param null $iCategory
     * @param bool $sView
     * @param $iPage
     * @param $iPageSize
     * @return array
     */
    public function get($iUserId = null, $iCategory = null, $sView = false, $iPage, $iPageSize)
    {
        $oServiceEventBrowse = Phpfox::getService('event.browse');
        $oLibSearch = Phpfox::getLib('search');
        $aEvents = array();
        $aCallback = false;

        $oLibSearch->set(array(
                'type' => 'event',
                'field' => 'm.event_id',
                'search_tool' => array(
                    'default_when' => 'upcoming',
                    'when_field' => 'start_time',
                    'when_upcoming' => true,
                    'table_alias' => 'm',
                    'search' => array(
                        'default_value' => Phpfox::getPhrase('event.search_events'),
                        'name' => 'search',
                        'field' => 'm.title'
                    ),
                    'sort' => array(
                        'latest' => array('m.start_time', Phpfox::getPhrase('event.latest'), 'ASC'),
                        'most-viewed' => array('m.total_view', Phpfox::getPhrase('event.most_viewed')),
                        'most-liked' => array('m.total_like', Phpfox::getPhrase('event.most_liked')),
                        'most-talked' => array('m.total_comment', Phpfox::getPhrase('event.most_discussed'))
                    ),
                    'show' => array($iPageSize)
                )
            )
        );

        $aBrowseParams = array(
            'module_id' => 'event',
            'alias' => 'm',
            'field' => 'event_id',
            'table' => Phpfox::getT('event'),
            'hide_view' => array('pending', 'my')
        );

        switch ($sView)
        {
            case 'my':
                Phpfox::isUser(true);
                $oLibSearch->setCondition('AND m.user_id = ' . (int) Phpfox::getUserId());
                break;
            default:
                switch ($sView)
                {
                    case 'attending':
                        $oServiceEventBrowse->attending(1);
                        break;
                    case 'may-attend':
                        $oServiceEventBrowse->attending(2);
                        break;
                    case 'not-attending':
                        $oServiceEventBrowse->attending(3);
                        break;
                    case 'invites':
                        $oServiceEventBrowse->attending(0);
                        break;
                }

                if ($sView == 'attending') {
                    $oLibSearch->setCondition('AND m.view_id = 0 AND m.privacy IN(%PRIVACY%)');
                } else {
                    $oLibSearch->setCondition('AND m.view_id = 0 AND m.privacy IN(%PRIVACY%) AND m.item_id = 0');
                }

                break;
        }

        if (!empty($iCategory) && $iCategory !== null)
        {
            $oLibSearch->setCondition('AND mcd.category_id = ' . (int) $iCategory);

            $oServiceEventBrowse->callback($aCallback)->category($iCategory);
        }

        $oLibSearch->browse()->params($aBrowseParams)->execute();

        $_aEvents = $oLibSearch->browse()->getRows();

        Phpfox::getLib('pager')->set(array('page' => $iPage, 'size' => $oLibSearch->getDisplay(), 'count' => $oLibSearch->browse()->getCount()));

        foreach ($_aEvents as $sDate => $_aEvent) {
            foreach ($_aEvent as $iKey => $aEvent) {
                $aEvent['header_phrase'] = $sDate;
                $bCanPostComment = true;
                if (isset($aEvent['privacy_comment']) && $aEvent['user_id'] != $iUserId && !Phpfox::getUserParam('privacy.can_comment_on_all_items'))
                {
                    switch ($aEvent['privacy_comment'])
                    {
                        // Everyone is case 0. Skipped.
                        // Friends only
                        case 1:
                            if(!Phpfox::getService('friend')->isFriend($iUserId, $aEvent['user_id']))
                            {
                                $bCanPostComment = false;
                            }
                            break;
                        // Friend of friends
                        case 2:
                            if (!Phpfox::getService('friend')->isFriendOfFriend($aEvent['user_id']))
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
                $aEvent['can_post_comment'] = $bCanPostComment;
                if (isset($aEvent['description_parsed'])) {
                    $aEvent['description_parsed_html'] = $aEvent['description_parsed'];
                    $aEvent['description_parsed'] = Phpfox::getService('accountapi.emoticon')->processEmoticon($aEvent['description_parsed']);
                }
                if (isset($aEvent['info'])) {
                    $aEvent['info_html'] = $aEvent['info'];
                    $aEvent['info'] = Phpfox::getService('accountapi.emoticon')->processEmoticon($aEvent['info']);
                }


                $aEvent['image_path'] = $this->getEventImage($aEvent);
                $aEvent['user_image'] = $this->getUserImage($aEvent);

                $aEvent['time_phrase'] = Phpfox::getPhrase('event.at') . ' ' . $aEvent['start_time_phrase_stamp'];

                $aEvents[] = $aEvent;
            }
        }

        return array($oLibSearch->browse()->getCount(), $aEvents);
    }

    /**
     * get image
     * @param $aEvent
     */
    protected function getEventImage($aEvent)
    {
        if (isset($aEvent['image_path'])) {
            $sImage = Phpfox::getLib('image.helper')->display(array(
                    'path' => 'event.url_image',
                    'server_id' => $aEvent['server_id'],
                    'file' => $aEvent['image_path'],
                    'suffix' => '_120',
                    'return_url' => true
                )
            );
        } else {
            $sImage = Phpfox::getLib('image.helper')->display(array(
                    'theme' => 'noimage/item.png',
                    'return_url' => true
                )
            );
        }

        return $sImage;
    }

    /**
     * get user image
     * @param $aEvent
     */
    protected function getUserImage($aEvent) {
        $sImage = Phpfox::getLib('image.helper')->display(array(
                'user' => $aEvent,
                'suffix' => '_75_square',
                'return_url' => true
            )
        );
        return $sImage;
    }

    /**
     * Get countries
     * @param null $sValue
     * @return array
     */
    public function getCountries($sValue = null)
    {
        $aReturn = array();
        if (!empty($sValue)) {
            $aCountries = Phpfox::getService('core.country')->getChildren($sValue);
            if (!empty ($aCountries)) {
                $aReturn[] = array(
                    'value' => 0,
                    'name' => Phpfox::getPhrase('core.state_province'),
                    'default' => true
                );
            }
        } else {
            $aCountries = Phpfox::getService('core.country')->get();
            $sUserCountryIso = PHpfox::getUserBy('country_iso');
        }


        foreach($aCountries as $sIso => $sCountryName) {
            $aReturn[] = array(
                'value' => $sIso,
                'name' => $sCountryName,
                'default' => (isset($sUserCountryIso) && $sIso == $sUserCountryIso) ? true : false
            );
        }

        return $aReturn;
    }

    /**
     * Get category
     * @param null $iCategoryId
     * @return mixed
     */
    public function getCategories($iCategoryId = null)
    {
        $aReturn = array();

        if ($iCategoryId === null)
        {
            $aCategories = $this->database()->select('mc.category_id, mc.name')
                ->from(Phpfox::getT('event_category'), 'mc')
                ->where('mc.parent_id = ' . ($iCategoryId === null ? '0' : (int) $iCategoryId) . ' AND mc.is_active = 1')
                ->order('mc.ordering ASC')
                ->execute('getRows');
        } else {
            $aCategories = $this->database()->select('mc.category_id, mc.name')
                ->from(Phpfox::getT('event_category'), 'mc')
                ->where('mc.parent_id = ' . (int) $iCategoryId . ' AND mc.is_active = 1')
                ->order('mc.ordering ASC')
                ->execute('getRows');
        }

        if (!empty($aCategories)) {
            $aReturn[] = array(
                'value' => '0',
                'name' => ($iCategoryId == null ? Phpfox::getPhrase('event.select') : Phpfox::getPhrase('event.select_a_sub_category')),
                'default' => true
            );
        }

        foreach ($aCategories as $iKey => $aCategory) {
            $aReturn[] = array(
                'value' => $aCategory['category_id'],
                'name' => $aCategory['name'],
                'default' => false
            );
        }

        return $aReturn;
    }

    /**
     * get event by id
     * @param $iId
     * @return mixed
     */
    public function getEvent($iId)
    {
        $aEvent = Phpfox::getService('event')->getEvent($iId);

        //get image
        if (isset($aEvent['map_location'])) {
            $aEvent['map_img'] = 'http://maps.googleapis.com/maps/api/staticmap?center=' . $aEvent['map_location'] . '&amp;zoom=16&amp;size=390x250&amp;sensor=false&amp;maptype=roadmap';
        }

        //get user and event image
        $aEvent['image_path'] = $this->getEventImage($aEvent);
        $aEvent['user_image'] = $this->getUserImage($aEvent);

        //get country
        $aEvent['country'] = Phpfox::getService('core.country')->getCountry($aEvent['country_iso']);
        //get child country
        if (isset($aEvent['country_child_id']) && !empty($aEvent['country_child_id'])) {
            $aEvent['child_country'] = Phpfox::getService('core.country')->getChild($aEvent['country_child_id']);
        }

        if (!empty ($aEvent['categories'])) {
            $sCategories = '';
            foreach ($aEvent['categories'] as $aCategory) {
                foreach($aCategory as $iKey => $aValue) {
                    if ($iKey !== 0) {
                        $sCategories .= ' &#187; ';
                    }
                    $sCategories .=  $aCategory[0];
                }
            }
            $aEvent['categories'] = $sCategories;
        } else {
            $aEvent['categories'] = null;
        }

        //get location
        $sLocation = $aEvent['location'];
        if (!empty($aEvent['address'])) {
            $sLocation .= ' &#187; ' . $aEvent['address'];
        }
        if (!empty($aEvent['city'])) {
            $sLocation .= ' &#187; ' . $aEvent['city'];
        }
        if (!empty($aEvent['postal_code'])) {
            $sLocation .= ' &#187; ' . $aEvent['postal_code'];
        }
        if (!empty($aEvent['country_child_id'])) {
            $sLocation .= ' &#187; ' . $aEvent['child_country'];
        }
        $sLocation .= ' &#187; ' . $aEvent['country'];
        $aEvent['event_location'] = $sLocation;

        $bCanPostComment = true;
        if (isset($aEvent['privacy_comment']) && $aEvent['user_id'] != Phpfox::getUserId() && !Phpfox::getUserParam('privacy.can_comment_on_all_items'))
        {
            switch ($aEvent['privacy_comment'])
            {
                // Everyone is case 0. Skipped.
                // Friends only
                case 1:
                    if(!Phpfox::getService('friend')->isFriend(Phpfox::getUserId(), $aEvent['user_id']))
                    {
                        $bCanPostComment = false;
                    }
                    break;
                // Friend of friends
                case 2:
                    if (!Phpfox::getService('friend')->isFriendOfFriend($aEvent['user_id']))
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

        if (Phpfox::getUserId())
        {
            $bIsBlocked = Phpfox::getService('user.block')->isBlocked($aEvent['user_id'], Phpfox::getUserId());
            if ($bIsBlocked)
            {
                $bCanPostComment = false;
            }
        }

        $aEvent['can_post_comment'] = $bCanPostComment;
        if (isset ($aEvent['description_parsed'])) {
            $aEvent['description_parsed_html'] = $aEvent['description_parsed'];
            $aEvent['description_parsed'] = Phpfox::getService('accountapi.emoticon')->processEmoticon($aEvent['description_parsed']);
        }

        if (isset ($aEvent['info'])) {
            $aEvent['info_html'] = $aEvent['info'];
            $aEvent['info'] = Phpfox::getService('accountapi.emoticon')->processEmoticon($aEvent['info']);
        }
        $aEvent['feed_callback'] = array(
            'module' => 'event',
            'table_prefix' => 'event_',
            'ajax_request' => 'event.addFeedComment',
            'item_id' => $aEvent['event_id'],
            'disable_share' => ($bCanPostComment ? false : true)
		);

        return $aEvent;
    }

    /**
     * @param $aMembers
     */
    private function getMemberInfo($aMembers)
    {
        foreach ($aMembers as $iKey => $aMember)
        {
            $sImage = $this->getUserImage($aMember);
            $aMembers[$iKey]['user_image'] = $sImage;
        }

        return $aMembers;
    }

    /**
     * Get event member
     * @param $iEventId
     * @return mixed
     */
    public function getMember($iEventId)
    {
        $iPageSize = 6;

        list($iCnt, $aInvites) = Phpfox::getService('event')->getInvites($iEventId, 1, 1, $iPageSize);
        $aInvites = $this->getMemberInfo($aInvites);

        list($iMaybeCnt, $aMaybeInvites) = Phpfox::getService('event')->getInvites($iEventId, 2, 1, $iPageSize);
        $aMaybeInvites = $this->getMemberInfo($aMaybeInvites);

        list($iNotAttendingCnt, $aNotAttendingInvites) = Phpfox::getService('event')->getInvites($iEventId, 3, 1, $iPageSize);
        $aNotAttendingInvites = $this->getMemberInfo($aNotAttendingInvites);

        $aReturn['attending'] = array(
            'length_attending' => (int) $iCnt,
            'phrase' => Phpfox::getPhrase('event.attending'),
            'value' => $aInvites
        );

        $aReturn['maybe_attending'] = array(
            'length_maybe_attending' => (int) $iMaybeCnt,
            'phrase' => Phpfox::getPhrase('event.maybe_attending'),
            'value' => $aMaybeInvites
        );

        $aReturn['not_attending'] = array(
            'length_not_attending' => (int) $iNotAttendingCnt,
            'phrase' => Phpfox::getPhrase('event.not_attending'),
            'value' => $aNotAttendingInvites
        );

        return $aReturn;
    }

    /**
     * Get event member list by rsvp
     * @param $iRsvp
     * @param $iEventId
     * @param $iPage
     * @return mixed
     */
    public function getMemberLst($iEventId, $iRsvp, $iPage)
    {
        $iPageSize = 20;

        list($iCnt, $aInvites) = Phpfox::getService('event')->getInvites($iEventId, $iRsvp, $iPage, $iPageSize);

        foreach($aInvites as $iKey => $aInvite)
        {
            $aInvites[$iKey]['user_image_path'] = Phpfox::getLib('image.helper')->display(array(
                    'user' => $aInvite,
                    'suffix' => '_75_square',
                    'return_url' => true
                )
            );
        }

        return array($iCnt, $aInvites);
    }
}