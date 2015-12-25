<?php
class Themesupporter_Service_Pages_Pages extends Phpfox_Service {
    public function __construct() {
        $this->_sTable = Phpfox::getT('pages');
    }

    /**
     * get pages from database
     * @return mixed
     */
    public function get() {
        $sType = Phpfox::getParam('themesupporter.block_pages_type');
        $sCacheId = $this->cache()->set('brodev_themesupporter_pages_'. $sType);
        if (!$aRecords = $this->cache()->get($sCacheId, 300)) {
            switch ($sType) {
                case 'recent': $sOrder = "time_stamp desc";
                break;
                case 'most like': $sOrder = "total_like desc";
            }
            $aRecords = $this->database()
                ->select('p.*, c.name as category_name, u2.server_id AS profile_server_id, u2.user_image AS profile_user_image, pu.vanity_url')
                ->from($this->_sTable, 'p')
                ->leftJoin(Phpfox::getT('pages_category'), 'c', 'p.category_id = c.category_id')
                ->leftJoin(Phpfox::getT('user'), 'u2', 'u2.profile_page_id = p.page_id')
                ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = p.page_id')
                ->where('p.app_id = 0 AND p.view_id = 0 AND p.privacy = 0')
                ->order($sOrder)
                ->limit(Phpfox::getParam('themesupporter.block_pages_number'))
                ->execute('getRows');
            if (empty($aRecords)){
                return false;
            }
            foreach ($aRecords AS $iKey =>$aRow) {
                $aRecords[$iKey]['link'] = Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']);
                $aRecords[$iKey]['aFeed'] = array(
                    'feed_display' => 'mini',
                    'comment_type_id' => 'pages',
                    'privacy' => 0,
                    'comment_privacy' => 0,
                    'like_type_id' => 'pages',
                    'feed_is_liked' => (isset($aRow['is_liked']) ? $aRow['is_liked'] : false),
                    'feed_is_friend' => (isset($aRow['is_friend']) ? $aRow['is_friend'] : false),
                    'item_id' => $aRow['page_id'],
                    'user_id' => $aRow['user_id'],
                    'total_comment' => $aRow['total_comment'],
                    'feed_total_like' => $aRow['total_like'],
                    'total_like' => $aRow['total_like'],
                    'feed_link' => Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']),
                    'feed_title' => $aRow['title']
                );
            }

            $this->cache()->save($sCacheId, $aRecords);
        }

        return $aRecords;
    }
}