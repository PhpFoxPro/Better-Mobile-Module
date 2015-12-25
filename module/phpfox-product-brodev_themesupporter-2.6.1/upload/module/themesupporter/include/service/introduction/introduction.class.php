<?php
class Themesupporter_Service_Introduction_Introduction extends Phpfox_Service {
    private $_sDisplay = 'admincp';

    public function __construct() {
        $this->_sTable = Phpfox::getT('brodev_block_introduction');
    }

    /**
     * get type of display
     * @param $sDislpay
     * @return Themesupporter_Service_Introduction_Introduction
     */
    public function display( $sDisplay ) {
        $this->_sDisplay = $sDisplay;
        return $this;
    }
    public function get() {

        if ($this->_sDisplay == "admincp") {

            return $this->_get();

        }
    }
    /**
     * display record
     * @return string
     */
    private function _get() {
        $sOutput = "";
        if ($this->_sDisplay =='admincp') {
            $sOutput = "<ul>";
        }

        $aRecords = Phpfox::getService('themesupporter.introduction.process')->getListNoPage();

        foreach ($aRecords as $aItem) {

            if ($this->_sDisplay =='admincp') {

                $sOutput .= '<li><img src="' . Phpfox::getLib('template')->getStyle('image', 'misc/draggable.png') . '" alt="" /> <input type="hidden" name="order[' . $aItem['id'] . ']" value="' . $aItem['ordering'] . '" class="js_mp_order" /><a href="#?id=' . $aItem['id'] . '" class="js_drop_down">' . Phpfox::getLib('locale')->convert($aItem['title']) . '</a>' .  '</li>' . "\n";

            }
        }
        if ($this->_sDisplay == "admincp") {
            $sOutput .="</ul>";
        }

        return  $sOutput;
    }

    /**
     * delele all image of introduction
     * @param int $iId
     * @return bool
     */
    public function deleteImage($iId = 0) {
        if ($iId == 0) {
            return false;
        }
        $aIntroduction = Phpfox::getService('themesupporter.introduction.process')->getForEdit('id = '. $iId);
        $sFileName = $aIntroduction['image'];
        $aPhotoSizes = Phpfox::getService('themesupporter.introduction.process')->aPhotoSizes();
        foreach ($aPhotoSizes as $iSize) {
            unlink(Phpfox::getParam('themesupporter.image_folder') . sprintf($sFileName, '_' . $iSize));
            unlink(Phpfox::getParam('themesupporter.image_folder') . sprintf($sFileName, '_' . $iSize. '_square'));
        }
        unlink(Phpfox::getParam('themesupporter.image_folder') . sprintf($sFileName, ''));
        $aVal['id'] = $iId;
        $aVal['image'] = "";
        Phpfox::getService('themesupporter.introduction.process')->saveDatabase($aVal);
        return true;
    }
}