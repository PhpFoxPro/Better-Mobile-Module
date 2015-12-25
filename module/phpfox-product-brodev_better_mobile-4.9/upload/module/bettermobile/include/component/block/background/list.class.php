<?php
class Bettermobile_Component_Block_Background_List extends Phpfox_Component {
    public function process() {
        $aImages = Phpfox::getService('bettermobile.background')->getList();
        $this->template()
            ->assign(array(
            'aImages' => $aImages
        ));
    }
}
