<?php
/**
 * Created by JetBrains PhpStorm.
 * User: huy nguyen
 * Date: 10/22/13
 * Time: 4:33 PM
 * To change this template use File | Settings | File Templates.
 */
class Accountapi_Service_Blog extends Phpfox_Service
{
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('blog');
    }

    /**
     * get blog detail
     * @param $iBlogId
     * @return mixed
     */
    public function getBlogDetail($iBlogId)
    {
        $aBlog = Phpfox::getService('blog')->getBlog($iBlogId);
        $this->_processBlog($aBlog);
        return $aBlog;
    }

    /**
     * change time stamp, add blog image
     * @param $aBlog
     */
    private function _processBlog(&$aBlog)
    {
        $aBlog['user_image_path'] = Phpfox::getLib('image.helper')->display(array(
                'server_id' => $aBlog['user_server_id'],
                'user' => $aBlog,
                'suffix' => '_75_square',
                'return_url' => true
            )
        );
        $aBlog['time_stamp'] = Phpfox::getLib('date')->convertTime($aBlog['time_stamp']);
        $aBlog['text_html'] = $aBlog['text'];
        $aBlog['text'] = Phpfox::getService('accountapi.emoticon')->processEmoticon($aBlog['text']);
        $aBlog['short_text'] = $aBlog['text'];
        $aBlog['can_post_comment'] = Phpfox::getService('comment')->canPostComment($aBlog['user_id'], $aBlog['privacy_comment']);
    }

    /**
     * get list blog
     * @param $sList
     * @param $iCategory
     * @param $iPage
     * @param $iUserId
     * @return array
     */
    public function getBlogs($sList, $iCategory, $iPage, $iUserId)
    {

        $sWhere = "1 = 1 AND b.is_approved = 1";
        if ($sList == "my") {
            $sWhere .= " AND b.user_id = " . $iUserId;

        } else if ($sList == "friend") {
            list(, $aFriends) = Phpfox::getService('friend')->get(array(), 'friend.time_stamp DESC', '', '', true, false, false, $iUserId);
            $sWhere .= " AND b.user_id in (0";
            foreach ($aFriends as $aFriend) {
                $sWhere .= ", " . $aFriend['user_id'];
            }
            $sWhere .= ")";
        }


        if ($iCategory != 0) {
            $iBlogsIds = $this->database()
                ->select('blog_id')
                ->from(Phpfox::getT('blog_category_data'))
                ->where('category_id = ' . $iCategory)
                ->execute('getRows');
            $sWhere .= " AND b.blog_id in (0";
            foreach ($iBlogsIds as $iBlogId) {
                $sWhere .= ", " . $iBlogId['blog_id'];
            }
            $sWhere .= ")";
        }

        $iCount = $this->database()
            ->select('COUNT(*)')
            ->from(Phpfox::getT('blog'), 'b')
            ->where($sWhere)
            ->execute('getSlaveField');

        if ($iPage == 0) {
            $iPage = 1;
        }
        $iSize = Phpfox::getParam('accountapi.blog_page_size');

        if (Phpfox::isModule('like')) {
            $this->database()->select('lik.like_id AS is_liked, ')->leftJoin(Phpfox::getT('like'), 'lik', 'lik.type_id = \'blog\' AND lik.item_id = b.blog_id AND lik.user_id = ' . $iUserId);
        }

        $aLists = $this->database()->select('b.*, bt.text_parsed as text, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('blog'), 'b')
            ->join(Phpfox::getT('blog_text'), 'bt', 'bt.blog_id = b.blog_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = b.user_id')
            ->where($sWhere)
            ->limit($iPage, $iSize, $iCount)
            ->order('b.time_stamp desc')
            ->execute('getSlaveRows');
        if (!empty($aLists)) {
            foreach ($aLists as $iKey => $aItem) {
                $this->_processBlog($aLists[$iKey]);
            }
        } else {
            $aLists['notice'] = Phpfox::getPhrase('blog.no_blogs_found');

        }
        return array($iCount, $aLists);
    }

    /**
     * add new blog
     * @param $aVals
     * @return mixed
     */
    public function addBlog($aVals)
    {
        if ($aVals['selected_categories'] != "") {
            $sCategories = substr($aVals['selected_categories'], 0, -1);
            $aVals['category'] = preg_split('[,]', $sCategories);
        }
        return Phpfox::getService('blog.process')->add($aVals);
    }
}