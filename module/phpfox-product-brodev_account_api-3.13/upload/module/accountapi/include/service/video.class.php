<?php
/**
 * Created by JetBrains PhpStorm.
 * User: huy nguyen
 * Date: 10/22/13
 * Time: 4:33 PM
 * To change this template use File | Settings | File Templates.
 */
class Accountapi_Service_Video extends Phpfox_Service
{
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('video');
    }

    /**
     * get video detail
     * @param $iVideoId
     * @return mixed
     */
    public function getDetail($iVideoId)
    {
        if (Phpfox::isModule('like')) {
            $this->database()->select('lik.like_id AS is_liked, ')->leftJoin(Phpfox::getT('like'), 'lik', 'lik.type_id = \'video\' AND lik.item_id = v.video_id AND lik.user_id = ' . Phpfox::getUserId());
        }
        $aVideo = $this->database()->select('v.*, vt.text_parsed, ve.video_url, ve.embed_code, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'v')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = v.user_id')
            ->join(Phpfox::getT('video_text'), 'vt', 'vt.video_id = v.video_id')
            ->leftJoin(Phpfox::getT('video_embed'), 've', 've.video_id = v.video_id')
            ->where('v.video_id = ' . $iVideoId)
            ->order('v.time_stamp desc')
            ->execute('getSlaveRow');
        $aVideo = $this->_processVideo($aVideo);
        return $aVideo;
    }

    /**
     * Get video by id
     * @param $iVideo
     * @return array
     */
    public function get($iVideo) {
        $aVideo = Phpfox::getService('video')->callback(false)->getVideo($iVideo);

        if (!$aVideo) {
            return array(
                'notice' => Phpfox::getPhrase('video.the_video_you_are_looking_for_does_not_exist_or_has_been_removed')
            );
        }

        if (!empty($aVideo['vidly_url_id'])) {
            $aVideo['embed_url'] = 'http://s.vid.ly/embeded.html?link=' . $aVideo['vidly_url_id'] . 'autoplay=false';
        } else {
            if (isset($aVideo['youtube_video_url'])) {
                $aVideo['embed_url'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http') . '://www.youtube.com/embed/' . $aVideo['youtube_video_url'];
            } else {
                if (!$aVideo['in_process'] && $aVideo['is_stream']) {
                    $aVideo['embed_url'] = $aVideo['embed_code'];
                }

                preg_match_all('/src="([^"]+)"/', $aVideo['embed_url'], $sUrl);
                if (isset ($sUrl[1][0]) && $this->startsWith($sUrl[1][0], '//')) {
                    $aVideo['embed_url'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http') . ':' . $sUrl[1][0];
                }
            }
        }

        //get image url
        if (isset ($aVideo['image_path'])) {
            $aVideo['photo'] = Phpfox::getLib('image.helper')->display(array(
                    'path' => 'video.url_image',
                    'server_id' => $aVideo['image_server_id'],
                    'file' => $aVideo['image_path'],
                    'suffix' => '_120',
                    'return_url' => true
                )
            );
        }

        if (isset ($aVideo['user_image'])) {
            $aVideo['user_image_path'] = Phpfox::getLib('image.helper')->display(array(
                    'user' => $aVideo,
                    'suffix' => '_50_square',
                    'return_url' => true
                )
            );
        }
        //can post comment
        $aVideo['can_post_comment'] = Phpfox::getService('comment')->canPostComment($aVideo['user_id'], $aVideo['privacy_comment']);
        $aVideo['is_liked'] = empty($aVideo['is_liked']) ? false : true;
        $aVideo['time_stamp'] = Phpfox::getLib('date')->convertTime($aVideo['time_stamp'], 'comment.comment_time_stamp');
        $aVideo['id'] =  $aVideo['video_id'];

        return $aVideo;
    }

    /**
     * Check start with string
     * @param $haystack
     * @param $needle
     * @return bool
     */
    private function startsWith($haystack, $needle)
    {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }

    /**
     * process video
     * @param $aVideo
     * @return array
     */
    private function _processVideo($aVideo)
    {
        if (!$aVideo['is_stream']) {
            $sConfig = '{\'clip\':{';
            $sConfig .= '\'baseUrl\': \'' . Phpfox::getParam('video.url') . '\',';
            $sConfig .= '\'url\': \'' . $aVideo['destination'] . '\',';
            $sConfig .= '\'autoBuffering\': true,';
            $sConfig .= '\'autoPlay\': false';
            $sConfig .= '}';
            $sConfig .= '}';

            $aVideo['embed_code'] = '<object width="430" height="344">';
            $aVideo['embed_code'] .= '<embed width="430" height="344" type="application/x-shockwave-flash" wmode="transparent" src="' . Phpfox::getParam('core.url_static_script') . 'player/flowplayer/flowplayer.swf?config=' . $sConfig . '"></embed>';
            $aVideo['embed_code'] .= '</object>';
            $aVideo['video_link'] = Phpfox::getParam('video.url'). $aVideo['destination'];
        }

        $sUserImage = Phpfox::getLib('image.helper')->display(array(
                'user' => $aVideo,
                'suffix' => '_50_square',
                'return_url' => true
            )
        );

        $sVideoPhoto = Phpfox::getLib('image.helper')->display(array(
                'path' => 'video.url_image',
                'server_id' => $aVideo['image_server_id'],
                'file' => $aVideo['image_path'],
                'suffix' => '_120',
                'return_url' => true
            )
        );

        $aReturns = array(
            'id' => $aVideo['video_id'],
            'title' => $aVideo['title'],
            'text_html' => $aVideo['text_parsed'],
            'text' => Phpfox::getLib('parse.output')->parse(Phpfox::getService('accountapi.emoticon')->processEmoticon($aVideo['text_parsed'])),
            'total_like' => $aVideo['total_like'],
            'permalink' => Phpfox::getLib('url')->permalink('video', $aVideo['video_id'], $aVideo['title']),
            'embed_code' => $aVideo['embed_code'],
            'photo' => $sVideoPhoto,
            'is_liked' => (empty($aVideo['is_liked']) ? false : true),
            'total_comment' => $aVideo['total_comment'],
            'full_name' => $aVideo['full_name'],
            'user_id' => $aVideo['user_id'],
            'uploaded_by_url' => Phpfox::getLib('url')->makeUrl($aVideo['user_name']),
            'uploaded_by_username' => $aVideo['user_name'],
            'user_image_path' => $sUserImage,
            'time_stamp' => Phpfox::getLib('date')->convertTime($aVideo['time_stamp'], 'comment.comment_time_stamp'),
            'video_url' => $aVideo['video_url'],
            'can_post_comment' => Phpfox::getService('comment')->canPostComment($aVideo['user_id'], $aVideo['privacy_comment']),
            'duration' => $aVideo['duration'] == null ? "" : $aVideo['duration'],
            'video_link' =>  Phpfox::getParam('video.url'). $aVideo['destination']
        );
        return $aReturns;
    }

    /**
     * get list blog
     * @param $sList
     * @param $iCategory
     * @param $iPage
     * @param $iUserId
     * @return array
     */
    public function getVideos($sList, $iCategory, $iPage, $iUserId)
    {

        $sWhere = "v.privacy = 0 AND v.in_process = 0 AND v.view_id = 0";
        if ($sList == "my") {
            $sWhere .= " AND v.user_id = " . $iUserId;

        } else if ($sList == "friend") {
            list(, $aFriends) = Phpfox::getService('friend')->get(array(), 'friend.time_stamp DESC', '', '', true, false, false, $iUserId);
            $sWhere .= " AND v.user_id in (0";
            foreach ($aFriends as $aFriend) {
                $sWhere .= ", " . $aFriend['user_id'];
            }
            $sWhere .= ")";
        }


        if ($iCategory != 0) {
            $iVideoIds = $this->database()
                ->select('video_id')
                ->from(Phpfox::getT('video_category_data'))
                ->where('category_id = ' . $iCategory)
                ->execute('getRows');
            $sWhere .= " AND v.video_id in (0";
            foreach ($iVideoIds as $iId) {
                $sWhere .= ", " . $iId['video_id'];
            }
            $sWhere .= ")";
        }

        $iCount = $this->database()
            ->select('COUNT(*)')
            ->from($this->_sTable, 'v')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = v.user_id')
            ->join(Phpfox::getT('video_text'), 'vt', 'vt.video_id = v.video_id')
            ->leftJoin(Phpfox::getT('video_embed'), 've', 've.video_id = v.video_id')
            ->where($sWhere)
            ->execute('getSlaveField');

        if ($iPage == 0) {
            $iPage = 1;
        }

        $iSize = Phpfox::getParam('accountapi.video_page_size');

        if (Phpfox::isModule('like')) {
            $this->database()->select('lik.like_id AS is_liked, ')->leftJoin(Phpfox::getT('like'), 'lik', 'lik.type_id = \'video\' AND lik.item_id = v.video_id AND lik.user_id = ' . $iUserId);
        }
        $aLists = $this->database()->select('v.*, vt.text_parsed, ve.video_url, ve.embed_code, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'v')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = v.user_id')
            ->join(Phpfox::getT('video_text'), 'vt', 'vt.video_id = v.video_id')
            ->leftJoin(Phpfox::getT('video_embed'), 've', 've.video_id = v.video_id')
            ->where($sWhere)
            ->limit($iPage, $iSize, $iCount)
            ->order('v.time_stamp desc')
            ->execute('getSlaveRows');

        $aReturns = array();
        foreach ($aLists as $aItem) {
            $aReturns[] = $this->_processVideo($aItem);
        }
        if (empty($aReturns)) {
            $aReturns['notice'] = Phpfox::getPhrase('video.no_videos_found');
        }
        return array($iCount, $aReturns);
    }


}