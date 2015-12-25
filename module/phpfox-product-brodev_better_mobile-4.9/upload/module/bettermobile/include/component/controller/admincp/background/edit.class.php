<?php
class Bettermobile_Component_Controller_Admincp_Background_Edit extends Phpfox_Component {
    public function process() {
        $iId = $this->request()->getInt('id');
        if ($aVals = $this->request()->getArray('val')) {
            if (Phpfox::getService('bettermobile.background.process')->update($aVals, $aVals['id'])) {
                $this->url()->send('admincp.bettermobile.background', array(), Phpfox::getPhrase('bettermobile.update_successfully'));
            }
        }
        if ($iId == 0) {
            $this->url()->send('admincp.bettermobile.background', array(), Phpfox::getPhrase('bettermobile.item_not_found'));
        }
        $aImage = Phpfox::getService('bettermobile.background')->getForEdit($iId);
        if (empty($aImage)) {
            $this->url()->send('admincp.bettermobile.background', array(), Phpfox::getPhrase('bettermobile.item_not_found'));
        }
        $this->template()
            ->setBreadcrumb(Phpfox::getPhrase('bettermobile.manager_background_images'))
            ->assign('aForms', $aImage);
        return true;
    }
}
