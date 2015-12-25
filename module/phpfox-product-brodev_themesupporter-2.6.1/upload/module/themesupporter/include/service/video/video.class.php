<?php
class Themesupporter_Service_Video_Video extends Phpfox_Service {
    public function __construct() {
        $this->_sTable = Phpfox::getT('video');
    }

    public function get() {
        $sType = Phpfox::getParam('themesupporter.block_video_type');
        $sCacheId = $this->cache()->set('brodev_themesupporter_video_'. $sType);
        if (!$aRecords = $this->cache()->get($sCacheId, 300)) {
            switch ($sType) {
                case 'Recently Uploaded Videos': $sOrder = 'time_stamp desc';
                break;
                case 'Most Viewed Videos':$sOrder = 'total_view desc';
                break;
                case 'Random Videos': $sOrder = 'rand()';
                break;
            }
            $aRecords = $this->database()
                ->select('v.*, u.user_name as user_name, u.full_name as full_name')
                ->from($this->_sTable, 'v')
                ->leftJoin(Phpfox::getT('user'), 'u', 'v.user_id = u.user_id')
                ->order($sOrder)
                ->where('v.privacy = 0 AND v.in_process = 0 AND v.view_id = 0')
                ->limit(Phpfox::getParam('themesupporter.block_video_number'))
                ->execute('getRows');
            if (empty($aRecords)) {
                return false;
            }
            foreach ($aRecords as $iKey => $aRow)
            {
                $aRecords[$iKey]['link'] = Phpfox::permalink('video', $aRow['video_id'], $aRow['title']);
            }
            $this->cache()->save($sCacheId, $aRecords);
        }

        return $aRecords;
    }
    public function processRows(&$aRows)
    {


    }

    /**
     * Get random featured video
     * @return array
     */
    public function getRandomVideo()
    {
        $aRecord = $this->database()
            ->select('e.*')
            ->from(Phpfox::getT('video_embed'), 'e')
            ->leftJoin(Phpfox::getT('video'), 'v', 'v.video_id = e.video_id')
            ->where('v.is_featured = 1')
            ->order('rand()')
            ->execute('getRow');
        return $aRecord;
    }
}