<?php
if (Phpfox::isMobile()) {
    $aDefaultStyle = Phpfox::getService('themesupporter.theme')->getDefaultMobileStyle();
    $this->_sStyleFolder = $aDefaultStyle['style_folder_name'];
    $this->_sThemeFolder = $aDefaultStyle['theme_folder_name'];
}
