<?php
class Themesupporter_Component_Controller_Admincp_Introduction_Edit extends Phpfox_Component {
    public function process() {

        if ($iId = $this->request()->getInt('id')) {
            if (!Phpfox::getService('themesupporter.introduction.process')->isExist('id = '. $iId)) {
                $this->url()->send('admincp.themesupporter.introduction', null, Phpfox::getPhrase('themesupporter.introduction_not_found'));
            }
            if ($aIntroduction = Phpfox::getService('themesupporter.introduction.process')->getForEdit('id = '.$iId)) {

                $this->template()->assign('aForms', $aIntroduction);
            }
        }
        if ($aVal = $this->request()->getArray('val')) {
            if (Phpfox::getService('themesupporter.introduction.process')->saveDatabase($aVal)) {
                $this->url()->send('admincp.themesupporter.introduction', null, Phpfox::getPhrase('themesupporter.update_introduction_successfully'));
            }
        }
        $this->template()
            ->setBreadcrumb(Phpfox::getPhrase('themesupporter.edit_introduction'))
            ->setEditor();
    }
}