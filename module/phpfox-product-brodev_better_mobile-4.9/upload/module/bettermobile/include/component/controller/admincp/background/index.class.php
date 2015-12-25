<?php
class Bettermobile_Component_Controller_Admincp_Background_Index extends Phpfox_Component {
    public function process() {
        //bread crumb
        $this->template()
            ->setBreadcrumb(Phpfox::getPhrase('bettermobile.manager_background_images'));
        //add new
        if ($aVals = $this->request()->getArray('val')) {
            if (Phpfox::getService('bettermobile.background.process')->add($aVals)) {
                $this->url()->send('admincp.bettermobile.background', array(), Phpfox::getPhrase('bettermobile.add_new_image_successfully'));
            }
        }
        // delete
        if ($iDelete = $this->request()->get('delete')) {
            if (Phpfox::getService('bettermobile.background.process')->delete($iDelete)) {
                $this->url()->send('admincp.bettermobile.background', array(), Phpfox::getPhrase('bettermobile.delete_image_successfully'));
            }
        }
    }
}

