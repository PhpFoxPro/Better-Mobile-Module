<?php
/**
 * [PHPFOX_HEADER]
 */

defined('PHPFOX') or exit('NO DICE!');

/**
 *
 *
 * @copyright        [PHPFOX_COPYRIGHT]
 * @author          Raymond Benc
 * @package         Phpfox_Service
 * @version         $Id: forum.class.php 5382 2013-02-18 09:48:39Z Miguel_Espinoza $
 */

class Accountapi_Service_Forum extends Phpfox_Service
{
    /**
     * Add thread
     * @param $iFourmId
     * @param $sTitle
     * @param $sText
     * @param $iSubscribed
     * @return bool|null
     */
    public function addThread($iFourmId, $sTitle, $sText, $iSubscribed)
    {
        $aForum = Phpfox::getService('forum')->id($iFourmId)->getForum();

        if (!isset($aForum['forum_id'])) {
            return Phpfox_Error::display(Phpfox::getPhrase('forum.not_a_valid_forum'));
        }

        if ($aForum['is_closed']) {
            return Phpfox_Error::display(Phpfox::getPhrase('forum.forum_is_closed'));
        }

        $bPass = false;
        if (Phpfox::getUserParam('forum.can_add_new_thread') || Phpfox::getService('forum.moderate')->hasAccess($aForum['forum_id'], 'add_thread')) {
            $bPass = true;
        }

        if ($bPass === false) {
            return Phpfox_Error::display(Phpfox::getPhrase('forum.insufficient_permission_to_reply_to_this_thread'));
        }

        $aVals = array(
            'forum_id' => $iFourmId,
            'title' => $sTitle,
            'text' => $sText,
            'is_subscribed' => $iSubscribed
        );

        if (($iFlood = Phpfox::getUserParam('forum.forum_thread_flood_control')) !== 0) {
            $aFlood = array(
                'action' => 'last_post', // The SPAM action
                'params' => array(
                    'field' => 'time_stamp', // The time stamp field
                    'table' => Phpfox::getT('forum_thread'), // Database table we plan to check
                    'condition' => 'user_id = ' . Phpfox::getUserId(), // Database WHERE query
                    'time_stamp' => $iFlood * 60 // Seconds);
                )
            );

            // actually check if flooding
            if (Phpfox::getLib('spam')->check($aFlood)) {
                Phpfox_Error::set(Phpfox::getPhrase('forum.posting_a_new_thread_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime());
            }
        }

        //add thread
        if (Phpfox_Error::isPassed() && ($iId = Phpfox::getService('forum.thread.process')->add($aVals, false))) {
            //return thread
            return $this->getThreadById($iId, 1, 10, null);
        }

        return null;
    }

    /**
     * Add post to thread
     *
     * @param $iThreadId
     * @param $sText
     * @param $iSubcribe
     * @return array
     */
    public function addPost($iThreadId, $sText, $iSubcribe)
    {
        $aVals = array(
            'thread_id' => $iThreadId,
            'text' => $sText,
            'is_subscribed' => $iSubcribe
        );

        Phpfox::getService('ban')->checkAutomaticBan($aVals['text']);

        $aThread = Phpfox::getService('forum.thread')->getActualThread($aVals['thread_id'], false);

        $bPass = false;
        if ((Phpfox::getUserParam('forum.can_reply_to_own_thread') && $aThread['user_id'] == Phpfox::getUserId()) || Phpfox::getUserParam('forum.can_reply_on_other_threads') || Phpfox::getService('forum.moderate')->hasAccess($aThread['forum_id'], 'can_reply')) {
            $bPass = true;
        }

        if ($bPass === false) {
            return array(
                'notice' => Phpfox::getPhrase('forum.insufficient_permission_to_reply_to_this_thread')
            );
        }

        if (($iFlood = Phpfox::getUserParam('forum.forum_post_flood_control')) !== 0) {
            $aFlood = array(
                'action' => 'last_post', // The SPAM action
                'params' => array(
                    'field' => 'time_stamp', // The time stamp field
                    'table' => Phpfox::getT('forum_post'), // Database table we plan to check
                    'condition' => 'user_id = ' . Phpfox::getUserId(), // Database WHERE query
                    'time_stamp' => $iFlood * 60 // Seconds);
                )
            );

            // actually check if flooding
            if (Phpfox::getLib('spam')->check($aFlood)) {
                return array(
                    'notice' => Phpfox::getPhrase('forum.posting_a_new_thread_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime()
                );
            }
        }

        if ($iId = Phpfox::getService('forum.post.process')->add($aVals, false)) {
            $aPost = Phpfox::getService('forum.post')->getPost($iId);
            if ($aThread['forum_id'] > 0) {
                Phpfox::getService('forum.process')->updateTrack($aThread['forum_id']);
            }

            Phpfox::getService('forum.thread.process')->updateTrack($aThread['thread_id']);

            $aPost['time_phrase'] = Phpfox::getLib('date')->convertTime($aPost['time_stamp'], 'mail.mail_time_stamp');

            if (isset ($aPost['text'])) {
                $aPost['text'] = Phpfox::getLib('parse.output')->parse($aPost['text']);
            }

            if (!empty ($aPost['aFeed']['feed_link'])) {
                $aPost['share_feed_link'] = Phpfox::getPhrase('share.hi_check_this_out_bbcode', array('url' => $aPost['aFeed']['feed_link']));

                $aPost['share_feed_link_url'] = Phpfox::getPhrase('share.hi_check_this_out_url', array('url' => $aPost['aFeed']['feed_link']));
            }

            if (Phpfox::getUserParam('forum.can_multi_quote_forum')) {
                $sQuote = Phpfox::getService('forum.post')->getQuotes($aPost['thread_id'], $aPost['post_id']);
                $aPost['quote'] = $sQuote;
            }

            return $aPost;
        }
    }

    /**
     * Get forum
     * @return array
     */
    public function getForums()
    {
        $_aForums = Phpfox::getService('forum')->live()->getForums();

        //replace sub forum
        foreach ($_aForums as $iKey => $aForum) {

            foreach ($_aForums[$iKey]['sub_forum'] as $_iKey => $aSubForum) {
                if (!empty($aSubForum['thread_time_stamp'])) {
                    $sTimePhrase = Phpfox::getLib('date')->convertTime($aSubForum['thread_time_stamp'], 'feed.feed_display_time_stamp');
                    $_aForums[$iKey]['sub_forum'][$_iKey]['phrase'] = Phpfox::getPhrase('forum.by_full_name_on_time', array(
                        'full_name' => $_aForums[$iKey]['sub_forum'][$_iKey]['full_name'],
                        'time' => $sTimePhrase
                    ));
                }
            }

            $_aForums[$iKey]['sub_forum'] = array_values($_aForums[$iKey]['sub_forum']);
        }

        $_aForums = array_values($_aForums);

        return $_aForums;

    }

    /**
     * get Sub Forums
     * @param $iForumId
     * @return mixed
     */
    public function getSubForums($iForumId)
    {
        $aForums = Phpfox::getService('forum')->live()->id($iForumId)->getForums();
        //replace sub forum
        foreach ($aForums as $iKey => $aForum) {
            if (!empty($aForum['thread_time_stamp'])) {
                $sTimePhrase = Phpfox::getLib('date')->convertTime($aForum['thread_time_stamp'], 'feed.feed_display_time_stamp');
                $aForums[$iKey]['phrase'] = Phpfox::getPhrase('forum.by_full_name_on_time', array(
                    'full_name' => $aForum['full_name'],
                    'time' => $sTimePhrase
                ));
            }
            $aForums[$iKey]['sub_forum'] = array_values($aForums[$iKey]['sub_forum']);
        }
        $aForums = array_values($aForums);

        if (empty ($aForums))
            return array(
                'notice' => Phpfox::getPhrase('accountapi.no_sub_forum')
            );

        return $aForums;
    }

    /**
     * Get threads by id
     *
     * @param $iThreadId
     * @param $iPage
     * @param $iPageSize
     * @param null $iPost
     * @return array
     */
    public function getThreadById($iThreadId, $iPage, $iPageSize, $iPost = null)
    {
        $aThreadCondition = array();

        $aThreadCondition[] = 'ft.thread_id = ' . $iThreadId . '';

        list($iCnt, $aThreads) = Phpfox::getService('forum.thread')->getThread($aThreadCondition, array(), 'fp.time_stamp ASC', $iPage, $iPageSize, $iPost);

        //get user image link
        if (isset ($aThreads['posts'])) {
            foreach ($aThreads['posts'] as $iKey => $aThread) {

                $aThreads['posts'][$iKey]['user_image_path'] = Phpfox::getLib('image.helper')->display(array(
                    'user' => $aThread,
                    'server_id' => $aThread['user_server_id'],
                    'suffix' => '_75_square',
                    'return_url' => true
                ));
                $aThreads['posts'][$iKey]['time_phrase'] = Phpfox::getLib('date')->convertTime($aThread['time_stamp'], 'mail.mail_time_stamp');

                $aThreads['posts'][$iKey]['phrase'] = Phpfox::getPhrase('forum.by_full_name_on_time', array(
                    'full_name' => $aThread['full_name'],
                    'time' => $aThreads['posts'][$iKey]['time_phrase']
                ));

                if (isset ($aThreads['posts'][$iKey]['text'])) {
                    $aThreads['posts'][$iKey]['text'] = Phpfox::getLib('parse.output')->parse($aThreads['posts'][$iKey]['text']);
                }

                //get quote
                if (Phpfox::getUserParam('forum.can_multi_quote_forum')) {
                    $sQuote = Phpfox::getService('forum.post')->getQuotes($aThread['thread_id'], $aThread['post_id']);
                    $aThreads['posts'][$iKey]['quote'] = $sQuote;
                }

                //get share feed link
                if (!empty ($aThread['aFeed']['feed_link'])) {
                    $aThreads['posts'][$iKey]['share_feed_link'] = Phpfox::getPhrase('share.hi_check_this_out_bbcode', array('url' => $aThread['aFeed']['feed_link']));

                    $aThreads['posts'][$iKey]['share_feed_link_url'] = Phpfox::getPhrase('share.hi_check_this_out_url', array('url' => $aThread['aFeed']['feed_link']));
                }
                $aThreads['posts'][$iKey]['text_html'] = $aThreads['posts'][$iKey]['text'];
                $aThreads['posts'][$iKey]['text'] = Phpfox::getService('accountapi.emoticon')->processEmoticon($aThreads['posts'][$iKey]['text']);
            }

            return array(
                'thread' => $aThreads['posts'],
                'size' => (int)$iCnt,
                'page_size' => $iPageSize
            );
        }

        if (!isset ($aThreads['posts'])) {
            return array(
                'notice' => Phpfox::getPhrase('forum.nothing_found')
            );
        }
    }

    /**
     * Get My threads/ My Posts
     *
     * @param $iPage
     * @param null $sThread
     * @param $iPageSize
     * @param null $iForumId
     * @return array
     */
    public function getMyThreadsPosts($iPage, $sThread = null, $iPageSize, $iForumId = null)
    {
        $bIsTagSearch = false;
        $bIsModuleTagSearch = false;

        $oSearch = Phpfox::getService('forum')->getSearchFilter(true);

        $sViewId = 'ft.view_id = 0';
        $bIsSearch = false;

        if (!empty ($iForumId)) {
            if (Phpfox::getUserParam('forum.can_approve_forum_thread') || Phpfox::getService('forum.moderate')->hasAccess($iForumId, 'approve_thread')) {
                $sViewId = 'ft.view_id >= 0';
            }
            $aForum = Phpfox::getService('forum')->id($iForumId)->getForum();
        }

        if (!$bIsSearch && $this->request()->get('view') != 'pending-post') {
            if (!empty($sThread)) {
                switch ($sThread) {
                    case 'my-thread':
                        $oSearch->setCondition('AND ft.user_id = ' . Phpfox::getUserId());
                        break;
                    case 'pending-thread':
                        if (Phpfox::getUserParam('forum.can_approve_forum_thread')) {
                            $sViewId = 'ft.view_id = 1';
                        }
                        break;
                    default:
                        break;
                }

                $oSearch->setCondition('AND ft.group_id = 0 AND ' . $sViewId . ' AND ft.is_announcement = 0');

                $bIsSearch = true;
            } else {
                $oSearch->setCondition('ft.forum_id = ' . $aForum['forum_id'] . ' AND ft.group_id = 0 AND ' . $sViewId . ' AND ft.is_announcement = 0');
            }
        }

        if (($iDaysPrune = $oSearch->get('days_prune')) && $iDaysPrune != '-1') {
            $oSearch->setCondition('AND ft.time_stamp >= ' . (PHPFOX_TIME - ($iDaysPrune * 86400)));
        }

        if (empty($iForumId)) {
            if ($bIsTagSearch === true) {
                $oSearch->setCondition("AND ft.group_id = 0 AND tag.tag_url = '" . Phpfox::getLib('database')->escape($this->request()->get('req3')) . "'");
            }
        }

        list($iCnt, $aThreads) = Phpfox::getService('forum.thread')->isSearch($bIsSearch)
            ->isTagSearch($bIsTagSearch)
            ->isNewSearch(($sThread == 'new' ? true : false))
            ->isSubscribeSearch(($sThread == 'subscribed' ? true : false))
            ->isModuleSearch($bIsModuleTagSearch)
            ->get($oSearch->getConditions(), 'ft.order_id DESC, ' . $oSearch->getSort(), $iPage, $iPageSize);

        foreach ($aThreads as $iKey => $aThread) {
            $sTimePhrase = Phpfox::getLib('date')->convertTime($aThread['time_stamp'], 'feed.feed_display_time_stamp');

            //set time phrase
            $aThreads[$iKey]['phrase'] = Phpfox::getPhrase('forum.by_full_name_on_time', array(
                'full_name' => $aThread['full_name'],
                'time' => $sTimePhrase
            ));

            //get user image path
            $aThreads[$iKey]['user_image_path'] = Phpfox::getLib('image.helper')->display(array(
                    'file' => $aThread['user_image'],
                    'path' => 'core.url_user',
                    'suffix' => '_75_square',
                    'return_url' => true
                )
            );

        }

        return array(
            'thread' => (!empty($aThreads)) ? $aThreads : array('notice' => Phpfox::getPhrase('forum.no_threads_found')),
            'size' => (int)$iCnt,
            'page_size' => $iPageSize
        );
    }

    /**
     * Get thread
     * @param $iPostId
     * @return array
     */
    public function getThreadByPostId($iPostId)
    {
        //get thread id
        $iThreadId = $this->database()
            ->select('thread_id')
            ->from(Phpfox::getT('forum_post'))
            ->where('post_id = ' . (int)$iPostId)
            ->execute('getSlaveField');

        return $this->getThreadById($iThreadId, 1, 20, $iPostId);
    }
}