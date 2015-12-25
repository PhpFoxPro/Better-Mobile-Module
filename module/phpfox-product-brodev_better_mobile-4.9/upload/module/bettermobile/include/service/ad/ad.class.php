<?php
class Bettermobile_Service_Ad_Ad extends Phpfox_Service {
    public function filterAd($aAds) {
        if (!is_array($aAds)) {
            return $aAds;
        }
        $aAdId = Phpfox::getParam('bettermobile.mobile_ad_id');
        if (!empty($aAdId)){
            foreach ($aAds as $iKey => $aAd) {
                if (Phpfox::isMobile()) {
                    if (!in_array($aAd['ad_id'], $aAdId)) {
                        unset($aAds[$iKey]);
                    }
                } else {
                    if (in_array($aAd['ad_id'], $aAdId)) {
                        unset($aAds[$iKey]);
                    }
                }

            }
        }

        return $aAds;
    }
}
