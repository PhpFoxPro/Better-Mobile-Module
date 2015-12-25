<?php
/**
 * [PHPFOX_HEADER]
 */

defined('PHPFOX') or exit('NO DICE!');


class Bettermobile_Component_Block_Profilefooter extends Phpfox_Component
{
    /**
     * Class process method wnich is used to execute this component.
     */
    public function process()
    {
        if (!defined('PHPFOX_IS_USER_PROFILE'))
        {
            return false;
        }
        $mUser = $this->request()->get('req1');
        $aUser = Phpfox::getService('user')->get($mUser, false);

        $aUserInfo = array(
            'title' => $aUser['full_name'],
            'path' => 'core.url_user',
            'file' => $aUser['user_image'],
            'suffix' => '_75_square',
            'max_width' => 75,
            'max_height' => 75,
            'no_default' => (Phpfox::getUserId() == $aUser['user_id'] ? false : true),
            'thickbox' => true,
            'class' => 'profile_user_image'
        );

        $sImage = Phpfox::getLib('image.helper')->display(array_merge(array('user' => Phpfox::getService('user')->getUserFields(true, $aUser)), $aUserInfo));
        $sType = Phpfox::getLib('request')->get('req2');
        if ($sType != 'wall' && $sType != 'info' && $sType != 'photo') {
            $sType = Phpfox::getParam('profile.profile_default_landing_page');
        }

        if ( $sType == 'wall') {
            $bType = '1';
        } elseif ($sType == 'info') {
            $bType = '2';
        } elseif ($sType == 'photo') {
            $bType = '3';
        }
        $this->template()->assign(array(
                'sProfileImage' => $sImage,
                'bIsInfo' => $bType,
                'bCanPoke' => Phpfox::isModule('poke') && Phpfox::getService('poke')->canSendPoke($aUser['user_id'])
            )
        );
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('profile.component_block_mobile_clean')) ? eval($sPlugin) : false);
    }
}

?>