<?php
class Themesupporter_Component_Block_Member extends Phpfox_Component {
    public function process() {
        if ($aRecords = Phpfox::getService('themesupporter.member')->get()) {
            $this->template()
                ->assign(array(
                'aRecords' => $aRecords
            ));
            return 'block';
        } else {
            return false;
        }
    }
}