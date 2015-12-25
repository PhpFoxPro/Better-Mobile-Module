<?php
// for friend notification
$iTotal = Phpfox::getService('friend.request')->getUnseenTotal();
if ($iTotal > 0)
{
    Phpfox::getLib('ajax')->call('$(\'#js_total_new_friend_requests\').html(\'' . (int) $iTotal . '\').css({display: \'block\'}).show();');
}
//for mail notification
$iTotal = Phpfox::getService('mail')->getUnseenTotal();
if ($iTotal > 0)
{
    Phpfox::getLib('ajax')->call('$(\'#js_total_new_messages\').html(\'' . (int) $iTotal . '\').css({display: \'block\'}).show();');
}
