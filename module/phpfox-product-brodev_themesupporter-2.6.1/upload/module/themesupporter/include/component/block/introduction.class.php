<?php
class Themesupporter_Component_Block_Introduction extends Phpfox_Component {

    public function process() {
        $aRecords = Phpfox::getService('themesupporter.introduction.process')->getListNoPage('', 'is_show = 1');
        $this->template()
            ->assign(array(
            'aRecords' => $aRecords
        ));
    }

}