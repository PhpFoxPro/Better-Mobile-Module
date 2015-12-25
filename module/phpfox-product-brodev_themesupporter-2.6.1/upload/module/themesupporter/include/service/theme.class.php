<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Hz
 * Date: 11/15/12
 * Time: 2:36 PM
 * To change this template use File | Settings | File Templates.
 */
class Themesupporter_Service_Theme extends Phpfox_Service
{
  public function __construct() {
  }

  /*
  * function get array themes
  */
  public function getThemes() {
    $this->_sTable = Phpfox::getT('theme');
    return $this->database()->select('t.*')
        ->from($this->_sTable, 't')
        ->where('t.is_active = 1')
        ->execute('getrows');
  }

  /*
  *  function get styles
  */
  public function getStyles() {
    $this->_sTable = Phpfox::getT('theme_style');
    return $this->database()->select('s.*')
        ->from($this->_sTable, 's')
        ->where('s.is_active = 1')
        ->execute('getrows');
  }


  /*
  *  function save default theme mobile
  */
  public function saveMobileTheme($aVals) {
    $this->database()->update(Phpfox::getT('setting'), array(
          'value_actual' => $aVals['mobile_style']
        ), 'var_name = \'default_mobile_style\' AND module_id = "themesupporter"');

    // Clear the setting cache
    $this->cache()->remove();

    return true;
  }

    /**
     * Get default mobile theme
     * @return mixed
     */
    public function getDefaultMobileStyle()
    {
        $sCacheId = $this->cache()->set('defaultMobileStyle');

        $aDefaultStyle = $this->cache()->get($sCacheId);
        if ($aDefaultStyle === false) {

            $iDefaultStyleId = Phpfox::getParam('themesupporter.default_mobile_style');
            if ($iDefaultStyleId) {
                $aDefaultStyle = $this->getStyle($iDefaultStyleId);
            }
            if (!isset($aDefaultStyle) || !$aDefaultStyle) {
                $aTheme = Phpfox::getService('theme')->getTheme('default', true);
                $aStyles = Phpfox::getService('theme.style')->get($aTheme['theme_id']);

                $aDefaultStyle = $this->getStyle($aStyles[0]['style_id']);
            }
            $this->cache()->save($sCacheId, $aDefaultStyle);
        }

        return $aDefaultStyle;
    }

    /**
     * Get style by id
     * @param $iId
     * @return mixed
     */
    protected function getStyle($iId)
    {
        $aTheme = Phpfox::getLib('database')->select('s.style_id, s.parent_id AS style_parent_id, s.folder AS style_folder_name, t.folder AS theme_folder_name, t.parent_id AS theme_parent_id, t.total_column, s.l_width, s.c_width, s.r_width')
            ->from(Phpfox::getT('theme_style'), 's')
            ->join(Phpfox::getT('theme'), 't', 't.theme_id = s.theme_id AND t.is_active')
            ->where('s.style_id = ' . $iId)
            ->execute('getRow');

        return $aTheme;
    }
}
