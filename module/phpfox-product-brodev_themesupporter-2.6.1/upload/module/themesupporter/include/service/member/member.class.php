<?php
class Themesupporter_Service_Member_Member extends Phpfox_Service {
    public function __construct() {
        $this->_sTable = Phpfox::getT('user');
    }

    /**
     * get all info of user
     * @return array
     */
    public function get() {

        $sType = Phpfox::getParam('themesupporter.block_member_type');
        $sCacheId = $this->cache()->set('brodev_themesupporter_member_'. $sType);
        if (!$aMembers = $this->cache()->get($sCacheId, 300)) {
            $sWhere = 'u.profile_page_id = 0';
            switch ($sType) {
                case 'Newest':
                case 'Latest':  $this->database()->order('u.joined desc');
                break;
                case 'Recent':  $this->database()->order('u.last_login desc');
                break;
                case 'Popular':
                    $this->database()->join(Phpfox::getT('user_field'), 'uf', 'uf.user_id = u.user_id')->order('uf.total_view desc');
                    break;
                case 'Online':
                    $iActiveSession = PHPFOX_TIME - (Phpfox::getParam('log.active_session') * 60);
                    $aUserLogIds = $this->database()
                        ->select('ls.user_id')
                        ->from(Phpfox::getT('log_session'), 'ls')
                        ->where('ls.user_id != 0 AND ls.last_activity > ' . $iActiveSession)
                        ->limit(Phpfox::getParam('themesupporter.block_member_number'))
                        ->group('ls.user_id')
                        ->execute('getSlaveRows');
                    $aUserIds = array();
                    $aUserIds[] = 0;
                    foreach($aUserLogIds as $aUserLog) {
                        $aUserIds[] = $aUserLog['user_id'];
                    }
                    $sWhere .= " AND u.user_id IN (". implode(", ", $aUserIds) .")";
                break;
                case 'Top':     $this->database()->join(Phpfox::getT('user_activity'), 'ua', 'u.user_id = ua.user_id')->order('ua.activity_points desc');
                break;
            }
            $aMembers = $this->database()
                ->select('u.*')
                ->from($this->_sTable, 'u')
                ->where($sWhere)
                ->limit(Phpfox::getParam('themesupporter.block_member_number'))
                ->execute('getRows');
            if (empty($aMembers)) {
                return false;
            }
            $this->cache()->save($sCacheId, $aMembers);
        }

        return $aMembers;
    }

}