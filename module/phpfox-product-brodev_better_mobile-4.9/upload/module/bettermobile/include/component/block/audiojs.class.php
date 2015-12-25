<?php


defined('PHPFOX') or exit('NO DICE!');


class Bettermobile_Component_Block_Audiojs extends Phpfox_Component
{

    public function process()
    {
        $aSong = $this->getParam('aSong');
        $this->template()->assign(array(
            'aSong' => $aSong
        ));
    }
}
