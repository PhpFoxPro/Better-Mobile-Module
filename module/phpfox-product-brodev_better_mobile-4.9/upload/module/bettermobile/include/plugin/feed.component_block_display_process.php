<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ADMIN
 * Date: 12/12/12
 * Time: 9:18 AM
 * To change this template use File | Settings | File Templates.
 */

$bDefault = false;

$this->template()->assign(array(
    'bDefault' => $bDefault
));
if (Phpfox::isMobile()) {
    if (!empty($aRows)) {
        foreach($aRows as $iKey => $aRow) {
            if (isset($aRow['feed_image_onclick']) && $aRow['feed_image_onclick'] != "") {
                $aRows[$iKey]['feed_image_onclick'] = preg_replace('[music.playInFeed]', 'bettermobile.playInFeed', $aRows[$iKey]['feed_image_onclick']);
            }
        }
    }
}
