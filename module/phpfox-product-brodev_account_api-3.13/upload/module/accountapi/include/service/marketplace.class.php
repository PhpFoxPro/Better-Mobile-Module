<?php
/**
 * Created by JetBrains PhpStorm.
 * User: huy nguyen
 * Date: 10/22/13
 * Time: 4:33 PM
 * To change this template use File | Settings | File Templates.
 */
class Accountapi_Service_Marketplace extends Phpfox_Service
{
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('marketplace');
    }

    /**
     * get blog detail
     * @param $iId
     * @return mixed
     */
    public function getDetail($iId)
    {
        if (Phpfox::isModule('like')) {
            $this->database()->select('lik.like_id AS is_liked, ')->leftJoin(Phpfox::getT('like'), 'lik', 'lik.type_id = \'marketplace\' AND lik.item_id = p.listing_id AND lik.user_id = ' . Phpfox::getUserId());
        }
        $aReturn = $this->database()->select('p.*, vt.description_parsed, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'p')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
            ->join(Phpfox::getT('marketplace_text'), 'vt', 'vt.listing_id = p.listing_id')
            ->where('p.listing_id = ' . $iId)
            ->order('p.time_stamp DESC')
            ->execute('getSlaveRow');
        $this->_process($aReturn);
        return $aReturn;
    }

    /**
     * change time stamp, add blog image
     * @param $aRow
     * @return array
     */
    private function _process(&$aRow)
    {
        $aImages = array();
        $aImageRows = $this->database()->select('*')
            ->from(Phpfox::getT('marketplace_image'))
            ->where('listing_id = ' . (int)$aRow['listing_id'])
            ->execute('getSlaveRows');


        foreach ($aImageRows as $iKey => $aImageRow) {
            $aImages[$iKey]["image_path"] = Phpfox::getLib('image.helper')->display(array(
                    'path' => 'marketplace.url_image',
                    'server_id' => $aImageRow['server_id'],
                    'file' => $aImageRow['image_path'],
                    'suffix' => '_400',
                    'return_url' => true
                )
            );
        }
        $aRow['image_path'] = Phpfox::getLib('image.helper')->display(array(
                'path' => 'marketplace.url_image',
                'server_id' => $aRow['server_id'],
                'file' => $aRow['image_path'],
                'suffix' => '_120_square',
                'return_url' => true
            )
        );

        $aRow['short_text'] = Phpfox::getLib('parse.output')->parse(Phpfox::getService('accountapi.emoticon')->processEmoticon($aRow['mini_description']));

        $aRow['short_text_html'] = $aRow['short_text'];

        $aRow['text'] = Phpfox::getLib('parse.output')->parse(Phpfox::getService('accountapi.emoticon')->processEmoticon($aRow['description_parsed']));

        $aRow['text_html'] = $aRow['text'];

        $aRow['currency'] = Phpfox::getService('core.currency')->getSymbol($aRow['currency_id']);
        $aRow['country_name'] = Phpfox::getService('core.country')->getCountry($aRow['country_iso']);
        $aRow['country_child_name'] = $aRow['country_child_id'] == 0 ? null : Phpfox::getService('core.country')->getChild($aRow['country_child_id']);
        $aRow['images'] = json_encode($aImages);
        $aRow['can_post_comment'] = Phpfox::getUserParam('marketplace.can_post_comment_on_listing') ? Phpfox::getService('comment')->canPostComment($aRow['user_id'], $aRow['privacy_comment']) : false;

        $aRow['user_image_path'] = Phpfox::getLib('image.helper')->display(array(
                'server_id' => $aRow['user_server_id'],
                'user' => $aRow,
                'suffix' => '_75_square',
                'return_url' => true
            )
        );
        $aRow['time_stamp'] = Phpfox::getLib('date')->convertTime($aRow['time_stamp']);

    }

    /**
     * get list blog
     * @param $sList
     * @param $iCategory
     * @param $iPage
     * @param $iUserId
     * @return array
     */
    public function getListings($sList, $iCategory, $iPage, $iUserId)
    {

        $sWhere = "p.privacy = 0";
        if ($sList == "my") {
            $sWhere .= " AND p.user_id = " . $iUserId;

        } else if ($sList == "friend") {
            list(, $aFriends) = Phpfox::getService('friend')->get(array(), 'friend.time_stamp DESC', '', '', true, false, false, $iUserId);
            $sWhere .= " AND p.user_id in (0";
            foreach ($aFriends as $aFriend) {
                $sWhere .= ", " . $aFriend['user_id'];
            }
            $sWhere .= ")";
        }


        if ($iCategory != 0) {
            $aListingIds = $this->database()
                ->select('listing_id')
                ->from(Phpfox::getT('marketplace_category_data'))
                ->where('category_id = ' . $iCategory)
                ->execute('getRows');
            $sWhere .= " AND p.listing_id in (0";
            foreach ($aListingIds as $iId) {
                $sWhere .= ", " . $iId['listing_id'];
            }
            $sWhere .= ")";
        }

        $iCnt = $this->database()->select('COUNT(*)')
            ->from($this->_sTable, 'p')
            ->where($sWhere)
            ->execute('getSlaveField');


        if ($iPage == 0) {
            $iPage = 1;
        }
        $iSize = Phpfox::getParam('accountapi.marketplace_page_size');
        if (Phpfox::isModule('like')) {
            $this->database()->select('lik.like_id AS is_liked, ')->leftJoin(Phpfox::getT('like'), 'lik', 'lik.type_id = \'marketplace\' AND lik.item_id = p.listing_id AND lik.user_id = ' . $iUserId);
        }
        $aRows = $this->database()->select('p.*, vt.description_parsed, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'p')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
            ->join(Phpfox::getT('marketplace_text'), 'vt', 'vt.listing_id = p.listing_id')
            ->where($sWhere)
            ->limit($iPage, $iSize, $iCnt)
            ->order('p.time_stamp DESC')
            ->execute('getSlaveRows');
        if (!empty($aRows)) {
            foreach ($aRows as $iKey => $aRow) {
                $this->_process($aRows[$iKey]);
            }

            return array($iCnt, $aRows);
        } else {
            $aReturn['notice'] = Phpfox::getPhrase('marketplace.no_marketplace_listings_found');
            return array($iCnt, $aReturn);
        }

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