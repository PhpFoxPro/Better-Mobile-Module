<?php
class Themesupporter_Service_Blog_Blog extends Phpfox_Service {
    public function __construct() {
        $this->_sTable = Phpfox::getT('blog');
    }

    /**
     * get all blog
     * @return array
     */
    public function get() {
        $sType = Phpfox::getParam('themesupporter.block_blog_type');
        $sCacheId = $this->cache()->set('brodev_themesupporter_blog_'. $sType);
        if (!$aRecords = $this->cache()->geT($sCacheId, 300)) {
            switch ($sType) {
                case 'Most Viewed blogs': $sOrder = "total_view desc";
                break;
                case 'Recent blogs': $sOrder = "time_stamp desc";
                break;
            }
            $aRecords = $this->database()
                ->select('b.*, u.user_name as user_name, u.full_name as full_name, u.user_image as user_image, bt.text as text, bt.text_parsed as text_parsed')
                ->from($this->_sTable, 'b')
                ->leftJoin(Phpfox::getT('user'), 'u', 'b.user_id = u.user_id')
                ->leftJoin(Phpfox::getT('blog_text'), 'bt', 'bt.blog_id = b.blog_id')
                ->where('b.is_approved = 1 AND b.privacy = 0 AND b.post_status = 1')
                ->order($sOrder)
                ->limit(Phpfox::getParam('themesupporter.block_blog_number'))
                ->execute('getRows');
            if (empty($aRecords)) {
                return false;
            }
            foreach ($aRecords as $iKey => $aValue) {
                $aRecords[$iKey]['aFeed'] = array(
                    'feed_display' => 'mini',
                    'comment_type_id' => 'blog',
                    'privacy' => $aValue['privacy'],
                    'comment_privacy' => $aValue['privacy_comment'],
                    'like_type_id' => 'blog',
                    'feed_is_liked' => (isset($aValue['is_liked']) ? $aValue['is_liked'] : false),
                    'feed_is_friend' => (isset($aValue['is_friend']) ? $aValue['is_friend'] : false),
                    'item_id' => $aValue['blog_id'],
                    'user_id' => $aValue['user_id'],
                    'total_comment' => $aValue['total_comment'],
                    'feed_total_like' => $aValue['total_like'],
                    'total_like' => $aValue['total_like'],
                    'feed_link' => Phpfox::getLib('url')->permalink('blog', $aValue['blog_id'], $aValue['title']),
                    'feed_title' => $aValue['title'],
                    'time_stamp' => $aValue['time_stamp']
                );
            }
            $this->cache()->save($sCacheId, $aRecords);
        }

        return $aRecords;
    }
}