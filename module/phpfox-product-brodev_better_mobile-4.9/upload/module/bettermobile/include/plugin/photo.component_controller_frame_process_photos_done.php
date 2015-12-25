<?php
/**
 * Created by PhpStorm.
 * User: kiemdv
 * Date: 12/18/13
 * Time: 9:38 AM
 */
if (Phpfox::isMobile()) {
    if (!defined('PHPFOX_HTML5_PHOTO_UPLOAD')) {
        echo '<script type="text/javascript">';
    }
    echo 'window.parent.oStatus.hide();';
    if (!defined('PHPFOX_HTML5_PHOTO_UPLOAD')) {
        echo '</script>';
    }
}
