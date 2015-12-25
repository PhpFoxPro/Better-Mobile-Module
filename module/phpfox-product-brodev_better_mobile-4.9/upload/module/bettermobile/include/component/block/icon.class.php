<?php
class Bettermobile_Component_Block_Icon extends Phpfox_Component {
    public function process() {
        $sIcon = $this->getParam('sIcon');
        list($sNormal, $sRetina) = Phpfox::getService('bettermobile.template')->getSetinaLogo($sIcon);
        $this->template()
            ->assign(array(
            'sNormal' => $sNormal,
            'sRetina' => $sRetina
        ));
    }
}
