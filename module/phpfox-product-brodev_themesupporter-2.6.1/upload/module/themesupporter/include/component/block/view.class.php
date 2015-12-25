<?php
class Themesupporter_Component_Block_View extends Phpfox_Component {
    public function process() {
        $aRecord = Phpfox::getService('themesupporter.video')->getRandomVideo();

        $this->template()
            ->assign(array(
            'aRecord' => $aRecord
        ));
        return 'block';
    }
}