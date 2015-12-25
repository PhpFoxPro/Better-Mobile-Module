<?php

class Accountapi_Service_Search extends Phpfox_Service {

    //construct
    public function __construct() {

    }

    /**
     * Search friend
     * @param $iUserId
     * @param $sFind
     * @return array
     */
    public function searchFriend($iUserId, $sFind)
    {
        $oDb = Phpfox::getLib('database');

        list($iCnt, $aFriends) = Phpfox::getService('friend')
            ->get('friend.is_page = 0 AND friend.user_id = ' . (int) $iUserId .
                ' AND (u.full_name LIKE \'%' . Phpfox::getLib('parse.input')->convert($oDb->escape($sFind)) .
                '%\' OR (u.email LIKE \'%' . $oDb->escape($sFind) . '@%\' OR u.email = \'' . $oDb->escape($sFind) . '\'))',
                'friend.time_stamp DESC', 0, 10, true, true);

        foreach ($aFriends as $iKey => $aFriend) {
            $aFriends[$iKey]['user_image'] = Phpfox::getLib('image.helper')->display(array(
                'user' => $aFriend,
                'server_id' => $aFriend['user_server_id'],
                'suffix' => '_50_square',
                'return_url' => true,
            ));
        }

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
    }

    public function query($sQuery, $iPage, $iTotalShow, $sView = null, $sType= 'all')
    {

        if ($sView !== null && Phpfox::isModule($sView))
        {
            $aModuleResults = Phpfox::callback($sView . '.globalUnionSearch', $this->preParse()->clean($sQuery));
        }
        else
        {
            $aModuleResults = Phpfox::massCallback('globalUnionSearch', $this->preParse()->clean($sQuery));
        }

        $iOffset = ($iPage * $iTotalShow);

        if ($sType == 'user') {
            $sCondition = 'item_type_id = \'user\'';
        } elseif ($sType == 'photo') {
            $sCondition = 'item_type_id = \'photo\'';
        } else {
            $sCondition = '1';
        }

        $aRows = $this->database()->select('item.*, ' . Phpfox::getUserField())
            ->unionFrom('item')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = item.item_user_id')
            ->where($sCondition)
            ->limit($iOffset, $iTotalShow)
            ->order('item_time_stamp DESC')
            ->execute('getSlaveRows');

        foreach ($aRows as $iKey => $aRow) {
            $aRows[$iKey]['user_image'] = Phpfox::getLib('image.helper')->display(array(
                'user' => $aRow,
                'server_id' => $aRow['user_server_id'],
                'suffix' => '_50_square',
                'return_url' => true,
            ));
            $aRows[$iKey]['time_phrase'] = Phpfox::getLib('date')->convertTime($aRow['item_time_stamp'], 'feed.feed_display_time_stamp');
        }

        $aResults = array();
        foreach ($aRows as $iKey => $aRow)
        {
            $aResults[] = array_merge($aRow, (array) Phpfox::callback($aRow['item_type_id'] . '.getSearchInfo', $aRow));
        }

        foreach ($aResults as $iKey => $aResult) {
            if (isset($aResult['item_display_photo'])) {
                list($aResults[$iKey]['item_display_photo_mobile'],
                    $aResults[$iKey]['item_display_photo_height'],
                    $aResults[$iKey]['item_display_photo_width']) = $this->getImageInfo($aResult['item_display_photo']);
            } else {
                $aResults[$iKey]['item_display_photo_mobile'] = null;
            }
        }

        return $aResults;
    }

    public function getImageInfo($sHTML) {
        $sHTML = str_replace('"', '', trim($sHTML));
        $aResults = explode(' ', $sHTML);
		$height = 150; $width = 150;
        foreach ($aResults as $sResult) {
            if (strpos($sResult, 'src=') === 0) {
                $src = str_replace('src=', '', $sResult);
            }

            if (strpos($sResult, 'height=') === 0) {
                $height = str_replace('height=', '', $sResult);
            }

            if (strpos($sResult, 'width=') === 0) {
                $width = str_replace('width=', '', $sResult);
            }
        }
        return array($src, $height, $width);
    }
}
?>