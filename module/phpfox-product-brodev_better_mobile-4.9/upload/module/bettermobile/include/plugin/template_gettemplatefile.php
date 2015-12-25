<?php
/**
 * Created by Phpfox.Pro.
 * User: Huy Nguyen
 * Date: 10/29/13
 * Time: 5:51 PM 
 */

if (Phpfox::isMobile() && $this->_sThemeFolder != "bettermobile") {
    $this->_sThemeFolder = "bettermobile";
}