<?php
class Themesupporter_Component_Block_Event extends Phpfox_Component {
    public function process() {
        if ($aRecords = Phpfox::getService('themesupporter.event')->get()) {
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