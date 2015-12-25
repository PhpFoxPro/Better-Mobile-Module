<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Huy Nguyen
 * Date: 3/12/13
 * Time: 5:29 PM
 * To change this template use File | Settings | File Templates.
 */
if (empty($_REQUEST['facebook-process-login']))
{
    if (Phpfox::getLib('setting')->getParam('bettermobile.change_sub_domain')) {
        $sHost = $_SERVER['HTTP_HOST'];
        if (preg_match('[m\.]', $sHost)) {
            $_REQUEST['js_mobile_version'] = true;
        }
    }
    if (Phpfox::isMobile() && Phpfox::isModule('comment')) {
        Phpfox::getLib('setting')->setParam('comment.load_delayed_comments_items', false);
    }
}
