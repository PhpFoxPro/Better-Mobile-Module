<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Hz
 * Date: 11/15/12
 * Time: 2:32 PM
 * To change this template use File | Settings | File Templates.
 */
class Themesupporter_Component_Controller_Admincp_Mobiletheme_Index extends Phpfox_Component
{
    public function process()
    {
        $aThemes = Phpfox::getService('themesupporter.theme')->getThemes();
        $aStyles = Phpfox::getService('themesupporter.theme')->getStyles();

        $aDefaultStyle = Phpfox::getService('themesupporter.theme')
            ->getDefaultMobileStyle();

        $this->template()->setBreadcrumb(Phpfox::getPhrase('themesupporter.mobile_theme'))
            ->assign(array(
                'aThemes' => $aThemes,
                'aStyles' => $aStyles,
                'aDefaultStyle' => $aDefaultStyle
            )
        );

        if ($aVals = $this->request()->getArray('val')) {
            if (Phpfox::getService('themesupporter.theme')->saveMobileTheme($aVals)) {
                $this->url()->send(
                    'admincp.themesupporter.mobiletheme', array(), 'Updated!'
                );
            }
        }
    }
}
