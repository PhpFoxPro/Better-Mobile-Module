<?php
/**
 * Created by Phpfox Pro.
 * User: Huy Nguyen
 * Date: 3/17/14
 * Time: 11:02 AM
 */
class Accountapi_Service_Emoticon extends Phpfox_Service
{
    private $_aEmoticons = array();

    public function __construct()
    {
        require_once PHPFOX_DIR . 'module' . PHPFOX_DS . 'accountapi' . PHPFOX_DS . 'include' . PHPFOX_DS . 'library' . PHPFOX_DS . 'simple_html_dom.php';
        $sCacheId = $this->cache()->set('accountapi_emoticon_parse');
        if (!($this->_aEmoticons = $this->cache()->get($sCacheId))) {
            $aRows = $this->database()->select('e.title, e.text, e.image, ep.package_path')
                ->from(Phpfox::getT('emoticon'), 'e')
                ->join(Phpfox::getT('emoticon_package'), 'ep', 'ep.package_path = e.package_path')
                ->execute('getSlaveRows');

            foreach ($aRows as $aRow) {
                $sKey = $aRow['package_path'] . '/' . $aRow['image'];
                $this->_aEmoticons[$sKey] = $aRow;
            }

            $this->cache()->save($sCacheId, $this->_aEmoticons);
        }
    }

    /**
     * find img, check is emoticon,
     * if true change back to text
     * @param $sUrl
     * @return string
     */
    public function processEmoticon($sUrl)
    {
        if ($sUrl == null) return null;
        $oHtml = str_get_html($sUrl);
        foreach ($oHtml->find('img') as $oImage) {
            $sKey = preg_replace('[' . Phpfox::getParam('core.url_emoticon') . ']', '', $oImage->src);
            if (isset($this->_aEmoticons[$sKey])) {
                $oImage->outertext = $this->_aEmoticons[$sKey]['text'] . ' ';
            }
        }

        return (string)$oHtml;
    }
    public function getEmoticonList() {
        return array_flip($this->_aEmoticons);
    }
}