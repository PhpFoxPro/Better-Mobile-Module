<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ADMIN
 * Date: 11/16/12
 * Time: 3:56 PM
 * To change this template use File | Settings | File Templates.
 */

class Bettermobile_Component_Ajax_Ajax extends Phpfox_Ajax
{

    public function showSideBar(){
        $sShow = $this->get('sShow');
        $sShow = ($sShow == 'true') ? 'false' : 'true';
        if($sShow == 'true'){
            $sFocus= $this->get('sFocus');
            $this->call("$('#mobile_holder').css('left',270);");
            $this->call("$('#mobile_holder').css({position:'fixed'});");
            $this->call("$('#showsidebar').css({display:'block'});");
            if($sFocus == 'true'){
                $this->call("$('#header_sub_menu_search_input').focus();");
            }
        }else{
            $this->call("$('#mobile_holder').css('left',0);");
            $this->call("$('#mobile_holder').css({position:'relative'});");
            $this->call("$('#showsidebar').css({display:'none'});");
        }
        $this->html('#mobile_header_home1', "<a href=\"#\" onclick=\"$.ajaxCall('bettermobile.showSideBar', 'sShow=". $sShow ."')\" id=\"mobile_header_home\">Home</a>");
    }
    //notification ajax
    public function getAll()
    {
        if (!Phpfox::isUser())
        {
            $this->call('<script type="text/javascript">window.location.href = \'' . Phpfox::getLib('url')->makeUrl('user.login') . '\';</script>');
        }
        else
        {
            $_REQUEST['js_mobile_version']= true;
            Phpfox::getBlock('bettermobile.link');
        }
    }
    //friend ajax
    public function getRequests()
    {
        if (!Phpfox::isUser())
        {
            $this->call('<script type="text/javascript">window.location.href = \'' . Phpfox::getLib('url')->makeUrl('user.login') . '\';</script>');
        }
        else
        {
            $_REQUEST['js_mobile_version']= true;
            Phpfox::getBlock('bettermobile.accept');
        }
    }
    //mail ajax
    public function getLatest()
    {
        if (!Phpfox::isUser())
        {
            $this->call('<script type="text/javascript">window.location.href = \'' . Phpfox::getLib('url')->makeUrl('user.login') . '\';</script>');
        }
        else
        {
            $_REQUEST['js_mobile_version']= true;
            Phpfox::getBlock('bettermobile.latest');
        }
    }


    public function playInFeed()
    {
        $aSong = Phpfox::getService('music')->getSong($this->get('id'));

        if (!isset($aSong['song_id']))
        {
            $this->alert(Phpfox::getPhrase('music.unable_to_find_the_song_you_are_trying_to_play'));

            return false;
        }
        Phpfox::getService('music.process')->play($aSong['song_id']);

        $sSongPath = $aSong['song_path'];

        $sWidth = '425px';
        if ($this->get('track'))
        {
            $sWidth = '100%';
        }

        if ($this->get('is_player'))
        {
            $sDivId = 'js_music_player_all';
        }
        else
        {
            $sDivId = 'js_tmp_music_player_' . $aSong['song_id'];

            if ($this->get('feed_id'))
            {
                Phpfox::getBlock('bettermobile.audiojs', array('aSong' => $aSong));
                $this->call('$(\'#js_item_feed_' . $this->get('feed_id') . '\').find(\'.activity_feed_content_link:first\').html(\'<div id="' . $sDivId . '" >'. $this->getContent(false) .'</div>\');');
            }
            else
            {
                Phpfox::getBlock('bettermobile.audiojs', array('aSong' => $aSong));

                $this->call('$(\'#' . ($this->get('track') ? $this->get('track') : 'js_controller_music_play_' . $this->get('id') . '') . '\').html(\'<div id="' . $sDivId . '" >'. $this->getContent(false) .'</div>\');');
            }
        }

        // $this->call('$Core.player.load({id: \'' . $sDivId . '\', auto: true, type: \'music\', play: \'' . $sSongPath . '\'});');
        // Fixes http://www.phpfox.com/tracker/view/7262/
        $this->call('audiojs.events.ready(function(){audiojs.createAll();});');

    }
    public function aUserProfile(){

        $this->bUser = true;
    }
    // like process
    public function add()
    {
        Phpfox::isUser(true);

        if (Phpfox::getService('like.process')->add($this->get('type_id'), $this->get('item_id')))
        {
            if ($this->get('type_id') == 'feed_mini' && $this->get('custom_inline'))
            {
                $this->_loadCommentLikes(Phpfox::getParam('like.allow_dislike'));
            }
            else
            {
                $this->_loadLikes(true);
            }
        }
    }

    public function delete()
    {
        Phpfox::isUser(true);
        Phpfox::getService('like.process')->delete($this->get('type_id'), $this->get('item_id'), (int) $this->get('force_user_id'));
    }

    public function addAction()
    {
        Phpfox::isUser(true);
        $sTypeId = str_replace('-', '_', $this->get('item_type_id'));
        $this->set(array('type_id' => $sTypeId));

        if (Phpfox::getService('like.process')->doAction($this->get('action_type_id'), $this->get('item_type_id'), $this->get('item_id'), $this->get('module_name') ))
        {
            if ($this->get('type_id') == 'feed_mini')// && $this->get('custom_inline'))
            {
                $this->_loadCommentLikes(Phpfox::getParam('like.allow_dislike'));
            }
            else
            {
                $bIsLiked = Phpfox::getService('like')->didILike($sTypeId, $this->get('item_id'));
                $this->_loadLikes($bIsLiked);
            }
        }

    }

    public function removeAction()
    {
        $sTypeId = $this->get('type_id');
        $sModuleId = $this->get('module_name');
        // $sDeleteAction = $this->get('action_type_id');// for now dislike is the only available and = 2

        if (empty($sTypeId))
        {
            $sTypeId = $this->get('like_type_id');
        }

        if (empty($sModuleId) && !empty($sTypeId))
        {
            $this->set('module_name', $sTypeId);
            $sModuleId = $sTypeId;
        }
        if (empty($sTypeId) && $this->get('item_type_id') != '')
        {
            $this->set('type_id', $this->get('item_type_id'));
            $sTypeId = $this->get('item_type_id');
        }

        // its not decrementing the total_dislike column

        if (Phpfox::getService('like.process')->removeAction( 2, $sTypeId, $this->get('item_id'), $sModuleId ))
        {
            if ($this->get('type_id') == 'feed_mini' || $this->get('item_type_id') == 'feed_mini')// && $this->get('custom_inline'))
            {
                $this->_loadCommentLikes(true);
            }
            else
            {
                $bIsLiked = Phpfox::getService('like')->didILike($sTypeId, $this->get('item_id'));
                $this->_loadLikes($bIsLiked);
            }
        }
    }

    public function browse()
    {
        $this->error(false);
        Phpfox::getBlock('like.browse');
        $this->setTitle((($this->get('type_id') == 'pages' && $this->get('force_like') == '') ? Phpfox::getPhrase('like.members') : Phpfox::getPhrase('like.people_who_like_this')));
    }

    private function _loadCommentLikes($bIsDislike = false)
    {
        if ($bIsDislike == true)
        {
            // get the total dislikes
            // $iDislikes = Phpfox::getService('like')->getDislikes($this->get('item_type_id'), $this->get('item_type_id'), true);
            $aComment = Phpfox::getService('comment')->getComment($this->get('item_id'));
            $iDislikes = $aComment['total_dislike'];
            $sCall = '$("#js_comment_' . $this->get('item_id') . '").find(".comment_mini_action:first").find(".js_dislike_link_holder").show();';

            if ($iDislikes > 1)
            {
                $sPhrase = Phpfox::getPhrase('like.total_people', array('total' => $iDislikes));
            }
            else if ($iDislikes > 0)
            {
                $sPhrase = Phpfox::getPhrase('like.1_person');
            }
            else
            {
                $sCall = '$(\'#js_comment_' . $this->get('item_id') . '\').find(\'.comment_mini_action:first\').find(\'.js_dislike_link_holder\').hide();';
                $sPhrase = '0';
            }
            $sCall .= '$("#js_dislike_mini_a_'. $this->get('item_id') .'").html("'. $sPhrase .'");';
            $this->call($sCall);
        }
        else
        {
            $aComment = Phpfox::getService('comment')->getComment($this->get('item_id'));
            if ($aComment['total_like'] > 0)
            {
                $sPhrase = Phpfox::getPhrase('like.1_person');
                if ($aComment['total_like'] > 1)
                {
                    $sPhrase = Phpfox::getPhrase('like.total_people', array('total' => $aComment['total_like']));
                }
                $this->call('$(\'#js_comment_' . $this->get('item_id') . '\').find(\'.comment_mini_action:first\').find(\'.js_like_link_holder\').show();');
                $this->call('$(\'#js_comment_' . $this->get('item_id') . '\').find(\'.comment_mini_action:first\').find(\'.js_like_link_holder_info\').html(\'' . $sPhrase . '\');');
            }
            else
            {
                $this->call('$(\'#js_comment_' . $this->get('item_id') . '\').find(\'.comment_mini_action:first\').find(\'.js_like_link_holder\').hide();');
            }
        }

    }

    private function _loadLikes($bIsLiked)
    {
        $sType = $this->get('type_id');
        if (empty($sType))
        {
            $sType = $this->get('item_type_id');
        }

        if (Phpfox::getParam('like.show_user_photos'))
        {
            // The block like.block.display works very different if this setting is enabled
            $aLikes = Phpfox::getService('like')->getLikesForFeed($sType, $this->get('item_id'), false, Phpfox::getParam('feed.total_likes_to_display'), true);

            // The dislikes are fetched and displayed from the template
            $aFeed = array(
                'like_type_id' => $sType,
                'item_id' => $this->get('item_id'),
                'likes' => $aLikes,
                'feed_total_like' => Phpfox::getService('like')->getTotalLikeCount(),
                'call_displayactions' => true,
                'feed_id' => $this->get('parent_id')
            );
        }
        else
        {

            // We get the dislikes and likes and the template only displays them
            $aFeed = Phpfox::getService('like')->getAll( $sType, $this->get('item_id') );

            // Fix for likes
            $aFeed['feed_like_phrase'] = $aFeed['likes']['phrase'];
            $aFeed['feed_id'] = $this->get('parent_id');

            // Fix for dislikes
            $aFeed['call_displayactions'] = true;
            $aFeed['type_id'] = $this->get('type_id');
            $aFeed['dislike_phrase'] = $aFeed['dislikes']['phrase'];
            $aFeed['total_dislike'] = Phpfox::getService('like')->getDislikes($sType, $this->get('item_id'));
            $aFeed['feed_total_like'] = Phpfox::getService('like')->getTotalLikes();
        }

        $this->template()->assign(array('aFeed' => $aFeed));
        $this->template()->getTemplate('bettermobile.block.display');
    }

    /**
     * remove friend from profile
     * @return mixed
     */
    public function removeFriend() {
        $bDeleted = $this->get('id') ? Phpfox::getService('friend.process')->delete($this->get('id'), false) : Phpfox::getService('friend.process')->delete($this->get('friend_user_id'), false);
        if ($bDeleted)        {
            $this->call('$("#core_js_messages").message("' . Phpfox::getPhrase('friend.friend_successfully_removed') . '", "valid").slideDown("slow").fadeOut(5000);');

        }
        return false;
    }

    /**
     * get friend chat messenger
     */
    public function getFriendChat() {
        //Call from auto ?
        $bIsAuto = (boolean) $this->get('auto');
        //Paging
        if (!$sPage = $this->get('p')) {
            $sPage = 1;
        }
        if (!$iSize = $this->get('size')) {
            $iSize = 0;
        }
        /** @var Messenger_Service_Process $oProcess */
        $oProcess = Phpfox::getService('messenger.process');
        $aFriends = $oProcess->getChatFriend(Phpfox::getUserId(), $sPage, $iSize);
        if (count($aFriends) < $oProcess->getDefaultPageSize() || empty($aFriends)) {
            $sPage = 0; // Paging end
        } else {
            $sPage += 1; // Page next
        }
        $aData = array(
            'is_first' => $this->get('p') ? false : true,
            // --> Chat paging info
            'page_size' => $oProcess->getDefaultPageSize(),
            'page_next' => $sPage,
            // --> flag as this request is reload from server
            'auto' => $bIsAuto,
            // --> Convert minute to second
            'reload_interval' => $oProcess->getChatCacheTime() * 60,
            // End reload options
            // --> Friends is an array of data which contain in chat main
            'friends' => array(),
        );
        $aData['friends'] = $oProcess->buildFriendListJson($aFriends);

        $this->call('$Core.betterMobileMessengerHandle.getFriends(' . json_encode($aData) . ');');
    }

    /**
     * @param $iUserId
     * @return mixed
     */
    private function _getMessengerUrl($iUserId)
    {
        return Phpfox::getLib('url')->makeUrl('messenger', array(
            'uid' => $iUserId,
        ));
    }
    /**
     *
     */
    public function searchFriendChat()
    {
        $sQuery = $this->get('query');
        $aSearch = Phpfox::getService('messenger.process')->searchFriend(Phpfox::getUserId(), $sQuery);

        $aData = array(
            'count' => count($aSearch),
            'empty_message' => "<div class='chat_message_text_box'>" . Phpfox::getPhrase('messenger.search_not_found') . "</div>",
            'friends' => array(),
        );
        foreach ($aSearch as $aFriend) {
            $aData['friends'][] = array(
                'user_id' => $aFriend['user_id'],
                'avatar_url' => $aFriend['avatar_url'],
                'full_name' => $aFriend['full_name'],
                'full_name_clean' => $aFriend['full_name'],
                'online' => $aFriend['isOnline'],
            );
        }
        $this->call('$Core.betterMobileMessengerHandle.searchFriend(' . json_encode($aData) . ');');
    }

}