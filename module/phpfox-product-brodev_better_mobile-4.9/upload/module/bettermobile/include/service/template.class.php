<?php
/**
 * User: quanmt
 * Date: 12/6/12
 * Time: 11:20 AM
 */

class Bettermobile_Service_Template extends Phpfox_Service
{
    protected $template = 'template';

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function isTemplate2()
    {
        if ($this->template == 'template2') {
            return true;
        }
        return false;
    }
    public function isDefault()
    {
        $bDefault = Phpfox::getParam('bDefault');
        return $bDefault;
    }

    /**
     * get better mobile logo
     * @return string
     */
    public function getStyleLogo() {
        $aTheme = Phpfox::getLib('database')->select('tsl.logo_id, tsl.logo, tsl.file_ext')
            ->from(Phpfox::getT('theme_style_logo'), 'tsl')
            ->leftJoin(Phpfox::getT('theme_style'), 'ts', 'ts.style_id = tsl.style_id')
            ->where('ts.folder = \'bettermobile\'')
            ->execute('getRow');
        return (isset($aTheme['file_ext']) && file_exists(PHPFOX_DIR_FILE . 'static' . PHPFOX_DS . md5('bettermobile' . 'bettermobile') . '.' . $aTheme['file_ext']) ? Phpfox::getParam('core.url_file') . 'static/' . md5('bettermobile' . 'bettermobile') . '.' . $aTheme['file_ext'] . '?id=' . $aTheme['logo_id'] : '');
    }

    /**
     * get link retina link
     * @param $sLink
     * @return array
     */
    public function getSetinaLogo($sLink) {
        $aSplit = preg_split('[\/]', $sLink);
        $sIcon = $aSplit[count($aSplit) - 1];
        $sIconRetina = substr($sIcon, 0, strlen($sIcon) - 4) . '@2x' . substr($sIcon, -4);
        $sLinkRetina = PHPFOX_DIR_THEME . 'frontend'. PHPFOX_DS . 'bettermobile' . PHPFOX_DS .'style'. PHPFOX_DS . 'bettermobile'. PHPFOX_DS. 'image' . PHPFOX_DS . 'mobile' . PHPFOX_DS . $sIconRetina;
        if (file_exists($sLinkRetina)) {
            $sRetina = substr($sLink, 0, strlen($sLink) - 4) . '@2x' . substr($sLink, -4);
        } else {
            $sRetina = $sLink;
        }

        return array($sLink, $sRetina);
    }
    // get block not phpfox block
    public function getBlockOther(){
        $sCacheId = Phpfox::getLib('cache')->set('brodev_bettermobile_block_other');
        if (!$aBlocks = Phpfox::getLib('cache')->get($sCacheId)) {
            $aBlocks = Phpfox::getLib('database')->select('*')
                ->from(Phpfox::getT('block'))
                ->where('product_id != "phpfox"')
                ->execute('getRows');
            Phpfox::getLib('cache')->save($sCacheId, $aBlocks);
        }
        foreach($aBlocks as $iKey => $aBlock){
            $aBlocks[$iKey] = $aBlock['module_id'] . '.' . $aBlock['component'];
        }
        return $aBlocks;
    }

    /**
     * get block feed display of phpfox
     * @return mixed
     */
    public function getFeedDislay() {
        $aFeedBlock = Phpfox::getLib('database')->select('*')
            ->from(Phpfox::getT('block'))
            ->where('m_connection = "profile.index" AND module_id = "feed" AND component ="display" AND product_id = "phpfox" AND is_active = 0')
            ->execute('getRow');
       return $aFeedBlock;
    }
    // check photo share is existed
    public function isPhotoShare()
    {
        $aLinks = $this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('feed_share'), 'fs')
            ->where('module_id = "photo"')
            ->execute('getField');
        if($aLinks > 0){
            return true;
        }
    }
    public function processMenu($aMenus) {
        $bExist = false;
        foreach ($aMenus as $iKey => $aMenu) {
            if ($aMenu['url'] == 'photo.add') {
                if (!$bExist) {
                    $bExist = true;
                } else {
                    unset($aMenus[$iKey]);
                }
            }
        }
        return $aMenus;
    }

    public function removeCustomTheme($aThemes) {
        if (empty($aThemes)) {
            return $aThemes;
        }
        foreach($aThemes as $iKey => $aTheme) {
            if ($aTheme['theme_folder'] == 'bettermobile') {
                unset($aThemes[$iKey]);
            }
        }
        return $aThemes;
    }
}
