<?php
defined('PHPFOX') or die('NO DICE!');

class Accountapi_Service_Callback extends Phpfox_Service
{
    public function getActivityFeedProfileComment($aRow)
    {
        if (!isset($aRow['item_user_id']))
        {
            return false;
        }

        if ($aRow['user_id'] == $aRow['item_user_id'])
        {
            $aItem = $this->database()->select(Phpfox::getUserField('u', 'parent_'))
                ->from(Phpfox::getT('user'), 'u')
                ->where('u.user_id = ' . (int) $aRow['item_user_id'])
                ->execute('getSlaveRow');
        }
        else
        {
            $aItem = $this->database()->select(Phpfox::getUserField('u', 'parent_'))
                ->from(Phpfox::getT('user'), 'u')
                ->where('u.user_id = ' . (int) $aRow['item_id'])
                ->execute('getSlaveRow');

            $aItem2 = $this->database()->select(Phpfox::getUserField('u', 'parent_'))
                ->from(Phpfox::getT('user'), 'u')
                ->where('u.user_id = ' . (int) $aRow['item_user_id'])
                ->execute('getSlaveRow');
        }

        if (empty($aItem['parent_user_id']))
        {
            // $this->database()->delete(Phpfox::getT('feed'), 'feed_id = ' . (int) $aRow['feed_id']);

            return false;
        }

        $sLink = Phpfox::getLib('url')->makeUrl($aItem['parent_user_name'], array('feed' => $aRow['feed_id']));

        $aReturn = array(
            'no_share' => true,
            'feed_status' => $aRow['content'],
            'feed_link' => $sLink,
            // 'total_comment' => $aRow['total_comment'],
            // 'feed_total_like' => $aRow['total_like'],
            // 'feed_is_liked' => $aRow['is_liked'],
            'feed_icon' => Phpfox::getLib('image.helper')->display(array('theme' => 'misc/comment.png', 'return_url' => true)),
            'time_stamp' => $aRow['time_stamp'],
            'enable_like' => false,
            // 'comment_type_id' => 'feed',
            // 'like_type_id' => 'feed_comment'
        );

        if ($aRow['user_id'] != $aRow['item_user_id'])
        {
            $aRow['server_id'] = $aRow['user_server_id'];
            $aReturn['parent_user'] = Phpfox::getService('user')->getUserFields(true, $aItem2, 'parent_');
        }

        $aReturn['force_user']['full_name'] = $aItem['parent_full_name'];
        $aReturn['force_user']['user_name'] = $aItem['parent_user_name'];
        $aReturn['force_user']['user_image'] = $aItem['parent_user_image'];
        $aReturn['force_user']['server_id'] = $aItem['user_parent_server_id'];

        return $aReturn;
    }


    public function getActivityFeedCustom($aItem)
    {
        $sLink = Phpfox::getLib('url')->makeUrl($aItem['user_name']);
        $aReturn = array(
            'feed_link' => $sLink,
            'feed_title' => '',
            'feed_info' => Phpfox::getPhrase('feed.updated_gender_profile_information', array('gender' => Phpfox::getService('user')->gender($aItem['gender'], 1))),
            //'total_comment' => $aRow['total_comment'],
            //'feed_total_like' => $aRow['total_like'],
            //'feed_is_liked' => isset($aRow['is_liked']) ? $aRow['is_liked'] : false,
            'feed_icon' => Phpfox::getLib('image.helper')->display(array('theme' => 'misc/page_edit.png', 'return_url' => true)),
            'time_stamp' => $aItem['time_stamp'],
            'enable_like' => false,
            //'comment_type_id' => 'custom',
            //'like_type_id' => 'custom'
        );
        (($sPlugin = Phpfox_Plugin::get('custom.component_service_callback_getactivityfeed__1')) ? eval($sPlugin) : false);
        return $aReturn;

    }

    public function getActivityFeedPhoto($aItem, $aCallback = null)
    {
        if ($aCallback === null)
        {
            $this->database()->select(Phpfox::getUserField('u', 'parent_') . ', ')->leftJoin(Phpfox::getT('user'), 'u', 'u.user_id = photo.parent_user_id');
        }

        $aRow = $this->database()->select('photo.*, l.like_id AS is_liked, pi.description, pfeed.photo_id AS extra_photo_id, pa.album_id, pa.name')
            ->from(Phpfox::getT('photo'), 'photo')
            ->join(Phpfox::getT('photo_info'), 'pi', 'pi.photo_id = photo.photo_id')
            ->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = \'photo\' AND l.item_id = photo.photo_id AND l.user_id = ' . Phpfox::getUserId())
            ->leftJoin(Phpfox::getT('photo_feed'), 'pfeed', 'pfeed.feed_id = ' . (int) $aItem['feed_id'])
            ->leftJoin(Phpfox::getT('photo_album'), 'pa', 'pa.album_id = photo.album_id')
            ->where('photo.photo_id = ' . (int) $aItem['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aRow['photo_id']))
        {
            return false;
        }

        if ((defined('PHPFOX_IS_PAGES_VIEW') && !Phpfox::getService('pages')->hasPerm(null, 'photo.view_browse_photos'))
            || (!defined('PHPFOX_IS_PAGES_VIEW') && $aRow['module_id'] == 'pages' && !Phpfox::getService('pages')->hasPerm($aRow['group_id'], 'photo.view_browse_photos'))
        )
        {
            return false;
        }

        $bIsPhotoAlbum = false;
        if ($aRow['album_id'])
        {
            $bIsPhotoAlbum = true;
        }

        $sLink = Phpfox::permalink('photo', $aRow['photo_id'], $aRow['title']) . ($bIsPhotoAlbum ? 'albumid_' . $aRow['album_id'] : 'userid_' . $aRow['user_id']) . '/';
        $sCustomCss = '';
        $sFeedImageOnClick = '';

        if (($aRow['mature'] == 0 || (($aRow['mature'] == 1 || $aRow['mature'] == 2) && Phpfox::getUserId() && Phpfox::getUserParam('photo.photo_mature_age_limit') <= Phpfox::getUserBy('age'))) || $aRow['user_id'] == Phpfox::getUserId())
        {
            $sCustomCss = 'thickbox photo_holder_image';
            $sImage = Phpfox::getLib('image.helper')->display(array(
                    'server_id' => $aRow['server_id'],
                    'path' => 'photo.url_photo',
                    'file' => Phpfox::getService('photo')->getPhotoUrl(array_merge($aRow, array('full_name' => $aItem['full_name']))),
                    'suffix' => '_100',
                    'class' => 'photo_holder',
                    'return_url' => true,
                )
            );
        }
        else
        {
            $sImage = Phpfox::getLib('image.helper')->display(array(
                    'theme' => 'misc/no_access.png',
                    'return_url' => true,
                )
            );

            $sFeedImageOnClick = ' onclick="tb_show(\'' . Phpfox::getPhrase('photo.warning') . '\', $.ajaxBox(\'photo.warning\', \'height=300&width=350&link=' . $sLink . '\')); return false;" ';
            $sCustomCss = 'no_ajax_link';
        }

        $aListPhotos = array();
        if ($aRow['extra_photo_id'] > 0)
        {
            $aPhotos = $this->database()->select('p.photo_id, p.album_id, p.user_id, p.title, p.server_id, p.destination, p.mature')
                ->from(Phpfox::getT('photo_feed'), 'pfeed')
                ->join(Phpfox::getT('photo'), 'p', 'p.photo_id = pfeed.photo_id')
                ->where('pfeed.feed_id = ' . (int) $aItem['feed_id'])
                ->limit(3)
                ->order('p.time_stamp DESC')
                ->execute('getSlaveRows');

            foreach ($aPhotos as $aPhoto)
            {
                if (($aPhoto['mature'] == 0 || (($aPhoto['mature'] == 1 || $aPhoto['mature'] == 2) && Phpfox::getUserId() && Phpfox::getUserParam('photo.photo_mature_age_limit') <= Phpfox::getUserBy('age'))) || $aPhoto['user_id'] == Phpfox::getUserId())
                {
                    $aListPhotos[] = Phpfox::getLib('image.helper')->display(array(
                            'server_id' => $aPhoto['server_id'],
                            'path' => 'photo.url_photo',
                            'file' => Phpfox::getService('photo')->getPhotoUrl(array_merge($aPhoto, array('full_name' => $aItem['full_name']))),
                            'suffix' => '_100',
                            'return_url' => true,
                        )
                    );
                }
                else
                {
                    $aListPhotos[] = $sImage;
                }
            }

            $aListPhotos = array_merge($aListPhotos, array($sImage));
        }

        $aReturn = array(
            'feed_title' => '',
            'feed_image' => (count($aListPhotos) ? $aListPhotos : $sImage),
            'feed_status' => $aRow['description'],
            'feed_link' => $sLink,
            'total_comment' => $aRow['total_comment'],
            'feed_total_like' => $aRow['total_like'],
            'feed_is_liked' => $aRow['is_liked'],
            'feed_icon' => Phpfox::getLib('image.helper')->display(array('theme' => 'module/photo.png', 'return_url' => true)),
            'time_stamp' => $aRow['time_stamp'],
            'enable_like' => true,
            'comment_type_id' => 'photo',
            'like_type_id' => 'photo',
            'custom_css' => $sCustomCss,
            'custom_rel' => $aRow['photo_id'],
            'custom_js' => $sFeedImageOnClick
        );

        if ($aRow['module_id'] == 'pages')
        {
            $aRow['parent_user_id'] = '';
            $aRow['parent_user_name'] = '';
        }

        if (empty($aRow['parent_user_id']))
        {
            if ($aRow['album_id'])
            {
                $aReturn['feed_status'] = '';
                $aReturn['feed_info'] = Phpfox::getPhrase('feed.added_new_photos_to_gender_album_a_href_link_name_a', array('gender' => Phpfox::getService('user')->gender($aItem['gender'], 1), 'link' => Phpfox::permalink('photo.album', $aRow['album_id'], $aRow['name']), 'name' => Phpfox::getLib('parse.output')->shorten($aRow['name'], (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength ), '...')));
            }
            else
            {
                $aReturn['feed_info'] = (count($aListPhotos) ? Phpfox::getPhrase('feed.shared_a_few_photos') : Phpfox::getPhrase('feed.shared_a_photo'));
            }
        }

        if ($aCallback === null)
        {
            if (!empty($aRow['parent_user_name']) && !defined('PHPFOX_IS_USER_PROFILE') && empty($_POST))
            {
                $aReturn['parent_user'] = Phpfox::getService('user')->getUserFields(true, $aRow, 'parent_');
            }

            if (!PHPFOX_IS_AJAX && defined('PHPFOX_IS_USER_PROFILE') && !empty($aRow['parent_user_name']) && $aRow['parent_user_id'] != Phpfox::getService('profile')->getProfileUserId())
            {
                $aReturn['feed_mini'] = true;
                $aReturn['feed_mini_content'] = Phpfox::getPhrase('photo.full_name_posted_a_href_link_photo_a_photo_a_on_a_href_link_user_parent_full_name_a_s_a_href_link_wall_wall_a',array('full_name' => Phpfox::getService('user')->getFirstName($aItem['full_name']), 'link_photo' => Phpfox::permalink('photo', $aRow['photo_id'], $aRow['title']), 'link_user' => Phpfox::getLib('url')->makeUrl($aRow['parent_user_name']), 'parent_full_name' => $aRow['parent_full_name'], 'link_wall' => Phpfox::getLib('url')->makeUrl($aRow['parent_user_name'])));

                unset($aReturn['feed_status'], $aReturn['feed_image'], $aReturn['feed_content']);
            }
        }
        return $aReturn;
    }


    public function getActivityFeedPhoto_Comment($aRow)
    {
        if (Phpfox::isUser())
        {
            $this->database()->select('l.like_id AS is_liked, ')->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = \'feed_mini\' AND l.item_id = c.comment_id AND l.user_id = ' . Phpfox::getUserId());
        }

        $aItem = $this->database()->select('b.photo_id, b.server_id, b.destination, b.title, b.time_stamp, b.privacy, b.total_comment, b.total_like, c.total_like, ct.text_parsed AS text, f.friend_id AS is_friend, b.mature, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('comment'), 'c')
            ->join(Phpfox::getT('comment_text'), 'ct', 'ct.comment_id = c.comment_id')
            ->join(Phpfox::getT('photo'), 'b', 'c.type_id = \'photo\' AND c.item_id = b.photo_id AND c.view_id = 0')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = b.user_id')
            ->leftJoin(Phpfox::getT('friend'), 'f', "f.user_id = b.user_id AND f.friend_user_id = " . Phpfox::getUserId())
            ->where('c.comment_id = ' . (int) $aRow['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aItem['photo_id']))
        {
            return false;
        }

        $sLink = Phpfox::permalink('photo', $aItem['photo_id'], $aItem['title']);
        $sUser = '<a href="' . Phpfox::getLib('url')->makeUrl($aItem['user_name']) . '">' . $aItem['full_name'] . '</a>';
        $sGender = Phpfox::getService('user')->gender($aItem['gender'], 1);

        if ($aRow['user_id'] == $aItem['user_id'])
        {
            $sMessage = Phpfox::getPhrase('photo.posted_a_comment_on_gender_photo',array('gender' => $sGender));
        }
        else
        {
            $sMessage = Phpfox::getPhrase('photo.posted_a_comment_on_user_name_s_photo',array('user_name' => $sUser));
        }

        $aFeed = array(
            'no_share' => true,
            'feed_info' => $sMessage,
            'feed_link' => $sLink,
            'feed_status' => $aItem['text'],
            'feed_total_like' => $aItem['total_like'],
            'feed_is_liked' => isset($aItem['is_liked']) ? $aItem['is_liked'] : false,
            'feed_icon' => Phpfox::getLib('image.helper')->display(array('theme' => 'module/photo.png', 'return_url' => true)),
            'time_stamp' => isset($aRow['time_stamp']) ? $aRow['time_stamp'] : time(),
            'like_type_id' => 'feed_mini',
            'custom_rel' => $aItem['photo_id'],
            'custom_css' => 'thickbox photo_holder_image'
        );

        $bCanViewItem = true;
        if ($aItem['privacy'] > 0)
        {
            $bCanViewItem = Phpfox::getService('privacy')->check('photo', $aItem['photo_id'], $aItem['user_id'], $aItem['privacy'], $aItem['is_friend'], true);
        }

        if ($bCanViewItem)
        {
            $aFeed['feed_image'] = Phpfox::getLib('image.helper')->display(array(
                    'server_id' => $aItem['server_id'],
                    'path' => 'photo.url_photo',
                    'file' => Phpfox::getService('photo')->getPhotoUrl($aItem),
                    'suffix' => '_100',
                    'return_url' => true,
                )
            );

            if (isset($aItem['mature']))
            {
                if (($aItem['mature'] == 0 || // its public
                    (
                        ($aItem['mature'] == 1 || $aItem['mature'] == 2) && // its restricted
                            Phpfox::getUserId() && // is user
                            (Phpfox::getUserParam('photo.photo_mature_age_limit') <= Phpfox::getUserBy('age')))) || // user is older
                    $aRow['user_id'] == Phpfox::getUserId() // owner and viewer are the same
                )
                {

                }
                elseif ($aItem['mature'] == 1)
                {
                    $aFeed['custom_css'] = 'no_ajax_link';
                    $aFeed['custom_js'] = 'onclick="tb_show(\'' . Phpfox::getPhrase('photo.warning') . '\', $.ajaxBox(\'photo.warning\', \'height=300&amp;width=350&amp;link=' . $aFeed['feed_link'] . '\')); return false;"';
                    $aFeed['feed_image'] = Phpfox::getLib('template')->getStyle('image', 'misc/no_access.png');
                }
                else
                {
                    $aFeed['feed_image'] = Phpfox::getLib('template')->getStyle('image', 'misc/no_access.png');
                }
            }
        }

        return $aFeed;
    }

    public function getActivityFeedStatus($aItem)
    {
        $aRow = $this->database()->select('us.*, l.like_id AS is_liked')
            ->from(Phpfox::getT('user_status'), 'us')
            ->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = \'user_status\' AND l.item_id = us.status_id AND l.user_id = ' . Phpfox::getUserId())
            ->where('us.status_id = ' . (int) $aItem['item_id'])
            ->execute('getSlaveRow');

        if (!empty($aItem['content']))
        {
            if (!empty($aItem['content']))
            {
                $sLink = Phpfox::getLib('url')->makeUrl($aItem['user_name'], array('feed' => $aItem['feed_id']));

                $aReturn = array(
                    'no_share' => true,
                    'feed_status' => $aItem['content'],
                    'feed_link' => $sLink,
                    'feed_icon' => Phpfox::getLib('image.helper')->display(array('theme' => 'misc/application_add.png', 'return_url' => true)),
                    'time_stamp' => $aItem['time_stamp'],
                    'enable_like' => false

                );

                return $aReturn;
            }

            return false;
        }

        $sLink = Phpfox::getLib('url')->makeUrl($aItem['user_name'], array('status-id' => $aRow['status_id']));

        $aReturn = array(
            'no_share' => true,
            'feed_status' => $aRow['content'],
            'feed_link' => $sLink,
            'total_comment' => $aRow['total_comment'],
            'feed_total_like' => $aRow['total_like'],
            'feed_is_liked' => $aRow['is_liked'],
            'feed_icon' => Phpfox::getLib('image.helper')->display(array('theme' => 'misc/application_add.png', 'return_url' => true)),
            'time_stamp' => $aRow['time_stamp'],
            'enable_like' => true,
            'comment_type_id' => 'user_status',
            'like_type_id' => 'user_status'
        );
        if (!empty($aItem['app_id']))
        {
            $aApp = $this->database()->select('app_title, app_id')->from(Phpfox::getT('app'))
                ->where('app_id = ' . (int)$aItem['app_id'])
                ->execute('getSlaveRow');
            $sLink =  Phpfox::permalink('apps', $aApp['app_id'], $aApp['app_title']);
            $aReturn['app_link'] = $sLink;
        }
        return $aReturn;
    }

    public function getActivityFeedVideo($aItem, $aCallback = null)
    {
        if (!Phpfox::getUserParam('video.can_access_videos'))
        {
            return false;
        }

        if ($aCallback === null)
        {
            $this->database()->select(Phpfox::getUserField('u', 'parent_') . ', ')->leftJoin(Phpfox::getT('user'), 'u', 'u.user_id = v.parent_user_id');
        }

        $aRow = $this->database()->select('v.video_id, v.module_id, v.item_id, v.title, v.time_stamp, v.total_comment, v.total_like, v.image_path, v.image_server_id, l.like_id AS is_liked, vt.text_parsed')
            ->from(Phpfox::getT('video'), 'v')
            ->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = \'video\' AND l.item_id = v.video_id AND l.user_id = ' . Phpfox::getUserId())
            ->leftJoin(Phpfox::getT('video_text'), 'vt', 'vt.video_id = v.video_id')
            ->where('v.video_id = ' . (int) $aItem['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aRow['video_id']))
        {
            return false;
        }

        if ((defined('PHPFOX_IS_PAGES_VIEW') && !Phpfox::getService('pages')->hasPerm(null, 'video.view_browse_videos'))
            || (!defined('PHPFOX_IS_PAGES_VIEW') && $aRow['module_id'] == 'pages' && !Phpfox::getService('pages')->hasPerm($aRow['item_id'], 'video.view_browse_videos'))
        )
        {
            return false;
        }

        $aReturn = array(
            'feed_title' => $aRow['title'],
            // 'feed_info' => 'shared a video.',
            'feed_link' => Phpfox::permalink('video', $aRow['video_id'], $aRow['title']),
            'feed_content' => $aRow['text_parsed'],
            'total_comment' => $aRow['total_comment'],
            'feed_total_like' => $aRow['total_like'],
            'feed_is_liked' => $aRow['is_liked'],
            'feed_icon' => Phpfox::getLib('image.helper')->display(array('theme' => 'module/video.png', 'return_url' => true)),
            'time_stamp' => $aRow['time_stamp'],
            'enable_like' => true,
            'comment_type_id' => 'video',
            'like_type_id' => 'video'
        );

        if ($aRow['module_id'] == 'pages')
        {
            $aRow['parent_user_id'] = '';
            $aRow['parent_user_name'] = '';
        }

        if (empty($aRow['parent_user_id']))
        {
            $aReturn['feed_info'] = Phpfox::getPhrase('feed.shared_a_video');
        }

        if ($aCallback === null)
        {
            if (!empty($aRow['parent_user_name']) && !defined('PHPFOX_IS_USER_PROFILE') && empty($_POST))
            {
                $aReturn['parent_user'] = Phpfox::getService('user')->getUserFields(true, $aRow, 'parent_');
            }

            if (!PHPFOX_IS_AJAX && defined('PHPFOX_IS_USER_PROFILE') && !empty($aRow['parent_user_name']) && $aRow['parent_user_id'] != Phpfox::getService('profile')->getProfileUserId())
            {
                $aReturn['feed_mini'] = true;
                $aReturn['feed_mini_content'] = Phpfox::getPhrase('feed.full_name_posted_a_href_link_a_video_a_on_a_href_profile_parent_full_name_a_s_a_href_profile_link_wall_a', array('full_name' => Phpfox::getService('user')->getFirstName($aItem['full_name']), 'link' => Phpfox::permalink('video', $aRow['video_id'], $aRow['title']), 'profile' => Phpfox::getLib('url')->makeUrl($aRow['parent_user_name']), 'parent_full_name' => $aRow['parent_full_name'], 'profile_link' => Phpfox::getLib('url')->makeUrl($aRow['parent_user_name'])));
                $aReturn['feed_title'] = '';
                unset($aReturn['feed_status'], $aReturn['feed_image'], $aReturn['feed_content']);
            }
        }

        if (!PHPFOX_IS_AJAX && defined('PHPFOX_IS_USER_PROFILE') && !empty($aRow['parent_user_name']) && $aRow['parent_user_id'] != Phpfox::getService('profile')->getProfileUserId())
        {

        }
        else
        {
            if (!empty($aRow['image_path']))
            {
                $sImage = Phpfox::getLib('image.helper')->display(array(
                        'server_id' => $aRow['image_server_id'],
                        'path' => 'video.url_image',
                        'file' => $aRow['image_path'],
                        'suffix' => '_120',
                        'return_url' => true,
                    )
                );

                $aReturn['feed_image'] = $sImage;
            }

            $aReturn['feed_image_onclick'] = '$Core.box(\'video.play\', 700, \'id=' . $aRow['video_id'] . '&amp;feed_id=' . $aItem['feed_id'] . '&amp;popup=true\', \'GET\'); return false;';
        }
        (($sPlugin = Phpfox_Plugin::get('video.component_service_callback_getactivityfeed__1')) ? eval($sPlugin) : false);
        return $aReturn;
    }

    public function getActivityFeedVideo_Comment($aRow)
    {
        if (Phpfox::isUser())
        {
            $this->database()->select('l.like_id AS is_liked, ')->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = \'feed_mini\' AND l.item_id = c.comment_id AND l.user_id = ' . Phpfox::getUserId());
        }

        $aItem = $this->database()->select('b.video_id, b.image_server_id, b.image_path, b.title, b.time_stamp, b.privacy, b.total_comment, b.total_like, c.total_like, ct.text_parsed AS text, f.friend_id AS is_friend, vt.text_parsed, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('comment'), 'c')
            ->join(Phpfox::getT('comment_text'), 'ct', 'ct.comment_id = c.comment_id')
            ->join(Phpfox::getT('video'), 'b', 'c.type_id = \'video\' AND c.item_id = b.video_id AND c.view_id = 0')
            ->join(Phpfox::getT('video_text'), 'vt', 'vt.video_id = b.video_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = b.user_id')
            ->leftJoin(Phpfox::getT('friend'), 'f', "f.user_id = b.user_id AND f.friend_user_id = " . Phpfox::getUserId())
            ->where('c.comment_id = ' . (int) $aRow['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aItem['video_id']))
        {
            return false;
        }

        $sLink = Phpfox::permalink('video', $aItem['video_id'], $aItem['title']);
        $sUser = Phpfox::getLib('url')->makeUrl($aItem['user_name']);
        $sGender = Phpfox::getService('user')->gender($aItem['gender'], 1);

        if ($aRow['user_id'] == $aItem['user_id'])
        {
            $sMessage = Phpfox::getPhrase('video.posted_a_comment_on_gender_video',array('gender' => $sGender));
        }
        else
        {
            $sMessage = Phpfox::getPhrase('video.posted_a_comment_on_user_name_s_video',array('user_name' => $sUser));
        }

        $aFeed = array(
            'feed_title' => $aItem['title'],
            'feed_content' => $aItem['text_parsed'],
            'no_share' => true,
            'feed_info' => $sMessage,
            'feed_link' => $sLink,
            'feed_status' => $aItem['text'],
            'feed_total_like' => $aItem['total_like'],
            'feed_is_liked' => isset($aItem['is_liked']) ? $aItem['is_liked'] : false,
            'feed_icon' => Phpfox::getLib('image.helper')->display(array('theme' => 'module/video.png', 'return_url' => true)),
            'time_stamp' => $aRow['time_stamp'],
            'like_type_id' => 'feed_mini'
        );

        $bCanViewItem = true;
        if ($aItem['privacy'] > 0)
        {
            $bCanViewItem = Phpfox::getService('privacy')->check('video', $aItem['video_id'], $aItem['user_id'], $aItem['privacy'], $aItem['is_friend'], true);
        }

        if ($bCanViewItem)
        {
            if (!empty($aItem['image_path']))
            {
                $aFeed['feed_image'] = Phpfox::getLib('image.helper')->display(array(
                        'server_id' => $aItem['image_server_id'],
                        'path' => 'video.url_image',
                        'file' => $aItem['image_path'],
                        'suffix' => '_120',
                        'return_url' => true,
                    )
                );
            }

            $aFeed['feed_image_onclick'] = '$Core.box(\'video.play\', 700, \'id=' . $aItem['video_id'] . '&amp;feed_id=' . $aRow['feed_id'] . '&amp;popup=true\', \'GET\'); return false;';
        }

        return $aFeed;
    }

    public function getActivityFeedLink($aItem)
    {
        $aRow = $this->database()->select('link.*, l.like_id AS is_liked, ' . Phpfox::getUserField('u', 'parent_'))
            ->from(Phpfox::getT('link'), 'link')
            ->leftJoin(Phpfox::getT('user'), 'u', 'u.user_id = link.parent_user_id')
            ->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = \'link\' AND l.item_id = link.link_id AND l.user_id = ' . Phpfox::getUserId())
            ->where('link.link_id = ' . (int) $aItem['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aRow['link_id']))
        {
            return false;
        }

        if ((defined('PHPFOX_IS_PAGES_VIEW') && !Phpfox::getService('pages')->hasPerm(null, 'link.view_browse_links'))
            || (!defined('PHPFOX_IS_PAGES_VIEW') && $aRow['module_id'] == 'pages' && !Phpfox::getService('pages')->hasPerm($aRow['item_id'], 'link.view_browse_links'))
        )
        {
            return false;
        }

        if (substr($aRow['link'], 0, 7) != 'http://' && substr($aRow['link'], 0, 8) != 'https://')
        {
            $aRow['link'] = 'http://' . $aRow['link'];
        }

        $aParts = parse_url($aRow['link']);

        $sLink = Phpfox::getLib('url')->makeUrl($aRow['parent_user_name']);

        $aReturn = array(
            'feed_title' => $aRow['title'],
            'feed_status' => $aRow['status_info'],
            'feed_link_comment' => $aItem['user_name'] . '/link-id_' . $aRow['link_id'] . '/',
            'feed_link' => $aRow['link'],
            'feed_content' => $aRow['description'],
            'total_comment' => $aRow['total_comment'],
            'feed_total_like' => $aRow['total_like'],
            'feed_is_liked' => $aRow['is_liked'],
            'feed_icon' => Phpfox::getLib('image.helper')->display(array('theme' => 'feed/link.png', 'return_url' => true)),
            'time_stamp' => $aRow['time_stamp'],
            'enable_like' => true,
            'comment_type_id' => 'link',
            'like_type_id' => 'link',
            'feed_title_extra' => $aParts['host'],
            'feed_title_extra_link' => $aParts['scheme'] . '://' . $aParts['host']
        );

        if (Phpfox::getParam('core.warn_on_external_links'))
        {
            if (!preg_match('/' . preg_quote(Phpfox::getParam('core.host')) . '/i', $aReturn['feed_link']))
            {
                $aReturn['feed_link'] = Phpfox::getLib('url')->makeUrl('core.redirect', array('url' => Phpfox::getLib('url')->encode($aReturn['feed_link'])));
                $aReturn['feed_title_extra_link'] = Phpfox::getLib('url')->makeUrl('core.redirect', array('url' => Phpfox::getLib('url')->encode($aReturn['feed_title_extra_link'])));
            }
        }

        if (!empty($aRow['image']))
        {
            $aReturn['feed_image'] = $aRow['image'];
        }

        if ($aRow['module_id'] == 'pages')
        {
            $aRow['parent_user_id'] = '';
            $aRow['parent_user_name'] = '';
        }

        if (empty($aRow['module_id']) && !empty($aRow['parent_user_name']) && !defined('PHPFOX_IS_USER_PROFILE') && empty($_POST))
        {
            $aReturn['parent_user'] = Phpfox::getService('user')->getUserFields(true, $aRow, 'parent_');
        }

        if (!PHPFOX_IS_AJAX && defined('PHPFOX_IS_USER_PROFILE') && !empty($aRow['parent_user_name']) && $aRow['parent_user_id'] != Phpfox::getService('profile')->getProfileUserId())
        {
            $aReturn['feed_mini'] = true;
            $aReturn['no_share'] = true;
            $aReturn['feed_mini_content'] = Phpfox::getPhrase('friend.full_name_posted_a_href_link_a_link_a_on_a_href_parent_user_name', array('full_name' => Phpfox::getService('user')->getFirstName($aItem['full_name']), 'link' => $sLink, 'parent_user_name' => Phpfox::getLib('url')->makeUrl($aRow['parent_user_name']), 'parent_full_name' => $aRow['parent_full_name']));

            unset($aReturn['feed_status'], $aReturn['feed_image'], $aReturn['feed_title'], $aReturn['feed_content']);
        }
        else
        {
            if ($aRow['has_embed'])
            {
                $aReturn['feed_image_onclick'] = '$Core.box(\'link.play\', 700, \'id=' . $aRow['link_id'] . '&amp;feed_id=' . $aItem['feed_id'] . '&amp;popup=true\', \'GET\'); return false;';
            }
        }

        return $aReturn;
    }

    public function getActivityFeedPoll($aRow)
    {
        $aRow = Phpfox::getService('poll')->getPollByUrl($aRow['item_id']);

        $oTpl = Phpfox::getLib('template');
        $oTpl->assign(array('aPoll' => $aRow, 'iKey' => rand(2,900)));
        $sOutput = $oTpl->getTemplate('poll.block.vote', true);

        $aReturn = array(
            'feed_title' => $aRow['question'],
            'feed_info' => Phpfox::getPhrase('feed.created_a_poll'),
            'feed_link' => Phpfox::permalink('poll', $aRow['poll_id'], $aRow['question']),
            'total_comment' => $aRow['total_comment'],
            'feed_total_like' => $aRow['total_like'],
            'feed_is_liked' => $aRow['is_liked'],
            'feed_icon' => Phpfox::getLib('image.helper')->display(array('theme' => 'module/poll.png', 'return_url' => true)),
            'time_stamp' => $aRow['time_stamp'],
            'enable_like' => true,
            'comment_type_id' => 'poll',
            'like_type_id' => 'poll',
            //'feed_custom_html' => $sOutput
        );

        if (!empty($aRow['image_path']))
        {
            $sImage = Phpfox::getLib('image.helper')->display(array(
                    'server_id' => $aRow['server_id'],
                    'path' => 'poll.url_image',
                    'file' => $aRow['image_path'],
                    'suffix' => '_' . Phpfox::getParam('poll.poll_max_image_pic_size'),
                    'max_width' => Phpfox::getParam('poll.poll_max_image_pic_size'),
                    'max_height' => Phpfox::getParam('poll.poll_max_image_pic_size')
                )
            );

            $aReturn['feed_image'] = $sImage;
            $aReturn['feed_custom_width'] = '78px';
        }
        (($sPlugin = Phpfox_Plugin::get('poll.component_service_callback_getactivityfeed__1')) ? eval($sPlugin) : false);
        return $aReturn;
    }
    public function getActivityFeedPoll_Comment($aRow)
    {
        if (Phpfox::isUser())
        {
            $this->database()->select('l.like_id AS is_liked, ')->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = \'feed_mini\' AND l.item_id = c.comment_id AND l.user_id = ' . Phpfox::getUserId());
        }

        $aItem = $this->database()->select('b.poll_id, b.question, b.time_stamp, b.total_comment, b.total_like, c.total_like, ct.text_parsed AS text, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('comment'), 'c')
            ->join(Phpfox::getT('comment_text'), 'ct', 'ct.comment_id = c.comment_id')
            ->join(Phpfox::getT('poll'), 'b', 'c.type_id = \'poll\' AND c.item_id = b.poll_id AND c.view_id = 0')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = b.user_id')
            ->where('c.comment_id = ' . (int) $aRow['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aItem['poll_id']))
        {
            return false;
        }

        $sLink = Phpfox::permalink('poll', $aItem['poll_id'], $aItem['question']);
        $sTitle = Phpfox::getLib('parse.output')->shorten($aItem['question'], (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') :50));
        $sUser = Phpfox::getLib('url')->makeUrl($aItem['user_name']);
        $sGender = Phpfox::getService('user')->gender($aItem['gender'], 1);

        if ($aRow['user_id'] == $aItem['user_id'])
        {
            $sMessage = Phpfox::getPhrase('poll.posted_a_comment_on_gender_poll_a_href_link_title_a',array('gender' => $sGender, 'link' => $sLink, 'title' => $sTitle));
        }
        else
        {
            $sMessage = Phpfox::getPhrase('poll.posted_a_comment_on_user_name_s_poll_a_href_link_title_a',array('user_name' => $sUser, 'link' => $sLink, 'title' => $sTitle));
        }

        return array(
            'no_share' => true,
            'feed_info' => $sMessage,
            'feed_link' => $sLink,
            'feed_status' => $aItem['text'],
            'feed_total_like' => $aItem['total_like'],
            'feed_is_liked' => isset($aItem['is_liked']) ? $aItem['is_liked'] : false,
            'feed_icon' => Phpfox::getLib('image.helper')->display(array('theme' => 'module/poll.png', 'return_url' => true)),
            'time_stamp' => $aRow['time_stamp'],
            'like_type_id' => 'feed_mini'
        );
    }

    public function getActivityFeedMusic($aItem, $bIsAlbum = false)
    {
        if ($bIsAlbum)
        {
            $this->database()->select('ma.name AS album_name, ma.album_id, u.gender, ')
                ->leftJoin(Phpfox::getT('music_album'), 'ma', 'ma.album_id = ms.album_id')
                ->leftJoin(Phpfox::getT('user'), 'u', 'u.user_id = ma.user_id');
        }

        $this->database()->select('mp.play_id AS is_on_profile, ')->leftJoin(Phpfox::getT('music_profile'), 'mp', 'mp.song_id = ms.song_id AND mp.user_id = ' . Phpfox::getUserId());

        $aRow = $this->database()->select('ms.song_id, ms.title, ms.module_id, ms.item_id, ms.description, ms.total_play, ms.privacy, ms.time_stamp, ms.total_comment, ms.total_like, ms.user_id, l.like_id AS is_liked')
            ->from(Phpfox::getT('music_song'), 'ms')
            ->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = \'music_song\' AND l.item_id = ms.song_id AND l.user_id = ' . Phpfox::getUserId())
            ->where('ms.song_id = ' . (int) $aItem['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aRow['song_id']))
        {
            return false;
        }

        if ((defined('PHPFOX_IS_PAGES_VIEW') && !Phpfox::getService('pages')->hasPerm(null, 'music.view_browse_music'))
            || (!defined('PHPFOX_IS_PAGES_VIEW') && $aRow['module_id'] == 'pages' && !Phpfox::getService('pages')->hasPerm($aRow['item_id'], 'music.view_browse_music'))
        )
        {
            return false;
        }

        if ($bIsAlbum && empty($aRow['album_name']))
        {
            $bIsAlbum = false;
        }

        $iTitleLength = (Phpfox::isModule('notification') ? (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength ) : 50);
        $aReturn = array(
            'feed_title' => Phpfox::getLib('parse.output')->shorten($aRow['title'], $iTitleLength, '...'),
            'feed_status' => $aRow['description'],
            'feed_info' => ($bIsAlbum ? Phpfox::getPhrase('feed.shared_a_song_from_gender_album_a_href_album_link_album_name_a', array('gender' => Phpfox::getService('user')->gender($aRow['gender'], 1), 'album_link' => Phpfox::getLib('url')->permalink('music.album', $aRow['album_id'], $aRow['album_name']), 'album_name' => Phpfox::getLib('parse.output')->shorten($aRow['album_name'], (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength ), '...'))) : Phpfox::getPhrase('feed.shared_a_song')),
            'feed_link' => Phpfox::permalink('music', $aRow['song_id'], $aRow['title']),
            'feed_content' => ($aRow['total_play'] > 1 ? $aRow['total_play'] . ' ' . Phpfox::getPhrase('music.plays_lowercase') : Phpfox::getPhrase('music.1_play')),
            'total_comment' => $aRow['total_comment'],
            'feed_total_like' => $aRow['total_like'],
            'feed_is_liked' => $aRow['is_liked'],
            'feed_icon' => Phpfox::getLib('image.helper')->display(array('theme' => 'module/music.png', 'return_url' => true)),
            'time_stamp' => $aRow['time_stamp'],
            'enable_like' => true,
            'comment_type_id' => 'music_song',
            'like_type_id' => 'music_song',
            'feed_custom_width' => '38px',
            'song' => array(
                'privacy' => $aRow['privacy'],
                'song_id' => $aRow['song_id'],
                'user_id' => $aRow['user_id'],
                'is_on_profile' => $aRow['is_on_profile']
            )
        );

        $aReturn['feed_image'] = Phpfox::getLib('image.helper')->display(array(
                'theme' => 'misc/play_button.png',
                'return_url' => true
            )
        );

        $aReturn['feed_image_onclick'] = '$.ajaxCall(\'music.playInFeed\', \'id=' . $aRow['song_id'] . '&amp;feed_id=' . $aItem['feed_id'] . '\', \'GET\'); return false;';
        $aReturn['feed_image_onclick_no_image'] = true;

        return $aReturn;
    }


    public function getActivityFeedMusic_Comment($aRow)
    {
        if (Phpfox::isUser())
        {
            $this->database()->select('l.like_id AS is_liked, ')->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = \'feed_mini\' AND l.item_id = c.comment_id AND l.user_id = ' . Phpfox::getUserId());
        }

        $aItem = $this->database()->select('b.song_id, b.title, b.time_stamp, b.privacy, b.total_comment, b.total_like, c.total_like, ct.text_parsed AS text,  f.friend_id AS is_friend, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('comment'), 'c')
            ->join(Phpfox::getT('comment_text'), 'ct', 'ct.comment_id = c.comment_id')
            ->join(Phpfox::getT('music_song'), 'b', 'c.type_id = \'music_song\' AND c.item_id = b.song_id AND c.view_id = 0')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = b.user_id')
            ->leftJoin(Phpfox::getT('friend'), 'f', "f.user_id = b.user_id AND f.friend_user_id = " . Phpfox::getUserId())
            ->where('c.comment_id = ' . (int) $aRow['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aItem['song_id']))
        {
            return false;
        }

        $sLink = Phpfox::permalink('music', $aItem['song_id'], $aItem['title']);
        $sTitle = Phpfox::getLib('parse.output')->shorten($aItem['title'], (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') :50));
        $sUser = Phpfox::getLib('url')->makeUrl($aItem['user_name']);
        $sGender = Phpfox::getService('user')->gender($aItem['gender'], 1);

        if ($aRow['user_id'] == $aItem['user_id'])
        {
            $sMessage = Phpfox::getPhrase('music.posted_a_comment_on_gender_song_a_href_link_title_a',array('gender' => $sGender, 'link' => $sLink, 'title' => $sTitle));
        }
        else
        {
            $sMessage = Phpfox::getPhrase('music.posted_a_comment_on_user_name_s_song_a_href_link_title_a',array('user_name' => $sUser, 'link' => $sLink, 'title' => $sTitle));
        }

        $bCanViewItem = true;
        if ($aItem['privacy'] > 0)
        {
            $bCanViewItem = Phpfox::getService('privacy')->check('music', $aItem['song_id'], $aItem['user_id'], $aItem['privacy'], $aItem['is_friend'], true);
        }

        $aReturn = array(
            'no_share' => true,
            'feed_info' => '',
            'feed_link' => $sLink,
            'feed_status' => $aItem['text'],
            'feed_total_like' => $aItem['total_like'],
            'feed_is_liked' => isset($aItem['is_liked']) ? $aItem['is_liked'] : false,
            'feed_icon' => Phpfox::getLib('image.helper')->display(array('theme' => 'module/music.png', 'return_url' => true)),
            'time_stamp' => $aRow['time_stamp'],
            'like_type_id' => 'feed_mini'
        );

        if ($bCanViewItem)
        {
            $aReturn['feed_image'] = Phpfox::getLib('image.helper')->display(array(
                    'theme' => 'misc/play_button.png',
                    'return_url' => true
                )
            );

            $aReturn['feed_image_onclick'] = '$.ajaxCall(\'music.playInFeed\', \'id=' . $aItem['song_id'] . '&amp;feed_id=' . $aRow['feed_id'] . '\', \'GET\'); return false;';
            $aReturn['feed_image_onclick_no_image'] = true;
        }

        return $aReturn;
    }

    // NOT A FEED
    public function getParamFeedPhoto($aPhoto) {
        $aFeed = array(
            'comment_type_id' => 'photo',
            'privacy' => $aPhoto['privacy'],
            'comment_privacy' => $aPhoto['privacy_comment'],
            'like_type_id' => 'photo',
            'feed_is_liked' => $aPhoto['is_liked'],
            'feed_is_friend' => $aPhoto['is_friend'],
            'item_id' => $aPhoto['photo_id'],
            'user_id' => $aPhoto['user_id'],
            'total_comment' => $aPhoto['total_comment'],
            'total_like' => $aPhoto['total_like'],
            'feed_icon' => Phpfox::getLib('image.helper')->display(array('theme' => 'module/photo.png', 'return_url' => true)),
            'feed_title' => $aPhoto['title'],
            'feed_display' => 'view',
            'feed_total_like' => $aPhoto['total_like'],
            'report_module' => 'photo',
            'report_phrase' => Phpfox::getPhrase('photo.report_this_photo')
        );

        return $aFeed;
    }

    // NOTIFICATION
    public function getNotificationMini_Like($aNotification) {
        $aRow = $this->database()->select('c.comment_id, c.type_id, c.user_id, ct.text_parsed AS text')
            ->from(Phpfox::getT('comment'), 'c')
            ->join(Phpfox::getT('comment_text'), 'ct', 'ct.comment_id = c.comment_id')
            ->where('c.comment_id = ' . (int) $aNotification['item_id'])
            ->execute('getSlaveRow');

        return $aRow['type_id'];
    }

    public function __call($sMethod, $aArguments)
    {
        /**
         * Check if such a plug-in exists and if it does call it.
         */
        if ($sPlugin = Phpfox_Plugin::get('photo.service_callback__call'))
        {
            return eval($sPlugin);
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }
}