<?php
if (Phpfox::isMobile()) {
    $aDefaultStyle = Phpfox::getService('themesupporter.theme')->getDefaultMobileStyle();
    if ($aDefaultStyle['style_parent_id'] > 0)
    {
        $aStyleExtend = Phpfox::getLib('database')->select('folder AS parent_style_folder')
            ->from(Phpfox::getT('theme_style'))
            ->where('style_id = ' . $aDefaultStyle['style_parent_id'])
            ->execute('getRow');

        if (isset($aStyleExtend['parent_style_folder']))
        {
            $aDefaultStyle['parent_style_folder'] = $aStyleExtend['parent_style_folder'];
        }
    }
    $this->setStyle($aDefaultStyle);
}