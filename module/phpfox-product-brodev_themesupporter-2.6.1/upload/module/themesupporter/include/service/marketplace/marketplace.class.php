<?php
class Themesupporter_Service_Marketplace_Marketplace extends Phpfox_Service {
    public function __construct() {
        $this->_sTable = Phpfox::getT('marketplace');
    }

    /**
     * get all blog
     * @return array
     */
    public function get() {
        $sType = Phpfox::getParam('themesupporter.marketplace_type');
        $sCacheId = $this->cache()->set('brodev_themesupporter_listing_'. $sType);
        if (!$aRecords = $this->cache()->get($sCacheId, 300)) {
            $sWhere = "m.privacy = 0";
            switch ($sType) {
                case 'Most Liked': $sOrder = "total_like desc";
                break;
                case 'Recent Marketplace': $sOrder = "time_stamp desc";
                break;
                case 'Featured Marketplace': $sOrder = 'time_stamp desc';
                $sWhere .= " AND m.is_featured = 1";
                break;
            }
            $aRecords = $this->database()
                ->select('m.*')
                ->from($this->_sTable, 'm')
                ->order($sOrder)
                ->where($sWhere)
                ->limit(Phpfox::getParam('themesupporter.marketplace_number'))
                ->execute('getRows');
            if (empty($aRecords)) {
                return false;
            }
            $this->cache()->save($sCacheId, $aRecords);
        }

        return $aRecords;
    }
}