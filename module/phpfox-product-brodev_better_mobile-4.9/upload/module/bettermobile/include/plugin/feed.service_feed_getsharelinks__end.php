<?php
    //get all feed share
    $oCache = Phpfox::getLib('cache');
    $sCacheId = $oCache->set('feed_share_link');
    $aFeedLinks = $oCache->get($sCacheId);
    $iPhotoShare = -1;
    foreach($aFeedLinks as $iKey => $aFeedLink) {
        if (($aFeedLink['product_id'] == 'phpfox') && ($aFeedLink['module_id'] == 'photo')) {
            $iPhotoShare = $iKey;
        }
    }
    if ($iPhotoShare >= 0) {
        if (!isset($aLinks[$iPhotoShare])) {
            $aLinks[] = $aFeedLinks[$iPhotoShare];
        }
    }


