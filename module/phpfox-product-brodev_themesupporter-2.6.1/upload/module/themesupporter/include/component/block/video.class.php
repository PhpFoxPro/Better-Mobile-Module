<?php
class Themesupporter_Component_Block_Video extends Phpfox_Component {
    public function process() {
        if ($aRecords = Phpfox::getService('themesupporter.video')->get()) {
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