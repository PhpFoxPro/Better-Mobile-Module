<?php
class Themesupporter_Service_Event_Event extends Phpfox_Service {
    public function __construct() {
        $this->_sTable = Phpfox::getT('event');
    }

    /**
     * get database in block
     * @return array
     */
    public function get() {
        $sType = Phpfox::getParam('themesupporter.block_event_type');
        $sCacheId = $this->cache()->set('brodev_themesupporter_event_'. $sType);
        if (!$aRecords = $this->cache()->get($sCacheId, 300)) {
            $iTimeDisplay = Phpfox::getLib('date')->mktime(0, 0, 0, Phpfox::getTime('m'), Phpfox::getTime('d'), Phpfox::getTime('Y'));
            $sWhere = 'e.view_id = 0 AND e.privacy = 0 AND e.item_id = 0 ';
            switch ($sType) {
                case 'Today':
                    $sWhere .= " AND e.start_time > ". Phpfox::getLib('date')->mktime(0, 0, 0, Phpfox::getTime('m'), Phpfox::getTime('d') , Phpfox::getTime('Y')) ." AND e.start_time < " .Phpfox::getLib('date')->mktime(0, 0, 0, Phpfox::getTime('m'), Phpfox::getTime('d') + 1 , Phpfox::getTime('Y'));
                $this->database()->order('e.time_stamp');
                break;
                case 'Week': $iFirstDay = @date("w", @mktime(0,0,0,Phpfox::getTime('m'), Phpfox::getTime('d') , Phpfox::getTime('Y')));
                $iLastDay = 7- $iFirstDay;
                $sWhere .= " AND e.start_time > ". Phpfox::getLib('date')->mktime(0, 0, 0, Phpfox::getTime('m'), Phpfox::getTime('d') - $iFirstDay , Phpfox::getTime('Y')) . " AND e.start_time < " .Phpfox::getLib('date')->mktime(0, 0, 0, Phpfox::getTime('m'), Phpfox::getTime('d') + $iLastDay , Phpfox::getTime('Y'));
                $this->database()->order('e.time_stamp');
                break;
                case 'Month':
                    $sWhere .= " AND e.start_time > ". Phpfox::getLib('date')->mktime(0, 0, 0, Phpfox::getTime('m'), 1 , Phpfox::getTime('Y')) ." AND e.start_time < " .Phpfox::getLib('date')->mktime(0, 0, 0, Phpfox::getTime('m') + 1, 1 , Phpfox::getTime('Y'));
                $this->database()->order('e.time_stamp');
                break;
                case 'All':
                    $this->database()->order('e.time_stamp');
                    $sWhere .= " AND e.start_time >= " . $iTimeDisplay;
                    break;
                case 'Most':
                    $sWhere .= " AND e.start_time >= " . $iTimeDisplay;
                    $aMostEventId = $this->database()
                        ->select('event_id')
                        ->from(Phpfox::getT('event_invite'))
                        ->group('event_id')
                        ->order('count(event_id) desc')
                        ->limit(Phpfox::getParam('themesupporter.block_event_number'))
                        ->execute('getRows');
                    $aEventIds[] = 0;
                    foreach($aMostEventId as $aMostEvent) {
                        $aEventIds[] = $aMostEvent['event_id'];
                    }
                    $sWhere .= " AND e.event_id IN (". implode(", ", $aEventIds) .")";

            }
            $iLimit = Phpfox::getParam('themesupporter.block_event_number');
            $aRecords = $this->database()
                ->select('e.*, u.user_name as user_name, u.full_name as full_name, u.user_image as user_image, et.description_parsed as description')
                ->from($this->_sTable, 'e')
                ->leftJoin(Phpfox::getT('user'), 'u', 'e.user_id = u.user_id')
                ->leftJoin(Phpfox::getT('event_text'), 'et', 'e.event_id = et.event_id')
                ->where($sWhere)
                ->limit($iLimit)
                ->execute('getRows');
            if (empty($aRecords)) {
                return false;
            }
            foreach ($aRecords as $iKey => $aVal) {
                $aRecords[$iKey]['attending'] = $this->countInvite($aVal['event_id']);
                $aRecords[$iKey]['url'] = Phpfox::getLib('url')->permalink('event', $aVal['event_id'], $aVal['title']);
                $aRecords[$iKey]['start_time_phrase'] = Phpfox::getTime(Phpfox::getParam('event.event_browse_time_stamp'), $aVal['start_time']);
                $aRecords[$iKey]['start_time_phrase_stamp'] = Phpfox::getTime('g:sa', $aVal['start_time']);
                $aRecords[$iKey]['aFeed'] = array(
                    'feed_display' => 'mini',
                    'comment_type_id' => 'event',
                    'privacy' => $aVal['privacy'],
                    'comment_privacy' => $aVal['privacy_comment'],
                    'like_type_id' => 'event',
                    'feed_is_liked' => (isset($aVal['is_liked']) ? $aVal['is_liked'] : false),
                    'feed_is_friend' => (isset($aVal['is_friend']) ? $aVal['is_friend'] : false),
                    'item_id' => $aVal['event_id'],
                    'user_id' => $aVal['user_id'],
                    'total_comment' => $aVal['total_comment'],
                    'feed_total_like' => $aVal['total_like'],
                    'total_like' => $aVal['total_like'],
                    'feed_link' => Phpfox::getLib('url')->permalink('event', $aVal['event_id'], $aVal['title']),
                    'feed_title' => $aVal['title']
                );
            }

            $this->cache()->save($sCacheId, $aRecords);
        }


        return $aRecords;

    }

    /**
     * count all attend invite
     * @param int $iId
     * @return int
     */
    private function countInvite($iId = 0) {
        if ($iId == 0) {
            return 0;
        }
        $iCount = $this->database()
            ->select('count(*)')
            ->from(Phpfox::getT('event_invite'))
            ->where('event_id = '. $iId." AND rsvp_id = 1")
            ->execute('getField');
        return $iCount;
    }
    /**
     * sort array by key
     * @param $aArray
     * @param $sKey
     * @return array
     */
    function subval_sort($aArray,$sKey) {
        foreach($aArray as $k=>$v) {
            $aTemp[$k] = strtolower($v[$sKey]);
        }
        arsort($aTemp);
        foreach($aTemp as $key=>$val) {
            $aResult[] = $aArray[$key];
        }
        return $aResult;
    }
}