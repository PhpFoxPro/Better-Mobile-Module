<?php
class Themesupporter_Component_Block_Blog extends Phpfox_Component {
    public function process() {
        if ($aRecords = Phpfox::getService('themesupporter.blog')->get()) {
            $this->template()
                ->assign(array(
                'aRecords' => $aRecords,
                'iShorten' => Phpfox::getParam('themesupporter.block_blog_detail_length')
            ));
            return 'block';
        } else {
            return false;
        }
    }
}