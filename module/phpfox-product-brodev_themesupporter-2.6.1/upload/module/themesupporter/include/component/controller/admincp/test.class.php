<?php
/**
 * User: quanmt
 * Date: 10/25/12
 * Time: 7:58 PM
 */

class Themesupporter_Component_Controller_Admincp_Test extends Phpfox_Component
{

    public function process()
    {
        $aBlocks = array(
            'blog',
            'event',
            'introduction',
            'member',
            'pages',
            'video',
        );
        $this->template()
            ->assign('aBlocks', $aBlocks)
            ->setBreadcrumb('Theme Supporter Test')
            ->setTitle('Theme Supporter Test');
    }

}
