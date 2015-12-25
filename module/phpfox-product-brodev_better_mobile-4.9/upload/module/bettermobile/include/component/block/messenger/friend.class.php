<?php
/**
 * Created by Phpfox Pro.
 * User: Huy Nguyen
 * Date: 3/8/14
 * Time: 4:59 PM
 */
class Bettermobile_Component_Block_Messenger_Friend extends Phpfox_Component {
    public function process() {
        if (!Phpfox::isModule('messenger') || !Phpfox::isUser()) {
            return false;
        }
        $aUser = Phpfox::getUserBy();
        $this->template()
            ->assign(array(
            'aOwner' => $aUser
        ));
    }
}