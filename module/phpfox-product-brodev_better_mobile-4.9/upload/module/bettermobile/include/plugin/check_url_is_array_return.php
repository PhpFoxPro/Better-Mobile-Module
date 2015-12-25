<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Huy Nguyen
 * Date: 9/25/13
 * Time: 11:38 AM
 * To change this template use File | Settings | File Templates.
 */
if (Phpfox::getLib('setting')->getParam('bettermobile.change_sub_domain')) {
    $aParts = explode('.', $sUrl);
    $bAddWWW = false;
    if (preg_match('[www.]', $sUrls)) {
        $bAddWWW = true;
    }
    if ($aParts[0] == 'mobile') {
        if (!$bAddWWW) {
            $sUrls = str_replace('http://', 'http://m.', $sUrls);
        } else {
            $sUrls = str_replace('www.', 'www.m.', $sUrls);
        }

        $sUrls = str_replace('/mobile/', '/', $sUrls);
    }
}
$sCacheId = Phpfox::getLib('cache')->set('video_category_browse');
if (!Phpfox::getLib('cache')->isCached($sCacheId)) {
    $aChangeUrls = array(
        'photo.category',
        'event.category',
        'group.category',
        'marketplace.category',
        'video.category'
    );
    foreach($aChangeUrls as $sChangeUrl) {
        if (strpos($sUrl, $sChangeUrl) > 0) {
            if (Phpfox::getLib('setting')->getParam('bettermobile.change_sub_domain')) {
                $sUrls = str_replace('m.', '', $sUrls);
            } else {
                $sUrls = str_replace('/mobile/', '/', $sUrls);
            }

        }
    }
}

