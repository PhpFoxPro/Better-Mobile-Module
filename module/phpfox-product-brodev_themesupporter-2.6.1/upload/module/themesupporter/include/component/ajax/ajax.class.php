<?php
class Themesupporter_Component_Ajax_Ajax extends Phpfox_Ajax {
    public function deleteImage()
    {

        $iId =(int) $this->get('id');

        if (Phpfox::getService('themesupporter.introduction')->deleteImage($iId)) {

            $sHtml ="<input type=\"file\" name=\"image\">";
            $this->html('.image_value', $sHtml);
        }

    }
}