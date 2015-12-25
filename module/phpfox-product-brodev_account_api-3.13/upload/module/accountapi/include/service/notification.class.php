<?php

class Accountapi_Service_Notification extends Phpfox_Service
{
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('notification');
        $this->_oApi = Phpfox::getService('api');
    }

    /**
     * get recent notifications
     */
    public function get()
    {
        $aRows = Phpfox::getService('notification')->get();

        $aNotifications = array();
        foreach ($aRows as $aRow) {
            $aNotifications[] = $this->_processNotification($aRow);
        }

        return $aNotifications;
    }

    public function _getNotification($iUserId)
    {
        $aGetRows = $this->database()->select('n.*, n.user_id as item_user_id, COUNT(n.notification_id) AS total_extra, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'n')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = n.owner_user_id')
            ->innerJoin('(SELECT * FROM ' . $this->_sTable . ' AS n WHERE n.user_id = ' . $iUserId . ' ORDER BY n.time_stamp DESC)', 'ninner', 'ninner.notification_id = n.notification_id')
            ->where('n.user_id = ' . $iUserId . '')
            ->group('n.type_id, n.item_id')
            ->order('n.is_seen ASC, n.time_stamp DESC')
            ->limit(5)
            ->execute('getSlaveRows');

        $aRows = array();
        foreach ($aGetRows as $aGetRow) {
            $aRows[(int)$aGetRow['notification_id']] = $aGetRow;
        }

        arsort($aRows);

        $aNotifications = array();
        foreach ($aRows as $aRow) {
            $aParts1 = explode('.', $aRow['type_id']);
            $sModule = $aParts1[0];
            if (strpos($sModule, '_')) {
                $aParts = explode('_', $sModule);
                $sModule = $aParts[0];
            }

            if (Phpfox::isModule($sModule)) {
                if ((int)$aRow['total_extra'] > 1) {
                    $aExtra = $this->database()->select('n.owner_user_id, n.time_stamp, n.is_seen, u.full_name')
                        ->from($this->_sTable, 'n')
                        ->join(Phpfox::getT('user'), 'u', 'u.user_id = n.owner_user_id')
                        ->where('n.type_id = \'' . $this->database()->escape($aRow['type_id']) . '\' AND n.item_id = ' . (int)$aRow['item_id'])
                        ->group('u.user_id')
                        ->order('n.time_stamp DESC')
                        ->limit(10)
                        ->execute('getSlaveRows');

                    foreach ($aExtra as $iKey => $aExtraUser) {
                        if ($aExtraUser['owner_user_id'] == $aRow['user_id']) {
                            unset($aExtra[$iKey]);
                        }

                        if (!$aRow['is_seen'] && $aExtraUser['is_seen']) {
                            unset($aExtra[$iKey]);
                        }
                    }

                    if (count($aExtra)) {
                        $aRow['extra_users'] = $aExtra;
                    }
                }

                if (substr($aRow['type_id'], 0, 8) != 'comment_' && !Phpfox::hasCallback($aRow['type_id'], 'getNotification')) {
                    $aCallBack['link'] = '#';
                    $aCallBack['message'] = '2. Notification is missing a callback. [' . $aRow['type_id'] . '::getNotification]';
                } elseif (substr($aRow['type_id'], 0, 8) == 'comment_' && substr($aRow['type_id'], 0, 12) != 'comment_feed' && !Phpfox::hasCallback(substr_replace($aRow['type_id'], '', 0, 8), 'getCommentNotification')) {
                    $aCallBack['link'] = '#';
                    $aCallBack['message'] = 'Notification is missing a callback. [' . substr_replace($aRow['type_id'], '', 0, 8) . '::getCommentNotification]';
                }
                else {
                    // set wanted user id
                    Phpfox::getService('user.auth')->setUserId($iUserId);

                    $aCallBack = Phpfox::callback($aRow['type_id'] . '.getNotification', $aRow);

                    // reset current user id
                    Phpfox::getService('user.auth')->setUserId(null);

                    if ($aCallBack === false) {
                        $this->database()->delete($this->_sTable, 'notification_id = ' . (int)$aRow['notification_id']);

                        continue;
                    }
                }

                $aNotifications[] = array_merge($aRow, (array)$aCallBack);
            }

//            $this->database()->update($this->_sTable, array('is_seen' => '1'), 'type_id = \'' . $this->database()->escape($aRow['type_id']) . '\' AND item_id = ' . (int) $aRow['item_id']);
        }

        return $aNotifications;

    }

    /*
      * Get newest Notification
      */
    public function getNewestNotification($iUserId)
    {
        $sIds = $this->getUnseenNotificationIds($iUserId);
        $aNotifications = $this->_getNotification($iUserId);
        if (count($aNotifications) <= 0) {
            return false;
        }
        if (!empty($sIds)) {
            $this->database()->update(Phpfox::getT('notification'), array('is_seen' => '0'), 'notification_id IN(' . $sIds . ')');
        }

        return $this->_processNotification($aNotifications[0]);
    }

    /**
     * get unseen notifications
     */
    public function getUnseenNotificationIds($iUserId)
    {
        $aNotifications = $this->database()->select('n.*, n.user_id as item_user_id, COUNT(n.notification_id) AS total_extra')
            ->from($this->_sTable, 'n')->where('n.user_id = ' . $iUserId . ' AND n.is_seen = 0')->execute('getSlaveRows');
        $aIds = array();
        foreach ($aNotifications as $aNotification) {
            $aIds[] = $aNotification['notification_id'];
        }

        $sIds = implode(',', $aIds);
        return $sIds;
    }

    /**
     * Process notification
     */
    function _processNotification($aRow)
    {
        $aNotification = array(
            'notification_id' => $aRow['notification_id'],
            'type_id' => $aRow['type_id'],
            'link' => $aRow['link'],
            'message_html' => $this->_removeSpanUSer($aRow['message']),
            'message' => Phpfox::getService('accountapi.emoticon')->processEmoticon($this->_removeSpanUSer($aRow['message'])),
            'time_phrase' => Phpfox::getLib('date')->convertTime($aRow['time_stamp'], 'feed.feed_display_time_stamp'),
            'user_image' => Phpfox::getLib('image.helper')->display(array(
                'user' => $aRow,
                'server_id' => $aRow['user_server_id'],
                'suffix' => '_75_square',
                'return_url' => true,
            ))
        );
        if (!empty($aRow['icon'])) {
            $aNotification['icon'] = $aRow['icon'];
        }
        $aNotification['social_app'] = array(
            'link' => $this->_getNotificationRoute($aRow)
        );

        return $aNotification;
    }

    /**
     * Remove span tag.
     */
    public function _removeSpanUSer($sString)
    {
        $sString = str_replace('<span class="drop_data_user">', '', $sString);
        $sString = str_replace('</span>', '', $sString);
        return $sString;
    }

    /**
     * Route notification
     */
    public function _getNotificationRoute($aRow)
    {
        $sType = $aRow['type_id'];

        if ($sType == 'friend_accepted') {
            return array(
                'route' => 'user/profile',
                'request' => array(
                    'user_id' => $aRow['item_id']
                )
            );
        } elseif ($sType == 'pages_like') {
            return array(
                'route' => 'pages/dashboard',
                'request' => array(
                    'page_id' => $aRow['item_id']
                )
            );
        } else {
            if ($sType != 'comment_feed' && strpos($sType, 'comment_') === 0) {
                $sType = substr($sType, 8);
            }

            if (strpos($sType, '_like') === strlen($sType) - 5) {
                $sType = substr($sType, 0, strlen($sType) - 5);
            }

            switch ($sType) {
                case 'feed_comment_profile' :
                case 'comment_feed' :
                    $sType = 'feed_comment';
                    break;
                case 'comment_photo' :
                    $sType = 'photo';
                    break;
                case 'pages_comment' :
                case 'pages_comment_feed' :
                    $sType = 'pages_comment';
                    break;
                case 'photo_like' :
                case 'photo_tag' :
                    $sType = 'photo';
                    break;
                case 'comment_user_status' :
                case 'user_status_like' :
                    $sType = 'user_status';
                    break;
                case 'custom_comment_relation' :
                case 'custom_relation_like' :
                    $sType = 'custom_relation';
                    break;
                case 'feed_mini' :
                    $sType = Phpfox::callback('accountapi_mini_like.getNotification', $aRow) . '_comment';
                    break;

            }
            return array(
                'route' => 'feed/viewItem',
                'request' => array(
                    'item_id' => $aRow['item_id'],
                    'type_id' => $sType
                )
            );
        }

        return false;
    }
}