<?php
/**
 * User: quanmt
 * Date: 12/21/11
 * Time: 1:50 AM
 */

class Brodev_Component_Controller_Admincp_Index extends Phpfox_Component
{

    public function process()
    {
        if ($aValues = $this->request()->getArray('val')) {
            if ($aValues['type'] == 'product') {
                Phpfox::getService('brodev.product')->exportToFile($aValues['product_id']);
            } else {
                Phpfox::getService('brodev.theme')->exportToFile($aValues['theme_id']);
            }
            $this->url()->send('admincp.brodev', null, 'Exported');
        }

        $aProductsList = Phpfox::getService('brodev.product')->getList();
        $aThemesList = Phpfox::getService('brodev.theme')->getList();

        $this->template()
            ->setBreadcrumb('Brodev Tools')
            ->assign(array(
                'aProductsList' => $aProductsList,
                'aThemesList' => $aThemesList,
            ));
    }

}
