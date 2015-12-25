<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vantt
 * Date: 12/12/13
 * Time: 9:02 AM
 * To change this template use File | Settings | File Templates.
 */
if (isset($aParams['class']) && $aParams['class'] == 'profile_user_image' && Phpfox::isMobile()) {
    $aParams['suffix'] = "_200_square";
}