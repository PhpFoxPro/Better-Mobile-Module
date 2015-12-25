<?php
class Themesupporter_Component_Controller_Admincp_Introduction_Index extends Phpfox_Component {
    public function process() {
        if ($aOrder = $this->request()->getArray('order'))
        {

            if (Phpfox::getService('themesupporter.introduction.process')->setOrder($aOrder)) {
                $this->url()->send('admincp.themesupporter.introduction', null, Phpfox::getPhrase('themesupporter.introduction_order_update_successfully'));
            }

        }

        if ($iDelete = $this->request()->getInt('delete')) {
            Phpfox::getService('themesupporter.introduction.process')->delete('id = '. $iDelete);
            $this->url()->send('admincp.themesupporter.introduction', null, Phpfox::getPhrase('themesupporter.introduction_delete_successfully'));
        }

        if ($aVal = $this->request()->getArray('val')) {
            $aVal['id'] = 0;
            if (Phpfox::getService('themesupporter.introduction.process')->saveDatabase($aVal)) {
                $this->url()->send('admincp.themesupporter.introduction', null, Phpfox::getPhrase('themesupporter.introduction_add_successfully'));
            }
        }

        $this->template()
            ->setBreadcrumb(Phpfox::getPhrase('themesupporter.introduction'))
            ->setEditor()
            ->setHeader(array(
                'admin.js' => 'module_themesupporter',
                '<script type="text/javascript">$Behavior.loadThemesupporterUrl = function() { $Themesupporter.url(\'' . $this->url()->makeUrl('admincp.themesupporter.introduction') . '\'); }</script>'
            )
        )
            ->assign('sIntroduction', Phpfox::getService('themesupporter.introduction')->display('admincp')->get())
        ;
    }
}