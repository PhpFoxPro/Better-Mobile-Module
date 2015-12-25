<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dat
 * Date: 10/14/13
 * Time: 3:28 PM
 * To change this template use File | Settings | File Templates.
 */
class Accountapi_Service_Music extends Phpfox_Service{
    public function __construct(){
        $this->_sTable = Phpfox::getT('music_song');
    }

    /**song
     * get all detail of song
     * @param $iMusicId
     * @return mixed
     */
    public function getSong($iMusicId) {
        $aSong = Phpfox::getService('music')->getSong($iMusicId);
        $this->_processSong($aSong);
        return $aSong;
    }

    /**
     * add user image, song path
     * @param $aSong
     * @return mixed
     */
    private function _processSong(&$aSong) {
        $aSong['user_image_path'] = Phpfox::getLib('image.helper')->display(array(
                'server_id' => $aSong['server_id'],
                'user' => $aSong,
                'suffix' => '_75_square',
                'return_url' => true
            )
        );

        $aSong['time_stamp'] = Phpfox::getLib('date')->convertTime($aSong['time_stamp']);
        $aSong['short_text'] = Phpfox::getPhrase('music.by_lowercase') . ' '. $aSong['full_name'] ;
        $aSong['can_post_comment'] = Phpfox::getService('comment')->canPostComment($aSong['user_id'], $aSong['privacy_comment']);

        //check have share
        if (Phpfox::isModule('share')) {
            $aSong['no_share'] = true;
        }

        return $aSong;
    }

    /**
     * get all songs
     * @param $iUserId
     * @param $sType
     * @param $iGenreId
     * @param $sList
     * @param $iPage
     * @return array
     */
    public function getAllSongs($iUserId, $sType, $iGenreId, $sList, $iPage) {
        $sWhere = "1 = 1";
        if ($iGenreId != "0") {
            if ($sType == "album") {
                $sWhere .= " AND ms.album_id = ". $iGenreId;
            } else {
                $sWhere .= " AND ms.genre_id = ". $iGenreId;
            }

        }
        if ($sList == "my") {
            $sWhere .= " AND ms.user_id = ". $iUserId;
        } else if ($sList == "friend") {
            list(, $aFriends) = Phpfox::getService('friend')->get(array(), 'friend.time_stamp DESC', '', '', true, false, false, $iUserId);
            $sWhere .= " AND ms.user_id in (0";
            foreach($aFriends as $aFriend) {
                $sWhere .= ", ". $aFriend['user_id'];
            }
            $sWhere .= ")";
        }
        $iCount = $this->database()
            ->select('COUNT(*)')
            ->from($this->_sTable,'ms')
            ->leftJoin(Phpfox::getT('music_album'), 'ma', 'ma.album_id = ms.album_id')
            ->leftJoin(Phpfox::getT('music_profile'), 'mp', 'mp.song_id = ms.song_id AND mp.user_id = ' . $iUserId)
            ->leftJoin(Phpfox::getT('music_song_rating'), 'vr', 'vr.item_id = ms.song_id AND vr.user_id = ' . $iUserId)
            ->where($sWhere)
            ->execute('getSlaveField');




        $iSize = Phpfox::getParam('accountapi.music_page_size');
        if (!$iPage) {
            $iPage = 1;
        }

        if (Phpfox::isModule('like'))
        {
            $this->database()->select('lik.like_id AS is_liked, ')->leftJoin(Phpfox::getT('like'), 'lik', 'lik.type_id = \'music_song\' AND lik.item_id = ms.song_id AND lik.user_id = '. $iUserId);
        }
        $aSongs = $this->database()->select('ms.*, ms.total_comment as song_total_comment, ms.total_play as song_total_play, ms.time_stamp as song_time_stamp, ms.is_sponsor AS song_is_sponsor, ma.name AS album_url, mp.play_id AS is_on_profile, mp.user_id AS profile_user_id, vr.rate_id AS has_rated, ' . Phpfox::getUserField())
            ->from($this->_sTable,'ms')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = ms.user_id')
            ->leftJoin(Phpfox::getT('music_album'), 'ma', 'ma.album_id = ms.album_id')
            ->leftJoin(Phpfox::getT('music_profile'), 'mp', 'mp.song_id = ms.song_id AND mp.user_id = ' . $iUserId)
            ->leftJoin(Phpfox::getT('music_song_rating'), 'vr', 'vr.item_id = ms.song_id AND vr.user_id = ' . $iUserId)
            ->where($sWhere)
            ->limit($iPage, $iSize, $iCount)
            ->order('ms.time_stamp desc')
            ->execute('getSlaveRows');
        if (!empty($aSongs)) {
        foreach ($aSongs as $iKey => $aSong)
            {
                $this->_processSong($aSongs[$iKey]);
                $aSongs[$iKey]['song_path'] = Phpfox::getService('music')->getSongPath($aSong['song_path'], $aSong['server_id']);
            }

        } else {
            $aSongs['notice'] = Phpfox::getPhrase('music.no_songs_found');
        }
        return array($iCount, $aSongs);
    }

    /**
     * get album detail
     * @param $iAlbumId
     * @return mixed
     */
    public function getAlbum($iAlbumId) {
        $aAlbum = Phpfox::getService('music.album')->getAlbum($iAlbumId);
        $aAlbum['album_image_path'] = Phpfox::getLib('image.helper')->display(array(
                'server_id' => $aAlbum['server_id'],
                'path' => 'music.url_image',
                'file' => $aAlbum['image_path'],
                'suffix' => '_75_square',
                'return_url' => true
            )
        );
        $aAlbum['time_stamp'] = Phpfox::getLib('date')->convertTime($aAlbum['time_stamp']);
        return $aAlbum;
    }
}