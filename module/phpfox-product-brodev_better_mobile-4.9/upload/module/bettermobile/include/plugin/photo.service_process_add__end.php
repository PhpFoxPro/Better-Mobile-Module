<?php
/**
 * Created by Phpfox.Pro.
 * User: Quang Huy
 * Date: 4/14/13
 * Time: 5:20 PM
 */

if (Phpfox::isMobile() && !$bIsUpdate && Phpfox::getParam('photo.rename_uploaded_photo_names')) {
    Phpfox::getLib('database')->update(Phpfox::getT('photo'), array('title' => $aVals['title'] .'_id_'. $iId), 'photo_id = '. $iId);
}
