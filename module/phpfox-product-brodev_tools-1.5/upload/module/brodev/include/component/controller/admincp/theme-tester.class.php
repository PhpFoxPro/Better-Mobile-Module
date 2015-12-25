<?php
/**
 * User: quanmt
 * Date: 10/20/12
 * Time: 3:02 PM
 */

class Brodev_Component_Controller_Admincp_Theme_Tester extends Phpfox_Component
{

    public function process()
    {

        $aSections = Phpfox::getService('brodev.theme')->getTestList();

        $this->template()
            ->setBreadcrumb('Theme Tester')
            ->assign(array(
                'aSections' => $aSections,
            ));

    }

}
