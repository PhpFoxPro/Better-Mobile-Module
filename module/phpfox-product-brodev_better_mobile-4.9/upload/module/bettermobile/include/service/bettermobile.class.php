<?php
class Bettermobile_Service_Bettermobile extends Phpfox_Service {
    /**
     * check check in param
     * @return bool|mixed
     */
    public function isCheckIn() {
        $bCheckIn = false;
        if (Phpfox::getLib('setting')->isParam('feed.enable_check_in')) {
            $bCheckIn = Phpfox::getParam('feed.enable_check_in');
        }
        return $bCheckIn;
    }
}