<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ADMIN
 * Date: 12/12/12
 * Time: 11:36 AM
 * To change this template use File | Settings | File Templates.
 */
$aFeeds[0]['isDefault'] = false;
if (Phpfox::isMobile() && Phpfox::getLib('setting')->isParam('tag.enable_hashtag_support') && Phpfox::isModule('tag') && Phpfox::getLib('setting')->getParam('tag.enable_hashtag_support')) {
    $sReq1 = Phpfox::getLib('request')->get('req1');
    if ($sReq1 == "hashtag") {
        $sHashTagSearch = Phpfox::getLib('request')->get('req2');
        Phpfox::getLib('request')->set('hashtagsearch', $sHashTagSearch);
        Phpfox::getLib('request')->set('hashtagpopup', true);
    }
}
