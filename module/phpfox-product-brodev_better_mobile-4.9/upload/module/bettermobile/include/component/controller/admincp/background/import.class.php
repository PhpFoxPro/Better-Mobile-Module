<?php
class Bettermobile_Component_Controller_Admincp_Background_Import extends Phpfox_Component {
    public function process() {
        $sLink = "admincp.bettermobile.background.import";
        if ($this->request()->get('import') ) {
            if (Phpfox::getService('bettermobile.background')->importSample()) {
                $this->url()->send($sLink,array(),Phpfox::getPhrase('bettermobile.import_sample_background_successfully'));
            }

        }
        $this->template()
            ->setBreadcrumb(Phpfox::getPhrase('bettermobile.import_background_sample'));
    }
}
