<?php
// notification
if (isset($bDoNotInsert) || defined('SKIP_NOTIFICATION'))
{
    return true;
}

$aInsert = array(
    'type_id' => $sType,
    'item_id' => $iItemId,
    'user_id' => $iOwnerUserId,
    'owner_user_id' => ($iSenderUserId === null ? Phpfox::getUserId() : $iSenderUserId),
    'time_stamp' => PHPFOX_TIME
);

$this->database()->insert($this->_sTable, $aInsert);

if (Phpfox::isModule('accountapi') && Phpfox::getParam('accountapi.enable_push_notification')) {

    Phpfox::getService('accountapi.push')->notification($iOwnerUserId);

}
$bDoNotInsert = true;